<?php

namespace App\Tests\Command;

use App\Dto\PaymentRequest;
use PHPUnit\Framework\TestCase;
use App\Service\GatewayRegistry;
use App\Service\PaymentProcessor;
use App\Command\AppExampleCommand;
use App\Schema\PaymentRequestSchema;
use App\Service\JsonSchemaValidator;
use App\Factory\PaymentRequestFactory;
use PHPUnit\Framework\Attributes\TestDox;
use App\Exception\GatewayNotFoundException;
use JsonSchema\Exception\ValidationException;
use Symfony\Component\Console\Tester\CommandTester;
use App\Gateway\Core\PaymentGatewayCanProcessInterface;

#[TestDox('App Example Command')]
class AppExampleCommandTest extends TestCase
{
    private $paymentProcessor;
    private $validator;
    private $paymentRequestFactory;
    private $gatewayRegistry;

    protected function setUp(): void
    {
        $this->paymentProcessor = $this->createMock(PaymentProcessor::class);
        $this->validator = $this->createMock(JsonSchemaValidator::class);
        $this->paymentRequestFactory = $this->createMock(PaymentRequestFactory::class);
        $this->gatewayRegistry = $this->createMock(GatewayRegistry::class);
    }

    public function testCommandExecutesSuccessfully(): void
    {
        $this->gatewayRegistry->method('getGateway')
            ->with('shift4')
            ->willReturn($this->createMock(PaymentGatewayCanProcessInterface::class));

        $this->validator->expects($this->once())
            ->method('validate')
            ->with($this->isInstanceOf(\stdClass::class), PaymentRequestSchema::class);

        $this->paymentRequestFactory->expects($this->once())
            ->method('create')
            ->willReturn(new PaymentRequest(100.0, 'USD', '4111111111111111', 12, 2025, '123'));

        $this->paymentProcessor->expects($this->once())
            ->method('process')
            ->with('shift4', $this->isInstanceOf(PaymentRequest::class))
            ->willReturn([
                'status' => 'success',
                'code' => 200,
                'data' => ['transactionId' => 'tx_12345'],
            ]);

        $command = new AppExampleCommand(
            $this->paymentProcessor,
            $this->validator,
            $this->paymentRequestFactory,
            $this->gatewayRegistry
        );

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'system' => 'shift4',
            '--amount' => 100.0,
            '--currency' => 'USD',
            '--cardNumber' => '4111111111111111',
            '--cardExpMonth' => 12,
            '--cardExpYear' => 2025,
            '--cardCvv' => '123',
        ]);

        $output = $commandTester->getDisplay();

        $this->assertStringContainsString('Payment Processed Successfully', $output);
        $this->assertEquals(0, $commandTester->getStatusCode());
    }

    public function testCommandValidationFails(): void
    {
        $this->gatewayRegistry->method('getGateway')
            ->with('shift4')
            ->willReturn($this->createMock(PaymentGatewayCanProcessInterface::class));

        $this->validator->expects($this->once())
            ->method('validate')
            ->willThrowException(new ValidationException('Invalid input data'));

        $command = new AppExampleCommand(
            $this->paymentProcessor,
            $this->validator,
            $this->paymentRequestFactory,
            $this->gatewayRegistry
        );

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'system' => 'shift4',
            '--amount' => 100.0,
            '--currency' => 'USD',
            '--cardNumber' => '4111111111111111',
            '--cardExpMonth' => 12,
            '--cardExpYear' => 2025,
            '--cardCvv' => '123',
        ]);

        $output = $commandTester->getDisplay();

        $this->assertStringContainsString('Validation Error:', $output);
        $this->assertEquals(1, $commandTester->getStatusCode());
    }

    public function testCommandWithInvalidGateway(): void
    {
        $this->gatewayRegistry->method('getGateway')
            ->with('invalid_gateway')
            ->willThrowException(new GatewayNotFoundException('invalid_gateway'));

        $command = new AppExampleCommand(
            $this->paymentProcessor,
            $this->validator,
            $this->paymentRequestFactory,
            $this->gatewayRegistry
        );

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'system' => 'invalid_gateway',
            '--amount' => 100.0,
            '--currency' => 'USD',
            '--cardNumber' => '4111111111111111',
            '--cardExpMonth' => 12,
            '--cardExpYear' => 2025,
            '--cardCvv' => '123',
        ]);

        $output = $commandTester->getDisplay();

        $this->assertStringContainsString('Error: Payment gateway "invalid_gateway" not found.', $output);
        $this->assertEquals(1, $commandTester->getStatusCode());
    }
}
