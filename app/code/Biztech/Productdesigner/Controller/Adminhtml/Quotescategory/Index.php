<?php
/**
 * Copyright © 2015 Biztech. All rights reserved.
 */

namespace Biztech\Productdesigner\Controller\Adminhtml\Quotescategory;

class Index extends \Biztech\Productdesigner\Controller\Adminhtml\Quotescategory
{

    /**
     * Items list.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
       
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
       
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Biztech_Productdesigner:module');
        $resultPage->getConfig()->getTitle()->prepend(__('Quotes Category Manager'));
        
       
        return $resultPage;
    }
}
