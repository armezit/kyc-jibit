<?php

namespace Armezit\Kyc\Jibit\Message;

use Armezit\Kyc\Jibit\Common\AbstractRequest;
use JetBrains\PhpStorm\Pure;

class MatchCardNumberWithNationalCodeRequest extends AbstractRequest
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
        $this->validate('cardNumber', 'nationalCode', 'birthDate');

        return [
            'cardNumber' => $this->getParameter('cardNumber'),
            'nationalCode' => $this->getParameter('nationalCode'),
            'birthDate' => $this->getParameter('birthDate'),
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
     * @return MatchCardNumberWithNationalCodeResponse
     */
    #[Pure] protected function createResponse(array $data): MatchCardNumberWithNationalCodeResponse
    {
        return new MatchCardNumberWithNationalCodeResponse($this, $data);
    }

    /**
     * @param string $cardNumber
     * @return static
     */
    public function setCardNumber(string $cardNumber): static
    {
        return $this->setParameter('cardNumber', $cardNumber);
    }

    /**
     * @param string $nationalCode
     * @return static
     */
    public function setNationalCode(string $nationalCode): static
    {
        return $this->setParameter('nationalCode', $nationalCode);
    }

    /**
     * @param string $birthDate
     * @return static
     */
    public function setBirthDate(string $birthDate): static
    {
        return $this->setParameter('birthDate', $birthDate);
    }

}
