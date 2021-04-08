<?php

namespace Lamoda\Payture\InPayClient;

use Lamoda\Payture\InPayClient\Exception\TransportException;

interface PaytureInPayTerminalInterface
{
    /**
     * @see https://payture.com/api/#inpay_getstate_
     *
     * @param string $orderId Payment ID in Merchant system
     */
    public function getState(string $orderId): TerminalResponse;

    /**
     * @deprecated
     * @see PaytureInPayTerminalInterface::getState()
     * @see https://payture.com/api#inpay_paystatus_
     *
     * @param string $orderId Payment ID in Merchant system
     *
     * @throws TransportException
     */
    public function payStatus(string $orderId): TerminalResponse;

    /**
     * @see https://payture.com/api#inpay_init_
     *
     * @param string $orderId Payment ID in Merchant system
     * @param int $amount Payment amount
     * @param string $clientIp User IP address
     * @param string $url back URL
     * @param string $templateTag Used template tag. If empty string - no template tag will be passed
     * @param array $extra Payture none requirement extra fields
     *
     * @throws TransportException
     */
    public function init(
        SessionType $sessionType,
        string $orderId,
        string $product,
        int $amount,
        string $clientIp,
        string $url,
        string $templateTag = '',
        array $extra = []
    ): TerminalResponse;

    public function createPaymentUrl(string $sessionId): string;

    /**
     * @see https://payture.com/api#inpay_unblock_
     *
     * @param string $orderId Payment ID in Merchant system
     * @param int $amount Amount in kopecks that is to be returned
     *
     * @throws TransportException
     */
    public function unblock(string $orderId, int $amount): TerminalResponse;

    /**
     * @see https://payture.com/api#inpay_charge_
     *
     * @param string $orderId Payment ID in Merchant system
     * @param int $amount Charging amount in kopecks
     *
     * @throws TransportException
     */
    public function charge(string $orderId, int $amount): TerminalResponse;

    /**
     * The request is used both in one-step and two-step payment schemes.
     *
     * @see https://payture.com/api#inpay_refund_
     *
     * @param string $orderId Payment ID in Merchant system
     * @param int $amount Amount in kopecks that is to be returned
     *
     * @throws TransportException
     */
    public function refund(string $orderId, int $amount): TerminalResponse;
}
