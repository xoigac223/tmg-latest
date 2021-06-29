<?php
/**
 * Copyright © 2015 Biztech. All rights reserved.
 */

namespace Biztech\Productdesigner\Controller\Adminhtml\Clipart;

class Edit extends \Biztech\Productdesigner\Controller\Adminhtml\Clipart
{

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();

	$resultPage->getConfig()->getTitle()->set((__('Manage Clipart')));
        $id = $this->getRequest()->getParam('id');
        $model1 = $this->_objectManager->create('Biztech\Productdesigner\Model\Mysql4\Clipart\Collection');
        
        $model = $this->_objectManager->create('Biztech\Productdesigner\Model\Clipart');

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This item no longer exists.'));
                $this->_redirect('productdesigner/clipart');
                return;
            }
        }
        // set entered data if was error when we do save
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getPageData(true);
        
        if (!empty($data)) {
            $model->addData($data);
            
 $model1->addData($data);
        }
	
        $this->_coreRegistry->register('current_biztech_productdesigner_clipart', $model);
        $this->_coreRegistry->register('current_biztech_productdesigner_clipart1', $model1);
        $this->_initAction();
        //$this->_view->getLayout()->getBlock('items_items_edit');
        $this->_view->renderLayout();
    }
}
