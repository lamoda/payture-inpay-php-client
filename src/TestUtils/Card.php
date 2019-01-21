<?php

namespace Lamoda\Payture\InPayClient\TestUtils;

/**
 * @codeCoverageIgnore
 */
final class Card
{
    /** @var string */
    private $cardNumber;
    /** @var string */
    private $secureCode;
    /** @var string */
    private $expirationYear;
    /** @var string */
    private $expirationMonth;
    /** @var string */
    private $cardHolder;

    public function __construct(
        string $cardNumber,
        string $secureCode,
        string $expirationYear,
        string $expirationMonth,
        string $cardHolder
    ) {
        $this->cardNumber = $cardNumber;
        $this->secureCode = $secureCode;
        $this->expirationYear = $expirationYear;
        $this->expirationMonth = $expirationMonth;
        $this->cardHolder = $cardHolder;
    }

    public function getCardNumber(): string
    {
        return $this->cardNumber;
    }

    public function getSecureCode(): string
    {
        return $this->secureCode;
    }

    public function getExpirationYear(): string
    {
        return $this->expirationYear;
    }

    public function getExpirationMonth(): string
    {
        return $this->expirationMonth;
    }

    public function getCardHolder(): string
    {
        return $this->cardHolder;
    }
}
