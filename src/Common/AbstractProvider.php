<?php

namespace Armezit\Kyc\Jibit\Common;

use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Message\RequestFactory;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Client\ClientInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * AbstractProvider
 */
abstract class AbstractProvider implements ProviderInterface
{
    use ParametersTrait {
        ParametersTrait::setParameter as traitSetParameter;
        ParametersTrait::getParameter as traitGetParameter;
    }

    /**
     * Create a new gateway instance
     *
     * @param ClientInterface|null        $httpClient     HTTP client to make API calls.
     * @param RequestFactory|null         $requestFactory HTTP request factory.
     * @param CacheItemPoolInterface|null $cache
     */
    public function __construct(
        protected ?ClientInterface $httpClient = null,
        protected ?RequestFactory $requestFactory = null,
        protected ?CacheItemPoolInterface $cache = null,
    ) {
        if ($this->httpClient === null) {
            $this->httpClient = HttpClientDiscovery::find();
        }

        if ($this->requestFactory === null) {
            $this->requestFactory = MessageFactoryDiscovery::find();
        }

        if ($this->cache === null) {
            $this->cache = new FilesystemAdapter(namespace: static::getName(), directory: __DIR__ . '/cache/');
        }

        $this->initialize();
    }

    /**
     * Initialize this gateway with default parameters
     *
     * @param array $parameters
     * @return $this
     */
    public function initialize(array $parameters = []): static
    {
        $this->parameters = new ParameterBag();

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
    public function getDefaultParameters(): array
    {
        return [];
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getParameter(string $key): mixed
    {
        return $this->traitGetParameter($key);
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @return $this
     */
    public function setParameter(string $key, mixed $value): static
    {
        return $this->traitSetParameter($key, $value);
    }

    /**
     * Create and initialize a request object
     *
     * @param string $class      The request class name.
     * @param array  $parameters
     * @return RequestInterface
     */
    protected function createRequest(string $class, array $parameters): RequestInterface
    {
        /** @var RequestInterface $obj */
        $obj = new $class($this->httpClient, $this->requestFactory, $this->cache);

        return $obj->initialize(array_replace($this->getParameters(), $parameters));
    }
}
