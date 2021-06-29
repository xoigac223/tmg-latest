<?php
/**
 * Copyright © 2015 Biztech. All rights reserved.
 */

namespace Biztech\Productdesigner\Controller\Adminhtml\Masking;

class Edit extends \Biztech\Productdesigner\Controller\Adminhtml\Masking
{

    public function execute()
    {
	 $resultPage = $this->resultPageFactory->create();

	$resultPage->getConfig()->getTitle()->set((__('Masking')));
        $id = $this->getRequest()->getParam('id');
       
        $model1 = $this->_objectManager->create('Biztech\Productdesigner\Model\Mysql4\Masking\Collection');
        
        $model = $this->_objectManager->create('Biztech\Productdesigner\Model\Masking');

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This item no longer exists.'));
                $this->_redirect('productdesigner/masking');
                return;
            }
        }
        // set entered data if was error when we do save
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getPageData(true);
        
        if (!empty($data)) {
            $model->addData($data);
        }
        $this->_coreRegistry->register('current_biztech_productdesigner_masking', $model);
        $this->_coreRegistry->register('current_biztech_productdesigner_masking1', $model1);
        $this->_initAction();
        //$this->_view->getLayout()->getBlock('items_items_edit');
        $this->_view->renderLayout();
    }
}
