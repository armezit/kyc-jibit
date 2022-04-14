<?php

namespace Armezit\Kyc\Jibit\Common;

abstract class AbstractResponse implements ResponseInterface
{

    /**
     * The embodied request object.
     *
     * @var RequestInterface
     */
    protected $request;

    /**
     * The data contained in the response.
     *
     * @var mixed
     */
    protected $data;

    /**
     * @var array
     */
    protected $errorCodes = [];

    /**
     * Constructor
     *
     * @param RequestInterface $request the initiating request.
     * @param mixed $data
     */
    public function __construct(RequestInterface $request, $data)
    {
        $this->request = $request;
        $this->data = $data;
    }

    /**
     * Get the initiating request object.
     *
     * @return RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Is the response successful?
     */
    public function isSuccessful()
    {
        return $this->getCode() === 200;
    }

    /**
     * Get the response data.
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Response Message
     *
     * @return null|string A response message from the payment gateway
     */
    public function getMessage()
    {
        return isset($this->errorCodes[$this->getCode()]) ? $this->errorCodes[$this->getCode()] : null;
    }

    /**
     * Response code
     *
     * @return null|string A response code from the payment gateway
     */
    public function getCode()
    {
        return $this->data['httpStatus'] ?? null;
    }

}
