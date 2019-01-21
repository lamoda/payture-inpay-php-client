# Payture InPay Client

Simple php client for [Payture InPay API](https://payture.com/api#inpay_). This API allows payment transaction processing 
while managing user sensitive payment data (i.e cards) on the Payture side

## Installation

```bash
composer require lamoda/payture-inpay-client
```

## Usage

```php
<?php

# Minimal initialization
$configuration = new \Lamoda\Payture\InPayClient\TerminalConfiguration(
    'MerchantKey',
    'MerchantPassword',
    'https://sandbox.payture.com/'
);

$transport = new \Lamoda\Payture\InPayClient\GuzzleHttp\GuzzleHttpPaytureTransport(
    new \GuzzleHttp\Client(),
    $configuration
);

$terminal = new \Lamoda\Payture\InPayClient\PaytureInPayTerminal($configuration, $transport);

$terminal->charge('ORDER_NUMBER_123', 100500);
```

## Tuning

### Client configuration

You can pass 3-rd argument to the GuzzleHttpPaytureTransport with the instance of `\Lamoda\Payture\InPayClient\GuzzleHttp\GuzzleHttpOptionsBag`.
Instance is preconfigured with guzzle `\GuzzleHttp\RequestOptions` both global (first constructor argument) and per-operation 
(second constructor argument indexed by operation name)

### Logging

You can pass 4-th argument to the GuzzleHttpPaytureTransport with instance of PSR-3 `LoggerInterface` 
in order to log operations with parameters.

Also you can configure generic Guzzle [logging middleware](http://docs.guzzlephp.org/en/stable/handlers-and-middleware.html). 

## Testing 

```bash
vendor/bin/phpunit -c phpunit.xml
```

You can test this library against your own test terminal providing `PAYTURE_TEST_MERCHANT_KEY` and `PAYTURE_TEST_MERCHANT_PASSWORD`
environment variables while running the tests.

```bash
 PAYTURE_TEST_MERCHANT_KEY=MerchantKey \
 PAYTURE_TEST_MERCHANT_PASSWORD=MerchantPassword \
 vendor/bin/phpunit -c phpunit.xml
```

These test will run order processing sequence against payture sandbox.
