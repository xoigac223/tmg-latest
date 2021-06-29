<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Biztech\Productdesigner\Controller\Index;

class uploadImages extends \Magento\Framework\App\Action\Action {
    /**
     * Index action
     *
     * @return $this
     */
    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    //protected $uploader;

    /**
     * @var \Magento\Framework\Filesystem
     */
    //protected $filesystem;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
//    public function __construct(\Magento\Backend\App\Action\Context $context,
//            
//            \Magento\MediaStorage\Model\File\UploaderFactory $uploader)
//            //\Magento\Framework\Filesystem $filesystem)
//                    {
//        
//        $this->uploader = $uploader;
//       // $this->filesystem = $filesystem;
//        parent::__construct($context);
//    }

    protected $_fileUploaderFactory;
    protected $__uploader;

    public function __construct(
    \Magento\Framework\App\Action\Context $context, \Magento\Framework\File\UploaderFactory $fileUploaderFactory
    //\Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
    //\Magento\MediaStorage\Model\File\UploaderFactory 
//                \Magento\Framework\Model\File\UploaderFactory $fileUploaderFactory
//             \Magento\Framework\File\Uploader $fileUploaderFactory
//            \Magento\Framework\File\Uploader $fileUploaderFactory
    ) {
        $this->_fileUploaderFactory = $fileUploaderFactory;
//        $this->__uploader = $uploader;
        parent::__construct($context);
    }

    public function execute() {

        $resultPage = $this->_objectManager->create('Magento\Framework\View\LayoutInterface');
        $layout = $resultPage->createBlock('Biztech\Productdesigner\Block\Productdesigner');
        //$layout = $this->getLayout()->createBlock('productdesigner/productdesigner');
        try {

            $data = $this->getRequest()->getFiles();
            if (isset($data['image_upload'])) {
                $errors = array();
                foreach ($data['image_upload'] as $key => $detail) {

                    $file_name = $data['image_upload'][$key]['name'];
                    $file_size = $data['image_upload'][$key]['size'];
                    $file_tmp = $data['image_upload'][$key]['tmp_name'];
                    $file_type = $data['image_upload'][$key]['type'];
                    $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);



                    if ($file_size == 0) {
                        $result["error_message"] = __('Please select files for upload.');
                        $result["status"] = 'fail';
                        $this->getResponse()->setBody(json_encode($result));
                        return false;
                    }
                    if ($file_size > 8388608) {
                        $result["error_message"] = __('You can not upload files larger than ' . 8388608 / 1024 / 1024 . ' MB');
                        $result["status"] = 'fail';
                        $this->getResponse()->setBody(json_encode($result));
                        return false;
                    }
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $config = $objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
                    $min_size = $config->getValue('productdesigner/customimageuploadconfiguration/minimumsizeofimage');
                    if (isset($min_size) && $min_size != null) {
                        if ($file_size / 1024 / 1024 < $min_size) {
                            $result["error_message"] = __('You can not upload files smaller than ' . $min_size . ' MB');
                            $result["status"] = 'fail';
                            $this->getResponse()->setBody(json_encode($result));
                            return false;
                        }
                    }


                    if (!in_array($file_type, array('image/jpg', 'image/jpeg', 'image/gif', 'image/png', 'image/svg', 'image/svg+xml'))) {
                        $result["error_message"] = __('Cannot upload the file. The format is not supported.');
                        $result["status"] = 'fail';
                        $this->getResponse()->setBody(json_encode($result));
                        return false;
                    }


                    $data['image_upload_new'] = array(
                        'name' => $file_name,
                        'type' => $file_type,
                        'tmp_name' => $file_tmp,
                        'error' => 0,
                        'size' => $file_size
                    );
                    // $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    //$uploader = $objectManager->create(\Magento\MediaStorage\Model\File\UploaderFactory);
                    //$uploader = $uploader->create(
                    //      ['fileId' => 'image']
                    //);
                    //$uploader = $this->_fileUploaderFactory->_setUploadFileId(['fileId' => 'image']);
                    $uploader = $this->_fileUploaderFactory->create(['fileId' => $data['image_upload_new']]);
                    $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png', 'svg']);
                    $uploader->setAllowRenameFiles(true);
                    $uploader->setFilesDispersion(true);


                    //$uploader = new Varien_File_Uploader('image_upload_new');
                    //$uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png', 'svg'));
                    //$uploader->setAllowRenameFiles(true);
                    //$uploader->setFilesDispersion(true);
                    $filesystem = $objectManager->get('Magento\Framework\Filesystem');
                    $reader = $filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
                    $prod_image_dir = $reader->getAbsolutePath() . 'productdesigner/upload';
                    $result = $uploader->save(
                            $prod_image_dir
                    );
                    $result['tmp_name'] = $result['tmp_name'];
                    $result['path'] = $result['path'];
                    $demo = $objectManager->create('\Magento\Store\Model\StoreManagerInterface');
                    $result['url'] = $demo->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'productdesigner/upload' . $result['file'];
                    //$result['url'] = Mage::helper('productdesigner')->getTmpUploadedImageUrl($result['file']);
                    $result['file'] = $result['file'];

                    $files[] = $result;
                    $reader = $filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
                    $prod_image_dir = $reader->getAbsolutePath() . 'productdesigner/upload' . $result['file'];
                    //$dirImg = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) . DS . 'productdesigner' . DS . 'uploadedImage' . str_replace("/", DS, $result['file']);
                    $tab_type = 'uploadedImage';

                    //Mage::helper('productdesigner/info')->imageResize($dirImg, $result, $tab_type, $file_ext);
                }
            } else {
                $result["error_message"] = __('Please select files for upload.');
                $result["status"] = 'fail';
                $this->getResponse()->setBody(json_encode($result));
                return false;
            }
        } catch (\Exception $e) {
            $result = array(
                'error_message' => $e->getMessage(),
                'errorcode' => $e->getCode());
            $result["status"] = 'fail';
            $this->getResponse()->setBody(json_encode($result));
            return false;
        }

        $result['images'] = $layout->setData(array("files" => $files, 'result' => $result))->setTemplate('productdesigner/upload/images.phtml')->toHtml();
        $this->getResponse()->setBody($result['images']);
    }

}
