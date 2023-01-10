<?php

namespace Armezit\Kyc\Jibit\Tests;

use Armezit\Kyc\Jibit\Common\RequestInterface;
use GuzzleHttp\Psr7\Message;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\Strategy\MockClientStrategy;
use Http\Mock\Client as MockClient;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use ReflectionObject;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

abstract class TestCase extends BaseTestCase
{
    use MockeryPHPUnitIntegration;

    /** @var RequestInterface */
    private $mockRequest;

    /** @var MockClient */
    private $mockClient;

    /** @var RequestFactoryInterface */
    private $requestFactory;

    /** @var CacheItemPoolInterface */
    private $cache;

    protected function setUp(): void
    {
        parent::setUp();
        HttpClientDiscovery::prependStrategy(MockClientStrategy::class);
    }

    /**
     * @return string
     */
    protected function getDataDir()
    {
        return __DIR__ . '/data/';
    }

    /**
     * Get all the mocked requests
     *
     * @return array
     */
    public function getMockedRequests()
    {
        return $this->mockClient->getRequests();
    }

    /**
     * Get a mock response for a client by mock file name
     *
     * @param string|ResponseInterface $path Relative path to the mock response file
     *
     * @return ResponseInterface
     * @throws \ReflectionException
     */
    public function getMockHttpResponse($path)
    {
        if ($path instanceof ResponseInterface) {
            return $path;
        }

        $ref = new ReflectionObject($this);
        $dir = dirname($ref->getFileName());

        // if mock file doesn't exist, check parent directory
        if (!file_exists($dir . '/data/' . $path) && file_exists($dir . '/../data/' . $path)) {
            return Message::parseResponse(file_get_contents($dir . '/../data/' . $path));
        }

        return Message::parseResponse(file_get_contents($dir . '/data/' . $path));
    }

    /**
     * Set a mock response from a mock file on the next client request.
     *
     * This method assumes that mock response files are located under the
     * Mock/ subdirectory of the current class. A mock response is added to the next
     * request sent by the client.
     *
     * An array of path can be provided and the next x number of client requests are
     * mocked in the order of the array where x = the array length.
     *
     * @param array|string $paths Path to files within the Mock folder of the service
     *
     * @return void returns the created mock plugin
     */
    public function setMockHttpResponse($paths)
    {
        foreach ((array)$paths as $path) {
            $this->mockClient->addResponse($this->getMockHttpResponse($path));
        }
    }

    public function getMockRequest()
    {
        if (null === $this->mockRequest) {
            $this->mockRequest = Mockery::mock(RequestInterface::class);
        }
        return $this->mockRequest;
    }

    /**
     * @return MockClient
     */
    public function getMockClient()
    {
        if (null === $this->mockClient) {
            $this->mockClient = new MockClient();
        }
        return $this->mockClient;
    }

    /**
     * @return RequestFactoryInterface
     */
    public function getRequestFactory()
    {
        if (null === $this->requestFactory) {
            $this->requestFactory = new \GuzzleHttp\Psr7\HttpFactory();
        }
        return $this->requestFactory;
    }

    /**
     * @return CacheItemPoolInterface
     */
    public function getCache()
    {
        if (null === $this->cache) {
            $this->cache = new ArrayAdapter();
        }
        return $this->cache;
    }
}
