<?php

namespace Lamoda\Payture\InPayClient\Tests\Unit;

use Lamoda\Payture\InPayClient\PaytureOperation;
use Lamoda\Payture\InPayClient\TerminalConfiguration;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Lamoda\Payture\InPayClient\TerminalConfiguration
 */
final class TerminalConfigurationTest extends TestCase
{
    public function testValidConfig(): void
    {
        $config = new TerminalConfiguration('secret', 'pass', 'http://nowhere.payture.com');

        $this->assertInstanceOf(TerminalConfiguration::class, $config);

        $this->assertEquals('secret', $config->getKey());
        $this->assertEquals('http://nowhere.payture.com/', $config->getUrl());
        $this->assertEquals('pass', $config->getPassword());
    }

    /**
     * @dataProvider notValidConfigVariants
     *
     * @param array $options
     * @param string $exception
     * @param string $message
     */
    public function testNotValidConfig(array $options, string $exception, string $message): void
    {
        $this->expectExceptionMessage($message);
        $this->expectException($exception);

        new TerminalConfiguration($options['key'], $options['password'], $options['url']);
    }

    public function notValidConfigVariants(): array
    {
        return [
            // required validation
            [
                [
                    'key' => '',
                    'url' => 'test',
                    'password' => 'test',
                ],
                \InvalidArgumentException::class,
                'Empty terminal Key provided',
            ],
            [
                [
                    'key' => 'test',
                    'url' => '',
                    'password' => 'test',
                ],
                \InvalidArgumentException::class,
                'Invalid URL provided',
            ],
            [
                [
                    'key' => 'test',
                    'url' => 'test',
                    'password' => '',
                ],
                \InvalidArgumentException::class,
                'Empty terminal Password provided',
            ],
            // format validation
            [
                [
                    'key' => 'secret',
                    'url' => 'payture.com',
                    'password' => 'pass',
                ],
                \InvalidArgumentException::class,
                'Invalid URL provided',
            ],
        ];
    }

    /**
     * @dataProvider getOperationUrlProviders
     *
     * @param PaytureOperation $operation
     * @param array $parameters
     * @param string $expectedUrl
     */
    public function testBuildingOperationUrl(PaytureOperation $operation, array $parameters, string $expectedUrl): void
    {
        $configuration = new TerminalConfiguration('MerchantKey', 'MerchantPassword', 'https://nowhere.payture.com/');
        self::assertSame($expectedUrl, $configuration->buildOperationUrl($operation, 'apim', $parameters));
    }

    public function getOperationUrlProviders(): array
    {
        return [
            [
                PaytureOperation::INIT(),
                ['Key' => 'MerchantKey', 'Data' => 'SomeData'],
                'https://nowhere.payture.com/apim/Init?Key=MerchantKey&Data=SomeData',
            ],
            [
                PaytureOperation::PAY(),
                ['SessionId' => 'external-id'],
                'https://nowhere.payture.com/apim/Pay?SessionId=external-id',
            ],
            [
                PaytureOperation::CHARGE(),
                ['Key' => 'MerchantKey', 'Data' => 'SomeData'],
                'https://nowhere.payture.com/apim/Charge?Key=MerchantKey&Data=SomeData',
            ],
            [
                PaytureOperation::REFUND(),
                ['Key' => 'MerchantKey', 'Data' => 'SomeData'],
                'https://nowhere.payture.com/apim/Refund?Key=MerchantKey&Data=SomeData',
            ],
            [
                PaytureOperation::UNBLOCK(),
                ['Key' => 'MerchantKey', 'Data' => 'SomeData'],
                'https://nowhere.payture.com/apim/Unblock?Key=MerchantKey&Data=SomeData',
            ],
            [
                PaytureOperation::PAY_STATUS(),
                ['Key' => 'MerchantKey', 'Data' => 'SomeData'],
                'https://nowhere.payture.com/apim/PayStatus?Key=MerchantKey&Data=SomeData',
            ],
        ];
    }
}
