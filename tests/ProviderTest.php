<?php

namespace Armezit\Kyc\Jibit\Tests;

use Armezit\Kyc\Jibit\Exception\InvalidResponseException;
use Armezit\Kyc\Jibit\Message\MatchNationalCodeWithMobileNumberResponse;
use Armezit\Kyc\Jibit\Provider;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

/**
 * Class ProviderTest
 * @package Armezit\Kyc\Jibit\Tests
 */
class ProviderTest extends TestCase
{
    /**
     * @var Provider
     */
    protected Provider $provider;

    /**
     * @var array<string, integer|string|boolean>
     */
    protected $params;

    protected function setUp(): void
    {
        parent::setUp();

        $cache = $this->getCache();
        $cache->save($cache->getItem('accessToken')->set('ACCESS_TOKEN')->expiresAfter(86400));
        $cache->save($cache->getItem('refreshToken')->set('REFRESH_TOKEN')->expiresAfter(86400));

        $this->provider = new Provider($this->getMockClient(), cache: $cache);

        $this->provider->setApiKey('xxxxxxxx');
        $this->provider->setSecretKey('secret');

        $this->params = [
            'mobileNumber' => '+989993002010',
            'nationalCode' => '1111122222',
        ];
    }

    public function testRequestsNeedAuthToken(): void
    {
        $provider = new Provider($this->getMockClient(), cache: new ArrayAdapter());

        $this->expectException(InvalidResponseException::class);
        $provider->matchNationalCodeWithMobileNumber($this->params)->send();
    }

    public function testNationalCodeMatchesWithMobileNumber(): void
    {
        $this->setMockHttpResponse('NationalCodeMatchesWithMobileNumber.txt');

        /** @var MatchNationalCodeWithMobileNumberResponse $response */
        $response = $this->provider->matchNationalCodeWithMobileNumber($this->params)->send();

        self::assertTrue($response->isSuccessful());
        self::assertTrue($response->isMatched());
    }

    public function testNationalCodeNotMatchesWithMobileNumber(): void
    {
        $this->setMockHttpResponse('NationalCodeNotMatchesWithMobileNumber.txt');

        /** @var MatchNationalCodeWithMobileNumberResponse $response */
        $response = $this->provider->matchNationalCodeWithMobileNumber($this->params)->send();

        self::assertTrue($response->isSuccessful());
        self::assertFalse($response->isMatched());
    }

    public function testMatchNationalCodeWithMobileNumberHasError(): void
    {
        $this->setMockHttpResponse('MatchNationalCodeWithMobileNumberHasError.txt');

        /** @var MatchNationalCodeWithMobileNumberResponse $response */
        $response = $this->provider->matchNationalCodeWithMobileNumber($this->params)->send();

        self::assertFalse($response->isSuccessful());

        $errors = $response->getErrors();
        self::assertIsArray($errors);

        self::assertEquals('invalid.argument', $errors[0]['code']);
        self::assertEquals('nationalCode.is_invalid', $errors[0]['message']);
    }


}
