<?php

namespace App\Gateway\Core;

use App\Dto\PaymentRequest;

interface PaymentGatewayCanProcessInterface
{
    public function processPayment(PaymentRequest $paymentRequest): array;
}
