<?php


namespace Griiv\SynchroEngine\Core\Api;

use Griiv\SynchroEngine\Core\Notifier\Message\MessageInterface;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\Exception\RedirectionException;
use Symfony\Component\HttpClient\Exception\ServerException;
use Symfony\Component\HttpClient\Response\CurlResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;

class KchatApi
{
    private $client;

    protected $token;

    protected $apiUrl;

    protected $endPoint;

    public function __construct(string $token, string $apiUrl, string $endPoint)
    {
        $this->client = new CurlHttpClient();
        $this->token = $token;
        $this->apiUrl = $apiUrl;
        $this->endPoint = $endPoint;
    }

    public function sendMessage(MessageInterface $message, string $channelId)
    {
        $configuration = \Configuration::getMultiple(
            ['PS_SHOP_NAME', 'PS_SHOP_DOMAIN_SSL'],
            null,
            null,
            \Context::getContext()->shop->id
        );

        $body = [
            'channel_id' => $channelId,
            'message' => "### Griiv Synchro Engine - **" . $configuration['PS_SHOP_NAME'] . "** - " . "(" . $configuration['PS_SHOP_DOMAIN_SSL'] . ")" . PHP_EOL . PHP_EOL . $message->getMessage()->toString(),
        ];

        /** @var CurlResponse $response */
        $response = $this->client->request('POST', $this->apiUrl . $this->endPoint, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->token,
            ],
            'body' => json_encode($body)
        ]);

        $this->checkStatusCode($response);
    }

     private function checkStatusCode(ResponseInterface $response)
    {
        if (500 <= $response->getStatusCode()) {
            throw new ServerException($response);
        }

        if (400 <= $response->getStatusCode()) {
            throw new ClientException($response);
        }

        if (300 <= $response->getStatusCode()) {
            throw new RedirectionException($response);
        }
    }
}