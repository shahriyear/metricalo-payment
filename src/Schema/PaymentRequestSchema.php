<?php

namespace App\Schema;

class PaymentRequestSchema
{
    public static function getSchema(): array
    {
        return [
            '$schema' => 'http://json-schema.org/draft-07/schema#',
            'type' => 'object',
            'required' => ['amount', 'currency', 'cardNumber', 'cardExpMonth', 'cardExpYear', 'cardCvv'],
            'properties' => (object)[
                'amount' => (object)[
                    'type' => 'number',
                    'minimum' => 0,
                    'description' => 'The payment amount must be positive.',
                ],
                'currency' => (object)[
                    'type' => 'string',
                    'pattern' => '^[A-Z]{3}$',
                    'description' => 'The currency must be a valid ISO 4217 code.',
                ],
                'cardNumber' => (object)[
                    'type' => 'string',
                    'minLength' => 12,
                    'maxLength' => 19,
                    'description' => 'The card number must be between 12 and 19 digits.',
                ],
                'cardExpMonth' => (object)[
                    'type' => 'integer',
                    'minimum' => 1,
                    'maximum' => 12,
                    'description' => 'The expiration month must be between 1 and 12.',
                ],
                'cardExpYear' => (object)[
                    'type' => 'integer',
                    'minimum' => 2024,
                    'description' => 'The expiration year must be 2024 or later.',
                ],
                'cardCvv' => (object)[
                    'type' => 'string',
                    'minLength' => 3,
                    'maxLength' => 4,
                    'description' => 'The CVV must be 3 or 4 digits.',
                ],
            ],
        ];
    }
}
