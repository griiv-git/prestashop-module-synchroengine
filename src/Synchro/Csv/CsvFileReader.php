<?php
/**
 * This file is part of the Symfony package.
 *
 * (c) Arnaud Scoté <arnaud@griiv.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 **/

namespace Griiv\SynchroEngine\Synchro\Csv;


use Symfony\Component\Filesystem\Exception\IOException;

class CsvFileReader extends CsvFileAccessor
{
    protected string $escape = '\\';
    protected int $offset = 0;
    protected array $lineMap = [1 => 0];
    protected int $chunkSize = 0;
    protected bool $avoidFirstLine = false;

    protected string $toEncoding = 'UTF-8';

    protected bool $ignoreEmptyLines = false;

    protected $handlerForMap = false;

    protected bool $useEscape;

    public function __construct(string $fileName, string $mode = 'r', array $params)
    {
        parent::__construct($fileName, $mode, $params);

        if (isset($params['autoDetectLineEndings']) && is_bool($params['autoDetectLineEndings'])) {
            ini_set("auto_detect_line_endings", $params['autoDetectLineEndings']);
        }

        // Optionnal parameters
        if (isset($params['escape'])) $this->escape = $params['escape'];
        if (isset($params['chunkSize'])) $this->chunkSize = (int)$params['chunkSize'];
        if (isset($params['avoidFirstLine'])) $this->avoidFirstLine = (bool)$params['avoidFirstLine'];
        if (isset($params['ignoreEmptyLines'])) $this->ignoreEmptyLines = (bool)$params['ignoreEmptyLines'];

        $this->useEscape = true;

        if ($this->chunkSize > 0) {
            $this->mapChunks();
        }
    }

    /**
     * Returns whether file is opened
     * @return Boolean
     */
    public function isOpened()
    {
        return ($this->getHandler() !== false && is_resource($this->getHandler())) && ($this->handlerForMap !== false && is_resource($this->handlerForMap));
    }

    /**
     * Opens file
     * @return Boolean
     */
    public function open()
    {
        if ($this->isOpened()) {
            $this->close();
        }

        if (!is_readable($this->fileName)) {
            throw new IOException("Can not read {$this->fileName}");
        }

        $this->handler = fopen($this->fileName, $this->mode);
        $this->handlerForMap = fopen($this->fileName, $this->mode);
        if ($this->getHandler() === false || $this->handlerForMap === false) {
            throw new IOException("Could not open " . $this->fileName);
        }
        $this->offset = 0;
        fseek($this->handler, $this->offset, SEEK_SET);
        fseek($this->handlerForMap, $this->offset, SEEK_SET);
        //$this->registerEncodingFilter();

        return true;
    }

    /**
     * Maps $lineNumber in the line/offset mapping table
     * @return Boolean
     */
    public function mapLine($lineNumber)
    {
        if (!$this->isOpened()) {
            $this->open();
        }

        if ($lineNumber < 1) {
            $lineNumber = 1;
        }

        $eof = false;
        if (!isset($this->lineMap[$lineNumber])) {
            if ($lineNumber === 1) {
                $this->lineMap[$lineNumber] = 0;
            } else {
                $cLine = $this->getFloorKey($lineNumber);

                if ($cLine === 1) {
                    fseek($this->handlerForMap, 0);
                } else {
                    fseek($this->handlerForMap, $this->lineMap[$cLine]);
                }
                $pos = $this->lineMap[$cLine];

                while ($cLine <= $lineNumber) {
                    if ($cLine == $lineNumber) {
                        $this->lineMap[$cLine] = $pos;
                        break;
                    }
                    $buffer = $this->getCsvLine($this->handlerForMap);
                    $pos = ftell($this->handlerForMap);

                    $cLine++;
                    if ($buffer === false || feof($this->handlerForMap)) {
                        $eof = true;
                        break;
                    }
                }
            }
        }

        return !$eof;
    }

    protected function getCsvLine($handler)
    {
        if ($this->useEscape) {
            return fgetcsv($handler, 0, $this->delimiter, $this->enclosure, $this->escape);
        } else {
            return fgetcsv($handler, 0, $this->delimiter, $this->enclosure);
        }
    }

