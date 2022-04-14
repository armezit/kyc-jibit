<?php

namespace Armezit\Kyc\Jibit\Message;

use Armezit\Kyc\Jibit\Common\AbstractRequest;
use JetBrains\PhpStorm\Pure;

class MatchNationalCodeWithMobileNumberRequest extends AbstractRequest
{

    /**
     * @return string
     */
    protected function getHttpMethod(): string
    {
        return 'GET';
    }

    /**
     * Get the raw data array for this message.
     *
     * @return array
     * @throws \Armezit\Kyc\Jibit\Exception\InvalidRequestException
     */
    public function getData(): array
    {
        // Validate required parameters before return data
        $this->validate('mobileNumber', 'nationalCode');

        return [
            'mobileNumber' => $this->getParameter('mobileNumber'),
            'nationalCode' => $this->getParameter('nationalCode'),
        ];
    }

    /**
     * @param string $endpoint
     * @return string
     */
    protected function createUri(string $endpoint): string
    {
        return $endpoint . '/v1/services/matching';
    }

    /**
     * @param array $data
     * @return MatchNationalCodeWithMobileNumberResponse
     */
    #[Pure] protected function createResponse(array $data): MatchNationalCodeWithMobileNumberResponse
    {
        return new MatchNationalCodeWithMobileNumberResponse($this, $data);
    }

    /**
     * @param string $mobileNumber
     * @return static
     */
    public function setMobileNumber(string $mobileNumber): static
    {
        return $this->setParameter('mobileNumber', $mobileNumber);
    }

    /**
     * @param string $nationalCode
     * @return static
     */
    public function setNationalCode(string $nationalCode): static
    {
        return $this->setParameter('nationalCode', $nationalCode);
    }

}
