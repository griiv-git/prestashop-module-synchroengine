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


class DbQueryDataSource extends AbstractDataSource
{

    protected \DbQuery $query;

    protected $fetchMode;

    public function __construct(\DbQuery $query, $fetchMode = \PDO::FETCH_ASSOC)
    {
        $this->setQuery($query);
        $this->setFetchMode($fetchMode);
    }

    public function getCollection()
    {
        return \Db::getInstance()->executeS($this->getQuery());
    }

    public function getChunkedCollection(int $offset, int $chunkSize)
    {
        $query = $this->getQuery();
        $query->limit($chunkSize, $offset);

        if ($this->fetchMode !== \PDO::FETCH_ASSOC) {
            return \Db::getInstance()->executeS($query, false)->fetchAll($this->fetchMode);
        }

        return \Db::getInstance()->executeS($query);
    }

    public function getQuery(): \DbQuery
    {
        return $this->query;
    }

    public function setQuery(\DbQuery $query): DbQueryDataSource
    {
        $this->query = $query;
        return $this;
    }

    private function setFetchMode($fetchMode)
    {
        $this->fetchMode = $fetchMode;
    }


}
