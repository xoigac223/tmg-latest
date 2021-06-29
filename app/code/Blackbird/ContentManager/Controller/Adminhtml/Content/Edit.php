<?php
/**
 * Blackbird ContentManager Module
 *
 * NOTICE OF LICENSE
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@bird.eu so we can send you a copy immediately.
 *
 * @category        Blackbird
 * @package         Blackbird_ContentManager
 * @copyright       Copyright (c) 2017 Blackbird (https://black.bird.eu)
 * @author          Blackbird Team
 * @license         https://www.advancedcontentmanager.com/license/
 */
namespace Blackbird\ContentManager\Controller\Adminhtml\Content;

use Blackbird\ContentManager\Model\ContentType;
use Blackbird\ContentManager\Model\Content;

class Edit extends \Blackbird\ContentManager\Controller\Adminhtml\Content
{    
    /**
     * Edit content action
     *
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
        
        // Check if contettype exists
        if (!$this->_contentTypeModel instanceof ContentType) {
            // Redirect to the content type main page
            $this->messageManager->addErrorMessage(__('Create a new content type, or select one in the content manager menu.'));
            return $this->resultRedirect->setPath('*/contenttype/');
        }
        
        $pageData = $this->_getSession()->getPageData(true);
        
        if ($this->_contentModel) {
            if (!empty($pageData)) {
                $this->_contentModel = $this->_modelFactory->create(Content::class);
                $this->_contentModel->addData($pageData);
            }

            $this->_addBreadcrumb(__('Edit Content \'%1\'', $this->_contentTypeModel->getTitle()), __('Edit Content \'%1\' \'%2\'', $this->_contentTypeModel->getTitle(), $this->_contentModel->getTitle()));
            $this->_view->getPage()->getConfig()->getTitle()->prepend($this->_contentModel->getTitle());
        } else {
            $this->_addBreadcrumb(__('New Content \'%1\'', $this->_contentTypeModel->getTitle()), __('New Content \'%1\'', $this->_contentTypeModel->getTitle()));
            $this->_view->getPage()->getConfig()->getTitle()->prepend(__('New \'%1\'', $this->_contentTypeModel->getTitle()));
        }
        
        $this->_view->renderLayout();
    }
}
