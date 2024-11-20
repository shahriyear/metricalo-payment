<?php

namespace App\Dto;

class PaymentRequest
{


    public function __construct(
        private float $amount,
        private string $currency,
        private string $cardNumber,
        private int $cardExpMonth,
        private int $cardExpYear,
        private string $cardCvv
    ) {}

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getCardNumber(): string
    {
        return $this->cardNumber;
    }

    public function getCardExpMonth(): int
    {
        return $this->cardExpMonth;
    }

    public function getCardExpYear(): int
    {
        return $this->cardExpYear;
    }

    public function getCardCvv(): string
    {
        return $this->cardCvv;
    }

    public function toArray(): array
    {
        return [
            'amount' => $this->amount,
            'currency' => $this->currency,
            'cardNumber' => $this->cardNumber,
            'cardExpMonth' => $this->cardExpMonth,
            'cardExpYear' => $this->cardExpYear,
            'cardCvv' => $this->cardCvv,
        ];
    }

    public function toObject(): object
    {
        return (object) $this->toArray();
    }
}
