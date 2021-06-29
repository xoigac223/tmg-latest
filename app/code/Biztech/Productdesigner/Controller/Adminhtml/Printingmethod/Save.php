<?php
/**
 * Copyright © 2015 Biztech. All rights reserved.
 */

namespace Biztech\Productdesigner\Controller\Adminhtml\Printingmethod;

class Save extends \Biztech\Productdesigner\Controller\Adminhtml\Printingmethod
{
    public function execute()
    {
        if ($this->getRequest()->getPostValue()) {
            try {
                $model = $this->_objectManager->create('Biztech\Productdesigner\Model\Printingmethod');
			
		
                $data = $this->getRequest()->getPostValue();

               
                

                if (isset($data['links']['colors'])) {
                    $colors = $this->_objectManager->create('Magento\Backend\Helper\Js')->decodeGridSerializedInput($data['links']['colors']);
                    $keys = array();
                    foreach ($colors as $key => $color):
                        $keys[] = $key;
                    endforeach;
                    $data['colorqty'] = implode(',',$keys);
                }
                if (isset($data['links']['areasize'])) {
                    $areasize = $this->_objectManager->create('Magento\Backend\Helper\Js')->decodeGridSerializedInput($data['links']['areasize']);
                    $keys_area = array();
                    foreach ($areasize as $key_area => $areasize):
                        $keys_area[] = $key_area;
                    endforeach;
                    $data['areasize'] = implode(',',$keys_area);
                }
                if (isset($data['store_view'])) {
                    $data['store_id'] = implode(',',$data['store_view']);
                }
                if (isset($data['customer_groups'])) {
                    $data['customer_groups'] = implode(',',$data['customer_groups']);
                }

               

                $inputFilter = new \Zend_Filter_Input(
                    [],
                    [],
                    $data
                );
                $data = $inputFilter->getUnescaped();
                $id = $this->getRequest()->getParam('id');
                


                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                

                if (!$id) {
                    $printingmethodcol = $objectManager->create('Biztech\Productdesigner\Model\Mysql4\Printingmethod\Collection')->addFieldToFilter('printing_code', array('eq' => $data['printing_code']));
                    if(count($printingmethodcol)){
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
                    $this->_redirect('productdesigner/printingmethod/edit', ['id' => $model->getId()]);
                    return;
                }
                $this->_redirect('productdesigner/printingmethod/');
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                $id = (int)$this->getRequest()->getParam('id');
                if (!empty($id)) {
                    $this->_redirect('productdesigner/printingmethod/edit', ['id' => $id]);
                } else {
                    $this->_redirect('productdesigner/printingmethod/new');
                }
                return;
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('Something went wrong while saving the item data. Please review the error log.')
                );
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->_objectManager->get('Magento\Backend\Model\Session')->setPageData($data);
                $this->_redirect('productdesigner/printingmethod/edit', ['id' => $this->getRequest()->getParam('id')]);
                return;
            }
        }
        $this->_redirect('productdesigner/printingmethod/');
    }
}
