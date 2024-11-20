<?php

use App\Gateway\AciGateway;
use App\Gateway\Shift4Gateway;
use PHPUnit\Framework\TestCase;
use App\Service\GatewayRegistry;
use App\Service\HttpClientService;
use App\Exception\GatewayNotFoundException;
use App\Gateway\Core\PaymentGatewayCanConfigInterface;
use App\Gateway\Core\PaymentGatewayCanProcessInterface;

class GatewayRegistryTest extends TestCase
{
    public function testGetGatewayThrowsExceptionForInvalidGateway(): void
    {
        $httpClient = $this->createMock(HttpClientService::class);

        $gateways = [
            'shift4' => [
                'class' => Shift4Gateway::class,
                'configs' => [
                    'apiUrl' => 'https://api.shift4.com',
                    'apiKey' => 'test_key',
                ],
            ],
        ];

        $registry = new GatewayRegistry($gateways, $httpClient);

        $this->expectException(GatewayNotFoundException::class);
        $this->expectExceptionMessage('Payment gateway "aci" not found.');

        $registry->getGateway('aci'); // This gateway does not exist in the configuration.
    }

    public function testGetGatewaySuccessfully(): void
    {
        $httpClient = $this->createMock(HttpClientService::class);

        $gateways = [
            'shift4' => [
                'class' => Shift4Gateway::class,
                'configs' => [
                    'apiUrl' => 'https://api.shift4.com',
                    'apiKey' => 'test_key',
                ],
            ],
            'aci' => [
                'class' => AciGateway::class,
                'configs' => [
                    'apiUrl' => 'https://test.aci.com',
                    'entityId' => 'test_entity',
                    'apiVersion' => 'v1',
                ],
            ],
        ];

        $registry = new GatewayRegistry($gateways, $httpClient);

        $gateway = $registry->getGateway('shift4');

        $this->assertInstanceOf(Shift4Gateway::class, $gateway);
        $this->assertInstanceOf(PaymentGatewayCanConfigInterface::class, $gateway);
        $this->assertInstanceOf(PaymentGatewayCanProcessInterface::class, $gateway);
    }

    public function testGatewayConfiguration(): void
    {
        $httpClient = $this->createMock(HttpClientService::class);

        $gateways = [
            'shift4' => [
                'class' => Shift4Gateway::class,
                'configs' => [
                    'apiUrl' => 'https://api.shift4.com',
                    'apiKey' => 'test_key',
                ],
            ],
        ];

        $registry = new GatewayRegistry($gateways, $httpClient);

        /** @var PaymentGatewayCanConfigInterface */
        $gateway = $registry->getGateway('shift4');

        $this->assertInstanceOf(Shift4Gateway::class, $gateway);
        $this->assertEquals('https://api.shift4.com', $gateway->getConfigs()['apiUrl']);
        $this->assertEquals('test_key', $gateway->getConfigs()['apiKey']);
    }
}
