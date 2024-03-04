<?php
/**
 * This file is part of the Symfony package.
 *
 * (c) Arnaud Scoté <arnaud@griiv.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 **/

namespace Griiv\SynchroEngine\Synchro\DataSource;


class AkeneoApiRestDataSource extends AbstractDataSource
{
    protected int $startRow = 1;
    private string $apiUrl;

    private array $token;

    private ?string $nextChunkApiUrl = null;

    public function __construct(string $apiUrl, array $token)
    {
        $this->apiUrl = $apiUrl;
        $this->token = $token;
    }
    public function getCollection()
    {
        return [];
    }

    // For products only, not supported by categories, brands, etc.
    public function getChunkedCollection(int $offset, int $chunkSize)
    {
        if ($this->nextChunkApiUrl === null && $offset === 1) {
            $queryParams = [
                // ne marche pas pour le moment :(
                // 'search' => '{"enabled":[{"operator":"=","value":true}], "sku": [{"operator":"=","value":"1010425"}]}',
                'pagination_type' => 'search_after', 
                'limit' => $chunkSize
            ];
            $query = http_build_query($queryParams);

            $this->nextChunkApiUrl = "{$this->apiUrl}{$query}";
        }

        $bearer = $this->token['access_token'];
        $headers = [
            "Authorization: Bearer {$bearer}",
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $this->nextChunkApiUrl,
            CURLOPT_HTTPHEADER => $headers
        ]);

        $response = $this->handleResponse($ch, "PRODUCTS");

        if (isset($response['_embedded']['items'])) {
            // dump($response['_embedded']['items']); die();
            if (isset($response['_links']['next'])) {
                $this->nextChunkApiUrl = $response['_links']['next']['href'];
            } else {
                $this->nextChunkApiUrl = null;
                return [];
            }

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
