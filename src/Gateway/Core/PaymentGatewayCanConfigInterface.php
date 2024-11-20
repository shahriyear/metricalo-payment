<?php

namespace App\Gateway\Core;

interface PaymentGatewayCanConfigInterface
{
    public function setConfigs(array $params): self;
    public function getConfigs(): array;
}
