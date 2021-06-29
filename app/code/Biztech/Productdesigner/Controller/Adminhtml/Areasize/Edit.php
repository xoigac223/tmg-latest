<?php
/**
 * Copyright © 2015 Biztech. All rights reserved.
 */

namespace Biztech\Productdesigner\Controller\Adminhtml\Areasize;

class Edit extends \Biztech\Productdesigner\Controller\Adminhtml\Areasize
{

    public function execute()
    {
	 $resultPage = $this->resultPageFactory->create();

	$resultPage->getConfig()->getTitle()->set((__('Area size')));
        $id = $this->getRequest()->getParam('id');
       
        
        $model = $this->_objectManager->create('Biztech\Productdesigner\Model\Areasize');

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This item no longer exists.'));
                $this->_redirect('productdesigner/areasize');
                return;
            }
        }
        // set entered data if was error when we do save
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getPageData(true);
        
        if (!empty($data)) {
            $model->addData($data);
        }
        $this->_coreRegistry->register('current_biztech_productdesigner_areasize', $model);
        
        $this->_initAction();
        //$this->_view->getLayout()->getBlock('items_items_edit');
        $this->_view->renderLayout();
    }
}
