<?php

/**
 * Copyright © 2015 Biztech. All rights reserved.
 */

namespace Biztech\Productdesigner\Controller\Adminhtml\view;

use \Magento\Framework\View\LayoutFactory;

class viewDesign extends \Magento\Backend\App\Action {

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * Initialize Group Controller
     *
     * @param \Magento\Backend\App\Action\Context $context

     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    protected $_layoutFactory;

    public function __construct(
    \Magento\Backend\App\Action\Context $context,
            \Magento\Framework\View\Result\PageFactory $resultPageFactory,
            LayoutFactory $layoutFactory
    ) {

        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_layoutFactory = $layoutFactory;
    }

    public function execute() {
        

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('View Design'));
        return $resultPage;

    }

}
