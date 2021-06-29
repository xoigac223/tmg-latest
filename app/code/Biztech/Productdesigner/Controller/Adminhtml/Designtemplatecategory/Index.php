<?php
/**
 * Copyright © 2015 Biztech. All rights reserved.
 */

namespace Biztech\Productdesigner\Controller\Adminhtml\Designtemplatecategory;

class Index extends \Biztech\Productdesigner\Controller\Adminhtml\Designtemplatecategory
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
        $resultPage->setActiveMenu('Biztech_Productdesigner::designtemplatecategory');
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Design Templates Category'));
        
       
        return $resultPage;
    }
}
