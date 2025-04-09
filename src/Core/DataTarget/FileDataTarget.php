<?php
/**
 * This file is part of the Symfony package.
 *
 * (c) Arnaud Scoté <arnaud@griiv.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 **/

namespace Griiv\SynchroEngine\Core\DataTarget;

use ErrorException;
use Griiv\SynchroEngine\Core\Csv\CsvFileWriter;

class FileDataTarget extends AbstractDataTarget
{

    protected string $fileName;

    protected CsvFileWriter $currentFile;

    public function __construct(array $params)
    {
        if (!isset($params['fileName'])) {
            throw new ErrorException('fileName parameter is required');
        }

        $this->fileName = $params['fileName'];
        $this->currentFile = new CsvFileWriter($this->fileName, 'a', $params);
    }

    public function __toString(): string
    {
        return get_class($this) . " " . $this->fileName;
    }

    public function hasCurrentFile(): bool
    {
        return (isset($this->currentFile));
    }

    public function addItems($data)
    {
        if ($this->hasCurrentFile() && $this->getCurrentFile() !== null) {
            $this->getCurrentFile()->putLines($data);
        }
    }


    public function getCurrentFile(): ?CsvFileWriter
    {
        return ($this->hasCurrentFile()) ? $this->currentFile : null;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }
}
