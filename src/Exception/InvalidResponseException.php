<?php

namespace Lamoda\Payture\InPayClient\Exception;

/**
 * @codeCoverageIgnore
 */
final class InvalidResponseException extends TransportException
{
    private const MESSAGE_PREFIX = 'Invalid gateway response: ';

    public static function becauseEmptyAttributes(): self
    {
        return new self(self::MESSAGE_PREFIX . 'Empty attributes');
    }

    public static function becauseInvalidXML(): self
    {
        return new self(self::MESSAGE_PREFIX . 'Invalid XML');
    }

    public static function becauseRootTagMismatch(string $rootNode, string $operation): self
    {
        return new self(
            sprintf(self::MESSAGE_PREFIX . 'Invalid root tag name. Got "%s", expected "%s"', $rootNode, $operation)
        );
    }

    public static function becauseUndefinedSuccessAttribute(): self
    {
        return new self(self::MESSAGE_PREFIX . 'Undefined success attribute');
    }
}
