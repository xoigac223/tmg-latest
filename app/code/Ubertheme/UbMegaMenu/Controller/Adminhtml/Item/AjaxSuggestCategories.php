<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 */
namespace Ubertheme\UbMegaMenu\Controller\Adminhtml\Item;

class AjaxSuggestCategories extends \Magento\Catalog\Controller\Adminhtml\Category
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->layoutFactory = $layoutFactory;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $block = $this->layoutFactory->create()->createBlock('Ubertheme\UbMegaMenu\Block\Adminhtml\Item\SuggestCategories');
        $jsonData = $block->getJSONCategories($this->getRequest()->getParam('label_part'), $this->getRequest()->getParam('store_id'));

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setJsonData($jsonData);
    }
}
