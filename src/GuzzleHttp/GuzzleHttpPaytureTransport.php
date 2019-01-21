<?php

namespace Lamoda\Payture\InPayClient\GuzzleHttp;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Lamoda\Payture\InPayClient\Exception\TransportException;
use Lamoda\Payture\InPayClient\PaytureOperation;
use Lamoda\Payture\InPayClient\TerminalConfiguration;
use Lamoda\Payture\InPayClient\TransportInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class GuzzleHttpPaytureTransport implements TransportInterface
{
    /** @var ClientInterface */
    private $client;

    /** @var TerminalConfiguration */
    private $config;
    /**
     * @var LoggerInterface|null
     */
    private $logger;
    /**
     * @var GuzzleHttpOptionsBag
     */
    private $optionsBag;

    /**
     * @param ClientInterface $client
     * @param TerminalConfiguration $config
     * @param GuzzleHttpOptionsBag $optionsBag
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        ClientInterface $client,
        TerminalConfiguration $config,
        GuzzleHttpOptionsBag $optionsBag = null,
        LoggerInterface $logger = null
    ) {
        $this->client = $client;
        $this->config = $config;
        $this->optionsBag = $optionsBag ?: new GuzzleHttpOptionsBag();
        $this->logger = $logger ?: new NullLogger();
    }

    /** {@inheritdoc} */
    public function request(PaytureOperation $operation, string $interface, array $parameters): string
    {
        $this->logger->info(
            'Executing Payture InPay operation',
            [
                'interface' => $interface,
                'url' => $this->config->getUrl(),
                'operation' => (string) $operation,
                'url_params' => $parameters,
            ]
        );
        try {
            return $this->client->request(
                'GET',
                $this->config->buildOperationUrl($operation, $interface, $parameters),
                $this->optionsBag->getOperationOptions($operation)
            )->getBody();
        } catch (GuzzleException $e) {
            throw TransportException::becauseUnderlyingTransportFailed($e);
        }
    }
}
