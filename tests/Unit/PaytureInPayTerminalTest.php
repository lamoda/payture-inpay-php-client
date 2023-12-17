<?php

namespace Lamoda\Payture\InPayClient\Tests\Unit;

use Lamoda\Payture\InPayClient\PaytureInPayTerminal;
use Lamoda\Payture\InPayClient\PaytureOperation;
use Lamoda\Payture\InPayClient\SessionType;
use Lamoda\Payture\InPayClient\TerminalConfiguration;
use Lamoda\Payture\InPayClient\TransportInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Lamoda\Payture\InPayClient\PaytureInPayTerminal
 */
final class PaytureInPayTerminalTest extends TestCase
{
    private $config;
    private $transport;

    /** @var PaytureInPayTerminal */
    private $terminal;

    /**
     * @dataProvider getInitSessionTypes
     *
     * @throws \Lamoda\Payture\InPayClient\Exception\TransportException
     */
    public function testPaymentInit(SessionType $type, string $data): void
    {
        $this->transport->expects($this->once())
            ->method('request')
            ->with(
                PaytureOperation::INIT(),
                'apim',
                [
                    'Key' => 'MerchantKey',
                    'Data' => $data,
                ]
            )->willReturn('<Init Success="True" SessionId="external-id"/>');

        $response = $this->terminal->init(
            $type,
            'Order-123',
            'The order',
            10000,
            '127.0.0.1',
            'https://redirect-me.back/',
            'template',
            [
                'custom_data' => 'value',
            ]
        );

        self::assertTrue($response->isSuccess());
        self::assertEquals('external-id', $response->getSessionId());
    }

    public function getInitSessionTypes()
    {
        return [
            [
                SessionType::PAY(),
                'SessionType=Pay;OrderId=Order-123;Amount=10000;IP=127.0.0.1;Product=The+order;Url=https%3A%2F%2Fredirect-me.back%2F;TemplateTag=template;custom_data=value',
            ],
            [
                SessionType::BLOCK(),
                'SessionType=Block;OrderId=Order-123;Amount=10000;IP=127.0.0.1;Product=The+order;Url=https%3A%2F%2Fredirect-me.back%2F;TemplateTag=template;custom_data=value',
            ],
        ];
    }

    public function testPaymentCharge(): void
    {
        $this->transport->expects($this->once())
            ->method('request')
            ->with(
                PaytureOperation::CHARGE(),
                'apim',
                [
                    'Key' => 'MerchantKey',
                    'OrderId' => 'Order-123',
                    'Amount' => 10000,
                    'Password' => 'MerchantPassword',
                ]
            )->willReturn('<Charge Success="True" Amount="10000"/>');

        $response = $this->terminal->charge('Order-123', 10000);

        self::assertTrue($response->isSuccess());
    }

    public function testPaymentUnblock(): void
    {
        $this->transport->expects($this->once())
            ->method('request')
            ->with(
                PaytureOperation::UNBLOCK(),
                'apim',
                [
                    'Key' => 'MerchantKey',
                    'OrderId' => 'Order-123',
                    'Amount' => 10000,
                    'Password' => 'MerchantPassword',
                ]
            )->willReturn('<Unblock Success="True"/>');

        $response = $this->terminal->unblock('Order-123', 10000);

        self::assertTrue($response->isSuccess());
    }

    public function testPaymentRefund(): void
    {
        $this->transport->expects($this->once())
            ->method('request')
            ->with(
                PaytureOperation::REFUND(),
                'apim',
                [
                    'Key' => 'MerchantKey',
                    'OrderId' => 'Order-123',
                    'Amount' => 6000,
                    'Password' => 'MerchantPassword',
                ]
            )->willReturn('<Refund Success="True" NewAmount="4000"/>');

        $response = $this->terminal->refund('Order-123', 6000);

        self::assertTrue($response->isSuccess());
        self::assertEquals(4000, $response->getAmount());
    }

    public function testPaymentStatus(): void
    {
        $this->transport->expects($this->once())
            ->method('request')
            ->with(
                PaytureOperation::PAY_STATUS(),
                'apim',
                [
                    'Key' => 'MerchantKey',
                    'OrderId' => 'Order-123',
                ]
            )->willReturn('<PayStatus Success="True" State="Charged" Amount="10000"/>');

        $response = $this->terminal->payStatus('Order-123');

        self::assertTrue($response->isSuccess());
        self::assertTrue($response->isChargedState());
    }

    public function testGetState(): void
    {
        $rrn = '003770024290';
        $orderId = 'Order-123';
        $this->transport->expects($this->once())
            ->method('request')
            ->with(
                PaytureOperation::GET_STATE(),
                'apim',
                [
                    'Key' => 'MerchantKey',
                    'OrderId' => $orderId,
                ]
            )->willReturn('<GetState Success="True" OrderId="' . $orderId . '" State="Refunded"
                Forwarded="False" MerchantContract="Merchant" Amount="12461" RRN="' . $rrn . '"/>');

        $response = $this->terminal->getState($orderId);

        self::assertTrue($response->isSuccess());
        self::assertTrue($response->isRefundedState());
        self::assertEquals($rrn, $response->getRrn());
        self::assertEquals($orderId, $response->getOrderId());
    }

    public function testGetStateWithAdditionalInfo(): void
    {
        $rrn = '003770024290';
        $orderId = 'Order-123';
        $this->transport->expects($this->once())
            ->method('request')
            ->with(
                PaytureOperation::GET_STATE(),
                'apim',
                [
                    'Key' => 'MerchantKey',
                    'OrderId' => $orderId,
                ]
            )->willReturn('<GetState Success="True" OrderId="'.$orderId.'" State="Refunded" 
                Forwarded="False" MerchantContract="Merchant" Amount="12464" RRN="'.$rrn.'" VWUserLgn="123@ya.ru" 
                CardId="bd712147-48da-2ffc-ef31-8341806c65cf" PANMask="521885xxxxxx5484">
                <AddInfo Key="PaymentSystem" Value="MasterCard" />
                <AddInfo Key="BankHumanName" Value="SBERBANK" />
                <AddInfo Key="BankCountryCode" Value="RU" />
                <AddInfo Key="BankCity" Value="" />
                </GetState>');

        $response = $this->terminal->getState($orderId);

        self::assertTrue($response->isSuccess());
        self::assertTrue($response->isRefundedState());
        self::assertEquals($rrn, $response->getRrn());
        self::assertEquals($orderId, $response->getOrderId());
        self::assertEquals([
            'BankCity' => '',
            'BankHumanName' => 'SBERBANK',
            'PaymentSystem' => 'MasterCard',
            'BankCountryCode' => 'RU'
        ], $response->getAdditionalInfo());
    }

    public function testCreatingPaymentUrl(): void
    {
        self::assertEquals(
            'https://nowhere.payture.com/apim/Pay?SessionId=external-id',
            $this->terminal->createPaymentUrl('external-id')
        );
    }

    protected function setUp()
    {
        $this->config = new TerminalConfiguration('MerchantKey', 'MerchantPassword', 'https://nowhere.payture.com/');
        $this->transport = $this->createMock(TransportInterface::class);

        $this->terminal = new PaytureInPayTerminal($this->config, $this->transport);
    }
}
