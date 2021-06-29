<?php

/**
 * Copyright © 2015 Biztech. All rights reserved.
 */

namespace Biztech\Productdesigner\Controller\Adminhtml\Shapes;

class Save extends \Biztech\Productdesigner\Controller\Adminhtml\Shapes {

    public function execute() {
        if ($this->getRequest()->getPostValue()) {
            try {
                $model = $this->_objectManager->create('Biztech\Productdesigner\Model\Shapes');
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

                if (isset($data['shapes'])) {
                    $shapes_gallery = $data['shapes'];
                }




                if ($model->getId()) {
                    if (isset($shapes_gallery)) {
                        foreach ($shapes_gallery as $s_gallery) {
                            $shapes_model = $this->_objectManager->create('Biztech\Productdesigner\Model\Shapesmedia');
                            $inputFilter = new \Zend_Filter_Input(
                                    [], [], $s_gallery
                            );
                            $data = $inputFilter->getUnescaped();
                            $image_id = $data['image_id'];
                            if ($image_id) {
                                $shapes_model->load($image_id);
                            }

                            $exclude = (isset($data['exclude'])) ? 1 : 0;

                            $remove = isset($data['remove']) ? 1 : 0;

                            $dataArray = array(
                                'shapes_id' => $model->getId(),
                                'image_path' => $data['file'],
                                'label' => $data['label'],
                                'tags' => $data['tags'],
                                'position' => $data['sort'],
                                'disabled' => $exclude,
                                'remove' => $remove,
                            );




                            if ($dataArray['remove'] == 1) {
                                $shapes_model->load($image_id);
                                $shapes_model->delete();
                            } else {
                                if ($image_id) {

                                    $shapes_model->setData($dataArray)->setId($image_id);
                                    $shapes_model->save();
                                } else {
                                    
                                   
                                    $shapes_model->setData($dataArray);
                                    $shapes_model->save();
                                }
                            }
                            $session = $this->_objectManager->get('Magento\Backend\Model\Session');
                            $session->setPageData($shapes_model->getData());
                            $shapes_model->save();
                        }
                    }
                }

                // store cliaprt media images







                $this->messageManager->addSuccess(__('You saved the item.'));
                $session->setPageData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('productdesigner/shapes/edit', ['id' => $model->getId()]);
                    return;
                }
                $this->_redirect('productdesigner/*/');
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                $id = (int) $this->getRequest()->getParam('id');
                if (!empty($id)) {
                    $this->_redirect('productdesigner/shapes/edit', ['id' => $id]);
                } else {
                    $this->_redirect('productdesigner/shapes/new');
                }
                return;
            } catch (\Exception $e) {
                $this->messageManager->addError(
                        __('Something went wrong while saving the item data. Please review the error log.')
                );
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->_objectManager->get('Magento\Backend\Model\Session')->setPageData($data);
                $this->_redirect('productdesigner/shapes/edit', ['id' => $this->getRequest()->getParam('id')]);
                return;
            }
        }
        $this->_redirect('productdesigner/shapes/');
    }

}
