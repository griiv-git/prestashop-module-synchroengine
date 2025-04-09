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

use Griiv\SynchroEngine\Core\Helpers\EncodingConverter;

abstract class FileAccessor
{
    protected string $fileName;
    protected string $mode;

    protected string $fromEncoding = '';
    protected string $toEncoding = '';
    protected $handler = false;

    public function __construct(string $fileName, string $mode, array $params)
    {
        if (!isset($fileName)) {
            throw new \ErrorException('required fileName parameter !');
        }

        if (!isset($mode)) {
            throw new \ErrorException('required mode parameter !');
        }

        // Required parameters
        $this->fileName = $fileName;
        $this->mode = $mode;

        if (isset($params['fromEncoding'])) $this->fromEncoding = $params['fromEncoding'];
        if (isset($params['toEncoding'])) $this->toEncoding = $params['toEncoding'];
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        try
        {
            if ($this->isOpened()) {
                $this->close();
            }
        } catch (\Exception $e) {
            throw new \ErrorException($e->getMessage());
        }
    }

    /**
     * Returns whether file is opened
     * @return Boolean
     */
    public function isOpened()
    {
        return ($this->getHandler() !== false && is_resource($this->getHandler()));
    }

    protected function useEncodingFilter()
    {
        return !empty($this->toEncoding) && !empty($this->fromEncoding) && $this->toEncoding !== $this->fromEncoding;
    }

    protected function registerEncodingFilter()
    {
        if ($this->useEncodingFilter()) {
            EncodingConverter::setInputEncoding($this->fromEncoding);
            EncodingConverter::setOutputEncoding($this->toEncoding);

            stream_filter_register("synchro_convert_encoding", "Griiv\\SynchroEngine\\Synchro\\Helpers\\EncodingConverter");
            stream_filter_prepend($this->handler, "synchro_convert_encoding");
        }
    }

    /**
     * Closes file
     * @return Boolean
     */
    public function close()
    {
        if (!fclose($this->handler)) {
            throw new \Exception("Could not close " . $this->fileName);
        }
        $this->handler = false;
        return true;
    }

    /** Getters/Setters **/


    public function getInfo()
    {
        var_dump($this);
    }

    public function getFileName()
    {
        return $this->fileName;
    }

    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
        if ($this->isOpened()) {
            $this->close();
        }
        return true;
    }

    public function getMode()
    {
        return $this->mode;
    }

    public function setMode($mode)
    {
        $this->mode = $mode;
        if ($this->isOpened()) {
            $this->close();
        }
        return true;
    }

    public function getFromEncoding()
    {
        return $this->fromEncoding;
    }

    public function setFromEncoding($fromEncoding)
    {
        $this->fromEncoding = $fromEncoding;
        if ($this->isOpened()) {
            $this->close();
        }
        return true;
    }

    public function getToEncoding()
    {
        return $this->toEncoding;
    }

    public function setToEncoding($toEncoding)
    {
        $this->toEncoding = $toEncoding;
        if ($this->isOpened()) {
            $this->close();
        }
        return true;
    }

    public function getHandler()
    {
        return $this->handler;
    }

    abstract public function open();
}
