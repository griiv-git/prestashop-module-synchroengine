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


abstract class AbstractDataSource implements DataSourceInterface
{
    protected string $name;

    protected int $startRow = 0;

    const EVENT_INIT = "init";

    const EVENT_END = "end";

    protected array $_callbacks = [];

    abstract public function getCollection();

    abstract public function getChunkedCollection(int $offset, int $chunkSize);

    public function getCallBack($event = self::EVENT_END)
    {
        return isset($this->_callbacks[$event]) ? $this->_callbacks[$event] : null;
    }

    /**
     * Register the callback provided in the constructor
     * @param  callable $callback
     * @throws \Exception
     * @return void
     */
    public function registerCallBack($callback, $event = self::EVENT_END)
    {
        if(isset($callback) && !is_callable($callback)) {
            throw new \Exception("Invalid callback provided : not callable");
        }

        $this->_callbacks[$event] = $callback;
    }

    /**
     * Unregister callback
     * @return void
     */
    public function unregisterCallBack($event = self::EVENT_END)
    {
        $this->_callbacks[$event] = null;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getStartRow(): int
    {
        return $this->startRow;
    }

    public function getEvaluatedName(): string
    {
        return isset($this->name) ? (String) $this->name : $this->__toString();
    }

    public function __toString(): string
    {
        return get_class($this);
    }
}
