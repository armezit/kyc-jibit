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

/**
 * Class Provider
 */
class Provider extends AbstractProvider
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'jibit';
    }

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
     * @param array $parameters
     * @return $this
     */
    public function initialize(array $parameters = []): static
    {
        parent::initialize($parameters);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getApiKey(): ?string
    {
        return $this->getParameter('apiKey');
    }

    /**
     * @return string|null
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
