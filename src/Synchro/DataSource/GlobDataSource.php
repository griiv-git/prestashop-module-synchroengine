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


use GlobIterator;

class GlobDataSource extends AbstractDataSource
{

    protected ?GlobIterator $globIterator;

    protected string $path;

    public function __construct(string $path, int $flags = null)
    {
        $this->path = $path;
        $this->globIterator = new GlobIterator($path, $flags);
    }

    public function getCollection()
    {
        $tmp = clone($this->globIterator);
        $collectionArray = iterator_to_array($tmp, false);

        return array_map([$this, 'collectionCallback'], $collectionArray);
    }

    protected function collectionCallback($value)
    {
        return [$value];
    }

    public function getChunkedCollection(int $offset, int $chunkSize)
    {
        return array_slice($this->getCollection(), $offset, $chunkSize, true);
    }

    public function __toString(): string
    {
        return get_class($this) . " " . $this->path;
    }
}
