<?php
/**
 * Copyright © 2015 Biztech. All rights reserved.
 */

namespace Biztech\Productdesigner\Controller\Adminhtml\Simpleprintingmethod;

class Index extends \Biztech\Productdesigner\Controller\Adminhtml\Simpleprintingmethod
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
        $resultPage->setActiveMenu('Biztech_Productdesigner:simpleprintingmethod');
        $resultPage->getConfig()->getTitle()->prepend(__('Printing Method Manager'));
        
       
        return $resultPage;
    }
}
