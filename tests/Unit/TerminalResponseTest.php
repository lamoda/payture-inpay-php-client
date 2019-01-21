<?php

namespace Lamoda\Payture\InPayClient\Tests\Unit;

use Lamoda\Payture\InPayClient\TerminalResponse;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Lamoda\Payture\InPayClient\TerminalResponse
 */
final class TerminalResponseTest extends TestCase
{
    public function testSettersAndGetters(): void
    {
        $response = new TerminalResponse('True', 'Ord-123');
        self::assertTrue($response->isSuccess());
        self::assertEquals('Ord-123', $response->getOrderId());

        $response->setAmount(10000);
        self::assertEquals(10000, $response->getAmount());

        $response->setSessionId('external-id');
        self::assertEquals('external-id', $response->getSessionId());

        $response->setErrorCode(TerminalResponse::ERROR_NONE);
        self::assertEquals('ErrCode undefined', $response->getErrorCode());

        $response->setErrorCode(TerminalResponse::ERROR_ILLEGAL_ORDER_STATE);
        self::assertEquals(TerminalResponse::ERROR_ILLEGAL_ORDER_STATE, $response->getErrorCode());
    }

    public function testStateAccessors(): void
    {
        $response = new TerminalResponse('True', 'Ord-123');

        $response->setState('New');
        self::assertTrue($response->isNewState());

        $response->setState('PreAuthorized3DS');
        self::assertTrue($response->isPreAuthorized3DSState());

        $response->setState('PreAuthorizedAF');
        self::assertTrue($response->isPreAuthorizedAFState());

        $response->setState('Authorized');
        self::assertTrue($response->isAuthorizedState());

        $response->setState('Voided');
        self::assertTrue($response->isVoidedState());

        $response->setState('Charged');
        self::assertTrue($response->isChargedState());

        $response->setState('Refunded');
        self::assertTrue($response->isRefundedState());

        $response->setState('Forwarded');
        self::assertTrue($response->isForwardedState());

        $response->setState('Error');
        self::assertTrue($response->isErrorState());
    }

    public function testErrorCodeAccessorsForSuccessfulResponse(): void
    {
        $response = new TerminalResponse('True', 'Ord-123');

        $response->setErrorCode(TerminalResponse::ERROR_PROCESSING_FRAUD);
        self::assertTrue($response->isFraudError());

        $response->setErrorCode(TerminalResponse::ERROR_AMOUNT);
        self::assertTrue($response->isAmountError());
    }

    public function testErrorCodeAccessorsForFailedResponse(): void
    {
        $response = new TerminalResponse('False', 'Ord-123');

        $response->setErrorCode(TerminalResponse::ERROR_ORDER_TIME_OUT);
        self::assertTrue($response->isTimeout());

        $response->setErrorCode(TerminalResponse::ERROR_ILLEGAL_ORDER_STATE);
        self::assertTrue($response->isIllegalOrderState());

        $response->setErrorCode(TerminalResponse::ERROR_PROCESSING);
        self::assertTrue($response->isProcessingError());

        $response->setErrorCode(TerminalResponse::ERROR_ISSUER_FAIL);
        self::assertTrue($response->isIssuerFail());
    }
}
