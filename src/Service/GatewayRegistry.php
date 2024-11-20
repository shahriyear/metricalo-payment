<?php

namespace App\Service;

use App\Exception\GatewayNotFoundException;
use App\Gateway\Core\PaymentGatewayCanConfigInterface;
use App\Gateway\Core\PaymentGatewayCanProcessInterface;

class GatewayRegistry
{
    private array $gateways;

    public function __construct(array $gateways, public HttpClientService $httpClient)
    {
        $this->gateways = $gateways;
    }

    public function getGateway(string $system): PaymentGatewayCanProcessInterface
    {
        if (!in_array($system, array_keys($this->gateways))) {
            throw new GatewayNotFoundException($system);
        }

        $gateway = $this->gateways[$system];
        $instance = new $gateway['class']($this->httpClient);

        if ($instance instanceof PaymentGatewayCanConfigInterface) {
            $instance->setConfigs($gateway['configs']);
        }

        return $instance;
    }
}
