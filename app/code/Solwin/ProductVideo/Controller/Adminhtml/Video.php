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
namespace Solwin\ProductVideo\Controller\Adminhtml;

abstract class Video extends \Magento\Backend\App\Action
{
    /**
     * Video Factory
     * 
     * @var \Solwin\ProductVideo\Model\VideoFactory
     */
    protected $_videoFactory;

    /**
     * Core registry
     * 
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * constructor
     * 
     * @param \Solwin\ProductVideo\Model\VideoFactory $videoFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Solwin\ProductVideo\Model\VideoFactory $videoFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->_videoFactory          = $videoFactory;
        $this->_coreRegistry          = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Init Video
     *
     * @return \Solwin\ProductVideo\Model\Video
     */
    protected function initVideo()
    {
        $videoId  = (int) $this->getRequest()->getParam('video_id');
        /** @var \Solwin\ProductVideo\Model\Video $video */
        $video    = $this->_videoFactory->create();
        if ($videoId) {
            $video->load($videoId);
        }
        $this->_coreRegistry->register('solwin_productvideo_video', $video);
        return $video;
    }
}