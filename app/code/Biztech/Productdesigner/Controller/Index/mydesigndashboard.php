<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Biztech\Productdesigner\Controller\Index;

class mydesigndashboard extends \Magento\Framework\App\Action\Action {

    /**
     * Index action
     *
     * @return $this
     */
    protected $resultPageFactory;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory) {
        $this->resultPageFactory = $resultPageFactory;

        parent::__construct($context);
    }

    public function execute() {

        $resultPage = $this->resultPageFactory->create();        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();               
        $resultPage->getConfig()->getTitle()->set(__('My Design'));
        return $resultPage;
    }

}
