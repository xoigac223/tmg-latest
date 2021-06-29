<?php
/**
 * Blackbird ContentManager Module
 *
 * NOTICE OF LICENSE
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@bird.eu so we can send you a copy immediately.
 *
 * @category        Blackbird
 * @package         Blackbird_ContentManager
 * @copyright       Copyright (c) 2017 Blackbird (https://black.bird.eu)
 * @author          Blackbird Team
 * @license         https://www.advancedcontentmanager.com/license/
 */
namespace Blackbird\ContentManager\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\UrlInterface;
use Blackbird\ContentManager\Model\ContentType;

/**
 * Content Manager image helper
 */
class Image extends AbstractHelper
{
    const IMAGE_CACHE_DIR = 'cache/';
    
    const DEFAULT_QUALITY = 89;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    
    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $_mediaDirectory;
    
    /**
     * @var \Magento\Framework\Image\Factory
     */
    protected $_imageFactory;
    
    /**
     * @var \Magento\Framework\Image
     */
    protected $_imageProcessor;
    
    /**
     * @var string
     */
    protected $_baseFile;
    
    /**
     * @var string
     */
    protected $_fullBaseFile;
    
    /**
     * @var string
     */
    protected $_imageCacheDestination;
    
    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Image\Factory $imageFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Image\Factory $imageFactory
    ) {
        $this->_storeManager = $storeManager;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->_imageFactory = $imageFactory;
        parent::__construct($context);
    }
    
    /**
     * @param $file
     * @param $baseDir
     * @return $this
     * @throws \Exception
     */
    protected function setBaseFile($file, $baseDir)
    {
        $this->_baseFile = str_replace('//', '/', $baseDir . '/' . $file);
        $this->_fullBaseFile = '';

        if (!$file || !$this->_mediaDirectory->isExist($this->getFullBaseFile())) {
            $this->_baseFile = '';
            $this->_fullBaseFile = '';
            throw new \Exception(__('We can\'t find the image file.'));
        }
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getBaseFile()
    {
        return $this->_baseFile;
    }
    
    /**
     * @return string
     */
    public function getFullBaseFile()
    {
        if (!empty($this->getBaseFile()) && empty($this->_fullBaseFile)) {
            $this->_fullBaseFile = str_replace('//', '/', ContentType::CT_FILE_FOLDER . $this->getBaseFile());
        }

        return $this->_fullBaseFile;
    }
    
    /**
     * @return \Magento\Framework\Image
     */
    public function getImage()
    {
        if (!$this->_imageProcessor) {
            $this->_imageProcessor = $this->_imageFactory->create();
        }
        return $this->_imageProcessor;
    }
    
    /**
     * Init the helper
     * 
     * @param string $file
     * @param string $baseDir
     * @return $this
     */
    public function init($file, $baseDir)
    {
        try {
            $this->setBaseFile($file, $baseDir);
            $filename = $this->_mediaDirectory->getAbsolutePath($this->getFullBaseFile());
            $this->_imageProcessor = $this->_imageFactory->create($filename);
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
        }

        return $this;
    }
    
    /**
     * Resize the current processed image
     * 
     * @param int $width
     * @param int $height
     * @param bool $keepAspectRatio
     * @return \Magento\Framework\Image
     */
    public function resize($width, $height, $keepAspectRatio = true)
    {
        $width = (int) $width;
        $height = (int) $height;
        $image = $this->getImage();
        $resizePath = $width . 'x' . $height . '/';
        $destination = $this->getCacheDestination($resizePath) . $this->getBaseFile();
        
        try {
            $image->constrainOnly(true);
            $image->quality(self::DEFAULT_QUALITY);
            $image->keepAspectRatio($keepAspectRatio);
            $image->keepTransparency(true);
            $image->resize($width, $height);
            $image->save($this->_mediaDirectory->getAbsolutePath($destination));
        } catch (\Exception $e) {
            $this->_logger->critical($e->getMessage());
        }
        
        return $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $destination;
    }
    
    /**
     * @param string|null $subFolder
     * @return string
     */
    protected function getCacheDestination($subFolder = null)
    {
        $destinationCachePath = $this->getImageCacheDestination();
        if (!empty($subFolder)) {
            $destinationCachePath .= $subFolder;
            $this->_mediaDirectory->create($destinationCachePath);
        }
        
        return $destinationCachePath;
    }
    
    /**
     * @return string
     */
    protected function getImageCacheDestination()
    {
        if (!$this->_imageCacheDestination) {
            $destinationCache = ContentType::CT_FILE_FOLDER . self::IMAGE_CACHE_DIR;
            $this->_mediaDirectory->create($destinationCache);
            $this->_imageCacheDestination = $destinationCache;
        }
        
        return $this->_imageCacheDestination;
    }
}
