<?php

namespace Solwin\ProductVideo\Controller\Adminhtml\Video;

use Magento\Backend\App\Action;
use Magento\TestFramework\ErrorLog\Logger;

/**
 * Class Products
 * @package Solwin\ProductVideo\Controller\Adminhtml\Video
 */
class Products extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    private $resultLayoutFactory;

    /**
     * Products constructor.
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
    ) {
        parent::__construct($context);
        $this->resultLayoutFactory = $resultLayoutFactory;
    }

    /**
     * @return bool
     */
    public function _isAllowed()
    {
        return true;
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultLayout = $this->resultLayoutFactory->create();
        $resultLayout->getLayout()->getBlock('video.edit.tab.products')
                     ->setInBanner($this->getRequest()->getPost('video_products', null));

        return $resultLayout;
    }
}
