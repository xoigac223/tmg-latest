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
namespace Blackbird\ContentManager\Controller\Adminhtml\ContentType;

use Blackbird\ContentManager\Model\ContentType;

class Edit extends \Blackbird\ContentManager\Controller\Adminhtml\ContentType
{
    /**
     * Edit content type action
     *
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
        
        $pageData = $this->_getSession()->getPageData(true);
        if ($this->_contentTypeModel && !empty($pageData)) {
            $this->_contentTypeModel = $this->_modelFactory->create(ContentType::class);
            $this->_contentTypeModel->addData($pageData);
        }
        
        if ($this->_contentTypeModel) {
            $this->_addBreadcrumb(__('Edit Content Type \'%1\'', $this->_contentTypeModel->getTitle()), __('Edit Content Type \'%1\'', $this->_contentTypeModel->getTitle()));
            $this->_view->getPage()->getConfig()->getTitle()->prepend($this->_contentTypeModel->getTitle());
        } else {
            $this->_addBreadcrumb(__('New Content Type'), __('New Content Type'));
            $this->_view->getPage()->getConfig()->getTitle()->prepend(__('New Content Type'));
        }

        $this->_view->renderLayout();
    }
}
