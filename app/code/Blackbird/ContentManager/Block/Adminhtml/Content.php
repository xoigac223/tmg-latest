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
namespace Blackbird\ContentManager\Block\Adminhtml;

class Content extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
    
    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
            \Magento\Backend\Block\Widget\Context $context,
            \Magento\Framework\Registry $registry,
            array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }
    
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_content';
        $this->_addButtonLabel = __('Add Content') . ' ' . $this->_coreRegistry->registry('current_contenttype')->getTitle();
    
        parent::_construct();
    }
    
    public function getCreateUrl() {
        return $this->getUrl('*/*/new', ['_current' => true]);
    }
    
}
