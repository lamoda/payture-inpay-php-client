<?php

namespace Lamoda\Payture\InPayClient;

use Paillechat\Enum\Enum;

/**
 * Enum which determine session types in payture gateway.
 *
 * @see https://payture.com/api#inpay_init_
 *
 * @method static static PAY()
 * @method static static BLOCK()
 */
final class SessionType extends Enum
{
    protected const PAY = 'Pay';
    protected const BLOCK = 'Block';
}
