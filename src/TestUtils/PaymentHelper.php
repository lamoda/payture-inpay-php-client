<?php

namespace Lamoda\Payture\InPayClient\TestUtils;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\RequestOptions;
use PHPUnit\Framework\Assert;
use Psr\Http\Message\ResponseInterface;

/**
 * @codeCoverageIgnore
 */
final class PaymentHelper extends Assert
{
    /** @var ClientInterface */
    private $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    private static function formatCard(Card $card): array
    {
        return [
            'cardNumber' => $card->getCardNumber(),
            'SecureCode' => $card->getSecureCode(),
            'EYear' => $card->getExpirationYear(),
            'EMonth' => $card->getExpirationMonth(),
            'CardHolder' => $card->getCardHolder(),
        ];
    }

    private static function assertPaytureAcceptedCard(ResponseInterface $response): void
    {
        $body = \json_decode($response->getBody(), true);

        self::assertEquals(200, $response->getStatusCode(), 'Wrong status code.');
        self::assertEquals('application/json', $response->getHeaderLine('Content-Type'), 'Wrong content.');
        self::assertEquals(true, $body['Success'], "Payment failure: {$response->getBody()}.");
    }

    /**
     * @param ResponseInterface $response
     * @param string[] $names
     *
     * @return string[]
     */
    private static function getInputValues(ResponseInterface $response, array $names): array
    {
        $html = (string) $response->getBody();
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_use_internal_errors(false);
        $xpath = new \DOMXPath($dom);

        $values = array_map(
            function (string $name) use ($xpath) {
                $values = $xpath->query('//input[@name="' . $name . '"]/@value');

                return $values->item(0)->nodeValue;
            }, $names
        );

        return array_combine($names, $values);
    }

    /**
     * @param string $orderNr
     * @param int $amount
     * @param string $paymentUrl
     * @param Card $card
     * @param string $sandboxUrl
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function pay(
        string $orderNr,
        int $amount,
        string $paymentUrl,
        Card $card,
        string $sandboxUrl
    ): void {
        $this->sendPayment(
            $paymentUrl,
            $sandboxUrl,
            array_merge(
                [
                    'OrderId' => $orderNr,
                    'Amount' => $amount,
                    'TemplateTag' => 'json',
                ],
                self::formatCard($card)
            )
        );
    }

    /**
     * @param string $paymentUrl
     * @param string $sandboxUrl
     * @param array $data
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function sendPayment(string $paymentUrl, string $sandboxUrl, array $data): void
    {
        $response = $this->client->request('GET', $paymentUrl);

        self::assertEquals($response->getStatusCode(), 200, "Can't open payment page: $paymentUrl.");

        // Send request to sandbox with secret key:
        $response = $this->client->request(
            'POST',
            $sandboxUrl,
            [
                RequestOptions::COOKIES => new CookieJar(true, []),
                RequestOptions::HEADERS => [
                    'Referer' => $paymentUrl,
                    'X-Requested-With' => 'XMLHttpRequest',
                ],
                RequestOptions::FORM_PARAMS => [
                    'Data' => http_build_query(
                        array_merge(
                            $data,
                            self::getInputValues($response, ['Key'])
                        ),
                        '',
                        ';'
                    ),
                    'Json' => 'true',
                ],
            ]
        );

        self::assertPaytureAcceptedCard($response);
    }
}
