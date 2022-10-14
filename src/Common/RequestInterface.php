<?php

namespace Armezit\Kyc\Jibit\Common;

interface RequestInterface extends MessageInterface
{
    /**
     * Initialize request with parameters
     * @param array $parameters The parameters to send
     * @return static
     */
    public function initialize(array $parameters = array()): static;

    /**
     * Get all request parameters
     *
     * @return array
     */
    public function getParameters();

    /**
     * Get the response to this request (if the request has been sent)
     *
     * @return ResponseInterface
     */
    public function getResponse();

    /**
     * Send the request
     *
     * @return ResponseInterface
     */
    public function send();

    /**
     * Send the request with specified data
     *
     * @param mixed $data The data to send
     * @return ResponseInterface
     */
    public function sendData($data);
}
