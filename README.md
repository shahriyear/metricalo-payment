
# Payment Gateway Integration

## Overview
This project provides a flexible and extensible payment gateway integration framework in PHP using the Symfony framework. It supports multiple payment gateways, including **Shift4** and **ACI**, and is designed to simplify payment processing for developers and users.

## Features
- **Support for Multiple Gateways**: Shift4, ACI, and easily extensible to add new ones.
- **Schema Validation**: Ensures request payloads adhere to strict validation rules.
- **Command-Line and API**: Supports payment processing via console commands and HTTP API.
- **Error Handling**: Comprehensive error reporting and logging.
- **Docker Ready**: Simplified setup with Docker.

---

## Prerequisites
- PHP 8.3
- Symfony 6.4
- Docker (Recommended)

---

## Quick Start

### Run With Docker (Recommended)
1. Clone the repository:
   ```bash
   git clone https://github.com/shahriyear/metricalo-payment.git
   cd metricalo-payment
   ```
2. Copy .env:
   ```bash
   cp .env.example .env
   ```
3. Build and start the container:
   ```bash
   sh ./start
   ```
Optional: to make executable file 
    ```
chmod +x start.sh && ./start.sh
    ```

### Run Locally Without Docker
1. Clone the repository:
   ```bash
   git clone https://github.com/shahriyear/metricalo-payment.git
   cd metricalo-payment
   ```
2. Copy .env:
   ```bash
   cp .env.example .env
   ```
3. Install dependencies:
   ```bash
   composer install
   ```
4. Run the application:
   ```bash
   php -S localhost:8999 -t public
   ```


---
## Change Gateway Config
1. Go to **config/gateways.yaml**
2. Change config accordingly 
```bash
parameters:
    gateways:
        shift4:
            class: App\Gateway\Shift4Gateway
            configs:
                apiUrl: "https://api.shift4.com"
                apiKey: "sk_test_ikYubdTU3FtWh0Gt3qcCf6jA"
        aci:
            class: App\Gateway\AciGateway
            configs:
                apiUrl: "https://eu-test.oppwa.com"
                apiVersion: "v1"
                entityId: "8a8294174b7ecb28014b9699220015ca"

```

## Test Card
- Card Number: 4200000000000000
- Exp: 12/25
- CVC: 123
- Currency: EUR

## API Usage
### Endpoint
- URL: `http://localhost:8999/app/example/{shift4|aci}`
- HTTP Method: `POST`

- **Payload**:
  ```json
  {
    "amount": 10.12,
    "currency": "EUR",
    "cardNumber": "4200000000000000",
    "cardExpMonth": 12,
    "cardExpYear": 2025,
    "cardCvv": "123"
  }
  ```
- **Success Response**:
  ```json
  {
    "status": "success",
    "code": 200,
    "data": {
      "transactionId": "8ac7a4a2934787ec019348f19b666a34",
      "createdAt": "2024-11-20 09:40:13 UTC",
      "amount": "10.12",
      "currency": "EUR",
      "cardBin": "420000"
    }
  }
  ```
- **Error Response**:
  ```json
  {
    "status": "error",
    "code": 400,
    "errors": {
      "message": "Invalid input data"
    }
  }
  ```

---

## Adding a New Payment Gateway

1. **Create a New Gateway Class**:
   Implement `PaymentGatewayCanProcessInterface` and optionally `PaymentGatewayCanConfigInterface`.
   ```php
   namespace App\Gateway;

   use App\Dto\PaymentRequest;
   use App\Gateway\Core\PaymentGatewayAbstract;
   use App\Gateway\Core\PaymentGatewayCanProcessInterface;

   class NewGateway extends PaymentGatewayAbstract implements PaymentGatewayCanProcessInterface
   {
       public function processPayment(PaymentRequest $paymentRequest): array
       {
           // Implement logic to process payment
       }
   }
   ```

2. **Register the Gateway in `gateways.yaml`**:
   ```yaml
   parameters:
       gateways:
           newGateway:
               class: App\Gateway\NewGateway
               configs:
                   apiUrl: "https://api.newgateway.com"
                   apiKey: "your-api-key"
   ```

3. **Test the Gateway**:
   Run unit tests or manually test using the API or CLI commands.

---

## Source Code Structure
Here's a simplified directory tree:
```
src/
├── Command/                   # Symfony console commands
├── Controller/                # API endpoints
├── Dto/                       # Data Transfer Objects
├── EventListener/             # Exception and validation handlers
├── Factory/                   # Object factories
├── Gateway/                   # Payment gateway implementations
├── Schema/                    # JSON schema definitions
├── Service/                   # Core services like HttpClientService
├── Utils/                     # Helper utilities
tests/                         # Unit and integration tests
config/                        # Symfony configuration
```

---

## Testing
### Run Tests
- Docker
```bash
docker exec -it payment-php composer tests
```
- Locally
```bash
composer tests
```

---

## Commands
### Console Usage
```bash
php bin/console app:example {gateway} --amount={amount} --currency={currency} --cardNumber={cardNumber} --cardExpMonth={month} --cardExpYear={year} --cardCvv={cvv}
```
- Docker
```bash
docker exec -it payment-php php bin/console app:example shift4 --amount=100 --currency=USD --cardNumber=4200000000000000 --cardExpMonth=12 --cardExpYear=2025 --cardCvv=123
```
- Locally
```bash
php bin/console app:example shift4 --amount=100 --currency=USD --cardNumber=4200000000000000 --cardExpMonth=12 --cardExpYear=2025 --cardCvv=123
```

---

## Contributors
- Fahim Shahriyear Hossain - Senior Software Engineer

---

## License
This project is licensed under the MIT License.
