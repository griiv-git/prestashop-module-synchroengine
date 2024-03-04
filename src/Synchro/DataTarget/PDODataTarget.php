<?php
/**
 * This file is part of the Symfony package.
 *
 * (c) Arnaud Scoté <arnaud@griiv.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 **/

namespace Griiv\SynchroEngine\Synchro\DataTarget;

class PDODataTarget extends AbstractDataTarget implements PDODataTargetInterface
{

    protected string $sql;

    protected array $sqlParams;

    protected array $keys;

    /**
     * Constructor
     * @param String $sql
     * @param array $params
     */
    public function __construct($sql, array $params = array())
    {
        $this->setSqlParams($params);
        $this->sql = $sql;

    }

    /**
     * @throws \Exception
     */
    public function addItems(array $data)
    {
        foreach($data as $item) {
            $sql = $this->sql;

            $pdo = \DbPDO::getInstance()->connect();
            $statement = $pdo->prepare($sql);
            $params = array_merge($this->getSqlParams(), $item);
            $this->bindStatementValues($statement, $params);
            $statement->execute();
            $statement->closeCursor();
        }
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
    public function setSql(string $sql): PDODataTarget
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
    public function setSqlParams(array $sqlParams): PDODataTarget
    {
        $this->sqlParams = $sqlParams;
        return $this;
    }

    /**
     * @return array
     */
    public function getKeys(): array
    {
        return $this->keys;
    }

    /**
     * @param array $keys
     */
    public function setKeys(array $keys): PDODataTarget
    {
        $this->keys = $keys;
        return $this;
    }
}
