<?php
/**
 * Copyright © 2016 Ubertheme.com All rights reserved.
 *
 */
namespace Ubertheme\UbMegaMenu\Block\Adminhtml\Group\Edit;

/**
 * Admin group left menu
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('group_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Menu Group'));
    }
}
