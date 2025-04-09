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

use Griiv\SynchroEngine\Core\Csv\PlainFileWriter;

class PlainFileDataTarget extends AbstractDataTarget
{
    /**
     * @var String
     */
    protected $fileName;

    /**
     * @var String
     */
    protected $header = null;

    /**
     * @var String
     */
    protected $footer = null;

    /**
     * @var String
     */
    protected $toEncoding = 'UTF-8';

    /**
     * @var PlainFileWriter
     */
    protected $currentFile;

    public function __construct($params)
    {
        if (!isset($params['fileName']))
        {
            throw new \Exception('fileName parameter is required');
        }

        $this->fileName = $params['fileName'];
        if (isset($params['header'])) $this->header = $params['header'];
        if (isset($params['footer'])) $this->header = $params['footer'];
        if (isset($params['toEncoding'])) $this->toEncoding = $params['toEncoding'];
        $this->currentFile = new PlainFileWriter($this->fileName, 'a', $params);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return get_class($this)." ".$this->fileName;
    }

    /**
     * @return boolean
     */
    public function hasCurrentFile()
    {
        return (isset($this->currentFile));
    }

    public function addItems($data)
    {
        if ($this->hasCurrentFile() && $this->getCurrentFile() !== null)
        {
            $this->getCurrentFile()->putLines($data);
        }
    }

    public function addHeader()
    {
        if ($this->hasCurrentFile() && $this->getCurrentFile() !== null)
        {
            $this->getCurrentFile()->putHeader();
        }
    }

    public function addFooter()
    {
        if ($this->hasCurrentFile() && $this->getCurrentFile() !== null)
        {
            $this->getCurrentFile()->putFooter();
        }
    }

    /**
     * @return PlainFileWriter
     */
    public function getCurrentFile()
    {
        return ($this->hasCurrentFile()) ? $this->currentFile : null;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }
}