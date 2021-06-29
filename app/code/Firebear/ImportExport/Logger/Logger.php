<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExport\Logger;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

/**
 * Web UI Logger
 *
 * @package Magento\Setup\Model
 */
class Logger
{

    /**
     * Log File
     *
     * @var string
     */
    protected $logFile = '';

    /**
     * Currently open file resource
     *
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * Currently open file resource
     *
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $directory;

    /**
     * Indicator of whether inline output is started
     *
     * @var bool
     */
    protected $isInline = false;

    /**
     * Constructor
     *
     * @param Filesystem $filesystem
     * @param string $logFile
     */
    public function __construct(Filesystem $filesystem, $logFile = null)
    {
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::LOG);
        if ($logFile) {
            $this->logFile = $logFile;
        }
    }

    /**
     * Get log file name
     *
     * @return null|string
     */
    public function getFileName()
    {
        return $this->logFile;
    }

    /**
     * Set log file name
     *
     * @param $file
     *
     * @return $this
     */
    public function setFileName($file)
    {
        $this->logFile = '/firebear/' . $file . '.log';
        $logDir = $this->directory->getDriver()->getParentDirectory(
            $this->directory->getAbsolutePath() . 'firebear/' . $file . '.log'
        );
        if (!$this->directory->getDriver()->isDirectory($logDir)) {
            $this->directory->getDriver()->createDirectory($logDir);
        }
        return $this;
    }

    /**
     * Write critical message
     *
     * @param $message
     */
    public function critical($message)
    {
        $this->terminateLine();
        $this->writeToFile($message, 'critical');
    }

    /**
     * Write error message
     *
     * @param $message
     */
    public function error($message)
    {
        $this->terminateLine();
        $this->writeToFile($message, 'error');
    }

    /**
     * Write warning message
     *
     * @param $message
     */
    public function warning($message)
    {
        $this->terminateLine();
        $this->writeToFile($message, 'warning');
    }

    /**
     * Write info message
     *
     * @param       $message
     */
    public function info($message)
    {
        $this->terminateLine();
        $this->writeToFile($message, 'info');
    }

    /**
     * Write debug message
     *
     * @param $message
     */
    public function debug($message)
    {
        $this->terminateLine();
        $this->writeToFile($message, 'debug');
    }

    /**
     * Write success message
     *
     * @param $message
     */
    public function success($message)
    {
        $this->terminateLine();
        $this->writeToFile($message, 'success');
    }

    /**
     * Write the message to file
     *
     * @param string $message
     * @param string $type
     *
     * @return void
     */
    protected function writeToFile($message, $type = 'info')
    {
        if ($type) {
            $message = '<span class="console-' . $type . '">' . $message . '</span>';
        }
        $this->directory->writeFile($this->logFile, $message . "\r\n", 'a+');
    }

    /**
     * Gets contents of the log
     *
     * @return array
     */
    public function get()
    {
        $fileContents = explode(PHP_EOL, $this->directory->readFile($this->logFile));
        return $fileContents;
    }

    /**
     * Clears contents of the log
     *
     * @return void
     */
    public function clear()
    {
        if ($this->directory->isExist($this->logFile)) {
            $this->directory->delete($this->logFile);
        }
    }

    /**
     * Checks existence of install.log file
     *
     * @return bool
     */
    public function logfileExists()
    {
        return ($this->directory->isExist($this->logFile));
    }

    /**
     * Terminates line if the inline logging is started
     *
     * @return void
     */
    protected function terminateLine()
    {
        if ($this->isInline) {
            $this->writeToFile('<br>');
        }
    }
}
