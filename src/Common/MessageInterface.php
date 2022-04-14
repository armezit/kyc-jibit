<?php

namespace Armezit\Kyc\Jibit\Common;

interface MessageInterface
{
    /**
     * Get the raw data array for this message.
     *
     * @return mixed
     */
    public function getData();
}
