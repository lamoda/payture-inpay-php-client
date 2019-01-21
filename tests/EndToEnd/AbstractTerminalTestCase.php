<?php

namespace Lamoda\Payture\InPayClient\Tests\EndToEnd;

use GuzzleHttp\Client;
use Lamoda\Payture\InPayClient\GuzzleHttp\GuzzleHttpPaytureTransport;
use Lamoda\Payture\InPayClient\PaytureInPayTerminal;
use Lamoda\Payture\InPayClient\PaytureInPayTerminalInterface;
use Lamoda\Payture\InPayClient\TerminalConfiguration;
use Lamoda\Payture\InPayClient\TestUtils\Card;
use Lamoda\Payture\InPayClient\TestUtils\PaymentHelper;
use PHPUnit\Framework\TestCase;

abstract class AbstractTerminalTestCase extends TestCase
{
    private const ENV_KEY = 'PAYTURE_TEST_MERCHANT_KEY';
    private const ENV_PASSWORD = 'PAYTURE_TEST_MERCHANT_PASSWORD';

    protected const SANDBOX_PAY_SUBMIT_URL = 'https://sandbox.payture.com/apim/PaySubmit';
    protected const SANDBOX_API_URL = 'https://sandbox.payture.com';

    /** @var PaytureInPayTerminalInterface */
    private $terminal;
    /** @var PaymentHelper */
    private $helper;

    protected static function generateOrderId(): string
    {
        try {
            return 'TEST' . (new \DateTime())->format('ymd-His');
        } catch (\Exception $e) {
            throw new \RuntimeException('Unable to generate \DateTime instance');
        }
    }

    /**
     * Successful payment without 3DS and with optional CVV.
     *
     * @link https://payture.com/api#test-cards_
     *
     * @return Card
     */
    private static function getTestCard(): Card
    {
        return new Card('4111111111100031', '123', '22', '12', 'AUTO TESTS');
    }

    public function setUp()
    {
        if (getenv(self::ENV_KEY) === false || getenv(self::ENV_PASSWORD) === false) {
            self::markTestSkipped(
                sprintf(
                    'Provide both "%s" and "%s" env vars to run end-to-end test',
                    self::ENV_KEY,
                    self::ENV_PASSWORD
                )
            );
        }

        $configuration = new TerminalConfiguration(
            getenv(self::ENV_KEY), getenv(self::ENV_PASSWORD), self::SANDBOX_API_URL
        );
        $client = new Client();
        $transport = new GuzzleHttpPaytureTransport($client, $configuration);
        $this->terminal = new PaytureInPayTerminal($configuration, $transport);
        $this->helper = new PaymentHelper($client);
    }

    protected function pay(string $paymentUrl, string $orderId, int $amount): void
    {
        $this->helper->pay($orderId, $amount, $paymentUrl, self::getTestCard(), self::SANDBOX_PAY_SUBMIT_URL);
    }

    protected function getTerminal(): PaytureInPayTerminalInterface
    {
        return $this->terminal;
    }
}
