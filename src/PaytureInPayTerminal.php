<?php

namespace Lamoda\Payture\InPayClient;

use Lamoda\Payture\InPayClient\Exception\TransportException;

final class PaytureInPayTerminal implements PaytureInPayTerminalInterface
{
    private const API_PREFIX = 'apim';

    /** @var TerminalConfiguration */
    private $config;

    /** @var TransportInterface */
    private $transport;

    public function __construct(TerminalConfiguration $config, TransportInterface $transport)
    {
        $this->config = $config;
        $this->transport = $transport;
    }

    private static function mapSessionType(SessionType $sessionType): string
    {
        switch ((string) $sessionType) {
            case (string) SessionType::PAY():
                return 'Pay';
            case (string) SessionType::BLOCK():
                return 'Block';
        }

        // @codeCoverageIgnoreStart
        throw new \LogicException('Unknown session type');
        // @codeCoverageIgnoreEnd
    }

    /**
     * @link https://payture.com/api#inpay_init_
     *
     * @param SessionType $sessionType
     * @param string $orderId Payment ID in Merchant system
     * @param string $product
     * @param int $amount Payment amount
     * @param string $clientIp User IP address
     * @param string $url back URL
     * @param string $templateTag Used template tag. If empty string - no template tag will be passed
     * @param array $extra Payture none requirement extra fields
     *
     * @return TerminalResponse
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
    ): TerminalResponse {
        $data = [
            'SessionType' => self::mapSessionType($sessionType),
            'OrderId' => $orderId,
            'Amount' => $amount,
            'IP' => $clientIp,
            'Product' => $product,
            'Url' => $url,
        ];

        if ($templateTag !== '') {
            $data['TemplateTag'] = $templateTag;
        }

        if (\count($extra)) {
            $data = array_merge($data, $extra);
        }

        $urlParams = [
            'Key' => $this->config->getKey(),
            'Data' => http_build_query($data, '', ';'),
        ];

        return $this->sendRequest(PaytureOperation::INIT(), $urlParams);
    }

    /**
     * @link https://payture.com/api#inpay_charge_
     *
     * @param string $orderId Payment ID in Merchant system
     * @param int $amount Charging amount in kopecks
     *
     * @return TerminalResponse
     *
     * @throws TransportException
     */
    public function charge(string $orderId, int $amount): TerminalResponse
    {
        $data = [
            'Key' => $this->config->getKey(),
            'Password' => $this->config->getPassword(),
            'OrderId' => $orderId,
            'Amount' => $amount,
        ];

        return $this->sendRequest(PaytureOperation::CHARGE(), $data);
    }

    /**
     * Perform partial or full amount unblock for Block session type.
     *
     * @link https://payture.com/api#inpay_unblock_
     *
     * @param string $orderId Payment ID in Merchant system
     * @param int $amount Amount in kopecks that is to be returned
     *
     * @return TerminalResponse
     *
     * @throws TransportException
     */
    public function unblock(string $orderId, int $amount): TerminalResponse
    {
        $data = [
            'Key' => $this->config->getKey(),
            'Password' => $this->config->getPassword(),
            'OrderId' => $orderId,
            'Amount' => $amount,
        ];

        return $this->sendRequest(PaytureOperation::UNBLOCK(), $data);
    }

    /**
     * Create new deal to refund given Amount from the OrderId.
     *
     * @link https://payture.com/api#inpay_refund_
     *
     * @param string $orderId Payment ID in Merchant system
     * @param int $amount Amount in kopecks that is to be returned
     *
     * @return TerminalResponse
     *
     * @throws TransportException
     */
    public function refund(string $orderId, int $amount): TerminalResponse
    {
        $data = [
            'Key' => $this->config->getKey(),
            'Password' => $this->config->getPassword(),
            'OrderId' => $orderId,
            'Amount' => $amount,
        ];

        return $this->sendRequest(PaytureOperation::REFUND(), $data);
    }

    /**
     * Get status of the last deal for the OrderId.
     *
     * @link https://payture.com/api#inpay_paystatus_
     *
     * @param string $orderId Payment ID in Merchant system
     *
     * @return TerminalResponse
     *
     * @throws TransportException
     */
    public function payStatus(string $orderId): TerminalResponse
    {
        $data = [
            'Key' => $this->config->getKey(),
            'OrderId' => $orderId,
        ];

        return $this->sendRequest(PaytureOperation::PAY_STATUS(), $data);
    }

    public function createPaymentUrl(string $sessionId): string
    {
        $data = [
            'SessionId' => $sessionId,
        ];

        return $this->config->buildOperationUrl(PaytureOperation::PAY(), self::API_PREFIX, $data);
    }

    /**
     * @param PaytureOperation $operation
     * @param array $parameters
     *
     * @return TerminalResponse
     *
     * @throws TransportException
     */
    private function sendRequest(PaytureOperation $operation, array $parameters): TerminalResponse
    {
        $transportResponse = $this->transport->request($operation, self::API_PREFIX, $parameters);

        return TerminalResponseBuilder::parseTransportResponse($transportResponse, $operation);
    }
}
