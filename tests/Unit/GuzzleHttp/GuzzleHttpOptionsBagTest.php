<?php

namespace Lamoda\Payture\InPayClient\Tests\Unit\GuzzleHttp;

use Lamoda\Payture\InPayClient\GuzzleHttp\GuzzleHttpOptionsBag;
use Lamoda\Payture\InPayClient\PaytureOperation;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Lamoda\Payture\InPayClient\GuzzleHttp\GuzzleHttpOptionsBag
 */
final class GuzzleHttpOptionsBagTest extends TestCase
{
    public function testEmptyBag(): void
    {
        $bag = new GuzzleHttpOptionsBag();
        self::assertEmpty($bag->getOperationOptions(PaytureOperation::INIT()));
    }

    public function testOptionMering(): void
    {
        $bag = new GuzzleHttpOptionsBag(['timeout' => 5], ['Init' => ['timeout' => 15]]);
        self::assertEquals(['timeout' => 5], $bag->getOperationOptions(PaytureOperation::CHARGE()));
        self::assertEquals(['timeout' => 15], $bag->getOperationOptions(PaytureOperation::INIT()));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidOptionCausesValidationException(): void
    {
        new GuzzleHttpOptionsBag(['invalid option' => 5]);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidOperationCausesValidationException(): void
    {
        new GuzzleHttpOptionsBag([], ['Init' => ['invalid option' => 5]]);
    }
}
