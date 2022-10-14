<?php

namespace Armezit\Kyc\Jibit\Common;

interface ProviderInterface
{

    /**
     * Define default provider parameters as an associative array
     *
     * @return array
     */
    public function getDefaultParameters();

    /**
     * Initialize provider with parameters
     * @return $this
     */
    public function initialize(array $parameters = array());

    /**
     * Get all provider parameters
     * @return array
     */
    public function getParameters();

    /**
     * @param array $options
     * @return RequestInterface
     */
    public function matchNationalCodeWithMobileNumber(array $options = []): RequestInterface;

    /**
     * @param array $options
     * @return RequestInterface
     */
    public function matchCardNumberWithNationalCode(array $options = []): RequestInterface;
}
