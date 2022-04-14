<?php

namespace Armezit\Kyc\Jibit\Common;

interface ResponseInterface extends MessageInterface
{
    /**
     * Get the original request which generated this response
     *
     * @return RequestInterface
     */
    public function getRequest();

    /**
     * Is the response successful?
     *
     * @return boolean
     */
    public function isSuccessful();

    /**
     * Response Message
     *
     * @return null|string A response message from the payment gateway
     */
    public function getMessage();

    /**
     * Response code
     *
     * @return null|string A response code from the payment gateway
     */
    public function getCode();

}
