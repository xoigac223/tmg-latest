<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExport\Model\Source\Type;

use Magento\Framework\Filesystem\DriverPool;

/**
 * Class Url
 * @package Firebear\ImportExport\Model\Source\Type
 */
class Url extends AbstractType
{
    /**
     * @var string
     */
    protected $code = 'url';

    /**
     * @var string
     */
    protected $fileName;

    /**
     * Download remote source file to temporary directory
     *
     * @return bool|string
     */
    public function uploadSource()
    {
        if ($read = $this->_getSourceClient()) {
            $fileName = '';
            /* preg_match('/(\w+\.(csv|xml|txt))/i',$this->fileName, $matches);
             if ($matches) {
                 $fileName = $matches[0];
             }*/
            $fileName = $this->fileName;
            if ($this->getFormatFile()) {
                $fileName .= "." . strtolower($this->getFormatFile());
            }
            $fileName = str_replace("%", "_", $fileName);
            $this->directory->writeFile(
                $this->directory->getRelativePath($this->getImportPath() . '/' . $fileName),
                $read->readAll()
            );

            return $this->directory->getRelativePath($this->getImportPath() . '/' . $fileName);
        }

        return false;
    }

    /**
     * Download remote images to temporary media directory
     *
     * @param $importImage
     * @param $imageSting
     * @return bool
     */
    public function importImage($importImage, $imageSting)
    {
        $filePath = $this->directory->getAbsolutePath($this->getMediaImportPath() . $imageSting);
        $dirname = dirname($filePath);
        if (!is_dir($dirname)) {
            mkdir($dirname, 0775, true);
        }
        if (preg_match('/\bhttps?:\/\//i', $importImage, $matches)) {
            $url = str_replace($matches[0], '', $importImage);
        } else {
            $sourceFilePath = $this->getData($this->code . '_file_path');
            $sourceDir = dirname($sourceFilePath);
            $url = $sourceDir . '/' . $importImage;
            if (preg_match('/\bhttps?:\/\//i', $url, $matches)) {
                $url = str_replace($matches[0], '', $url);
            }
        }

        if ($url) {
            try {
                $driver = $this->getProperDriverCode($matches);
                $read = $this->readFactory->create($url, $driver);
                $this->directory->writeFile(
                    $filePath,
                    $read->readAll()
                );
            } catch (\Exception $e) {
            }
        }

        return true;
    }

    public function importImageCategory($importImage, $imageSting)
    {
        $filePath = $this->directory->getAbsolutePath('pub/media/catalog/category/' . $imageSting);
        $dirname = dirname($filePath);
        if (!is_dir($dirname)) {
            mkdir($dirname, 0775, true);
        }
        if (preg_match('/\bhttps?:\/\//i', $importImage, $matches)) {
            $url = str_replace($matches[0], '', $importImage);
        } else {
            $sourceFilePath = $this->getData($this->code . '_file_path');
            $sourceDir = dirname($sourceFilePath);
            $url = $sourceDir . '/' . $importImage;
            if (preg_match('/\bhttps?:\/\//i', $url, $matches)) {
                $url = str_replace($matches[0], '', $url);
            }
        }
        if ($url) {
            try {
                $driver = $this->getProperDriverCode($matches);
                $read = $this->readFactory->create($url, $driver);
                $this->directory->writeFile(
                    $filePath,
                    $read->readAll()
                );
            } catch (\Exception $e) {
            }
        }

        return true;
    }

    /**
     * Check if remote file was modified since the last import
     *
     * @param int $timestamp
     * @return bool|int
     */
    public function checkModified($timestamp)
    {
        $fileName = $this->getData($this->code . '_file_path');
        if (preg_match('/\bhttps?:\/\//i', $fileName, $matches)) {
            $url = str_replace($matches[0], '', $fileName);
            $driver = $this->getProperDriverCode($matches);
            $read = $this->readFactory->create($url, $driver);

            if (!$this->metadata) {
                $this->metadata = $read->stat();
            }

            $modified = strtotime($this->metadata['mtime']);

            return ($timestamp != $modified) ? $modified : false;
        }

        return false;
    }

    /**
     * Prepare and return Driver client
     *
     * @return \Magento\Framework\Filesystem\File\ReadInterface
     */
    protected function _getSourceClient()
    {
        if (!$this->fileName) {
            $this->fileName = $this->getData($this->code . '_file_path');
        }

        if (!$this->client) {
            if (preg_match('/\bhttps?:\/\//i', $this->fileName, $matches)) {
                $url = str_replace($matches[0], '', $this->fileName);
                $driver = $this->getProperDriverCode($matches);
                $this->client = $this->readFactory->create($url, $driver);
            }
        }

        return $this->client;
    }

    /**
     * @param $matches
     *
     * @return string
     */
    protected function getProperDriverCode($matches)
    {
        if (is_array($matches)) {
            return (false === strpos($matches[0], 'https'))
                ? DriverPool::HTTP
                : DriverPool::HTTPS;
        } else {
            return DriverPool::HTTP;
        }
    }
}
