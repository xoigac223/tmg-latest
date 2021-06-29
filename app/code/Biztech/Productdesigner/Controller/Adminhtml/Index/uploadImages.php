<?php

/**
 * Copyright © 2016 Magento. All rights reserved.
 */
namespace Biztech\Productdesigner\Controller\Adminhtml\Index;

class uploadImages extends \Magento\Framework\App\Action\Action
{
    /**
     * Index action
     *
     * @return $this
     */

    protected $_fileUploaderFactory;
    protected $__uploader;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\File\UploaderFactory $fileUploaderFactory

    ){
        $this->_fileUploaderFactory = $fileUploaderFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultPage = $this->_objectManager->create('Magento\Framework\View\LayoutInterface');
        $layout = $resultPage->createBlock('Biztech\Productdesigner\Block\Productdesigner');
        
        try {
            $data = $this->getRequest()->getFiles();
            if (isset($data['image_upload'])) {
                $errors = [];
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

                    if (!in_array($file_type, ['image/jpg', 'image/jpeg', 'image/gif', 'image/png', 'image/svg', 'image/svg+xml'])) {
                        $result["error_message"] = __('Cannot upload the file. The format is not supported.');
                        $result["status"] = 'fail';
                        $this->getResponse()->setBody(json_encode($result));
                        return false;
                    }

                    $data['image_upload_new'] = [
                        'name' => $file_name,
                        'type' => $file_type,
                        'tmp_name' => $file_tmp,
                        'error' => 0,
                        'size' => $file_size
                    ];
                    $uploader = $this->_fileUploaderFactory->create(['fileId' => $data['image_upload_new']]);
                    $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png','svg']);
                    $uploader->setAllowRenameFiles(true);
                    $uploader->setFilesDispersion(true);
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
                    $result['file'] = $result['file'];

                    $files[] = $result;
                    $reader = $filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
                    $prod_image_dir = $reader->getAbsolutePath() . 'productdesigner/upload' . $result['file'];
                    $tab_type = 'uploadedImage';
                }
            } else {
                $result["error_message"] = __('Please select files for upload.');
                $result["status"] = 'fail';
                $this->getResponse()->setBody(json_encode($result));
                return false;
            }
        } catch (\Exception $e) {
            $result = [
                'error_message' => $e->getMessage(),
                'errorcode' => $e->getCode()];
            $result["status"] = 'fail';
            $this->getResponse()->setBody(json_encode($result));
            return false;
        }
        $result['images'] = $layout->setData(["files" => $files, 'result' => $result])->setTemplate('productdesigner/upload/images.phtml')->toHtml();
        $this->getResponse()->setBody($result['images']);
    }

}
