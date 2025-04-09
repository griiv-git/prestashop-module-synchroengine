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

class PDODataSource extends AbstractDataSource implements PDODataSourceInterface
{
    protected string $sql;

    protected array $sqlParams;

    public function __construct(string $sql, array $sqlParams =  [])
    {
        $this->setSql($sql);
        $this->setSqlParams($sqlParams);
    }

    public function getCollection()
    {
        $sql = $this->getSql();

        $pdo = \DbPDO::getInstance()->connect();
        $statement = $pdo->prepare($sql);
        $this->bindStatementValues($statement, $this->getSqlParams());
        $statement->execute();
        $statement->setFetchMode(\PDO::FETCH_NUM);

        return $statement->fetchAll();
    }

    public function getChunkedCollection(int $offset, int $chunkSize)
    {
        $sql = $this->getSql();
        $sql .= "LIMIT $offset, $chunkSize";

        $pdo = \DbPDO::getInstance()->connect();
        $statement = $pdo->prepare($sql);
        $this->bindStatementValues($statement, $this->getSqlParams());
        $statement->execute();
        $statement->setFetchMode(\PDO::FETCH_NUM);

        return $statement->fetchAll();
    }

    public function bindStatementValues(\PDOStatement $statement, array $sqlParams)
    {
        foreach ($sqlParams as $name => $value) {
            $type = gettype($value);
            switch ($type) {
                case 'integer':
                    $pdoType = \PDO::PARAM_INT;
                break;
                case 'boolean':
                    $pdoType = \PDO::PARAM_BOOL;
                    $value = $value ? 1 : 0;
                break;
                case 'string':
                case 'double':
                    $pdoType = \PDO::PARAM_STR;
                break;
                case 'NULL':
                    $pdoType = \PDO::PARAM_NULL;
                break;
                default:
                    throw new \Exception("Unknown data type $type for field $name");
            }

            $statement->bindValue(':'.$name, $value, $pdoType);
        }
    }

    /**
     * @return string
     */
    public function getSql(): string
    {
        return $this->sql;
    }

    /**
     * @param string $sql
     */
    public function setSql(string $sql): PDODataSource
    {
        $this->sql = $sql;
        return $this;
    }

    /**
     * @return array
     */
    public function getSqlParams(): array
    {
        return $this->sqlParams;
    }

    /**
     * @param array $sqlParams
     */
    public function setSqlParams(array $sqlParams): PDODataSource
    {
        $this->sqlParams = $sqlParams;
        return $this;
    }
}
