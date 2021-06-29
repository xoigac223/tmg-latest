<?php
/**
 * Copyright © 2015 Biztech. All rights reserved.
 */

namespace Biztech\Productdesigner\Controller\Adminhtml\Shapes;

class Massdelete extends \Biztech\Productdesigner\Controller\Adminhtml\Shapes
{

    public function execute()
    {

                $id = $this->getRequest()->getParam('shapes');
	
        if ($id) {
            try {
	
 $model = $this->_objectManager->create('Biztech\Productdesigner\Model\Shapes');
		 foreach ($id as $quotesId) 
               {
                $model->load($quotesId);
                $model->delete();
		}
                $this->messageManager->addSuccess(__('You deleted the item.'));
                $this->_redirect('productdesigner/shapes/');
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('We can\'t delete item right now. Please review the log and try again.')
                );
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->_redirect('productdesigner/shapes/edit', ['id' => $this->getRequest()->getParam('id')]);
                return;
            }
        }
        $this->messageManager->addError(__('We can\'t find a item to delete.'));
        $this->_redirect('productdesigner/shapes/');
    }
}
