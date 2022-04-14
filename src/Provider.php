<?php

/**
 * @package Armezit\Kyc\Jibit
 * @author Armin Rezayati <armin.rezayati@gmail.com>
 */

namespace Armezit\Kyc\Jibit;

use Armezit\Kyc\Jibit\Common\AbstractProvider;
use Armezit\Kyc\Jibit\Common\RequestInterface;
use Armezit\Kyc\Jibit\Message\MatchCardNumberWithNationalCodeRequest;
use Armezit\Kyc\Jibit\Message\MatchNationalCodeWithMobileNumberRequest;
use Armezit\Kyc\Jibit\Message\MatchNationalCodeWithMobileNumberResponse;
use Nette\NotImplementedException;

/**
 * Class Gateway
 */
class Provider extends AbstractProvider
{

    /**
     * @return array
     */
    public function getDefaultParameters(): array
    {
        return [
            'apiKey' => '',
            'secretKey' => '',
            'accessToken' => '',
            'refreshToken' => '',
        ];
    }

    /**
     * @inheritDoc
     */
    public function initialize(array $parameters = [])
    {
        parent::initialize($parameters);

        $this->setParameter('cache', new Cache('Jibit'));

        return $this;
    }

    /**
     * @return string
     */
    public function getApiKey(): ?string
    {
        return $this->getParameter('apiKey');
    }

    /**
     * @return string
     */
    public function getSecretKey(): ?string
    {
        return $this->getParameter('secretKey');
    }

    /**
     * @param string $value
     * @return self
     */
    public function setApiKey(string $value): self
    {
        return $this->setParameter('apiKey', $value);
    }

    /**
     * @param string $value
     * @return self
     */
    public function setSecretKey(string $value): self
    {
        return $this->setParameter('secretKey', $value);
    }

    /**
     * @param array $options
     * @return RequestInterface
     */
    public function matchNationalCodeWithMobileNumber(array $options = []): RequestInterface
    {
        return $this->createRequest(MatchNationalCodeWithMobileNumberRequest::class, $options);
    }

    /**
     * @param array $options
     * @return RequestInterface
     */
    public function matchCardNumberWithNationalCode(array $options = []): RequestInterface
    {
        return $this->createRequest(MatchCardNumberWithNationalCodeRequest::class, $options);
    }

}
