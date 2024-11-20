<?php

namespace App\Gateway\Core;

use App\Service\HttpClientService;

abstract class PaymentGatewayAbstract implements PaymentGatewayCanConfigInterface
{
    protected array $configs = [];

    public function __construct(
        protected HttpClientService $httpClient
    ) {}

    public function setConfigs(array $configs): self
    {
        $this->configs = $configs;

        return $this;
    }

    public function getConfigs(): array
    {
        return $this->configs;
    }
}
