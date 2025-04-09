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

class PlainFileWriter extends FileAccessor
{
    protected $header = null;
    protected $footer = null;
    protected $lineEndings = PHP_EOL;
    protected string $delimiter = '';
    protected string $enclosure = '';

    protected string $fromEncoding = 'UTF-8';
    protected $fileEmpty;

    
    public function __construct(string $fileName, string $mode = 'a', array $params)
    {
        parent::__construct($fileName, $mode, $params);

        // Optionnal parameters
        if (isset($params['header'])) $this->header = $params['header'];
        if (isset($params['footer'])) $this->footer = $params['footer'];
        if (isset($params['lineEndings'])) $this->lineEndings = $params['lineEndings'];
        if (isset($params['delimiter'])) $this->delimiter = $params['delimiter'];
        if (isset($params['enclosure'])) $this->enclosure = $params['enclosure'];

        $this->fileEmpty = @filesize($this->fileName) === 0;
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

        if (file_exists($this->fileName) && !is_writable($this->fileName)) {
            throw new \Exception("Can not write to {$this->fileName}");
        }

        $this->handler = fopen($this->fileName, $this->mode);
        if ($this->getHandler() === false) {
            throw new \Exception("Could not open " . $this->fileName);
        }
        $this->registerEncodingFilter();

        $this->fileEmpty = filesize($this->fileName) == 0;

        return true;
    }

    /**
     * Reads the file : with no parameters, returns an array containing all the lines in the file.
     * If $startLine is valid, returns $limit lines or all lines between $startLine and the end of the file.
     * @param Integer $start
     * @param Integer $limit
     * @return array
     */
    public function putLines($lines)
    {
        if (!$this->isOpened()) {
            $this->open();
        }

        if ($this->fileEmpty && $this->header !== null) {
            $this->putHeader();
        }
        $enclosure = $this->enclosure;

        foreach($lines as $line) {
            $content = $enclosure . implode($enclosure . $this->delimiter . $enclosure, $line) . $enclosure;
            $this->writeStringToFile($content);
        }
        $this->fileEmpty = false;
    }

    public function putHeader()
    {
        if ($this->fileEmpty && $this->header !== null) {
            $this->writeStringToFile($this->header);
        }
    }

    public function putFooter()
    {
        if ($this->footer !== null && !$this->fileEmpty) {
            $this->writeStringToFile($this->footer);
        }
    }

    /**
     * @param String $stringFramework::fatal('attributes ' . var_export($attributes, true));
     */
    public function writeStringToFile($string)
    {
        if (!$this->isOpened()) {
            $this->open();
        }

        if ($this->lineEndings != PHP_EOL) {
            $outputHandler = fopen('php://output', 'w');
            ob_start();
            fputs($outputHandler, $string, strlen($string));
            fclose($outputHandler);
            $content = ob_get_clean();
            $content = substr($content, 0, 0 - strlen(PHP_EOL)).$this->lineEndings;
            fwrite($this->handler, $content);
        }
        else
        {
            $string = $string . $this->lineEndings;
            fputs($this->handler, $string, strlen($string));
        }
    }
}