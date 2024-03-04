<?php
/**
 * This file is part of the Symfony package.
 *
 * (c) Arnaud Scoté <arnaud@griiv.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 **/

namespace Griiv\SynchroEngine\Synchro\Item;

class ItemDefinition
{

    const SKIPPED = "skipped";

    /**
     * @var array<ItemProperty>
     */
    protected array $properties;

    /**
     * Make new zsynchro_ItemProperty and add it to the definition properties list
     * @param ItemProperty $property
     * @param mixed $columnIndex
     * @return ItemDefinition
     */
    public function add(ItemProperty $property, $columnIndex = null)
    {
        if (isset($columnIndex))
        {
            $this->properties[$columnIndex] = $property;
        }
        else
        {
            $this->properties[] = $property;
        }

        return $this;
    }

    /**
     * Skip a property index
     * @param mixed $columnIndex
     * @return ItemDefinition
     */
    public function skip($columnIndex = null)
    {
        if(isset($columnIndex))
        {
            $this->properties[$columnIndex] = self::SKIPPED;
        }
        else
        {
            $this->properties[] = self::SKIPPED;
        }

        return $this;
    }

    /**
     * @return Integer
     */
    public function getPropertyCount()
    {
        return count($this->properties);
    }

    /**
     * @return array<ItemProperty>
     */
    public function getProperties()
    {
        return $this->properties;
    }
}
