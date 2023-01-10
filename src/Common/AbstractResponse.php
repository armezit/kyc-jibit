<?php

namespace Armezit\Kyc\Jibit\Common;

/**
 * AbstractResponse
 */
abstract class AbstractResponse implements ResponseInterface
{
    /**
     * The embodied request object.
     *
     * @var RequestInterface
     */
    protected RequestInterface $request;

    /**
     * The data contained in the response.
     *
     * @var mixed
     */
    protected mixed $data;

    /**
     * @var array
     */
    protected array $errorCodes = [];

    /**
     * Constructor
     *
     * @param RequestInterface $request The initiating request.
     * @param mixed            $data
     */
    public function __construct(RequestInterface $request, mixed $data)
    {
        $this->request = $request;
        $this->data = $data;
    }

    /**
     * Get the initiating request object.
     *
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    /**
     * Is the response successful?
     * @return boolean
     */
    public function isSuccessful(): bool
    {
        return (int)$this->getCode() === 200 && empty($this->getErrors());
    }

    /**
     * Get the response data.
     *
     * @return mixed
     */
    public function getData(): mixed
    {
        return $this->data;
    }

    /**
     * Error messages
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->data['errors'] ?? [];
    }

    /**
     * Response code
     *
     * @return null|string A response code from the payment gateway
     */
    public function getCode(): ?string
    {
        return $this->data['httpStatus'] ?? null;
    }
}
