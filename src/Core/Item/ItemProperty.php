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

class ItemProperty
{
    protected string $name;

    protected bool $required = true;

    protected $notEmptyValidator = null;

    protected array $validators;

    protected $emptyFilter;

    protected array $filters;

    /**
     * Constructor
     * @param string $name
     * @param bool $isRequired
     */
    public function __construct($name, $isRequired = true, array $validators = null)
    {
        $this->setName($name);
        $this->setRequired($isRequired);

        /*if(isset($validators))
        {
            $this->setValidators($validators);
        }*/
    }

    /**
     * @param String $name
     * @throws \Exception
     */
    public function setName($name)
    {
        $name = (string) $name;

        if($name === "") {
            throw new \Exception("Name cannot be empty");
        }

        $this->name = $name;
    }

    /**
     * @return String
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param bool $isRequired
     */
    public function setRequired($isRequired)
    {
        $this->required = (bool) $isRequired;
        return $this;
    }

    /**
     * @return bool
     */
    public function isRequired()
    {
        return $this->required;
    }
}
