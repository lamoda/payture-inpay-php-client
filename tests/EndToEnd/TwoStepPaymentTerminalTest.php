<?php

namespace Lamoda\Payture\InPayClient\Tests\EndToEnd;

use Lamoda\Payture\InPayClient\SessionType;

/**
 * @coversNothing
 */
final class TwoStepPaymentTerminalTest extends AbstractTerminalTestCase
{
    private const ORDER_PRICE = 10000;

    public function testPaytureInPayApi(): void
    {
        $orderId = self::generateOrderId();

        $response = $this->getTerminal()->init(
            SessionType::BLOCK(),
            $orderId,
            'Auto Test purchase',
            self::ORDER_PRICE,
            '127.0.0.1',
            'https://github.com/lamoda'
        );
        $sessionId = $response->getSessionId();

        $response = $this->getTerminal()->getState($orderId);
        self::assertTrue($response->isSuccess());

        $url = $this->getTerminal()->createPaymentUrl($sessionId);

        $this->pay($url, $orderId, self::ORDER_PRICE);
        $response = $this->getTerminal()->getState($orderId);
        self::assertTrue($response->isAuthorizedState());

        $response = $this->getTerminal()->charge($orderId, self::ORDER_PRICE);
        self::assertTrue($response->isSuccess());
        $response = $this->getTerminal()->getState($orderId);
        self::assertTrue($response->isChargedState());

        $response = $this->getTerminal()->refund($orderId, self::ORDER_PRICE);
        self::assertTrue($response->isSuccess());
        $response = $this->getTerminal()->getState($orderId);
        self::assertTrue($response->isRefundedState());
    }
}
