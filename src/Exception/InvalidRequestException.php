<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class InvalidRequestException extends BadRequestHttpException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
