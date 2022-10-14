<?php
/**
 * Abstract Request
 */

namespace Armezit\Kyc\Jibit\Common;

use Armezit\Kyc\Jibit\Cache;
use Armezit\Kyc\Jibit\Exception\InvalidResponseException;
use Armezit\Kyc\Jibit\Exception\RuntimeException;
use Exception;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface as PsrRequestInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use GuzzleHttp\Psr7\Request;

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
    protected $endpoint = 'https://napi.jibit.ir/ide';

    /**
     * The request client.
     *
     * @var ClientInterface
     */
    protected $httpClient;

    /**
     * The HTTP request object.
     *
     * @var PsrRequestInterface
     */
    protected $httpRequest;

    /**
     * An associated ResponseInterface.
     *
     * @var ResponseInterface
     */
    protected $response;

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
    abstract protected function createResponse(array $data);

    /**
     * Create a new Request
     *
     * @param ClientInterface $httpClient  A (psr-18 compatible) HTTP client to make API calls with
     * @param PsrRequestInterface $httpRequest A (psr-7 compatible) HTTP request object
     */
    public function __construct(ClientInterface $httpClient, PsrRequestInterface $httpRequest)
    {
        $this->httpClient = $httpClient;
        $this->httpRequest = $httpRequest;
        $this->initialize();
    }

    /**
     * Initialize the object with parameters.
     *
     * If any unknown parameters passed, they will be ignored.
     *
     * @param array $parameters An associative array of parameters
     *
     * @return $this
     * @throws RuntimeException
     */
    public function initialize(array $parameters = array()): static
    {
        if (null !== $this->response) {
            throw new RuntimeException('Request cannot be modified after it has been sent!');
        }

        $this->parameters = new ParameterBag;

        Helper::initialize($this, $parameters);

        return $this;
    }

    /**
     * Set a single parameter
     *
     * @param string $key The parameter key
     * @param mixed $value The value to set
     * @return $this
     * @throws RuntimeException if a request parameter is modified after the request has been sent.
     */
    protected function setParameter($key, $value): static
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
     * @return Cache
     */
    public function getCache(): Cache
    {
        return $this->getParameter('cache');
    }

    /**
     * @param Cache $cache
     * @return static
     */
    public function setCache(Cache $cache): static
    {
        return $this->setParameter('cache', $cache);
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
     * @param bool $isForce
     * @return string
     * @throws InvalidResponseException
     */
    private function generateToken(bool $isForce = false): string
    {
        $cache = $this->getCache();

        $cache->eraseExpired();

        if ($isForce === false && $cache->isCached('accessToken')) {
            $this->setAccessToken($cache->retrieve('accessToken'));
            return 'ok';
        }

        if ($cache->isCached('refreshToken')) {
            $refreshToken = $this->refreshTokens();
            if ($refreshToken !== 'ok') {
                return $this->generateNewToken();
            }
        }

        return $this->generateNewToken();
    }

    /**
     * Refresh access token
     *
     * @return string
     * @throws InvalidResponseException
     */
    private function refreshTokens(): string
    {
        $cache = $this->getCache();

        try {
            $this->httpRequest = new Request(
                'POST',
                $this->getEndpoint() . '/v1/tokens/refresh',
                [
                    'json' => [
                        'accessToken' => str_replace('Bearer ', '', $cache->retrieve('accessToken')),
                        'refreshToken' => $cache->retrieve('refreshToken'),
                    ],
                ],
            );

            $httpResponse = $this->httpClient->sendRequest($this->httpRequest);
            $json = $httpResponse->getBody()->getContents();
            $result = !empty($json) ? json_decode($json, true) : [];

            if (empty($result['accessToken'])) {
                throw new \RuntimeException('Err in refresh token.');
            }

            $cache->store('accessToken', 'Bearer ' . $result['accessToken'], 24 * 60 * 60 - 60);
            $cache->store('refreshToken', $result['refreshToken'], 48 * 60 * 60 - 60);
            $this->setAccessToken('Bearer ' . $result['accessToken']);
            $this->setRefreshToken($result['refreshToken']);
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
     * @throws InvalidResponseException
     */
    private function generateNewToken(): string
    {
        try {
            $this->httpRequest = new Request(
                'POST',
                $this->getEndpoint() . '/v1/tokens/generate',
                [
                    'json' => [
                        'apiKey' => $this->getParameter('apiKey'),
                        'secretKey' => $this->getParameter('secretKey'),
                    ],
                ],
            );
            $httpResponse = $this->httpClient->sendRequest($this->httpRequest);
            $json = $httpResponse->getBody()->getContents();
            $result = !empty($json) ? json_decode($json, true) : [];

            if (empty($result['accessToken'])) {
                throw new \RuntimeException('Err in generate new token.');
            }

            $cache = $this->getCache();
            $cache->store('accessToken', 'Bearer ' . $result['accessToken'], 24 * 60 * 60 - 60);
            $cache->store('refreshToken', $result['refreshToken'], 48 * 60 * 60 - 60);
            $this->setAccessToken('Bearer ' . $result['accessToken']);
            $this->setRefreshToken($result['refreshToken']);
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
    public function sendData($data): ResponseInterface
    {
        $this->generateToken();
        $accessToken = $this->getAccessToken();

        $requestOptions = [
            'headers' => [
                'Accept' => 'application/json',
                'Content-type' => 'application/json',
                'Authorization' => $accessToken,
            ]
        ];

        if ($this->getHttpMethod() === 'GET') {
            $requestOptions['query'] = $data;
        } else {
            $requestOptions['json'] = $data;
        }

        try {
            $this->httpRequest = new Request(
                $this->getHttpMethod(),
                $this->createUri($this->getEndpoint()),
                $requestOptions,
            );

            $httpResponse = $this->httpClient->sendRequest($this->httpRequest);
            $json = $httpResponse->getBody()->getContents();
            $result = !empty($json) ? json_decode($json, true) : [];
            $result['httpStatus'] = $httpResponse->getStatusCode();

            if (isset($result['errors']) && $result['errors'][0]['code'] === 'security.auth_required') {
                $this->generateToken(true);
                $retries = !isset($data['retries']) ? 0 : (int)$data['retries'];
                if ($retries <= 0) {
                    $data['retries'] = 1;
                    return $this->sendData($data);
                }
                $result['httpStatus'] = 401;
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
