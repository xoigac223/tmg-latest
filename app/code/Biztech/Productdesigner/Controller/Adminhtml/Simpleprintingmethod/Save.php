<?php
/**
 * Copyright © 2015 Biztech. All rights reserved.
 */

namespace Biztech\Productdesigner\Controller\Adminhtml\Simpleprintingmethod;

class Save extends \Biztech\Productdesigner\Controller\Adminhtml\Simpleprintingmethod
{
    public function execute()
    {
        if ($this->getRequest()->getPostValue()) {
            try {
                $model = $this->_objectManager->create('Biztech\Productdesigner\Model\Simpleprintingmethod');
			
		
                $data = $this->getRequest()->getPostValue();
                $inputFilter = new \Zend_Filter_Input(
                    [],
                    [],
                    $data
                );
                $data = $inputFilter->getUnescaped();
                $id = $this->getRequest()->getParam('id');
                
                
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $simpleprintingmethodcol = $objectManager->create('Biztech\Productdesigner\Model\Mysql4\Simpleprintingmethod\Collection')->addFieldToFilter('simpleprinting_code', array('eq' => $data['simpleprinting_code']));

                if (!$id) {
                    if(count($simpleprintingmethodcol)){
                        throw new \Magento\Framework\Exception\LocalizedException(__('Duplicate Printing Method'));
                    }
                }


                
                if ($id) {

                    $model->load($id);
                    if ($id != $model->getId()) {
                        throw new \Magento\Framework\Exception\LocalizedException(__('The wrong item is specified.'));
                    }
                }



                if(isset($data['stores'])) {
                    if(in_array('0',$data['stores'])){
                       $data['store_id'] = '0';
                    }
                    else{
                       $data['store_id'] = implode(",", $data['stores']);
                    }
                    unset($data['stores']);
                }

                $model->setData($data)->setId($id);
                $session = $this->_objectManager->get('Magento\Backend\Model\Session');
                $session->setPageData($model->getData());
                $model->save();
                $this->messageManager->addSuccess(__('You saved the item.'));
                $session->setPageData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('productdesigner/simpleprintingmethod/edit', ['id' => $model->getId()]);
                    return;
                }
                $this->_redirect('productdesigner/simpleprintingmethod/');
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                $id = (int)$this->getRequest()->getParam('id');
                if (!empty($id)) {
                    $this->_redirect('productdesigner/simpleprintingmethod/edit', ['id' => $id]);
                } else {
                    $this->_redirect('productdesigner/simpleprintingmethod/new');
                }
                return;
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('Something went wrong while saving the item data. Please review the error log.')
                );
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->_objectManager->get('Magento\Backend\Model\Session')->setPageData($data);
                $this->_redirect('productdesigner/simpleprintingmethod/edit', ['id' => $this->getRequest()->getParam('id')]);
                return;
            }
        }
        $this->_redirect('productdesigner/simpleprintingmethod/');
    }
}
