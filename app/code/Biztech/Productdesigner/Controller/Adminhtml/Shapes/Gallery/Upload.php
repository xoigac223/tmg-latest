<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Biztech\Productdesigner\Controller\Adminhtml\Shapes\Gallery;



class Upload extends \Magento\Framework\App\Action\Action 
{
    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $_fileUploaderFactory;
    protected $__uploader;
    protected $resultRawFactory;


    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\File\UploaderFactory $fileUploaderFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
    ) {
        $this->_fileUploaderFactory = $fileUploaderFactory;
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
    }
    /**
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        
        try {

            $data = $this->getRequest()->getFiles();
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $config = $objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
            $uploader = $this->_fileUploaderFactory->create(['fileId' => $data['shapes-image']]);

            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png', 'svg']);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
            $filesystem = $objectManager->get('Magento\Framework\Filesystem');
            $reader = $filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
            $prod_image_dir = $reader->getAbsolutePath().'productdesigner/shapes';
            $result = $uploader->save(
                $prod_image_dir
            );

            $demo = $objectManager->create('\Magento\Store\Model\StoreManagerInterface');
            $url = $demo->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'productdesigner/shapes' . $result['file'];

            /*$result['url'] = $this->_objectManager->get('Magento\Catalog\Model\Product\Media\Config')
                ->getTmpMediaUrl($result['file']);*/

            $result['url'] = $url;
            $result['file'] = $result['file'];
            $result['state'] = 1;
                  

        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }
        $response = $this->resultRawFactory->create();
        $response->setHeader('Content-type', 'text/plain');
        $response->setContents(json_encode($result));
        return $response;
        
    }
}
