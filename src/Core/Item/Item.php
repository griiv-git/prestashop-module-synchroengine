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

use Griiv\SynchroEngine\Exception\BreakException;

class Item
{
    protected ItemDefinition $itemDefinition;

    protected array $data;

    protected array $errors;

    protected bool $inverse;

    public function __construct(array $data, ItemDefinition $definition, $inverse = false)
    {
        if (count($data) !== $definition->getPropertyCount()) {
            throw new BreakException(sprintf("Your item contains %d properties but itemDefinition mentionned %d properties", count($data), $definition->getPropertyCount()));
        }

        $this->inverse = $inverse;
        $this->definition = $definition;
        $this->data = $this->map($data);
    }

    protected function map(array $data)
    {
        $result = array();

        $properties = $this->getDefinitionProperties();

        foreach ($properties as $key => $property) {
            if ($property instanceof ItemProperty) {
                $name = $property->getName();
                if ($this->inverse) {
                    $result[$key] = isset($data[$name]) ? $data[$name] : null;
                } else {
                    $result[$name] = isset($data[$key]) ? $data[$key] : null;
                }
            }
        }

        return $result;
    }

    public function isValid()
    {
        $properties = $this->getDefinitionProperties();

        // Check all item properties
        foreach ($properties as $key => $property) {
            if ($property instanceof ItemProperty) {
                $propertyName = $property->getName();
                $propertyValue = $this->getRawDataByKey($this->inverse ? $key : $propertyName);

                // Property value is empty
                if ($property->getNotEmptyValidator()->isValid($propertyValue) === false) {
                    // And property is required : we add an error...
                    if ($property->isRequired() === true) {
                        $this->addErrors($propertyName, $property->getNotEmptyValidator()->getErrors());
                    }

                    // When property value is empty we skip other checks
                    continue;
                }

                // Check other validators if exists...
                if ($property->hasValidators()) {
                    $validators = $property->getValidators();

                    foreach ($validators as $validator) {
                        if (!$validator->isValid($propertyValue)) {
                            $this->addErrors($propertyName, $validator->getErrors());
                        }
                    }
                }
            }
        }

        if (count($this->getErrors())) {
            return false;
        }

        return true;
    }

    public function addErrors($propertyName, array $errors)
    {
        if (isset($this->errors[$propertyName])) {
            $this->errors[$propertyName] += $errors;
        } else {
            $this->errors[$propertyName] = $errors;
        }
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return Array<String>
     */
    public function getErrorStrings()
    {
        $strings = array();
        if ($this->getErrors()) {
            foreach ($this->getErrors() as $name => $messages) {
                foreach ($messages as $msg) {
                    $strings[] = $name . " '" . $this->getRawDataByKey($name) . "' : " . $msg;
                }
            }
        }
        return $strings;
    }

    /**
     * Retrieve all datas (mapped with definition)
     * @return array
     */
    public function getDataArray()
    {
        $properties = $this->getDefinitionProperties();

        $data = array();
        // Check all item properties
        foreach ($properties as $key => $property) {
            if ($property instanceof ItemProperty) {
                $propertyName = $property->getName();
                $propertyValue = $this->getRawDataByKey($this->inverse ? $key : $propertyName);

                // Property value is empty
                /*                if(!$property->isRequired() && !$property->getNotEmptyValidator()->isValid($propertyValue))
                                {
                                    $propertyValue = $property->getEmptyFilter()->filter($propertyValue);
                                }
                                else
                                {
                                    foreach($property->getFilters() as $filter)
                                    {
                                        $propertyValue = $filter->filter($propertyValue);
                                    }
                                }*/

                $data[$this->inverse ? $key : $propertyName] = $propertyValue;
            }
        }
        return $data;
    }

    public function getRawDataArray()
    {
        return $this->data;
    }

    /**
     * Retrieve 1 data from data array (mapped with definition)
     * @return mixed
     */
    public function getRawDataByKey($key)
    {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }
    }

    /**
     * Retrieve 1 data from data array (mapped with definition)
     * @return mixed
     */
    public function getDataByKey($key)
    {
        $data = $this->getDataArray();
        if (isset($data[$key])) {
            return $data[$key];
        }
    }

    /**
     * Retrieve item definition
     * @return ItemDefinition
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * shortcut for $this->getDefinition()->getProperties()
     * @return Array
     */
    public function getDefinitionProperties()
    {
        return $this->getDefinition()->getProperties();
    }

    public function getKeys()
    {
        return array_keys($this->data);
    }
}
