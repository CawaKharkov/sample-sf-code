<?php

namespace App\Exception;

use FOS\RestBundle\Exception\InvalidParameterException;

class InvalidParameterMessageConverter
{
    /**
     * Converts ugly technical validation message
     * to the user-firendly one
     *
     * @param InvalidParameterException $e
     * @return string
     */
    public function getMessage(InvalidParameterException $e): string
    {
        preg_match('/violated a constraint \"(.*)\"/', $e->getMessage(), $matches);

        return $matches[1] ?? $e->getMessage();
    }

}