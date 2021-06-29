<?php
/**
 * Solwin Infotech
 * Solwin Advanced Product Video Extension
 *
 * @category   Solwin
 * @package    Solwin_ProductVideo
 * @copyright  Copyright © 2006-2016 Solwin (https://www.solwininfotech.com)
 * @license    https://www.solwininfotech.com/magento-extension-license/ 
 */
namespace Solwin\ProductVideo\Model\Video;

use Magento\Framework\UrlInterface;

class File
{
    /**
     * Media sub folder
     * 
     * @var string
     */
    protected $_subDir = 'solwin/productvideo/video';

    /**
     * URL builder
     * 
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * File system model
     * 
     * @var \Magento\Framework\Filesystem
     */
    protected $_fileSystem;

    /**
     * constructor
     * 
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\Filesystem $fileSystem
     */
    public function __construct(
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\Filesystem $fileSystem
    ) {
        $this->_urlBuilder = $urlBuilder;
        $this->_fileSystem = $fileSystem;
    }

    /**
     * get images base url
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->_urlBuilder
                ->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA])
                .$this->_subDir.'/file';
    }
    /**
     * get base image dir
     *
     * @return string
     */
    public function getBaseDir()
    {
        return $this->_fileSystem
                ->getDirectoryWrite(
                        \Magento\Framework\App\Filesystem\DirectoryList::MEDIA)
                ->getAbsolutePath($this->_subDir.'/file');
    }
}