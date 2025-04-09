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

use Griiv\SynchroEngine\Synchro\Csv\CsvFileReader;

class FileDataSource extends AbstractDataSource
{

    protected string $fileName;

    protected CsvFileReader $currentFile;


    public function __construct($params)
    {
        if (!isset($params['fileName'])) {
            throw new \Exception('fileName parameter is required');
        }

        $this->fileName = $params['fileName'];
        $this->currentFile = new CsvFileReader($this->fileName, 'r', $params);
        $this->startRow = $params['startRow'] ?? 1;
        //Si ne pas traité première ligne fichier (en tete) => on commence ligne 2
        if ($this->currentFile->getAvoidFirstLine()) {
            $this->startRow = 2;
        }

    }

    public function __toString(): string
    {
        return get_class($this) . " " . $this->fileName;
    }

    /**
     * @return boolean
     */
    public function hasCurrentFile()
    {
        return (isset($this->currentFile));
    }

    public function getCurrentFile(): ?CsvFileReader
    {
        return ($this->hasCurrentFile()) ? $this->currentFile : null;
    }

    /**
     * @return string
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function getCollection()
    {
        if ($this->hasCurrentFile() && $this->getCurrentFile() !== null) {
            return $this->getCurrentFile()->getLines();
        }
    }

    public function getChunkedCollection(int $offset, int $chunkSize)
    {
        if ($this->hasCurrentFile() && $this->getCurrentFile() !== null) {
            return $this->getCurrentFile()->getLines($offset, $chunkSize);
        }
        return array();
    }
}
