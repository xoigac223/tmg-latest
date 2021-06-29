<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExport\Model\Source\Type;

use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class Url
 * @package Firebear\ImportExport\Model\Source\Type
 */
class Google extends AbstractType
{
    /**
     * @var string
     */
    protected $code = 'google';

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
            $fileName = $this->fileName .".csv";
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
                $read = $this->readFactory->create($url, DriverPool::HTTP);
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

            $read = $this->readFactory->create($url, DriverPool::HTTP);

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
                $this->fileName = str_replace($matches[0], '', $this->fileName);
            }
            $url = $this->fileName;
            $this->fileName = str_replace('&', '', $this->fileName);
            $this->client = $this->readFactory->create($url, DriverPool::HTTP);
        }

        return $this->client;
    }
}
