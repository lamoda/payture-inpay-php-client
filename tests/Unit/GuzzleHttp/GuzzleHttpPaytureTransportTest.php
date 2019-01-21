<?php

namespace Lamoda\Payture\InPayClient\Tests\Unit\GuzzleHttp;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Psr7\Response;
use Lamoda\Payture\InPayClient\GuzzleHttp\GuzzleHttpPaytureTransport;
use Lamoda\Payture\InPayClient\PaytureOperation;
use Lamoda\Payture\InPayClient\TerminalConfiguration;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @covers \Lamoda\Payture\InPayClient\GuzzleHttp\GuzzleHttpPaytureTransport
 */
final class GuzzleHttpPaytureTransportTest extends TestCase
{
    public function testOperationExecution(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $logger = $this->createMock(LoggerInterface::class);
        $config = new TerminalConfiguration('MerchantKey', 'MerchantPassword', 'https://nowhere.payture.com/');

        $transport = new GuzzleHttpPaytureTransport($client, $config, null, $logger);

        $response = '<Init Success="True"/>';
        $client->expects($this->once())
            ->method('request')
            ->with('GET', 'https://nowhere.payture.com/apim/Init?', [])
            ->willReturn(new Response(200, [], $response));

        $logger->expects($this->once())
            ->method('info');

        self::assertEquals($response, $transport->request(PaytureOperation::INIT(), 'apim', []));
    }

    /**
     * @expectedException \Lamoda\Payture\InPayClient\Exception\TransportException
     */
    public function testTransportConvertsGuzzleExceptionToTransportException(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $config = new TerminalConfiguration('MerchantKey', 'MerchantPassword', 'https://nowhere.payture.com/');

        $transport = new GuzzleHttpPaytureTransport($client, $config);

        $client->expects($this->once())
            ->method('request')
            ->with('GET', 'https://nowhere.payture.com/apim/Init?', [])
            ->willThrowException(new TransferException('Request failed'));

        $transport->request(PaytureOperation::INIT(), 'apim', []);
    }
}
