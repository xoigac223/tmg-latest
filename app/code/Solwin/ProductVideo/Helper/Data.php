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
namespace Solwin\ProductVideo\Helper;

use Solwin\ProductVideo\Model\VideoFactory;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $_assetRepo;

    /**
   * @var \Magento\Backend\Model\UrlInterface
   */
   private $backendUrl;
    /**
     * @var VideoFactory
     */
    protected $_modelVideoFactory;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        VideoFactory $modelVideoFactory,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Asset\Repository $assetRepo
    ) {
        $this->_modelVideoFactory = $modelVideoFactory;
        $this->_storeManager = $storeManager;
        $this->_assetRepo = $assetRepo;
        $this->backendUrl = $backendUrl;
        parent::__construct($context);
    }

    /**
     * Get video collection
     */
    public function getVideoCollection($video_id) {
        $videoCollection = $this->_modelVideoFactory->create()
                ->getCollection()
                ->addFieldToFilter('video_id', $video_id)
                ->addStoreFilter($this->_storeManager->getStore()->getId())
                ->addFieldToFilter('status', 1);

        return $videoCollection;
    }

    /**
     * Get media url
     */
    public function getMediaUrl() {
        return $this->_storeManager
                ->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * Get vimeo id
     */
    public function getVimeoId($vimeoUrl) {
        preg_match('/\/\/(www\.)?vimeo.com\/(\d+)($|\/)/', $vimeoUrl, $matches);
        $vimeoId = $matches[2];
        return $vimeoId;
    }

    /**
     * Get youtube id
     */
    public function getYoutubeId($youtubeUrl) {
        preg_match('/[\\?\\&]v=([^\\?\\&]+)/', $youtubeUrl, $matches);
        $youtubeId = "";
        if(!empty($matches))  {
			$youtubeId = $matches[1];
		}
        return $youtubeId;
    }

    /**
     * Get configuration settings value
     */
    public function getConfigValue($value = '') {
        return $this->scopeConfig
                ->getValue(
                        $value,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                        );
    }

    /**
     * Get default image
     */
    public function getVideoIcon() {
        return $this->_assetRepo
                ->getUrl('Solwin_ProductVideo::images/video.png');
    }

    public function getProductsGridUrl()
    {
        return $this->backendUrl->getUrl('solwin_productvideo/video/products', ['_current' => true]);
    }
}
