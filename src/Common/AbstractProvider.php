<?php

namespace Armezit\Kyc\Jibit\Common;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface as PsrRequestInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

abstract class AbstractProvider implements ProviderInterface
{
    use ParametersTrait {
        ParametersTrait::setParameter as traitSetParameter;
        ParametersTrait::getParameter as traitGetParameter;
    }

    /**
     * @var ClientInterface
     */
    protected $httpClient;

    /**
     * @var PsrRequestInterface
     */
    protected $httpRequest;

    /**
     * Create a new gateway instance
     *
     * @param ClientInterface $httpClient A HTTP client to make API calls with
     * @param PsrRequestInterface $httpRequest A HTTP request object
     */
    public function __construct(ClientInterface $httpClient = null, PsrRequestInterface $httpRequest = null)
    {
        $this->httpClient = $httpClient ?: $this->getDefaultHttpClient();
        $this->httpRequest = $httpRequest ?: $this->getDefaultHttpRequest();
        $this->initialize();
    }

    /**
     * Initialize this gateway with default parameters
     *
     * @param array $parameters
     * @return $this
     */
    public function initialize(array $parameters = array())
    {
        $this->parameters = new ParameterBag;

        // set default parameters
        foreach ($this->getDefaultParameters() as $key => $value) {
            if (is_array($value)) {
                $this->parameters->set($key, reset($value));
            } else {
                $this->parameters->set($key, $value);
            }
        }

        Helper::initialize($this, $parameters);

        return $this;
    }

    /**
     * @return array
     */
    public function getDefaultParameters()
    {
        return [];
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getParameter($key)
    {
        return $this->traitGetParameter($key);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function setParameter($key, $value)
    {
        return $this->traitSetParameter($key, $value);
    }

    /**
     * Create and initialize a request object
     *
     * @param string $class The request class name
     * @param array $parameters
     * @return RequestInterface|AbstractRequest
     */
    protected function createRequest($class, array $parameters)
    {
        /** @var RequestInterface $obj */
        $obj = new $class($this->httpClient, $this->httpRequest);

        return $obj->initialize(array_replace($this->getParameters(), $parameters));
    }

    /**
     * Get the global default HTTP client.
     *
     * @return ClientInterface
     */
    protected function getDefaultHttpClient()
    {
        return new \GuzzleHttp\Client();
    }

    /**
     * Get the global default HTTP request.
     *
     * @return \Symfony\Component\HttpFoundation\Request
     */
    protected function getDefaultHttpRequest()
    {
        return HttpRequest::createFromGlobals();
    }
}
