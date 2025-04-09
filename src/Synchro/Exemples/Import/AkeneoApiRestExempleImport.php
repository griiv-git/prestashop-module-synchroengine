<?php
/**
 * This file is part of the Symfony package.
 *
 * (c) Arnaud Scoté <arnaud@griiv.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 **/

namespace Griiv\SynchroEngine\Synchro\Exemples\Import;

use Griiv\SynchroEngine\Core\DataSource\AkeneoApiRestDataSource;
use Griiv\SynchroEngine\Core\Helpers\SynchroHelper;
use Griiv\SynchroEngine\Core\ImportBase;
use Griiv\SynchroEngine\Core\Item\ItemDefinition;
use Griiv\SynchroEngine\Core\Item\ItemProperty;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

class AkeneoApiRestExempleImport extends ImportBase
{
    protected int $chunkSize = 100;
    private $clientId;

    private $secret;

    private $username;

    private $password;

    private $baseUrl;

    private $authUrl;

    private $token;

    private $nb;


    protected function init()
    {
        $this->clientId = "";
        $this->secret = "";
        $this->username = "p";
        $this->password = "";
        $this->baseUrl = "";
        $this->authUrl = "";

        $this->token = $this->getToken();

        return true;
    }

    protected function initLogger(): LoggerInterface
    {
        $logger = new Logger(get_class($this));
        $logger->pushHandler(new StreamHandler(SynchroHelper::getLogsPath() . '/CustomersImport.log'));

        return $logger;
    }

    private function getToken()
    {
        $posts = [
            'grant_type' => "password",
            'username' => $this->username,
            'password' => $this->password
        ];

        $base64 = base64_encode("{$this->clientId}:{$this->secret}");

        $headers = [
            "Authorization: Basic {$base64}",
            "Content-Type: application/json",
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL => "{$this->authUrl}",
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($posts, true),
            CURLOPT_HTTPHEADER => $headers,
        ]);

        $result = curl_exec($ch);
        curl_close($ch);

        return json_decode($result, true);
    }

    protected function initDataSources()
    {
        $apiDataSource = new AkeneoApiRestDataSource($this->baseUrl, $this->token);

        return [$apiDataSource];
    }

    protected function initItemDefinition()
    {
        $definition = new ItemDefinition();

        $definition->add(new ItemProperty('_links', false), '_links');
        $definition->add(new ItemProperty('identifier', false), 'identifier');
        $definition->add(new ItemProperty('enabled', false), 'enabled');
        $definition->add(new ItemProperty('family', false), 'family');
        $definition->add(new ItemProperty('categories', false), 'categories');
        $definition->add(new ItemProperty('groups', false), 'groups');
        $definition->add(new ItemProperty('parent', false), 'parent');
        $definition->add(new ItemProperty('values', false), 'values');
        $definition->add(new ItemProperty('created', false), 'created');
        $definition->add(new ItemProperty('updated', false), 'updated');
        $definition->add(new ItemProperty('associations', false), 'associations');

        return $definition;
    }

    protected function processRow($dataArray)
    {
        $this->getLogger()->debug("Identifier : " . $dataArray['identifier']);
        $this->getLogger()->debug("created : " . $dataArray['created']);
        $this->getLogger()->debug("updated : " . $dataArray['updated']);
        $this->getLogger()->debug("PVP : " . $dataArray['values']['PVP'][0]['data']);
    }
}
