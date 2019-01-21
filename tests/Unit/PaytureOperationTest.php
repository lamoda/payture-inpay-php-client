<?php

namespace Lamoda\Payture\InPayClient\Tests\Unit;

use Lamoda\Payture\InPayClient\PaytureOperation;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Lamoda\Payture\InPayClient\PaytureOperation
 */
final class PaytureOperationTest extends TestCase
{
    public function testToStringReturnsOperationName(): void
    {
        $operation = PaytureOperation::INIT();
        self::assertSame('Init', (string) $operation);
    }
}
