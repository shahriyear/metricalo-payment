<?php

use App\Utils\DataMasker;
use App\Dto\PaymentRequest;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\TestCase;
use App\Service\GatewayRegistry;
use App\Service\PaymentProcessor;
use App\Exception\GatewayNotFoundException;
use App\Gateway\Core\PaymentGatewayCanProcessInterface;

class PaymentProcessorTest extends TestCase
{
    private $gatewayRegistry;
    private $dataMasker;
    private $logger;
    private $paymentProcessor;

    protected function setUp(): void
    {
        $this->gatewayRegistry = $this->createMock(GatewayRegistry::class);
        $this->dataMasker = $this->createMock(DataMasker::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->paymentProcessor = new PaymentProcessor(
            $this->gatewayRegistry,
            $this->dataMasker,
            $this->logger
        );
    }

    public function testProcessPaymentSuccessfully(): void
    {
        $paymentRequest = new PaymentRequest(100.0, 'USD', '4111111111111111', 12, 2025, '123');

        $gateway = $this->createMock(PaymentGatewayCanProcessInterface::class);
        $gateway->expects($this->once())
            ->method('processPayment')
            ->with($paymentRequest)
            ->willReturn([
                'status' => 'success',
                'code' => 200,
                'data' => ['transactionId' => 'tx_12345'],
            ]);

        // Mock the GatewayRegistry to return the mocked gateway
        $this->gatewayRegistry->expects($this->once())
            ->method('getGateway')
            ->with('shift4')
            ->willReturn($gateway);

        // Mock the DataMasker for masking sensitive data
        $this->dataMasker->expects($this->once())
            ->method('maskSensitiveData')
            ->with($paymentRequest->toArray())
            ->willReturn([
                'amount' => 100.0,
                'currency' => 'USD',
                'cardNumber' => '411111******1111',
                'cardExpMonth' => 12,
                'cardExpYear' => 2025,
                'cardCvv' => '***',
            ]);

        // Call the PaymentProcessor's process method
        $result = $this->paymentProcessor->process('shift4', $paymentRequest);

        $this->assertEquals('success', $result['status']);
        $this->assertEquals(200, $result['code']);
        $this->assertArrayHasKey('transactionId', $result['data']);
    }

    public function testProcessPaymentFailsWithGatewayError(): void
    {
        $paymentRequest = new PaymentRequest(50.0, 'EUR', '4222222222222222', 6, 2024, '456');

        // Mock the gateway to simulate a failure
        $gateway = $this->createMock(PaymentGatewayCanProcessInterface::class);
        $gateway->expects($this->once())
            ->method('processPayment')
            ->with($paymentRequest)
            ->willReturn([
                'status' => 'error',
                'code' => 500,
                'errors' => ['message' => 'Internal Server Error'],
            ]);

        // Mock the GatewayRegistry to return the mocked gateway
        $this->gatewayRegistry->expects($this->once())
            ->method('getGateway')
            ->with('shift4')
            ->willReturn($gateway);

        // Mock DataMasker to simulate masking sensitive data
        $this->dataMasker->expects($this->once())
            ->method('maskSensitiveData')
            ->with($paymentRequest->toArray())
            ->willReturn([
                'amount' => 50.0,
                'currency' => 'EUR',
                'cardNumber' => '422222******2222',
                'cardExpMonth' => 6,
                'cardExpYear' => 2024,
                'cardCvv' => '***',
            ]);

        // Call the PaymentProcessor's process method
        $result = $this->paymentProcessor->process('shift4', $paymentRequest);

        $this->assertEquals('error', $result['status']);
        $this->assertEquals(500, $result['code']);
        $this->assertArrayHasKey('errors', $result);
        $this->assertEquals('Internal Server Error', $result['errors']['message']);
    }

    public function testProcessPaymentWithInvalidGateway(): void
    {
        $paymentRequest = new PaymentRequest(25.0, 'GBP', '4111111111111111', 10, 2025, '789');

        // Mock GatewayRegistry to throw GatewayNotFoundException for an invalid gateway
        $this->gatewayRegistry->expects($this->once())
            ->method('getGateway')
            ->with('invalid_gateway')
            ->willThrowException(new GatewayNotFoundException('invalid_gateway'));

        // Expect the logger to log the error
        $this->logger->expects($this->once())
            ->method('info')
            ->with($this->stringContains('Processing payment'));

        $this->expectException(GatewayNotFoundException::class);
        $this->expectExceptionMessage('Payment gateway "invalid_gateway" not found.');

        // Call the PaymentProcessor's process method
        $this->paymentProcessor->process('invalid_gateway', $paymentRequest);
    }
}
