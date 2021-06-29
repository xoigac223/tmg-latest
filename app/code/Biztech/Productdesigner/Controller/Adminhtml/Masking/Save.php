<?php

/**
 * Copyright © 2015 Biztech. All rights reserved.
 */

namespace Biztech\Productdesigner\Controller\Adminhtml\Masking;

class Save extends \Biztech\Productdesigner\Controller\Adminhtml\Masking {

    public function execute() {
        if ($this->getRequest()->getPostValue()) {
            try {
                $model = $this->_objectManager->create('Biztech\Productdesigner\Model\Masking');


                $data = $this->getRequest()->getPostValue();
                $inputFilter = new \Zend_Filter_Input(
                        [], [], $data
                );
                $data = $inputFilter->getUnescaped();
                if (isset($data['parent_categories'])) {
                    $data['is_root_category'] = 0;
                }
                if ($data['is_root_category'] == 1) {
                    $data['parent_categories'] = '';
                }
                $id = $this->getRequest()->getParam('id');
                
                if(isset($data['stores'])) {
                    if(in_array('0',$data['stores'])){
                       $data['store_id'] = '0';
                    }
                    else{
                       $data['store_id'] = implode(",", $data['stores']);
                    }
                    unset($data['stores']);
                }
                
                 
                
                if ($id) {
                    $model->load($id);
                    if ($id != $model->getId()) {
                        throw new \Magento\Framework\Exception\LocalizedException(__('The wrong item is specified.'));
                    }
                }
                $model->setData($data)->setId($id);
                $session = $this->_objectManager->get('Magento\Backend\Model\Session');
                $session->setPageData($model->getData());
                $model->save();

                // store cliaprt media images

                if (isset($data['masking'])) {
                    $masking_gallery = $data['masking'];
                }

              

                if ($model->getId()) {
                    if (isset($masking_gallery)) {
                        foreach ($masking_gallery as $m_gallery) {
                            $masking_model = $this->_objectManager->create('Biztech\Productdesigner\Model\Maskingmedia');
                            $inputFilter = new \Zend_Filter_Input(
                                    [], [], $m_gallery
                            );
                            $data = $inputFilter->getUnescaped();
                            $image_id = $data['image_id'];

                            
                            if ($image_id) {
                                $masking_model->load($image_id);
                            }
                              $exclude = (isset($data['exclude'])) ? 1 : 0;
                             
                            $remove = isset($data['remove']) ? 1 : 0;
                            
                             $dataArray = array(
                                'masking_id' => $model->getId(),
                                'image_path' => $data['file'],
                                'label' => $data['label'],
                                'tags' => $data['tags'],
                                'position' => $data['sort'],
                                'disabled' => $exclude,
                                'remove' => $remove,
                            );
                             
                            if ($dataArray['remove'] == 1) {
                                $masking_model->load($image_id);
                                $masking_model->delete();
                            } else {
                                if ($image_id) {
                                    $masking_model->setData($dataArray)->setId($image_id);
                                    $masking_model->save();
                                } else {
                                    $masking_model->setData($dataArray);
                                    $masking_model->save();
                                }
                            }
                            $session = $this->_objectManager->get('Magento\Backend\Model\Session');
                            $session->setPageData($masking_model->getData());
                            $masking_model->save();
                        }
                    }
                }
                // store masking media images


                $this->messageManager->addSuccess(__('You saved the item.'));
                $session->setPageData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('productdesigner/masking/edit', ['id' => $model->getId()]);
                    return;
                }
                $this->_redirect('productdesigner/masking/');
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                $id = (int) $this->getRequest()->getParam('id');
                if (!empty($id)) {
                    $this->_redirect('productdesigner/masking/edit', ['id' => $id]);
                } else {
                    $this->_redirect('productdesigner/masking/new');
                }
                return;
            } catch (\Exception $e) {
                $this->messageManager->addError(
                        __('Something went wrong while saving the item data. Please review the error log.')
                );
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->_objectManager->get('Magento\Backend\Model\Session')->setPageData($data);
                $this->_redirect('productdesigner/masking/edit', ['id' => $this->getRequest()->getParam('id')]);
                return;
            }
        }
        $this->_redirect('productdesigner/masking/');
    }

}
