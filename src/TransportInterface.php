<?php

namespace Lamoda\Payture\InPayClient;

use Lamoda\Payture\InPayClient\Exception\TransportException;

interface TransportInterface
{
    /**
     * @param PaytureOperation $operation
     * @param string $interface
     * @param array $parameters
     *
     * @return string
     *
     * @throws TransportException
     */
    public function request(PaytureOperation $operation, string $interface, array $parameters): string;
}
