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
namespace Solwin\ProductVideo\Controller\Adminhtml\Video;

use Magento\Framework\Controller\Result\JsonFactory;

class Edit extends \Solwin\ProductVideo\Controller\Adminhtml\Video
{

    /**
     * Page factory
     * 
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * Result JSON factory
     * 
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_resultJsonFactory;

    /**
     * constructor
     * 
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     * @param \Solwin\ProductVideo\Model\VideoFactory $videoFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        \Solwin\ProductVideo\Model\VideoFactory $videoFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_resultJsonFactory = $resultJsonFactory;
        parent::__construct(
                $videoFactory, 
                $registry, 
                $context
                );
    }

    /**
     * is action allowed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Solwin_ProductVideo::video');
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page|
     * \Magento\Backend\Model\View\Result\Redirect|
     * \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('video_id');
        $video = $this->initVideo();
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Solwin_ProductVideo::video');
        $resultPage->getConfig()->getTitle()->set(__('Videos'));
        if ($id) {
            $video->load($id);
            if (!$video->getId()) {
                $this->messageManager
                        ->addError(__('This Video no longer exists.'));
                $resultRedirect = $this->_resultRedirectFactory->create();
                $resultRedirect->setPath(
                    'solwin_productvideo/*/edit',
                    [
                        'video_id' => $video->getId(),
                        '_current' => true
                    ]
                );
                return $resultRedirect;
            }
        }
        $title = $video->getId() ? $video->getTitle() : __('New Video');
        $resultPage->getConfig()->getTitle()->prepend($title);
        $data = $this->_session
                ->getData('solwin_productvideo_video_data', true);
        if (!empty($data)) {
            $video->setData($data);
        }
        return $resultPage;
    }
}