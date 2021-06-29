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
namespace Blackbird\ContentManager\Block\Content\Widget;

use Blackbird\ContentManager\Model\ContentType\CustomField;
use Blackbird\ContentManager\Model\ContentType;
use Blackbird\ContentManager\Model\Content;

class ContentList extends \Magento\Catalog\Block\Product\AbstractProduct
    implements \Magento\Framework\DataObject\IdentityInterface,
               \Magento\Widget\Block\BlockInterface
{
    /**
     * Name of request parameter for page number value
     */
    const PAGE_VAR_NAME = 'p';
    
    /**
     * Default value for contents count that will be shown
     */
    const DEFAULT_CONTENTS_COUNT = 10;
    
    /**
     * Default value for contents per page
     */
    const DEFAULT_CONTENTS_PER_PAGE = 5;

    /**
     * Default value whether show pager or not
     */
    const DEFAULT_SHOW_PAGER = false;

    /**
     * @var string
     */
    protected $_template = 'Blackbird_ContentManager::content/widget/list.phtml';
    
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
    /**
     * @var \Blackbird\ContentManager\Model\ResourceModel\Content\CollectionFactory
     */
    protected $_contentCollectionFactory;
    
    /**
     * @var \Blackbird\ContentManager\Model\ResourceModel\ContentType\CollectionFactory
     */
    protected $_contentTypeCollectionFactory;
    
    /**
     * @var \Blackbird\ContentManager\Model\ResourceModel\ContentType\CustomField\CollectionFactory
     */
    protected $_customFieldCollectionFactory;

    /**
     * @var \Blackbird\ContentManager\Model\Rule
     */
    protected $_rule;
    
    /**
     * @var \Magento\Widget\Helper\Conditions
     */
    protected $_conditionsHelper;

    /**
     * @var \Blackbird\ContentManager\Model\Rule\Condition\Sql\Builder
     */
    protected $_sqlBuilder;
    
    /**
     * @var \Blackbird\ContentManager\Model\ResourceModel\Content\Collection
     */
    protected $_contentCollection;
    
    /**
     * @var \Blackbird\ContentManager\Helper\ContentList\Widget\AttributeShow
     */
    protected $_attributeShowHelper;

    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $_filterProvider;

    /**
     * @var array
     */
    protected $_filter = [];

    /**
     * @var array
     */
    protected $_show = [];

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Blackbird\ContentManager\Model\ResourceModel\Content\CollectionFactory $contentCollectionFactory
     * @param \Blackbird\ContentManager\Model\ResourceModel\ContentType\CollectionFactory $contentTypeCollectionFactory
     * @param \Blackbird\ContentManager\Model\ResourceModel\ContentType\CustomField\CollectionFactory $customFieldCollectionFactory
     * @param \Magento\Widget\Helper\Conditions $conditionsHelper
     * @param \Blackbird\ContentManager\Model\Rule $rule
     * @param \Blackbird\ContentManager\Model\Rule\Condition\Sql\Builder $sqlBuilder
     * @param \Blackbird\ContentManager\Helper\ContentList\Widget\AttributeShow $attributeShowHelper
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Blackbird\ContentManager\Model\ResourceModel\Content\CollectionFactory $contentCollectionFactory,
        \Blackbird\ContentManager\Model\ResourceModel\ContentType\CollectionFactory $contentTypeCollectionFactory,
        \Blackbird\ContentManager\Model\ResourceModel\ContentType\CustomField\CollectionFactory $customFieldCollectionFactory,
        \Magento\Widget\Helper\Conditions $conditionsHelper,
        \Blackbird\ContentManager\Model\Rule $rule,
        \Blackbird\ContentManager\Model\Rule\Condition\Sql\Builder $sqlBuilder,
        \Blackbird\ContentManager\Helper\ContentList\Widget\AttributeShow $attributeShowHelper,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        array $data = []
    ) {
        $this->_contentCollectionFactory = $contentCollectionFactory;
        $this->_contentTypeCollectionFactory = $contentTypeCollectionFactory;
        $this->_customFieldCollectionFactory = $customFieldCollectionFactory;
        $this->_conditionsHelper = $conditionsHelper;
        $this->_rule = $rule;
        $this->_sqlBuilder = $sqlBuilder;
        $this->_attributeShowHelper = $attributeShowHelper;
        $this->_filterProvider = $filterProvider;
        parent::__construct($context, $data);
    }
    
    /**
     * Before rendering html, but after trying to load cache
     *
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $this->preparePager();
        $this->getCollection()->load();
        
        $this->prepareWidgetAttributeToShow();
        
        return parent::_beforeToHtml();
    }
    
    /**
     * Retrieve how many contents should be displayed
     *
     * @return int
     */
    public function getLimitDisplay()
    {
        if (!$this->hasData('limit_display')) {
            $this->setData('limit_display', self::DEFAULT_CONTENTS_COUNT);
        }
        return $this->getData('limit_display');
    }

    /**
     * Retrieve how many contents should be displayed per page
     *
     * @return int
     */
    public function getLimitPerPage()
    {
        if (!$this->hasData('limit_per_page')) {
            $this->setData('limit_per_page', self::DEFAULT_CONTENTS_PER_PAGE);
        }
        return $this->getData('limit_per_page');
    }
    
    /**
     * Return flag whether pager need to be shown or not
     *
     * @return bool
     */
    public function hasPager()
    {
        if (!$this->hasData('pager')) {
            $this->setData('pager', self::DEFAULT_SHOW_PAGER);
        }
        return (bool)$this->getData('pager');
    }
    
    /**
     * Return flag whether pager need to be shown at the top
     *
     * @return bool
     */
    public function hasPagerTop()
    {
        return ($this->hasPager() && ($this->getPagerPosition() == 0 || $this->getPagerPosition() == 2));
    }
    
    /**
     * Return flag whether pager need to be shown at the bottom
     *
     * @return bool
     */
    public function hasPagerBottom()
    {
        return ($this->hasPager() && ($this->getPagerPosition() == 1 || $this->getPagerPosition() == 2));
    }
    
    /**
     * Check if a link exists
     * 
     * @return bool
     */
    public function hasLink()
    {
        $hasLink = false;
        
        if ($this->hasData('link')) {
            $link = $this->getData('link');
            
            $hasLink = (is_array($link) && isset($link['label'], $link['position']));
        }
        
        return $hasLink;
    }
    
    /**
     * Check if the link is positioned at the top
     * 
     * @return bool
     */
    public function hasLinkTop()
    {
        $isTop = false;
        
        if ($this->hasLink()) {
            $link = $this->getData('link');
            
            $isTop = ($link['position'] === 'top');
        }
        
        return $isTop;
    }
    
    /**
     * Check if the link is positioned at the bottom
     * 
     * @return bool
     */
    public function hasLinkBottom()
    {
        $isBottom = false;
        
        if ($this->hasLink()) {
            $link = $this->getData('link');
            
            $isBottom = ($link['position'] === 'bottom');
        }
        
        return $isBottom;
    }
    
    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        $contentTypes = is_array($this->getContentType()) ? $this->getContentType() : [$this->getContentType()];
        $identities = [];

        foreach ($contentTypes as $contentType) {
            $identities[] = ContentType::CACHE_TAG . '_' . $contentType->getId();
        }

        foreach ($this->getContentCollection() as $content) {
            $identities[] = Content::CACHE_TAG . '_' . $content->getId();
        }

        return $identities;
    }
    
    /**
     * Add a link
     * The available position are : 'top', 'bottom'
     * 
     * @param string $label
     * @param string $position
     */
    public function addLink($label, $position = 'bottom')
    {
        $this->setData('link', ['label' => $label, 'position' => $position]);
    }

    /**
     * Add an attribute to filter to the content collection
     * 
     * @param string $attribute
     * @param string $condition
     * @param string $value
     * @return $this
     */
    public function addAttributeToFilter($attribute, $condition, $value)
    {
        if (!empty($attribute) && !empty($condition) && !empty($value)) {
            $this->_filter[] = [
                'attribute' => $attribute,
                'condition' => $condition,
                'value' => $value,
            ];
        }
        
        return $this;
    }
    
    /**
     * Returns the attributes to filter to the content collection
     * 
     * @return array
     */
    public function getAttributeToFilter()
    {
        if (!is_array($this->_filter)) {
            return [];
        }
        
        return $this->_filter;
    }
    
    /**
     * Add an attribute to show
     * 
     * @param string $attribute
     * @param array $params
     * @return $this
     */
    public function addAttributeToShow($attribute, array $params = [])
    {
        if (!empty($attribute)) {
            $this->_show[] = [
                'attribute' => $attribute,
                'params' => $params,
            ];
        }
        
        return $this;
    }
    
    /**
     * Returns the conditions
     * 
     * @return \Magento\Rule\Model\Condition\Combine
     */
    public function getConditions()
    {
        $conditions = $this->getData('conditions_encoded')
            ? $this->getData('conditions_encoded')
            : $this->getData('conditions');

        if ($conditions) {
            $conditions = $this->_conditionsHelper->decode($conditions);
        }
        
        $this->_rule->loadPost(['conditions' => $conditions]);
        
        return $this->_rule->getConditions();
    }
    
    /**
     * Returns the attributes to show
     * 
     * @return array
     */
    public function getAttributeToShow()
    {
        if (!is_array($this->_show)) {
            return [];
        }
        
        return $this->_show;
    }
    
    /**
     * Render pagination HTML
     *
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
    
    /**
     * Retrieve the page var name for requests
     * 
     * @return string
     */
    public function getPageVarName()
    {
        if (!$this->hasData('page_var_name')) {
            $this->setData('page_var_name', self::PAGE_VAR_NAME);
        }
        
        return $this->getData('page_var_name');
    }
    
    /**
     * Retrieve the content type
     * 
     * @return ContentType
     */
    public function getContentType()
    {
        if (!$this->hasData('content_type')) {
            $ctIdentifier = null;
            $contentType = null;
            $contentTypeCollection = $this->_contentTypeCollectionFactory->create();

            // Load the content type
            if ($this->hasData('ct_type')) {
                $ctIdentifier = $this->getData('ct_type');
                $contentTypeCollection->addFieldToFilter(ContentType::IDENTIFIER, $ctIdentifier);
            }
            if (!$ctIdentifier && $this->hasData('ct_id')) {
                $ctIdentifier = $this->getData('ct_id');
                $contentTypeCollection->addFieldToFilter(ContentType::ID, $ctIdentifier);
            }
            if ($ctIdentifier && $contentTypeCollection->count()) {
                if ($contentTypeCollection->count() > 1) {
                    $contentType = $contentTypeCollection->getItems();
                } else {
                    $contentType = $contentTypeCollection->getFirstItem();
                }
            }
            
            $this->setData('content_type', $contentType);
        }
        
        return $this->getData('content_type');
    }
    
    /**
     * Prepare the pager if it's enabled
     * 
     * @return $this
     */
    protected function preparePager()
    {
        if ($this->hasPager() && $this->getCollection()->getSize() > $this->getLimitPerPage()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Catalog\Block\Product\Widget\Html\Pager::class,
                'widget.contents.list.pager.'
            );

            $pager->setUseContainer(true)
                ->setShowAmounts(true)
                ->setShowPerPage(false)
                ->setPageVarName($this->getPageVarName())
                ->setLimit($this->getLimitPerPage())
                ->setTotalLimit($this->getLimitDisplay())
                ->setCollection($this->getCollection());
            
            $this->setChild('pager', $pager);
        }
        
        return $this;
    }
    
    /**
     * Retrieve the content collection
     * 
     * @return \Blackbird\ContentManager\Model\ResourceModel\Content\Collection
     */
    public function getCollection()
    {
        if (!$this->hasData('collection')) {
            $this->setData('collection', $this->getContentCollection());
        }
        
        return $this->getData('collection');
    }
    
    /**
     * Prepare the content collection
     * 
     * @return \Blackbird\ContentManager\Model\ResourceModel\Content\Collection
     */
    protected function getContentCollection()
    {
        if (is_null($this->_contentCollection)) {
            $collection = $this->_contentCollectionFactory->create();

            // Filter by : store, content type, status
            $collection->addStoreFilter()
                    ->addAttributeToSelect('*')
                    ->addAttributeToFilter(Content::STATUS, 1);
            
            if (!empty($this->getContentType())) {
                $collection->addContentTypeFilter($this->getContentType());
            }
            
            // Add the attributes to filter
            foreach ($this->getAttributeToFilter() as $filter) {
                if (isset($filter['attribute'], $filter['condition'], $filter['value'])) {
                    $collection->addAttributeToFilter($filter['attribute'], [$filter['condition'] => $filter['value']]);
                }
            }

            // Prepare needed Custom Fields
            $customFields = $this->_customFieldCollectionFactory->create()
                ->addFieldToFilter(CustomField::IDENTIFIER, array_keys($this->getRequest()->getParams()));
            
            // Add filters from url
            foreach ($this->getRequest()->getParams() as $key => $param) {
                if (!in_array($key, ['page_id', 'contentlist_id', $this->getPageVarName()])) {
                    $customField = $customFields->getItemByColumnValue('identifier', $key);

                    // If the associated custom field doesn't exists
                    if (!$customField) {
                        continue;
                    }
                    
                    // If the custom field values are array
                    //todo fix retrieve all generic data options eg: is_filterable
                    if (in_array($customField->getType(), ['drop_down', 'multiple', 'radio', 'checkbox', 'product', 'category', 'content', 'attribute', 'customer', 'country', 'currency', 'locale'])) {
                        $attributesFilter = [];
                        $param = explode(',', $param);

                        foreach ($param as $value) {
                            $attributesFilter[] = [
                                'attribute' => $key,
                                ['finset' => $value],
                            ];
                        }

                        $collection->addAttributeToFilter($attributesFilter);
                    }
                }
            }
            
            // Set the sort order
            if ($this->hasData('order_field')) {
                $orderField = $this->getOrderField();
                $sortOrder = $this->hasData('sort_order') ? $this->getSortOrder() : 'ASC';

                // Add multi sort oder
                if (is_array($orderField)) {
                    if (is_array($sortOrder)) {
                        foreach ($orderField as $key => $field) {
                            $collection->addOrder($field, $sortOrder[$key]);
                        }
                    } else {
                        foreach ($orderField as $field) {
                            $collection->addOrder($field, $sortOrder);
                        }
                    }
                } else {
                    $collection->setOrder($orderField, $sortOrder);
                }
            } else {
                $collection->setOrder('created_at', 'DESC');
            }
            
            // Set conditions to the collection
            $conditions = $this->getConditions();
            $conditions->collectValidatedAttributes($collection);
            $this->_sqlBuilder->attachConditionToCollection($collection, $conditions);

            // Limit the content to collect
            if (!$this->hasPager()) {
                $collection->getSelect()->limit($this->getLimitDisplay());
            }

            $this->_contentCollection = $collection;
        }
        
        return $this->_contentCollection;
    }

    /**
     * Prepare the widget attributes to show
     *
     * @return $this
     */
    protected function prepareWidgetAttributeToShow()
    {
        if (!empty($this->getData('attributes_show'))) {
            $attributeShow = $this->_attributeShowHelper->decode($this->getData('attributes_show'));

            foreach ($attributeShow as $attribute) {
                $this->addAttributeToShow($attribute['attribute'], $attribute['params']);
            }
        }

        return $this;
    }
    
    /**
     * Retrieve search result count
     *
     * @return string
     */
    public function getResultCount()
    {
        return $this->getContentCollection()->getSize();
    }

    /**
     * Get processed value
     *
     * @param string $value
     * @return string
     */
    public function getProcessedData($value)
    {
        return $this->_filterProvider->getBlockFilter()
            ->setStoreId($this->_storeManager->getStore()->getId())
            ->filter($value);
    }
    
}
