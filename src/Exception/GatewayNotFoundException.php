<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GatewayNotFoundException extends NotFoundHttpException
{
    public function __construct(string $gatewayName)
    {
        parent::__construct(sprintf('Payment gateway "%s" not found.', $gatewayName));
    }
}
