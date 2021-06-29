<?php

namespace Shreeji\Duplicateimage\Controller\Adminhtml\Manage;

class Find extends \Magento\Backend\App\Action {

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Shreeji_Duplicateimage::duplicateimages';

    /**
     * Delete action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute() {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            // init model            
            $model = $this->_objectManager->create('Shreeji\Duplicateimage\Model\FindDuplicate');
            $model->findDuplicateImages();
            // display success message
            $this->messageManager->addSuccess(__('Duplicate images has been successfully find.'));
            return $resultRedirect->setPath('*/*/');
        } catch (\Exception $e) {
            // display error message
            $this->messageManager->addError($e->getMessage());
            return $resultRedirect->setPath('*/*/');
        }
        // display error message
        $this->messageManager->addError(__('We can\'t find a duplicate images.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }

}
