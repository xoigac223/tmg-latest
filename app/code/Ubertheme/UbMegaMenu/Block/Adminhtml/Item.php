<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 *
 */
namespace Ubertheme\UbMegaMenu\Block\Adminhtml;

/**
 * Adminhtml menu items content block
 */
class Item extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Block constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_item';
        $this->_blockGroup = 'Ubertheme_UbMegaMenu';
        $this->_headerText = __('Manage Menu Items');

        parent::_construct();

        if ($this->_isAllowedAction('Ubertheme_UbMegaMenu::item_save')) {
            $this->buttonList->update('add', 'label', __('Add New Menu Item'));
        } else {
            $this->buttonList->remove('add');
        }
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
