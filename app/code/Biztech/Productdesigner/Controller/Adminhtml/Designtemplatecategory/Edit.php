<?php
/**
 * Copyright © 2015 Biztech. All rights reserved.
 */

namespace Biztech\Productdesigner\Controller\Adminhtml\Designtemplatecategory;

class Edit extends \Biztech\Productdesigner\Controller\Adminhtml\Designtemplatecategory
{

    public function execute()
    {
        //die("123");
	$resultPage = $this->resultPageFactory->create();

	$resultPage->getConfig()->getTitle()->set((__('Design Template category')));
        $id = $this->getRequest()->getParam('id');
        
        $model1 = $this->_objectManager->create('Biztech\Productdesigner\Model\Mysql4\Designtemplatecategory\Collection');
        $model = $this->_objectManager->create('Biztech\Productdesigner\Model\Designtemplatecategory');
        //print_r($model); die;
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This item no longer exists.'));
                $this->_redirect('productdesigner/designtemplatecategory');
                return;
            }
        }
        // set entered data if was error when we do save
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getPageData(true);
        
        if (!empty($data)) {
            $model->addData($data);
        }
        $this->_coreRegistry->register('current_biztech_productdesigner_designtemplatecategory', $model);
        $this->_coreRegistry->register('current_biztech_productdesigner_designtemplatecategory1', $model1);
        $this->_initAction();
        //$this->_view->getLayout()->getBlock('items_items_edit');
        $this->_view->renderLayout();
    }
}
