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

class CsvFileWriter extends CsvFileAccessor
{
    protected ?array $headers = null;
    protected string $lineEndings = PHP_EOL;

    protected string $fromEncoding = 'UTF-8';

    protected bool $fileEmpty;

    /**
     * Constructor
     * @param String $fileName
     * @param String $mode
     * @param array $params
     */

    public function __construct(string $fileName, string $mode = 'a', array $params)
    {
        parent::__construct($fileName, $mode, $params);

        // Optionnal parameters
        if (isset($params['headers'])) {
            $this->headers = $params['headers'];
        }

        if (isset($params['lineEndings'])) {
            $this->lineEndings = $params['lineEndings'];
        }
    }

    /**
     * Opens file
     * @return Boolean
     * @throws \Exception
     */
    public function open()
    {
        if ($this->isOpened()) {
            $this->close();
        }

        if (file_exists($this->fileName) && !is_writable($this->fileName)) {
            throw new \Exception("Can not write to {$this->fileName}");
        }

        $this->handler = fopen($this->fileName, $this->mode);
        if ($this->getHandler() === false) {
            throw new \Exception("Could not open " . $this->fileName);
        }
        $this->registerEncodingFilter();

        $this->fileEmpty = filesize($this->fileName) === 0;

        return true;
    }

    /**
     * Reads the file : with no parameters, returns an array containing all the lines in the file.
     * If $startLine is valid, returns $limit lines or all lines between $startLine and the end of the file.
     * @param array $lines
     * @return array
     * @throws \Exception
     */
    public function putLines(array $lines)
    {
        if (!$this->isOpened()) {
            $this->open();
        }

        if ($this->headers !== null && $this->fileEmpty) {
            array_unshift($lines, $this->headers);
        }

        foreach ($lines as $line) {
            if ($this->lineEndings !== PHP_EOL) {
                $outputHandler = fopen('php://output', 'w');
                ob_start();
                fputcsv($outputHandler, $line, $this->delimiter, $this->enclosure);
                fclose($outputHandler);
                $csv = ob_get_clean();
                $csv = substr($csv, 0, 0 - strlen(PHP_EOL)) . $this->lineEndings;
                fwrite($this->handler, $csv);
            } else {
                fputcsv($this->handler, $line, $this->delimiter, $this->enclosure);
            }
        }
        $this->fileEmpty = false;
    }
}
