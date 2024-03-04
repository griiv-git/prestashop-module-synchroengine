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

abstract class AbstractDataTarget implements DataTargetInterface
{
    protected string $name;

    abstract public function addItems(array $data);

    public function getName(): string
    {
        return $this->name;
    }

    public function setName($name): self
    {
        $this->name = $name;
        return $this;
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
