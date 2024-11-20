<?php

namespace App\Factory;

use App\Dto\PaymentRequest;

class PaymentRequestFactory
{
    public function create(object $data): PaymentRequest
    {
        return new PaymentRequest(
            $data->amount,
            $data->currency,
            $data->cardNumber,
            $data->cardExpMonth,
            $data->cardExpYear,
            $data->cardCvv
        );
    }
}
