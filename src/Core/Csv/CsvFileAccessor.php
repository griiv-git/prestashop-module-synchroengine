<?php
/**
 * This file is part of the Symfony package.
 *
 * (c) Arnaud Scoté <arnaud@griiv.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 **/

namespace Griiv\SynchroEngine\Core\Csv;

abstract class CsvFileAccessor extends FileAccessor
{
    protected string $delimiter = ";";
    protected string $enclosure = '"';

    public function __construct(string $fileName, string $mode, array $params)
    {
        parent::__construct($fileName, $mode, $params);

        // Optionnal parameters
        if (isset($params['delimiter'])) $this->delimiter = $params['delimiter'];
        if (isset($params['enclosure'])) $this->enclosure = $params['enclosure'];
    }

    public function getDelimiter()
    {
        return $this->delimiter;
    }

    public function setDelimiter($delimiter)
    {
        $this->delimiter = $delimiter;
        if ($this->isOpened()) {
            $this->close();
        }
        return true;
    }

    public function getEnclosure()
    {
        return $this->enclosure;
    }

    public function setEnclosure($enclosure)
    {
        $this->enclosure = $enclosure;
        if ($this->isOpened()) {
            $this->close();
        }
        return true;
    }
}
