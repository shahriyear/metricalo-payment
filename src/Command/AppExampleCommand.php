<?php

namespace App\Command;

use App\Exception\GatewayNotFoundException;
use Exception;
use App\Service\GatewayRegistry;
use App\Service\PaymentProcessor;
use App\Schema\PaymentRequestSchema;
use App\Service\JsonSchemaValidator;
use App\Factory\PaymentRequestFactory;
use JsonSchema\Exception\ValidationException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Gateway\Core\PaymentGatewayCanProcessInterface;

#[AsCommand(name: 'app:example', description: 'Process a payment using a specified gateway.')]
class AppExampleCommand extends Command
{
    public function __construct(
        private PaymentProcessor $paymentProcessor,
        private JsonSchemaValidator $validator,
        private PaymentRequestFactory $paymentRequestFactory,
        private GatewayRegistry $gatewayRegistry
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Process a payment using a specified gateway.')
            ->addArgument('system', InputArgument::REQUIRED, 'The payment system to use (e.g., shift4, aci)')
            ->addOption('amount', null, InputOption::VALUE_REQUIRED, 'Payment amount')
            ->addOption('currency', null, InputOption::VALUE_REQUIRED, 'Currency code')
            ->addOption('cardNumber', null, InputOption::VALUE_REQUIRED, 'Card number')
            ->addOption('cardExpMonth', null, InputOption::VALUE_REQUIRED, 'Card expiry month')
            ->addOption('cardExpYear', null, InputOption::VALUE_REQUIRED, 'Card expiry year')
            ->addOption('cardCvv', null, InputOption::VALUE_REQUIRED, 'Card CVV');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $system = $input->getArgument('system');

        try {
            if (!$this->gatewayRegistry->getGateway($system) instanceof PaymentGatewayCanProcessInterface) {
                return Command::FAILURE;
            }

            $params = [
                'amount' => floatval($input->getOption('amount')),
                'currency' => $input->getOption('currency'),
                'cardNumber' => $input->getOption('cardNumber'),
                'cardExpMonth' => intval($input->getOption('cardExpMonth')),
                'cardExpYear' => intval($input->getOption('cardExpYear')),
                'cardCvv' => $input->getOption('cardCvv'),
            ];

            $this->validator->validate((object) $params, PaymentRequestSchema::class);

            $paymentRequest = $this->paymentRequestFactory->create((object) $params);
            $response = $this->paymentProcessor->process($system, $paymentRequest);

            $output->writeln('<info>Payment Processed Successfully:</info>');
            $output->writeln(json_encode($response, JSON_PRETTY_PRINT));
            return Command::SUCCESS;
        } catch (GatewayNotFoundException $e) {
            $output->writeln('<error>Error: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        } catch (ValidationException $e) {
            $output->writeln('<error>Validation Error:</error> ' . $e->getMessage());
            return Command::FAILURE;
        } catch (Exception $e) {
            $output->writeln('<error>Error:</error> ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
