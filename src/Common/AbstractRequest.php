<?php

namespace Armezit\Kyc\Jibit\Common;

use Armezit\Kyc\Jibit\Exception\InvalidResponseException;
use Armezit\Kyc\Jibit\Exception\RuntimeException;
use Exception;
use Http\Message\RequestFactory;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * AbstractRequest
 */
abstract class AbstractRequest implements RequestInterface
{
    use ParametersTrait {
        ParametersTrait::setParameter as traitSetParameter;
    }

    /**
     * Endpoint URL
     *
     * @var string URL
     */
    protected string $endpoint = 'https://napi.jibit.ir/ide';

    /**
     * An associated ResponseInterface.
     *
     * @var ResponseInterface|null
     */
    protected ?ResponseInterface $response = null;

    /**
     * @return string
     */
    abstract protected function getHttpMethod(): string;

    /**
     * @param string $endpoint
     * @return string
     */
    abstract protected function createUri(string $endpoint): string;

    /**
     * @param array $data
     * @return AbstractResponse
     */
    abstract protected function createResponse(array $data): AbstractResponse;

    /**
     * Create a new Request
     *
     * @param ClientInterface        $httpClient     HTTP client to make API calls.
     * @param RequestFactory         $requestFactory HTTP request factory.
     * @param CacheItemPoolInterface $cache
     */
    public function __construct(
        protected ClientInterface $httpClient,
        protected RequestFactory $requestFactory,
        protected CacheItemPoolInterface $cache,
    ) {
        $this->initialize();
    }

    /**
     * Initialize the object with parameters.
     *
     * If any unknown parameters passed, they will be ignored.
     *
     * @param array $parameters An associative array of parameters.
     *
     * @return $this
     * @throws RuntimeException
     */
    public function initialize(array $parameters = array()): static
    {
        if (null !== $this->response) {
            throw new RuntimeException('Request cannot be modified after it has been sent!');
        }

        $this->parameters = new ParameterBag();

        Helper::initialize($this, $parameters);

        return $this;
    }

    /**
     * Set a single parameter
     *
     * @param string $key   The parameter key.
     * @param mixed  $value The value to set.
     * @return $this
     * @throws RuntimeException Throws if a request parameter is modified after the request has been sent.
     */
    protected function setParameter(string $key, mixed $value): static
    {
        if (null !== $this->response) {
            throw new RuntimeException('Request cannot be modified after it has been sent!');
        }

        return $this->traitSetParameter($key, $value);
    }

    /**
     * Send the request
     *
     * @return ResponseInterface
     * @throws InvalidResponseException
     * @throws ClientExceptionInterface
     */
    public function send(): ResponseInterface
    {
        $data = $this->getData();

        return $this->sendData($data);
    }

    /**
     * Get the associated Response.
     *
     * @return ResponseInterface
     * @throws RuntimeException
     */
    public function getResponse(): ResponseInterface
    {
        if (null === $this->response) {
            throw new RuntimeException('You must call send() before accessing the Response!');
        }

        return $this->response;
    }

    /**
     * @return string
     */
    public function getApiKey(): string
    {
        return $this->getParameter('apiKey');
    }

    /**
     * @return string
     */
    public function getSecretKey(): string
    {
        return $this->getParameter('secretKey');
    }

    /**
     * @return string
     */
    public function getAccessToken(): string
    {
        return $this->getParameter('accessToken');
    }

    /**
     * @return string
     */
    public function getRefreshToken(): string
    {
        return $this->getParameter('refreshToken');
    }

    /**
     * @param string $value
     * @return static
     */
    public function setApiKey(string $value): static
    {
        return $this->setParameter('apiKey', $value);
    }

    /**
     * @param string $value
     * @return static
     */
    public function setSecretKey(string $value): static
    {
        return $this->setParameter('secretKey', $value);
    }

    /**
     * @param string $accessToken
     * @return static
     */
    public function setAccessToken(string $accessToken): static
    {
        return $this->setParameter('accessToken', $accessToken);
    }

    /**
     * @param string $refreshToken
     * @return static
     */
    public function setRefreshToken(string $refreshToken): static
    {
        return $this->setParameter('refreshToken', $refreshToken);
    }

    /**
     * @return string
     */
    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    /**
     * @param boolean $isForce
     * @return string
     * @throws InvalidResponseException
     * @throws ClientExceptionInterface
     */
    private function generateToken(bool $isForce = false): string
    {
        $accessToken = $this->cache->getItem('accessToken');
        if ($isForce === false && $accessToken->isHit()) {
            $this->setAccessToken($accessToken->get());
            return 'ok';
        }

        $refreshToken = $this->cache->getItem('refreshToken');
        if ($refreshToken->isHit()) {
            $refreshToken = $this->refreshTokens();
            if ($refreshToken !== 'ok') {
                return $this->generateNewToken();
            }
        }

        return $this->generateNewToken();
    }

