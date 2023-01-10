<?php

namespace Armezit\Kyc\Jibit\Message;

use Armezit\Kyc\Jibit\Common\AbstractResponse;

/**
 * MatchNationalCodeWithMobileNumberResponse
 */
class MatchNationalCodeWithMobileNumberResponse extends AbstractResponse
{
    /**
     * @return bool
     */
    public function isMatched(): bool
    {
        return filter_var($this->data['matched'], FILTER_VALIDATE_BOOL);
    }
}
