<?php

namespace Lamoda\Payture\InPayClient;

use Paillechat\Enum\Enum;

/**
 * Operations that payture accepts.
 *
 * @method static static INIT()
 * @method static static PAY()
 * @method static static CHARGE()
 * @method static static UNBLOCK()
 * @method static static REFUND()
 * @method static static PAY_STATUS()
 * @method static static GET_STATE()
 *
 * @internal
 */
final class PaytureOperation extends Enum
{
    public const INIT = 'Init';
    public const PAY = 'Pay';
    public const CHARGE = 'Charge';
    public const UNBLOCK = 'Unblock';
    public const REFUND = 'Refund';
    /**
     * @deprecated
     * @see PaytureOperation::GET_STATE
     */
    public const PAY_STATUS = 'PayStatus';
    public const GET_STATE = 'GetState';

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
