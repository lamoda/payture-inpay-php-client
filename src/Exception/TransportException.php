<?php

namespace Lamoda\Payture\InPayClient\Exception;

/**
 * @codeCoverageIgnore
 */
class TransportException extends \Exception
{
    public static function becauseUnderlyingTransportFailed(\Throwable $exception): self
    {
        return new self(
            sprintf('Payture request failed: [%s] %s', $exception->getCode(), $exception->getMessage()),
            $exception->getCode(),
            $exception
        );
    }
}
