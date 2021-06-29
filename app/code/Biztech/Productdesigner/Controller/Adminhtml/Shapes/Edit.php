<?php
/**
 * Copyright © 2015 Biztech. All rights reserved.
 */

namespace Biztech\Productdesigner\Controller\Adminhtml\Shapes;

class Edit extends \Biztech\Productdesigner\Controller\Adminhtml\Shapes
{

    public function execute()
    {
	 $resultPage = $this->resultPageFactory->create();

	$resultPage->getConfig()->getTitle()->set((__('Manage Shape')));
        $id = $this->getRequest()->getParam('id');
        $model1 = $this->_objectManager->create('Biztech\Productdesigner\Model\Mysql4\Shapes\Collection');
        $model = $this->_objectManager->create('Biztech\Productdesigner\Model\Shapes');

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This item no longer exists.'));
                $this->_redirect('productdesigner/shapes');
                return;
            }
        }
        // set entered data if was error when we do save
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getPageData(true);
        
        if (!empty($data)) {
            $model->addData($data);
 $model1->addData($data);
        }
	
        $this->_coreRegistry->register('current_biztech_productdesigner_shapes', $model);
        $this->_coreRegistry->register('current_biztech_productdesigner_shapes1', $model1);
        $this->_initAction();
        //$this->_view->getLayout()->getBlock('items_items_edit');
        $this->_view->renderLayout();
    }
}
