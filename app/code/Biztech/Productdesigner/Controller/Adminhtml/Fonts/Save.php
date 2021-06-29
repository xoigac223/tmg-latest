<?php
/**
 * Copyright © 2015 Biztech. All rights reserved.
 */

namespace Biztech\Productdesigner\Controller\Adminhtml\Fonts;

class Save extends \Biztech\Productdesigner\Controller\Adminhtml\Fonts
{
    public function execute()
    {
        if ($this->getRequest()->getPostValue()) {
            try {
                

                // store cliaprt media images

                $data = $this->getRequest()->getPostValue();

                $fonts_gallery = $data['fonts'];

                
             

                $fonts_model = $this->_objectManager->create('Biztech\Productdesigner\Model\Fonts');

                
                    
                    foreach($fonts_gallery as $f_gallery){
                        
                        $inputFilter = new \Zend_Filter_Input(
                            [],
                            [],
                            $f_gallery
                        );
                        $data = $inputFilter->getUnescaped();




                        $fonts_id = $data['fonts_id'];


                        if ($fonts_id) {
                            $fonts_model->load($fonts_id);
                        }

                        $exclude = (isset($data['exclude'])) ? 1 : 0;
                        $remove = isset($data['remove']) ? 1 : 0;

                        $dataArray = array(
                            'font_file' => $data['file'],
                            'font_label' => $data['label'],
                            'position' => $data['sort'],
                            'disabled' => $exclude,
                            'remove' => $remove,
                        );




                        if ($dataArray['remove'] == 1) {                               
                            $fonts_model->load($fonts_id);
                            $fonts_model->delete();
                        } else {
                            if ($fonts_id) {



                                $fonts_model->setData($dataArray)->setId($fonts_id);
                                $fonts_model->save();
                            } else {
                                $fonts_model->setData($dataArray);
                                $fonts_model->save();
                            }
                        }






                      
                        


                        
                     
                        $session = $this->_objectManager->get('Magento\Backend\Model\Session');
                        $session->setPageData($fonts_model->getData());
                        $fonts_model->save();
                    }
                    
                    

                // store cliaprt media images







                
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                $id = (int)$this->getRequest()->getParam('id');
                if (!empty($id)) {
                    $this->_redirect('productdesigner/fonts/edit', ['id' => $id]);
                } else {
                    $this->_redirect('productdesigner/fonts/edit');
                }
                return;
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('Something went wrong while saving the item data. Please review the error log.')
                );
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->_objectManager->get('Magento\Backend\Model\Session')->setPageData($data);
                $this->_redirect('productdesigner/fonts/edit', ['id' => $this->getRequest()->getParam('id')]);
                return;
            }
        }
        $this->_redirect('productdesigner/fonts/edit');
    }
}
