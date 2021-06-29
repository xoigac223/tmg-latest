<?php
/**
 * Blackbird ContentManager Module
 *
 * NOTICE OF LICENSE
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@bird.eu so we can send you a copy immediately.
 *
 * @category            Blackbird
 * @package        Blackbird_ContentManager
 * @copyright           Copyright (c) 2016 Blackbird (http://black.bird.eu)
 * @author        Blackbird Team
 * @license        http://www.advancedcontentmanager.com/license/
 */
namespace Blackbird\ContentManager\Block\Adminhtml\Content\Widget;

use Blackbird\ContentManager\Model\ContentType;

class Grid extends \Magento\Backend\Block\Widget\Grid
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
    
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return Store
     */
    protected function _getStore()
    {
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        return $this->_storeManager->getStore($storeId);
    }
    
    /**
     * Apply sorting and filtering to collection
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _prepareCollection()
    {
        parent::_prepareCollection();
        
        $contentTypeModel = $this->_coreRegistry->registry('current_contenttype');
        
        if ($contentTypeModel && $this->getCollection()) {
            $this->getCollection()
                ->addAttributeToSelect('url_key')
                ->addAttributeToSelect('title')
                ->addAttributeToSelect('status')
                ->addFieldToFilter(ContentType::ID, $contentTypeModel->getCtId());
            
            $customFieldsCollection = $contentTypeModel->getCustomFieldCollection()
                ->addFieldToFilter('show_in_grid', 1);

            foreach($customFieldsCollection as $field) {
                $this->getCollection()->addAttributeToSelect($field->getIdentifier());
            }
        }        
        
        return $this;
    }    
}
