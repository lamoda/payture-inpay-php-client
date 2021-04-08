<?php

namespace Lamoda\Payture\InPayClient;

use Lamoda\Payture\InPayClient\Exception\TransportException;

interface TransportInterface
{
    /**
     * @throws TransportException
     */
    public function request(PaytureOperation $operation, string $interface, array $parameters): string;
}
