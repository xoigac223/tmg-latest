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
namespace Blackbird\ContentManager\Controller\Adminhtml\Flag;

class Index extends \Blackbird\ContentManager\Controller\Adminhtml\Flag
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
        $this->_addBreadcrumb(__('Store Flags'), __('Store Flags'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Store Flags'));
        $this->_view->renderLayout();
    }
}
