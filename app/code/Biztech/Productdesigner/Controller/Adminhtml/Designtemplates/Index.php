<?php
/**
 * Copyright © 2015 Biztech. All rights reserved.
 */

namespace Biztech\Productdesigner\Controller\Adminhtml\Designtemplates;

class Index extends \Biztech\Productdesigner\Controller\Adminhtml\Designtemplates
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
        $resultPage->setActiveMenu('Biztech_Productdesigner::designtemplate');
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Design Templates'));
        
       
        return $resultPage;
    }
    protected function _isAllowed() {
        return $this->_authorization->isAllowed('Biztech_Productdesigner::designtemplates');
    }
}
