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
namespace Blackbird\ContentManager\Model\Search;

use Blackbird\ContentManager\Model\ResourceModel\Content\Attribute\CollectionFactory;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\ResourceConnection as AppResource;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Request\FilterInterface;
use Magento\Framework\Search\Request\Filter\BoolExpression;
use Magento\Framework\Search\Request\Query\Filter;
use Magento\Framework\Search\RequestInterface;
use Magento\Framework\Search\Request\QueryInterface as RequestQueryInterface;
use Magento\Store\Model\StoreManagerInterface;

class TableMapper
{
    /**
     * @var Resource
     */
    protected $resource;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Blackbird\ContentManager\Model\ResourceModel\Content\Attribute\Collection
     */
    protected $attributeCollection;

    /**
     * @param AppResource $resource
     * @param StoreManagerInterface $storeManager
     * @param CollectionFactory $attributeCollectionFactory
     */
    public function __construct(
        AppResource $resource,
        StoreManagerInterface $storeManager,
        CollectionFactory $attributeCollectionFactory
    ) {
        $this->resource = $resource;
        $this->storeManager = $storeManager;
        $this->attributeCollection = $attributeCollectionFactory->create();
    }

    /**
     * @param Select $select
     * @param RequestInterface $request
     * @return Select
     */
    public function addTables(Select $select, RequestInterface $request)
    {
        $mappedTables = [];
        $filters = $this->getFilters($request->getQuery());
        foreach ($filters as $filter) {
            list($alias, $table, $mapOn, $mappedFields) = $this->getMappingData($filter);
            if (!array_key_exists($alias, $mappedTables)) {
                $select->joinLeft(
                    [$alias => $table],
                    $mapOn,
                    $mappedFields
                );
                $mappedTables[$alias] = $table;
            }
        }
        
        return $select;
    }

    /**
     * @param FilterInterface $filter
     * @return string
     */
    public function getMappingAlias(FilterInterface $filter)
    {
        list($alias) = $this->getMappingData($filter);
        return $alias;
    }

    /**
     * Returns mapping data for field in format: [
     *  'table_alias',
     *  'table',
     *  'join_condition',
     *  ['fields']
     * ]
     * 
     * @param FilterInterface $filter
     * @return array
     */
    protected function getMappingData(FilterInterface $filter)
    {
        $alias = null;
        $table = null;
        $mapOn = null;
        $mappedFields = null;
        $field = $filter->getField();
        if ($attribute = $this->getAttributeByCode($field)) {
            if ($attribute->getBackendType() === AbstractAttribute::TYPE_STATIC) {
                $table = $attribute->getBackendTable();
                $alias = $field . RequestGenerator::FILTER_SUFFIX;
                $mapOn = 'search_index.entity_id = ' . $alias . '.entity_id';
                $mappedFields = null;
            }
        }

        return [$alias, $table, $mapOn, $mappedFields];
    }

    /**
     * @param RequestQueryInterface $query
     * @return FilterInterface[]
     */
    protected function getFilters($query)
    {
        $filters = [];
        switch ($query->getType()) {
            case RequestQueryInterface::TYPE_BOOL:
                /** @var \Magento\Framework\Search\Request\Query\BoolExpression $query */
                foreach ($query->getMust() as $subQuery) {
                    $filters = array_merge($filters, $this->getFilters($subQuery));
                }
                foreach ($query->getShould() as $subQuery) {
                    $filters = array_merge($filters, $this->getFilters($subQuery));
                }
                foreach ($query->getMustNot() as $subQuery) {
                    $filters = array_merge($filters, $this->getFilters($subQuery));
                }
                break;
            case RequestQueryInterface::TYPE_FILTER:
                /** @var Filter $query */
                $filter = $query->getReference();
                if (FilterInterface::TYPE_BOOL === $filter->getType()) {
                    $filters = array_merge($filters, $this->getFiltersFromBoolFilter($filter));
                } else {
                    $filters[] = $filter;
                }
                break;
            default:
                break;
        }
        return $filters;
    }

    /**
     * @param BoolExpression $boolExpression
     * @return FilterInterface[]
     */
    protected function getFiltersFromBoolFilter(BoolExpression $boolExpression)
    {
        $filters = [];
        /** @var BoolExpression $filter */
        foreach ($boolExpression->getMust() as $filter) {
            if ($filter->getType() === FilterInterface::TYPE_BOOL) {
                $filters = array_merge($filters, $this->getFiltersFromBoolFilter($filter));
            } else {
                $filters[] = $filter;
            }
        }
        foreach ($boolExpression->getShould() as $filter) {
            if ($filter->getType() === FilterInterface::TYPE_BOOL) {
                $filters = array_merge($filters, $this->getFiltersFromBoolFilter($filter));
            } else {
                $filters[] = $filter;
            }
        }
        foreach ($boolExpression->getMustNot() as $filter) {
            if ($filter->getType() === FilterInterface::TYPE_BOOL) {
                $filters = array_merge($filters, $this->getFiltersFromBoolFilter($filter));
            } else {
                $filters[] = $filter;
            }
        }
        return $filters;
    }

    /**
     * @return int
     */
    protected function getWebsiteId()
    {
        return $this->storeManager->getWebsite()->getId();
    }

    /**
     * @return int
     */
    protected function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * @param string $field
     * @return \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     */
    protected function getAttributeByCode($field)
    {
        $attribute = $this->attributeCollection->getItemByColumnValue('attribute_code', $field);
        return $attribute;
    }
}
