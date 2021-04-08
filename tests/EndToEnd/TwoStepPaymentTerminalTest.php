<?php

namespace Lamoda\Payture\InPayClient\Tests\EndToEnd;

use Lamoda\Payture\InPayClient\SessionType;
use Lamoda\Payture\InPayClient\TerminalResponse;

/**
 * @coversNothing
 */
final class TwoStepPaymentTerminalTest extends AbstractTerminalTestCase
{
    private const ORDER_PRICE = 10000;

    /**
     * Keep the test suite with deprecated payStatus.
     */
    public function testPaytureInPayApiWithPayStatus(): void
    {
        $orderId = self::generateOrderId();

        $response = $this->initPayment($orderId);
        $sessionId = $response->getSessionId();

        $response = $this->getTerminal()->payStatus($orderId);
        self::assertTrue($response->isSuccess());

        $url = $this->getTerminal()->createPaymentUrl($sessionId);

        $this->pay($url, $orderId, self::ORDER_PRICE);
        $response = $this->getTerminal()->payStatus($orderId);
        self::assertTrue($response->isAuthorizedState());

        $response = $this->getTerminal()->charge($orderId, self::ORDER_PRICE);
        self::assertTrue($response->isSuccess());
        $response = $this->getTerminal()->payStatus($orderId);
        self::assertTrue($response->isChargedState());

        $response = $this->getTerminal()->refund($orderId, self::ORDER_PRICE);
        self::assertTrue($response->isSuccess());
        $response = $this->getTerminal()->payStatus($orderId);
        self::assertTrue($response->isRefundedState());
        self::assertNotEmpty($response->getRrn());
    }

    public function testPaytureInPayApi(): void
    {
        $orderId = self::generateOrderId();

        $response = $this->initPayment($orderId);
        $sessionId = $response->getSessionId();

        $url = $this->getTerminal()->createPaymentUrl($sessionId);

        $this->pay($url, $orderId, self::ORDER_PRICE);
        $response = $this->getTerminal()->getState($orderId);
        self::assertNotEmpty($response->getRrn());
        self::assertTrue($response->isAuthorizedState());

        $response = $this->getTerminal()->charge($orderId, self::ORDER_PRICE);
        self::assertTrue($response->isSuccess());
        $response = $this->getTerminal()->getState($orderId);
        self::assertTrue($response->isChargedState());
        self::assertNotEmpty($response->getRrn());

        $response = $this->getTerminal()->refund($orderId, self::ORDER_PRICE);
        self::assertTrue($response->isSuccess());
        $response = $this->getTerminal()->getState($orderId);
        self::assertTrue($response->isRefundedState());
        self::assertNotEmpty($response->getRrn());
    }

    private function initPayment(string $orderId): TerminalResponse
    {
        return $this->getTerminal()->init(
            SessionType::BLOCK(),
            $orderId,
            'Auto Test purchase',
            self::ORDER_PRICE,
            '127.0.0.1',
            'https://github.com/lamoda'
        );
    }
}
