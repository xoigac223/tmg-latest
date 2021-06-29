<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Blackbird\ContentManager\Controller\Adminhtml\ContentType;

use Blackbird\ContentManager\Api\Data\ContentTypeInterface;

class MassDelete extends \Blackbird\ContentManager\Controller\Adminhtml\ContentType
{
    /**
     * Mass Delete action
     * 
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $this->_initAction();
        
        $ids = $this->getRequest()->getParam('selected');
        $contentTypeCollection = $this->_contentTypeCollectionFactory->create();

        if (is_array($ids)) {
            $contentTypeCollection->addFieldToFilter(ContentTypeInterface::ID, ['in' => $ids]);
            $records = 0;
            
            // Delete content types
            foreach ($contentTypeCollection as $contentType) {
                try {
                    $contentType->delete();
                    $records++;
                } catch (\Exception $e) {
                    $this->messageManager->addExceptionMessage($e, __('Something went wrong while deleting the content type: %1', $e->getMessage()));
                }
            }
            
            $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been deleted.', $records));
            
            // Flush backend main menu cache
            $this->flushBackendMainMenuCache();
            
        } else {
            $this->messageManager->addErrorMessage(__('Please select item(s).'));
        }
        
        return $this->resultRedirect->setPath('*/*/');
    }

    /**
     * Returns result of current user permission check on resource and privilege
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Blackbird_ContentManager::contenttype_delete');
    }
}
