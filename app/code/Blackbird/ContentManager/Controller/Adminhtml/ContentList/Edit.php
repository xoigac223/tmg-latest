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
namespace Blackbird\ContentManager\Controller\Adminhtml\ContentList;

use Blackbird\ContentManager\Model\ContentList;

class Edit extends \Blackbird\ContentManager\Controller\Adminhtml\ContentList
{
    /**
     * Edit content list action
     *
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
        
        $pageData = $this->_getSession()->getPageData(true);
        
        if ($this->_contentListModel) {
            if (!empty($pageData)) {
                $this->_contentListModel = $this->_modelFactory->create(ContentList::class);
                $this->_contentListModel->addData($pageData);
            }
            
            $this->_addBreadcrumb(__('Edit Content List \'%1\'', $this->_contentListModel->getTitle()), __('Edit Content List \'%1\'', $this->_contentListModel->getTitle()));
            $this->_view->getPage()->getConfig()->getTitle()->prepend($this->_contentListModel->getTitle());
        } else {
            $this->_addBreadcrumb(__('New Content List'), __('New Content List'));
            $this->_view->getPage()->getConfig()->getTitle()->prepend(__('New Content List'));
        }

        $this->_view->renderLayout();
    }
}
