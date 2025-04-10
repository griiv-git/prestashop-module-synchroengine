<?php
/**
 * This file is part of the Symfony package.
 *
 * (c) Arnaud Scoté <arnaud@griiv.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 **/

namespace Griiv\SynchroEngine\Core\DataSource;


class AkeneoApiRestPageDataSource extends AbstractDataSource
{
    protected int $startRow = 1;
    private string $apiUrl;

    private array $token;

    public function __construct(string $apiUrl, array $token)
    {
        $this->apiUrl = $apiUrl;
        $this->token = $token;
    }
    public function getCollection()
    {
        return [];
    }

    public function getChunkedCollection(int $offset, int $chunkSize)
    {
        $queryParams = [
            "with_count" => "false",
            "limit" => $chunkSize ?? 20,
            "page" => $offset ?? 1
        ];
        $query = http_build_query($queryParams);

        $bearer = $this->token['access_token'];
        $headers = [
            "Authorization: Bearer {$bearer}",
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => "{$this->apiUrl}{$query}",
            CURLOPT_HTTPHEADER => $headers
        ]);

        $response = $this->handleResponse($ch, "CATEGORIES");
        
        if (isset($response['_embedded']['items'])) {
            // dump($response['_embedded']['items']);die();
            return $response['_embedded']['items'];
        }

        if (isset($response['code'], $response['message'])) {
            throw new \Exception($response['message'], $response['code']);
        }

        throw new \Exception("him");
    }

    private function handleResponse($ch, string $type)
    {
        $result = curl_exec($ch);
        curl_close($ch);
        $json = json_decode($result, true);

        if (empty($json)) {
            throw new \Exception("Akeneo request for {$type} is empty");
        }

       return $json;
    }
}
