<?php

namespace Lamoda\Payture\InPayClient\Tests\Unit;

use Lamoda\Payture\InPayClient\PaytureOperation;
use Lamoda\Payture\InPayClient\TerminalResponse;
use Lamoda\Payture\InPayClient\TerminalResponseBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Lamoda\Payture\InPayClient\TerminalResponseBuilder
 */
final class TerminalResponseBuilderTest extends TestCase
{
    /**
     * @dataProvider getValidResponseExamples
     *
     * @param string $xml
     * @param PaytureOperation $operation
     * @param bool $success
     *
     * @throws \Lamoda\Payture\InPayClient\Exception\InvalidResponseException
     */
    public function testBuilderParsesXmlStringIntoResponse(
        string $xml,
        PaytureOperation $operation,
        bool $success
    ): void {
        $response = TerminalResponseBuilder::parseTransportResponse($xml, $operation);
        self::assertEquals($success, $response->isSuccess());
    }

    public function getValidResponseExamples(): array
    {
        return [
            'Init' => [
                '<Init Success="true" OrderId="" Amount="" SessionId="external-id" />',
                PaytureOperation::INIT(),
                true,
            ],
            'Charge success' => [
                '<Charge Success="True" OrderId="nw9z5rl8hkhhpfbb4ual7w" Amount="12394" />',
                PaytureOperation::CHARGE(),
                true,
            ],
            'Charge failure' => [
                '<Charge Success="False" OrderId="nw9z5rl8hkhhpfbb4ual7w" Amount="0" ErrCode="ILLEGAL_ORDER_STATE" />',
                PaytureOperation::CHARGE(),
                false,
            ],
            'Unblock success' => [
                '<Unblock Success="True" OrderId="nw9z5rl8hkhhpfbb4ual7w" NewAmount="0" />',
                PaytureOperation::UNBLOCK(),
                true,
            ],
            'Unblock failure' => [
                '<Unblock Success="False" OrderId="nw9z5rl8hkhhpfbb4ual7w" ErrCode="ILLEGAL_ORDER_STATE" />',
                PaytureOperation::UNBLOCK(),
                false,
            ],
            'Refund success' => [
                '<Refund Success="True" OrderId="nw9z5rl8hkhhpfbb4ual7w" NewAmount="2000" />',
                PaytureOperation::REFUND(),
                true,
            ],
            'Refund failure' => [
                '<Refund Success="False" OrderId="nw9z5rl8hkhhpfbb4ual7w" ErrCode="AMOUNT_ERROR" />',
                PaytureOperation::REFUND(),
                false,
            ],
            'PayStatus' => [
                '<PayStatus Success="True" OrderId="nw9z5rl8hkhhpfbb4ual7w" Amount="2000" State="Charged" />',
                PaytureOperation::PAY_STATUS(),
                true,
            ],
            'GetState' => [
                '<GetState Success="True" OrderId="nw9z5rl8hkhhpfbb4ual7w" Amount="2000" State="Charged"
                    RRN="003770024290"/>',
                PaytureOperation::GET_STATE(),
                true,
            ],
        ];
    }

    /**
     * @dataProvider getPopulatedFieldExamples
     *
     * @param string $xml
     * @param string $accessMethod
     * @param mixed $expectedValue
     *
     * @throws \Lamoda\Payture\InPayClient\Exception\InvalidResponseException
     */
    public function testBuilderPopulatesResponseFields(string $xml, string $accessMethod, $expectedValue): void
    {
        $response = TerminalResponseBuilder::parseTransportResponse($xml, PaytureOperation::CHARGE());
        self::assertEquals($expectedValue, $response->$accessMethod());
    }

    public function getPopulatedFieldExamples(): array
    {
        return [
            'SessionId' => [
                '<Charge Success="False" SessionId="external-id" />',
                'getSessionId',
                'external-id',
            ],
            'ErrCode' => [
                '<Charge Success="False" ErrCode="ILLEGAL_ORDER_STATE" />',
                'getErrorCode',
                TerminalResponse::ERROR_ILLEGAL_ORDER_STATE,
            ],
            'State' => [
                '<Charge Success="True" State="CHARGED" />',
                'isChargedState',
                true,
            ],
            'Amount' => [
                '<Charge Success="True" Amount="10000" />',
                'getAmount',
                10000,
            ],
            'NewAmount' => [
                '<Charge Success="True" NewAmount="10000" />',
                'getAmount',
                10000,
            ],
            'RRN' => [
                '<Charge Success="True" RRN="003770024290" />',
                'getRrn',
                '003770024290',
            ],
        ];
    }

    /**
     * @expectedException \Lamoda\Payture\InPayClient\Exception\InvalidResponseException
     */
    public function testBuilderThrowsExceptionForInvalidXml(): void
    {
        TerminalResponseBuilder::parseTransportResponse('Definitely not an XML string', PaytureOperation::INIT());
    }

    /**
     * @expectedException \Lamoda\Payture\InPayClient\Exception\InvalidResponseException
     */
    public function testBuilderThrowsExceptionForOperationMismatch(): void
    {
        TerminalResponseBuilder::parseTransportResponse('<Charge Success="True"/>', PaytureOperation::INIT());
    }

    /**
     * @expectedException \Lamoda\Payture\InPayClient\Exception\InvalidResponseException
     */
    public function testBuildThrowsExceptionIfNoAttributeDefined(): void
    {
        TerminalResponseBuilder::parseTransportResponse('<Charge/>', PaytureOperation::CHARGE());
    }

    /**
     * @expectedException \Lamoda\Payture\InPayClient\Exception\InvalidResponseException
     */
    public function testBuildThrowsExceptionIfNoSuccessAttributeDefined(): void
    {
        TerminalResponseBuilder::parseTransportResponse('<Charge Amount="10000"/>', PaytureOperation::CHARGE());
    }
}
