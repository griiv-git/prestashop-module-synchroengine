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

interface DataSourceInterface
{
    public function getCollection();

    public function getChunkedCollection(int $offset, int $chunkSize);

    public function getEvaluatedName(): string;

    public function __toString(): string;
}
