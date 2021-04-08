<?php

namespace Lamoda\Payture\InPayClient;

final class TerminalConfiguration
{
    /** @var string */
    private $url;
    /** @var string */
    private $key;
    /** @var string */
    private $password;

    public function __construct(string $key, string $password, string $url)
    {
        $this->validateKey($key);
        $this->validatePassword($password);
        $this->validateUrl($url);

        $this->key = $key;
        $this->password = $password;
        $this->url = $this->normalizeUrl($url);
    }

    private static function mapOperationToPath(PaytureOperation $operation): string
    {
        switch ((string) $operation) {
            case (string) PaytureOperation::INIT():
                return 'Init';
            case (string) PaytureOperation::PAY():
                return 'Pay';
            case (string) PaytureOperation::CHARGE():
                return 'Charge';
            case (string) PaytureOperation::UNBLOCK():
                return 'Unblock';
            case (string) PaytureOperation::REFUND():
                return 'Refund';
            case (string) PaytureOperation::PAY_STATUS():
                return 'PayStatus';
            case (string) PaytureOperation::GET_STATE():
                return 'GetState';
        }

        // @codeCoverageIgnoreStart
        throw new \LogicException('Unknown operation');
        // @codeCoverageIgnoreEnd
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function buildOperationUrl(PaytureOperation $operation, string $interface, array $parameters): string
    {
        return $this->getUrl() . $interface .
            '/' . self::mapOperationToPath($operation) . '?' . http_build_query($parameters);
    }

    public function normalizeUrl(string $url): string
    {
        return rtrim($url, '/') . '/';
    }

    private function validateKey(string $key): void
    {
        if (empty($key)) {
            throw new \InvalidArgumentException('Empty terminal Key provided');
        }
    }

    private function validatePassword(string $password): void
    {
        if (empty($password)) {
            throw new \InvalidArgumentException('Empty terminal Password provided');
        }
    }

    private function validateUrl(string $url): void
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('Invalid URL provided');
        }
    }
}
