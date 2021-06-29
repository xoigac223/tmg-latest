<?php
/**
 * Copyright © 2015 Biztech. All rights reserved.
 */

namespace Biztech\Productdesigner\Controller\Adminhtml\Simpleprintingmethod;

class Edit extends \Biztech\Productdesigner\Controller\Adminhtml\Simpleprintingmethod
{

    public function execute()
    {
	 $resultPage = $this->resultPageFactory->create();

	$resultPage->getConfig()->getTitle()->set((__('Manage Printing Method')));
        $id = $this->getRequest()->getParam('id');
       
        
        $model = $this->_objectManager->create('Biztech\Productdesigner\Model\Simpleprintingmethod');

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This item no longer exists.'));
                $this->_redirect('productdesigner/simpleprintingmethod');
                return;
            }
        }
        // set entered data if was error when we do save
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getPageData(true);
        
        if (!empty($data)) {
            $model->addData($data);
        }
        $this->_coreRegistry->register('current_biztech_productdesigner_simpleprintingmethod', $model);
        
        $this->_initAction();
        //$this->_view->getLayout()->getBlock('items_items_edit');
        $this->_view->renderLayout();
    }
}
