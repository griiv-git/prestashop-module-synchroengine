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

interface DataTargetInterface
{
    public function getName(): string;

    public function setName($name): self;
    public function getEvaluatedName(): string;

    public function __toString(): string;

    public function addItems(array $data);
}
