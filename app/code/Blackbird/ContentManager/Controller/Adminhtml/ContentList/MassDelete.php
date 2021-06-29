<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Blackbird\ContentManager\Controller\Adminhtml\ContentList;

use Blackbird\ContentManager\Api\Data\ContentListInterface;

class MassDelete extends \Blackbird\ContentManager\Controller\Adminhtml\ContentList
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
        $contentListCollection = $this->_contentListCollectionFactory->create();
        
        if (is_array($ids)) {
            $contentListCollection->addFieldToFilter(ContentListInterface::ID, ['in' => $ids]);
            $records = 0;
            
            // Delete content lists
            foreach ($contentListCollection as $contentList) {
                try {
                    $contentList->delete();
                    $records++;
                } catch (\Exception $e) {
                    $this->messageManager->addExceptionMessage($e, __('Something went wrong while deleting the content list: %1', $e->getMessage()));
                }
            }
            
            $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been deleted.', $records));
            
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
        return $this->_authorization->isAllowed('Blackbird_ContentManager::contentlist_delete');
    }
}