    /**
     * @param string $accessToken
     * @param string $refreshToken
     * @return $this
     * @throws \Psr\Cache\InvalidArgumentException
     */
    private function storeTokens(string $accessToken, string $refreshToken): static
    {
        $item = $this->cache
            ->getItem('accessToken')
            ->set($accessToken)
            ->expiresAfter(24 * 60 * 60 - 60);
        $this->cache->saveDeferred($item);

        $item = $this->cache
            ->getItem('refreshToken')
            ->set($refreshToken)
            ->expiresAfter(48 * 60 * 60 - 60);
        $this->cache->saveDeferred($item);

        $this->setAccessToken($accessToken);
        $this->setRefreshToken($refreshToken);

        return $this;
    }

    /**
     * Refresh access token
     *
     * @return string
     * @throws RuntimeException
     * @throws InvalidResponseException
     */
    private function refreshTokens(): string
    {
        try {
            $request = $this->requestFactory->createRequest(
                'POST',
                $this->getEndpoint() . '/v1/tokens/refresh',
                [
                    'Accept' => 'application/json',
                    'Content-type' => 'application/json',
                ],
                json_encode([
                    'accessToken' => str_replace('Bearer ', '', $this->cache->getItem('accessToken')->get()),
                    'refreshToken' => $this->cache->getItem('refreshToken')->get(),
                ], JSON_THROW_ON_ERROR)
            );
            $httpResponse = $this->httpClient->sendRequest($request);

            $json = $httpResponse->getBody()->getContents();
            $result = !empty($json) ? json_decode($json, true) : [];

            if (empty($result['accessToken'])) {
                throw new RuntimeException('Err in refresh token.');
            }

            $this->storeTokens($result['accessToken'], $result['refreshToken']);

            return 'ok';
        } catch (Exception $e) {
            throw new InvalidResponseException(
                'Error communicating with provider: ' . $e->getMessage(),
                $e->getCode()
            );
        }
    }

    /**
     * generate new access token
     *
     * @return string
     * @throws RuntimeException
     * @throws InvalidResponseException
     */
    private function generateNewToken(): string
    {
        try {
            $request = $this->requestFactory->createRequest(
                'POST',
                $this->getEndpoint() . '/v1/tokens/generate',
                [
                    'Accept' => 'application/json',
                    'Content-type' => 'application/json',
                ],
                json_encode([
                    'apiKey' => $this->getParameter('apiKey'),
                    'secretKey' => $this->getParameter('secretKey'),
                ], JSON_THROW_ON_ERROR)
            );
            $httpResponse = $this->httpClient->sendRequest($request);

            $json = $httpResponse->getBody()->getContents();
            $result = !empty($json) ? json_decode($json, true) : [];

            if (empty($result['accessToken'])) {
                throw new RuntimeException('Err in generate new token.');
            }

            $this->storeTokens($result['accessToken'], $result['refreshToken']);

            return 'ok';
        } catch (Exception $e) {
            throw new InvalidResponseException(
                'Error communicating with provider: ' . $e->getMessage(),
                $e->getCode()
            );
        }
    }

    /**
     * Send the request with specified data
     *
     * @param mixed $data The data to send.
     * @return ResponseInterface
     * @throws InvalidResponseException
     */
    public function sendData(mixed $data): ResponseInterface
    {
        $this->generateToken();

        try {
            $request = $this->requestFactory->createRequest(
                $this->getHttpMethod(),
                $this->createUri($this->getEndpoint()),
                [
                    'Accept' => 'application/json',
                    'Content-type' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->getAccessToken(),
                ],
                json_encode($data, JSON_THROW_ON_ERROR)
            );
            $httpResponse = $this->httpClient->sendRequest($request);

            $json = $httpResponse->getBody()->getContents();
            $result = !empty($json) ? json_decode($json, true) : [];

            $result['httpStatus'] = $httpResponse->getStatusCode();

            if (isset($result['errors'])) {
                if ($result['errors'][0]['code'] === 'security.auth_required') {
                    $this->generateToken(true);
                    $retries = !isset($data['retries']) ? 0 : (int)$data['retries'];
                    if ($retries <= 0) {
                        $data['retries'] = 1;
                        return $this->sendData($data);
                    }
                    $result['httpStatus'] = 401;
                }
            }

            return $this->response = $this->createResponse($result);
        } catch (Exception $e) {
            throw new InvalidResponseException(
                'Error communicating with provider: ' . $e->getMessage(),
                $e->getCode()
            );
        }
    }
}
