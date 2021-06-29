<?php

namespace Shreeji\Duplicateimage\Controller\Adminhtml\Manage;

class Delete extends \Magento\Backend\App\Action {

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Shreeji_Duplicateimage::duplicateimages';

    /**
     *
     * @var \Magento\Catalog\Model\Product 
     */
    protected $_product;

    /**
     *
     * @var \Magento\Framework\App\ResourceConnection 
     */
    protected $_resource;

    /**
     *
     * @var connection 
     */
    protected $_connection;

    /**
     * Catalog product media config
     *
     * @var \Magento\Catalog\Model\Product\Media\Config
     */
    protected $_catalogProductMediaConfig;

    /**
     *
     * @var \Magento\Framework\App\Filesystem\DirectoryList  
     */
    protected $_directoryList;

    /**
     * 
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Catalog\Model\Product\Media\Config $catalogProductMediaConfig
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     */
    public function __construct(
    \Magento\Backend\App\Action\Context $context, \Magento\Catalog\Model\Product $product, \Magento\Framework\App\ResourceConnection $resource, \Magento\Catalog\Model\Product\Media\Config $catalogProductMediaConfig, \Magento\Framework\App\Filesystem\DirectoryList $directoryList
    ) {
        $this->_product = $product;
        $this->_resource = $resource;
        $this->_connection = $this->_resource->getConnection();
        $this->_directoryList = $directoryList;
        $this->_catalogProductMediaConfig = $catalogProductMediaConfig;
        parent::__construct($context);
    }

    /**
     * Delete action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute() {
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('manageimage_id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                // init model and delete
                $model = $this->_objectManager->create('Shreeji\Duplicateimage\Model\Duplicateimage')->load($id);
                $productId = $this->_product->getIdBySku($model->getData('sku'));
                $mediaPath = $this->_directoryList->getPath('media') . '/' . $this->_catalogProductMediaConfig->getBaseMediaPath();
                $filepath = $mediaPath . $model->getData('filename');
                if (!empty($productId)) {
                    $removeFile = $model->getData('filename');
                    $removeImage = $this->_removeImage($productId, $removeFile);
                    if ($removeImage == true) {
                        if (file_exists($filepath)) {
                            unlink($filepath); // for delete image from memory
                        }
                        $model->delete();
                        // display success message
                        $this->messageManager->addSuccess(__('Image has been deleted.'));
                        return $resultRedirect->setPath('*/*/');
                    } else {
                        $this->messageManager->addErrorMessage('Unable to delete image');
                        return $resultRedirect->setPath('*/*/');
                    }
                } else {
                    if (file_exists($filepath)) {
                        unlink($filepath); // for delete image from memory
                    }
                    $this->messageManager->addErrorMessage('Unable to find product in your');
                    return $resultRedirect->setPath('*/*/');
                }
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/');
            }
        }
        // display error message
        $this->messageManager->addError(__('We can\'t find a image to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * 
     * @param type $productId
     * @param type $filename
     * @return boolean
     * @throws \LogicException
     */
    protected function _removeImage($productId, $filename) {
        if (!empty($productId) && !empty($filename)) {
            $mediaTable = $this->_connection->getTableName('catalog_product_entity_media_gallery');
            $query = "DELETE FROM $mediaTable WHERE value='$filename'";
            try {
                $this->_connection->query($query);
            } catch (\Exception $e) {
                throw new \LogicException('Could not delete image: ' . $e->getMessage());
            }
            return true;
        } else {
            return null;
        }
    }
}
