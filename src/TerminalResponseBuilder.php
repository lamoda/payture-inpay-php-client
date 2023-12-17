<?php

namespace Lamoda\Payture\InPayClient;

use Lamoda\Payture\InPayClient\Exception\InvalidResponseException;

/**
 * @internal
 */
final class TerminalResponseBuilder
{
    /**
     * @throws InvalidResponseException
     */
    public static function parseTransportResponse(
        string $transportResponse,
        PaytureOperation $operation
    ): TerminalResponse {
        $response = self::parseXmlResponse($transportResponse, self::mapOperationToRootNode($operation));

        $attributes = $response['@attributes'];

        if (!isset($attributes['Success'])) {
            throw InvalidResponseException::becauseUndefinedSuccessAttribute();
        }

        $result = new TerminalResponse($attributes['Success'], $attributes['OrderId'] ?? '');

        if (isset($attributes['SessionId'])) {
            $result->setSessionId((string) $attributes['SessionId']);
        }

        if (isset($attributes['Amount'])) {
            $result->setAmount((int) $attributes['Amount']);
        }

        if (isset($attributes['NewAmount'])) {
            $result->setAmount((int) $attributes['NewAmount']);
        }

        if (isset($attributes['State'])) {
            $result->setState($attributes['State']);
        }

        if (isset($attributes['ErrCode'])) {
            $result->setErrorCode($attributes['ErrCode']);
        }

        if (isset($attributes['RRN'])) {
            $result->setRrn($attributes['RRN']);
        }

        if (isset($response['AddInfo'])) {
            $result->setAdditionalInfo($response['AddInfo']);
        }

        return $result;
    }

    /**
     * @throws InvalidResponseException
     */
    private static function parseXmlResponse(string $xml, string $operation): array
    {
        $oldUseInternalXmlErrors = libxml_use_internal_errors(true);
        $rootNode = simplexml_load_string($xml);
        libxml_use_internal_errors($oldUseInternalXmlErrors);

        if (!$rootNode instanceof \SimpleXMLElement) {
            throw InvalidResponseException::becauseInvalidXML();
        }

        if (mb_strtolower($rootNode->getName()) !== mb_strtolower($operation)) {
            throw InvalidResponseException::becauseRootTagMismatch($rootNode->getName(), $operation);
        }

        $data = (array) $rootNode;

        if (!isset($data['@attributes'])) {
            throw InvalidResponseException::becauseEmptyAttributes();
        }

        $result = [];
        $result['@attributes'] = $data['@attributes'];

        if (isset($data['AddInfo'])) {
            $result['AddInfo'] = self::parseAddInfo($data['AddInfo']);
        }

        return $result;
    }

    private static function parseAddInfo(array $rawAddInfo): array
    {
        $result = [];

        foreach ($rawAddInfo as $info) {
            $infoArray = (array) $info;
            $result[$infoArray['@attributes']['Key']] = $infoArray['@attributes']['Value'];
        }

        return $result;
    }

    private static function mapOperationToRootNode(PaytureOperation $operation): string
    {
        switch ((string) $operation) {
            case (string) PaytureOperation::INIT():
                return 'Init';
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

        //@codeCoverageIgnoreStart
        throw new \LogicException('Unknown operation');
        //@codeCoverageIgnoreEnd
    }
}
