<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Plugin\Elasticsearch\Model\Adapter\DataMapper;

use Amasty\Shopby\Plugin\Elasticsearch\Model\Adapter\DataMapperInterface;
use Magento\Customer\Model\ResourceModel\Group\CollectionFactory;
use Magento\Store\Model\ScopeInterface;

/**
 * Class OnSale
 * @package Amasty\Shopby\Plugin\Elasticsearch\Model\Adapter\DataMapper
 */
class OnSale implements DataMapperInterface
{
    const FIELD_NAME = 'am_on_sale';

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var \Amasty\Shopby\Model\Layer\Filter\OnSale\Helper
     */
    private $onSaleHelper;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Group\CollectionFactory
     */
    private $customerGrouprCollectionFactory;

    /**
     * @var array
     */
    private $onSaleProductIds = [];

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Customer\Model\ResourceModel\Group\CollectionFactory $customerGroupCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Amasty\Shopby\Model\Layer\Filter\OnSale\Helper $onSaleHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->customerGrouprCollectionFactory = $customerGroupCollectionFactory;
        $this->storeManager = $storeManager;
        $this->onSaleHelper = $onSaleHelper;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param int $entityId
     * @param array $entityIndexData
     * @param int $storeId
     * @param array $context
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function map($entityId, array $entityIndexData, $storeId, $context = [])
    {
        $collection = $this->customerGrouprCollectionFactory->create();
        $mappedData = [];
        $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
        foreach ($collection as $item) {
            $mappedData[self::FIELD_NAME . '_' . $item->getId() . '_' . $websiteId] =
                    (int)$this->isProductOnSale($entityId, $storeId, $item->getId());
        }
        return $mappedData;
    }

    /**
     * @return bool
     */
    public function isAllowed()
    {
        return $this->scopeConfig->isSetFlag('amshopby/am_on_sale_filter/enabled', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param int $entityId
     * @param int $storeId
     * @param itn $groupId
     * @return bool
     */
    private function isProductOnSale($entityId, $storeId, $groupId)
    {
        $onSaleProducts = $this->getOnSaleProductIds($storeId);
        if (isset($onSaleProducts[$entityId])) {
            $customerGroupIds = $onSaleProducts[$entityId];
            return empty($customerGroupIds) || in_array($groupId, $customerGroupIds);
        }
        return false;
    }

    /**
     * @param $storeId
     * @return array
     */
    private function getOnSaleProductIds($storeId)
    {
        if (!isset($this->onSaleProductIds[$storeId])) {
            $this->onSaleProductIds[$storeId] = [];
            $collection = $this->productCollectionFactory->create()->addStoreFilter($storeId);
            $this->onSaleHelper->addOnSaleFilter($collection, $storeId, false);
            $collection->getSelect()->group('e.entity_id');
            $collection->getSelect()->columns(
                ['customer_group_ids' =>
                    new \Zend_Db_Expr('GROUP_CONCAT(price_index.customer_group_id SEPARATOR ",")')]
            );
            foreach ($collection as $item) {
                $customerGroupIds = $item->getCustomerGroupIds() === null ?
                    [] : array_unique(explode(',', $item->getCustomerGroupIds()));
                $this->onSaleProductIds[$storeId][$item->getId()] = $customerGroupIds;
            }
        }
        return $this->onSaleProductIds[$storeId];
    }
}
