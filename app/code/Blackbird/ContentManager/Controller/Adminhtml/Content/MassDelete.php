<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Blackbird\ContentManager\Controller\Adminhtml\Content;

use Blackbird\ContentManager\Model\Content;

class MassDelete extends \Blackbird\ContentManager\Controller\Adminhtml\Content
{    
    /**
     * Mass Delete action
     * 
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $this->_initAction();
        
        $ids = $this->getRequest()->getParam('id');
        $contentTypeId = null;
        $contentCollection = $this->_contentCollectionFactory->create();
        
        if (is_array($ids)) {
            $contentCollection->addFieldToFilter(Content::ID, ['in' => $ids]);
            $records = 0;
            
            // Delete content types
            foreach ($contentCollection as $content) {
                try {
                    $contentTypeId = $content->getCtId();
                    $content->delete();
                    $records++;
                } catch (\Exception $e) {
                    $this->messageManager->addExceptionMessage($e, __('Something went wrong while deleting the content: %1', $e->getMessage()));
                }
            }
            
            $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been deleted.', $records));
            
        } else {
            $this->messageManager->addErrorMessage(__('Please select item(s).'));
        }
        
        return $this->resultRedirect->setPath('*/*/', ['ct_id' => $contentTypeId]);
    }

    /**
     * Returns result of current user permission check on resource and privilege
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Blackbird_ContentManager::content_delete');
    }
}
