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


class BufferDataSource extends AbstractDataSource
{

    /**
     * @var array|DataSourceInterface
     */
    protected $data;

    protected bool $preserveKeys;
    /**
     * @param array|DataSourceInterface $data
     * @param $preserveKeys
     */
    public function __construct($data, bool $preserveKeys = true)
    {
        if ($data instanceof DataSourceInterface) {
            $this->setName('buffer:' . $data->getEvaluatedName());
            $data = $data->getCollection();
        }

        if (!is_array($data)) {
            throw new \Exception(sprintf("Data must be an instance of zsynchro_DataSource or an array of data... ; received '%s'", is_object($data) ? get_class($data) : gettype($data)));
        }

        $this->setPreserveKeys($preserveKeys);
        $this->setData($data);
    }

    /**
     * @return array<array>
     */
    public function getCollection()
    {
        return $this->getData();
    }

    /**
     * @param int $offset
     * @param int $chunkSize
     * @return array<array>
     */
    public function getChunkedCollection(int $offset, int $chunkSize)
    {
        return array_slice($this->getData(), $offset, $chunkSize, $this->isPreserveKeys());
    }

    /**
     * @return array|DataSourceInterface
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array|DataSourceInterface $data
     */
    public function setData($data): BufferDataSource
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return bool
     */
    public function isPreserveKeys(): bool
    {
        return $this->preserveKeys;
    }

    /**
     * @param bool $preserveKeys
     */
    public function setPreserveKeys(bool $preserveKeys): BufferDataSource
    {
        $this->preserveKeys = $preserveKeys;
        return $this;
    }
}
