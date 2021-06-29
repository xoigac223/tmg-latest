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
namespace Blackbird\ContentManager\Block;

use Blackbird\ContentManager\Model\Content;
use Blackbird\ContentManager\Model\ContentType;
use Blackbird\ContentManager\Model\ContentType\CustomField;
use Blackbird\ContentManager\Model\ContentType\CustomField\Option;

/**
 * @method string getFilterDirect
 * @method Filter setFilterDirect(string $direct)
 * @method Filter setFilterPath(string $path)
 */
class Filter extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Blackbird\ContentManager\Model\ResourceModel\ContentType\CollectionFactory
     */
    protected $_contentTypeCollectionFactory;

    /**
     * @var \Blackbird\ContentManager\Model\ResourceModel\Content\CollectionFactory
     */
    protected $_contentCollectionFactory;

    /**
     * @var string
     */
    protected $_template = 'Blackbird_ContentManager::content/view/filter.phtml';

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Blackbird\ContentManager\Model\ResourceModel\ContentType\CollectionFactory $contentTypeCollectionFactory
     * @param \Blackbird\ContentManager\Model\ResourceModel\Content\CollectionFactory $contentCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Blackbird\ContentManager\Model\ResourceModel\ContentType\CollectionFactory $contentTypeCollectionFactory,
        \Blackbird\ContentManager\Model\ResourceModel\Content\CollectionFactory $contentCollectionFactory,
        array $data = []
    ) {
        $this->_contentTypeCollectionFactory = $contentTypeCollectionFactory;
        $this->_contentCollectionFactory = $contentCollectionFactory;
        parent::__construct($context, $data);
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
            if ($ctIdentifier) {
                $contentType = ($contentTypeCollection->count()) ? $contentTypeCollection->getFirstItem() : null;
            }

            $this->setData('content_type', $contentType);
        }

        return $this->getData('content_type');
    }

    /**
     * Retrieve the block filter title
     *
     * @return string
     */
    public function getTitle()
    {
        if (!$this->hasData('title')) {
            $this->setData('title', __('Filter By'));
        }

        return $this->getData('title');
    }

    /**
     * Retrieve the block filter subtitle
     *
     * @return string
     */
    public function getSubtitle()
    {
        if (!$this->hasData('subtitle')) {
            $this->setData('subtitle', __('Filter Options'));
        }

        return $this->getData('subtitle');
    }

    /**
     * Retrieve the filters as CustomField objects
     *
     * @return \Blackbird\ContentManager\Model\ResourceModel\ContentType\CustomField\Collection
     */
    public function getFilters()
    {
        if (!$this->hasData('loaded_filters') && $this->hasData('filters')) {
            $filters = $this->getContentType()
                ->getCustomFieldCollection()
                ->addFieldToFilter(CustomField::IDENTIFIER, array_values($this->getData('filters')))
                //todo fix retrieve all generic data options eg: is_filterable
                ;//->addFieldToFilter(CustomField::TYPE, ['drop_down', 'multiple', 'radio', 'checkbox', 'country', 'currency', 'locale']);

            $this->setData('loaded_filters', $filters);
        }

        return $this->getData('loaded_filters');
    }

    /**
     * Retrieve the extra params of the filter url
     *
     * @todo Exclude the page var name
     * @return array
     */
    public function getFilterParams()
    {
        if (!$this->hasData('filter_params')) {
            $params = [
                '_current' => true,
                '_use_rewrite' => true,
            ];

            if ($this->getFilterDirect()) {
                $params['_direct'] = $this->getFilterDirect();
            }

            $this->setData('filter_params', $params);
        }

        return $this->getData('filter_params');
    }

    /**
     * Retrieve the filter path to redirect where
     *
     * @return string
     */
    public function getFilterPath()
    {
        if ($this->getFilterDirect() && $this->hasData('filter_path')) {
            $this->setData('filter_path', '');
        } elseif (!$this->getFilterDirect() && !$this->hasData('filter_path')) {
            $this->setData('filter_path', '*/*/*');
        }

        return $this->getData('filter_path');
    }

    /**
     * Get the filter url for a custom field filter
     *
     * @param string $filterIdentifier
     * @param string $filterValue
     * @return string
     */
    public function getFilterUrl($filterIdentifier, $filterValue)
    {
        $params = $this->getFilterParams();
        $params['_query'] = [$filterIdentifier => $filterValue];

        return $this->getUrl($this->getFilterPath(), $params);
    }

    /**
     * Get the remove url for a custom field filter
     *
     * @param string $filterIdentifier
     * @return string
     */
    public function getRemoveUrl($filterIdentifier)
    {
        $params = [
            '_current' => true,
            '_use_rewrite' => true,
            '_query' => [$filterIdentifier => null],
            '_escape' => true
        ];

        return $this->getUrl('*/*/*', $params);
    }

    /**
     * Get url for 'Clear All' link
     *
     * @return string
     */
    public function getClearUrl()
    {
        $filterState = [];

        foreach ($this->getActiveFilters() as $filter) {
            $filterState[$filter->getIdentifier()] = null;
        }

        $params = [
            '_current' => true,
            '_use_rewrite' => true,
            '_query' => $filterState,
            '_escape' => true
        ];

        return $this->getUrl('*/*/*', $params);
    }

    /**
     * Check is there's active filters
     *
     * @return bool
     */
    public function hasActiveFilters()
    {
        return !empty($this->getActiveFilters());
    }

    /**
     * Retrieve the active filters
     *
     * @return \Blackbird\ContentManager\Model\ContentType\CustomField[]
     */
    public function getActiveFilters()
    {
        if (!$this->hasData('active_filters') && $this->getFilters() && $this->getFilters()->count()) {
            $activeFilters = [];

            foreach ($this->getFilters() as $filter) {
                if ($this->isFilterActive($filter->getIdentifier())) {
                    $value = $filter->getOptionCollection()
                        ->addFieldToFilter(Option::VALUE, $this->getRequest()->getParam($filter->getIdentifier()));

                    if ($value->count()) {
                        $filter->setValue($value->getFirstItem()->getTitle());
                        $activeFilters[] = $filter;
                    }
                }
            }

            $this->setData('active_filters', $activeFilters);
        }

        return $this->getData('active_filters');
    }

    /**
     * Check if a filter is active (by its value: optional)
     *
     * @param string $filterIdentifier
     * @param null|mixed $filterValue
     * @return bool
     */
    public function isFilterActive($filterIdentifier, $filterValue = null)
    {
        $isFilterActive = !empty($this->getRequest()->getParam($filterIdentifier));

        if ($isFilterActive && !is_null($filterValue)) {
            $isFilterActive = ($this->getRequest()->getParam($filterIdentifier) == $filterValue);
        }

        return $isFilterActive;
    }

    /**
     * Retrieve the total count of potential results for a filter
     *
     * @param string $filterIdentifier
     * @param mixed $filterValue
     * @return int
     */
    public function getResultFilterCount($filterIdentifier, $filterValue)
    {
        return $this->_contentCollectionFactory->create()
            ->addStoreFilter()
            ->addContentTypeFilter($this->getContentType())
            ->addAttributeToFilter(Content::STATUS, 1)
            ->addAttributeToFilter($filterIdentifier, ['finset' => $filterValue])
            ->getSize();
    }

    /**
     * @inheritdoc
     */
    protected function _beforeToHtml()
    {
        // Load the collection
        $this->getFilters()->load();

        return parent::_beforeToHtml();
    }
}