    /**
     * Returns offset for $lineNumber
     * @return Integer
     */
    public function getMappingForLine($lineNumber)
    {
        return (isset($this->lineMap[$lineNumber])) ? $this->lineMap[$lineNumber] : -1;
    }

    /**
     * Returns $lineNumber's closest line's key in line/offset mapping table
     * @return Integer
     */
    public function getFloorKey($lineNumber)
    {
        if (isset($this->lineMap[$lineNumber])) {
            return $lineNumber;
        }

        $keys = array_keys($this->lineMap);
        $keys[] = $lineNumber;
        rsort($keys);
        $key = array_search($lineNumber, $keys);
        $key++;
        return $keys[$key];
    }

    /**
     * Adds each chunks' firstline in line/offset mapping table. Used to optimize reading.
     * @return void
     */
    public function mapChunks()
    {
        if (!$this->isOpened()) {
            $this->open();
        }
        $isMapped = true;
        $i = 0;
        while ($isMapped) {
            $isMapped = $this->mapLine($i);
            $i += $this->chunkSize;
        }
        die();
    }

    /**
     * Adds each file line in line/offset mapping table. DO NOT USE.
     * @return void
     */
    public function mapAll()
    {
        if (!$this->isOpened()) {
            $this->open();
        }
        $isMapped = true;
        $i = 0;
        while ($isMapped) {
            $isMapped = $this->mapLine($i);
            $i++;
        }
    }

    /**
     * Checks whether $linenumber is already mapped in the line/offset table
     * @return Boolean
     */
    public function isLineMapped($lineNumber)
    {
        return (isset($this->lineMap[$lineNumber]));
    }

    /**
     * Returns line/offset mapping table
     * @return Array
     */
    public function getLineMap()
    {
        return $this->lineMap;
    }

    /**
     * Closes file
     * @return Boolean
     */
    public function close()
    {
        if (!fclose($this->handler) || !fclose($this->handlerForMap)) {
            throw new IOException("Could not close " . $this->fileName);
            return false;
        }
        $this->handler = false;
        $this->handlerForMap = false;
        return true;
    }

    public function getLines(int $startLine = 1, int $limit = 0)
    {
        if ($startLine < 0) {
            throw new \ErrorException('Invalid starting line : ' . $startLine);
        }
        if (!$this->isOpened()) {
            $this->open();
        }

        if (!isset($startLine) || $startLine == 0) {
            $startLine = 1;
        }

        $noLimit = (!isset($limit) || $limit <= 0);

        if ($this->avoidFirstLine && $startLine === 1) {
            $startLine++;
            $count = 1;
        } else {
            $count = 0;
        }

        $idx = $startLine;

        $isMapped = $this->mapLine($startLine);

        if (!$isMapped) {
            return [];
        }

        $lines = [];
        fseek($this->handler, $this->lineMap[$startLine], SEEK_SET);

        while (($noLimit || $count < $limit) && !feof($this->handler) && ($line = $this->getCsvLine($this->handler)) !== false) {
            if (isset($line[0]) or !$this->ignoreEmptyLines) {
                $lines[$idx] = $line;
            }
            $count++;
            $idx++;
        }
        if (!$this->useEncodingFilter()) // otherwise it will be evaluated on the fly on next iteration
        {
            $pos = ftell($this->handler);
            $this->lineMap[$startLine + $count] = $pos;
        }

        return $lines;
    }

    public function getEscape()
    {
        return $this->escape;
    }

    public function setEscape($escape)
    {
        $this->escape = $escape;
        if ($this->isOpened()) {
            $this->close();
        }
        return true;
    }

    public function getAvoidFirstLine()
    {
        return $this->avoidFirstLine;
    }

    public function setAvoidFirstLine($avoidFirstLine)
    {
        $this->avoidFirstLine = (bool)$avoidFirstLine;
        if ($this->isOpened()) {
            $this->close();
        }
        return true;
    }

    public function getOffset()
    {
        if ($this->isOpened()) {
            return ftell($this->handler);
        } else {
            return 0;
        }
    }

    public function getChunksize()
    {
        return $this->chunkSize;
    }

    public function setChunksize($chunkSize)
    {
        $this->chunkSize = (int)$chunkSize;
        if ($this->isOpened()) {
            $this->close();
        }
        $this->lineMap = array();
        $this->mapChunks();
        return true;
    }
}
