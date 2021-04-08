<?php

namespace Lamoda\Payture\InPayClient;

final class TerminalResponse
{
    /**
     * Payture error codes.
     *
     * @see https://payture.com/api#error-codes_
     */
    public const ERROR_NONE = 'NONE';
    public const ERROR_ORDER_TIME_OUT = 'ORDER_TIME_OUT';
    public const ERROR_ILLEGAL_ORDER_STATE = 'ILLEGAL_ORDER_STATE';
    public const ERROR_PROCESSING = 'PROCESSING_ERROR';
    public const ERROR_ISSUER_FAIL = 'ISSUER_FAIL';
    public const ERROR_AMOUNT = 'AMOUNT_ERROR';
    public const ERROR_PROCESSING_FRAUD = 'PROCESSING_FRAUD_ERROR';
    public const ERROR_ISSUER_BLOCKED_CARD = 'ISSUER_BLOCKED_CARD';

    private const STATUS_SUCCESS = 'True';

    private const STATE_NEW = 'New';
    private const STATE_PREAUTH_3DS = 'PreAuthorized3DS';
    private const STATE_PREAUTH_AF = 'PreAuthorizedAF';
    private const STATE_AUTHORIZED = 'Authorized';
    private const STATE_VOIDED = 'Voided'; // locked and unlocked
    private const STATE_CHARGED = 'Charged';
    private const STATE_REFUNDED = 'Refunded';
    private const STATE_FORWARDED = 'Forwarded';
    private const STATE_ERROR = 'Error';

    // custom status, for case with error code NONE
    private const STATE_PENDING = 'Pending';

    /**
     * Operation success flag.
     *
     * @var bool
     */
    private $success;

    /**
     * Payment ID in Merchant system.
     *
     * @var string
     */
    private $orderId;

    /**
     * Operation amount.
     *
     * @var int
     */
    private $amount = 0;

    /**
     * Payment status.
     *
     * @var string|null
     */
    private $state;

    /**
     * Payment ID in Payture system.
     *
     * @var string
     */
    private $sessionId = '';

    /**
     * Unique transaction number assigned by the acquiring bank
     *
     * @var string|null
     */
    private $rrn;

    /** Error code.
     *
     * @var string
     */
    private $errorCode = '';

    /**
     * @param string $success Operation success flag
     * @param mixed $orderId Payment ID in Merchant system
     */
    public function __construct(string $success, string $orderId)
    {
        $this->success = mb_strtolower($success) === mb_strtolower(static::STATUS_SUCCESS);
        $this->orderId = $orderId;
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getOrderId(): string
    {
        return $this->orderId;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): void
    {
        $this->amount = $amount;
    }

    public function getRrn(): ?string
    {
        return $this->rrn;
    }

    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    public function setSessionId(string $sessionId): void
    {
        $this->sessionId = $sessionId;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode ?: 'ErrCode undefined';
    }

    public function setErrorCode(string $errorCode): void
    {
        if ($errorCode === self::ERROR_NONE) {
            /* Payture hack
             * If error code equal NONE, operation was completed without errors!?
             * @link http://payture.com/integration/api/#error-codes_
             */
            $this->success = true;
            $this->errorCode = '';
            $this->state = self::STATE_PENDING;

            return;
        }

        $this->success = false;
        $this->errorCode = $errorCode;
    }

    public function setState(string $state): void
    {
        $this->state = $state;
    }

    public function setRrn(string $rrn): void
    {
        $this->rrn = $rrn;
    }

    public function isNewState(): bool
    {
        return $this->isStateEqual(static::STATE_NEW);
    }

    public function isPreAuthorized3DSState(): bool
    {
        return $this->isStateEqual(static::STATE_PREAUTH_3DS);
    }

    public function isPreAuthorizedAFState(): bool
    {
        return $this->isStateEqual(static::STATE_PREAUTH_AF);
    }

    public function isAuthorizedState(): bool
    {
        return $this->isStateEqual(static::STATE_AUTHORIZED);
    }

    public function isVoidedState(): bool
    {
        return $this->isStateEqual(static::STATE_VOIDED);
    }

    public function isChargedState(): bool
    {
        return $this->isStateEqual(static::STATE_CHARGED);
    }

    public function isRefundedState(): bool
    {
        return $this->isStateEqual(static::STATE_REFUNDED);
    }

    public function isForwardedState(): bool
    {
        return $this->isStateEqual(static::STATE_FORWARDED);
    }

    public function isErrorState(): bool
    {
        return $this->isStateEqual(static::STATE_ERROR);
    }

    public function isTimeout(): bool
    {
        return !$this->success && $this->getErrorCode() === static::ERROR_ORDER_TIME_OUT;
    }

    public function isIllegalOrderState(): bool
    {
        return !$this->success && $this->getErrorCode() === static::ERROR_ILLEGAL_ORDER_STATE;
    }

    public function isProcessingError(): bool
    {
        return !$this->success && $this->getErrorCode() === self::ERROR_PROCESSING;
    }

    public function isFraudError(): bool
    {
        return $this->getErrorCode() === self::ERROR_PROCESSING_FRAUD;
    }

    public function isAmountError(): bool
    {
        return $this->getErrorCode() === self::ERROR_AMOUNT;
    }

    public function isIssuerFail(): bool
    {
        return !$this->success && $this->getErrorCode() === self::ERROR_ISSUER_FAIL;
    }

    private function isStateEqual($expectedState): bool
    {
        return mb_strtolower($this->state) === mb_strtolower($expectedState);
    }
}
