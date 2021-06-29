<?php
/**
 * @copyright: Copyright © 2018 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExport\Model\Import\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\Option\Value\Collection as ProductOptionValueCollection;
use Magento\Catalog\Model\ResourceModel\Product\Option\Value\CollectionFactory as ProductOptionValueCollectionFactory;
use Magento\CatalogImportExport\Model\Import\Product;
use Magento\CatalogImportExport\Model\Import\Product\Option as BaseOption;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;

class Option extends BaseOption
{
    /**
     * Product entity link field
     *
     * @var string
     */
    private $productEntityLinkField;

    /**
     * @var ProductOptionValueCollectionFactory
     */
    private $productOptionValueCollectionFactory;

    /**
     * @var array
     */
    private $optionTypeTitles;

    /**
     * @param \Firebear\ImportExport\Model\ResourceModel\Import\Data $importData
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\Option\CollectionFactory $optionColFactory
     * @param \Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory $colIteratorFactory
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $dateTime
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @param ProductOptionValueCollectionFactory $productOptionValueCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Firebear\ImportExport\Model\ResourceModel\Import\Data $importData,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Option\CollectionFactory $optionColFactory,
        \Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory $colIteratorFactory,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $dateTime,
        ProcessingErrorAggregatorInterface $errorAggregator,
        ProductOptionValueCollectionFactory $productOptionValueCollectionFactory,
        array $data = []
    ) {
        parent::__construct(
            $importData,
            $resource,
            $resourceHelper,
            $storeManager,
            $productFactory,
            $optionColFactory,
            $colIteratorFactory,
            $catalogData,
            $scopeConfig,
            $dateTime,
            $errorAggregator,
            $data
        );
        $this->productOptionValueCollectionFactory = $productOptionValueCollectionFactory;
    }

    public function validateRow(array $rowData, $rowNumber)
    {
        if (isset($this->_validatedRows[$rowNumber])) {
            return !isset($this->_invalidRows[$rowNumber]);
        }
        $this->_validatedRows[$rowNumber] = true;

        $multiRowData = $this->_getMultiRowFormat($rowData);

        foreach ($multiRowData as $optionData) {
            $combinedData = array_merge($rowData, $optionData);

            if ($this->_isRowWithCustomOption($combinedData)) {
                if ($this->_isMainOptionRow($combinedData)) {
                    if (!$this->_validateMainRow($combinedData, $rowNumber)) {
                        return false;
                    }
                }
                if ($this->_isSecondaryOptionRow($combinedData)) {
                    if (!$this->_validateSecondaryRow($combinedData, $rowNumber)) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    protected function _importData()
    {
        $this->_initProductsSku();

        $nextOptionId = $this->_resourceHelper->getNextAutoincrement(
            $this->_tables['catalog_product_option']
        );
        $nextValueId = $this->_resourceHelper->getNextAutoincrement(
            $this->_tables['catalog_product_option_type_value']
        );
        $prevOptionId = 0;
        $optionId = null;
        $valueId = null;
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $products = [];
            $options = [];
            $titles = [];
            $prices = [];
            $typeValues = [];
            $typePrices = [];
            $typeTitles = [];
            $parentCount = [];
            $childCount = [];
            $optionsToRemove = [];

            foreach ($bunch as $rowNumber => $rowData) {
                if (isset($optionId, $valueId) && empty($rowData[Product::COL_STORE_VIEW_CODE])) {
                    $nextOptionId = $optionId;
                    $nextValueId = $valueId;
                }
                $optionId = $nextOptionId;
                $valueId = $nextValueId;
                $multiRowData = $this->_getMultiRowFormat($rowData);
                if (!empty($rowData[self::COLUMN_SKU]) && isset($this->_productsSkuToId[$rowData[self::COLUMN_SKU]])) {
                    $this->_rowProductId = $this->_productsSkuToId[$rowData[self::COLUMN_SKU]];
                    if (array_key_exists('custom_options', $rowData) && trim($rowData['custom_options']) === '') {
                        $optionsToRemove[] = $this->_rowProductId;
                    }
                }
                foreach ($multiRowData as $optionData) {
                    $combinedData = array_merge($rowData, $optionData);

                    if (!$this->isRowAllowedToImport($combinedData, $rowNumber)) {
                        continue;
                    }
                    if (!$this->_parseRequiredData($combinedData)) {
                        continue;
                    }
                    $optionData = $this->_collectOptionMainData(
                        $combinedData,
                        $prevOptionId,
                        $optionId,
                        $products,
                        $prices
                    );
                    if ($optionData != null) {
                        $options[] = $optionData;
                    }
                    $this->_collectOptionTypeData(
                        $combinedData,
                        $prevOptionId,
                        $valueId,
                        $typeValues,
                        $typePrices,
                        $typeTitles,
                        $parentCount,
                        $childCount
                    );
                    $this->_collectOptionTitle($combinedData, $prevOptionId, $titles);
                }
            }

            // Remove all existing options if import behaviour is APPEND
            // in other case remove options for products with empty "custom_options" row only
            if ($this->getBehavior() != Import::BEHAVIOR_APPEND) {
                $this->_deleteEntities(array_keys($products));
            } elseif (!empty($optionsToRemove)) {
                // Remove options for products with empty "custom_options" row
                $this->_deleteEntities($optionsToRemove);
            }

            // Save prepared custom options data
            if ($this->_isReadyForSaving($options, $titles, $typeValues)) {
                $types = [
                    'values' => $typeValues,
                    'prices' => $typePrices,
                    'titles' => $typeTitles
                ];
                $this->savePreparedCustomOptions($products, $options, $titles, $prices, $types);
            }
        }

        return true;
    }

    protected function _initProductsSku()
    {
        if (!$this->_productsSkuToId || !empty($this->_newOptionsNewData)) {
            $select = $this->_connection->select()->from(
                $this->_tables['catalog_product_entity'],
                [ProductInterface::SKU, $this->getProductEntityLinkField()]
            );
            $this->_productsSkuToId = $this->_connection->fetchPairs($select);
        }

        return $this;
    }

    protected function _initOldCustomOptions()
    {
        return $this;
    }

    /**
     * Initialize Custom Options By Product Identifiers
     *
     * @param array $productIds
     * @return $this
     */
    protected function initCustomOptionsByProductIds($productIds)
    {
        foreach ($this->_storeCodeToId as $storeId) {
            $select = $this->_connection->select()
                ->from(
                    ['option' => $this->_tables['catalog_product_option']],
                    ['option_id', 'product_id', 'type']
                )
                ->join(
                    ['option_title' => $this->_tables['catalog_product_option_title']],
                    'option_title.option_id = option.option_id',
                    ['title']
                )
                ->where(
                    'option_title.store_id = ?',
                    $storeId
                )->where(
                    'option.product_id IN (?)',
                    $productIds
                );

            $stmt = $this->_connection->query($select);
            while ($row = $stmt->fetch()) {
                $optionId = $row['option_id'];
                $productId = $row['product_id'];
                $type = $row['type'];
                $title = $row['title'];

                if (!isset($this->_oldCustomOptions[$productId])) {
                    $this->_oldCustomOptions[$productId] = [];
                }
                if (isset($this->_oldCustomOptions[$productId][$optionId])) {
                    $this->_oldCustomOptions[$productId][$optionId]['titles'][$storeId] = $title;
                } else {
                    $this->_oldCustomOptions[$productId][$optionId] = [
                        'titles' => [$storeId => $title],
                        'type' => $type,
                    ];
                }
            }
        }

        return $this;
    }

    protected function _compareOptionsWithExisting(array &$options, array &$titles, array &$prices, array &$typeValues)
    {
        $productIds = [];
        foreach ($options as $option) {
            $productIds[] = $option['product_id'];
        }
        $this->initCustomOptionsByProductIds($productIds);
        parent::_compareOptionsWithExisting($options, $titles, $prices, $typeValues);

        return $this;
    }

    protected function _findNewOldOptionsTypeMismatch()
    {
        $this->initCustomOptionsByProductIds(array_keys($this->_newOptionsOldData));

        return parent::_findNewOldOptionsTypeMismatch();
    }

    protected function _findOldOptionsWithTheSameTitles()
    {
        $this->initCustomOptionsByProductIds(array_keys($this->_newOptionsOldData));

        return parent::_findOldOptionsWithTheSameTitles();
    }

    protected function _getSpecificTypeData(array $rowData, $optionTypeId, $defaultStore = true)
    {
        if (!empty($rowData[self::COLUMN_ROW_TITLE]) && $defaultStore && empty($rowData[self::COLUMN_STORE])) {
            $valueData = [
                'option_type_id' => $optionTypeId,
                'sort_order' => empty($rowData[self::COLUMN_ROW_SORT]) ? 0 : abs($rowData[self::COLUMN_ROW_SORT]),
                'sku' => !empty($rowData[self::COLUMN_ROW_SKU]) ? $rowData[self::COLUMN_ROW_SKU] : '',
            ];

            if (!empty($rowData[self::COLUMN_ROW_PRICE])) {
                $priceData = [
                    'price' => (double)rtrim($rowData[self::COLUMN_ROW_PRICE], '%'),
                    'price_type' => 'fixed',
                ];
                if ('%' == substr($rowData[self::COLUMN_ROW_PRICE], -1)) {
                    $priceData['price_type'] = 'percent';
                }
            } else {
                $priceData = [
                    'price' => 0,
                    'price_type' => 'fixed'
                ];
            }

            return [
                'value' => $valueData,
                'title' => $rowData[self::COLUMN_ROW_TITLE],
                'price' => $priceData
            ];
        } elseif (!empty($rowData[self::COLUMN_ROW_TITLE]) && !$defaultStore && !empty($rowData[self::COLUMN_STORE])) {
            return [
                'title' => $rowData[self::COLUMN_ROW_TITLE]
            ];
        }

        return false;
    }

    /**
     * Prepare Existing Option Type Info
     *
     * @param array $products
     */
    protected function prepareExistingOptionTypeIds($products)
    {
        $productIds = array_keys($products);
        foreach ($this->_storeCodeToId as $storeId) {
            if (!isset($this->optionTypeTitles[$storeId])) {
                /** @var ProductOptionValueCollection $optionTypeCollection */
                $optionTypeCollection = $this->productOptionValueCollectionFactory->create();
                $optionTable = $optionTypeCollection->getTable('catalog_product_option');
                $optionTypeCollection->addTitleToResult($storeId);
                $optionTypeCollection->getSelect()
                    ->joinLeft(
                        ['product_option' => $optionTable],
                        'product_option.option_id = main_table.option_id',
                        ['product_id' => 'product_id']
                    )->where(
                        'product_id IN (?)',
                        $productIds
                    );

                $stmt = $this->_connection->query($optionTypeCollection->getSelect());
                while ($row = $stmt->fetch()) {
                    $this->optionTypeTitles[$storeId][$row['option_id']][$row['option_type_id']] = $row['title'];
                }
            }
        }
    }

    /**
     * Restore original IDs for existing option types.
     *
     * Warning: arguments are modified by reference
     *
     * @param array $typeValues
     * @param array $typePrices
     * @param array $typeTitles
     * @return void
     */
    private function restoreOriginalOptionTypeIds(array &$typeValues, array &$typePrices, array &$typeTitles)
    {
        foreach ($typeValues as $optionId => &$optionTypes) {
            foreach ($optionTypes as &$optionType) {
                $optionTypeId = $optionType['option_type_id'];
                foreach ($typeTitles[$optionTypeId] as $storeId => $optionTypeTitle) {
                    $existingTypeId = $this->getExistingOptionTypeId($optionId, $storeId, $optionTypeTitle);
                    if ($existingTypeId) {
                        $optionType['option_type_id'] = $existingTypeId;
                        $typeTitles[$existingTypeId] = $typeTitles[$optionTypeId];
                        unset($typeTitles[$optionTypeId]);
                        $typePrices[$existingTypeId] = $typePrices[$optionTypeId];
                        unset($typePrices[$optionTypeId]);
                        // If option type titles match at least in one store, consider current option type as existing
                        break;
                    }
                }
            }
        }
    }

    /**
     * Identify ID of the provided option type by its title in the specified store.
     *
     * @param int $optionId
     * @param int $storeId
     * @param string $optionTypeTitle
     * @return int|null
     */
    private function getExistingOptionTypeId($optionId, $storeId, $optionTypeTitle)
    {
        if (isset($this->optionTypeTitles[$storeId][$optionId])
            && is_array($this->optionTypeTitles[$storeId][$optionId])
        ) {
            foreach ($this->optionTypeTitles[$storeId][$optionId] as $optionTypeId => $currentTypeTitle) {
                if ($optionTypeTitle === $currentTypeTitle) {
                    return $optionTypeId;
                }
            }
        }

        return null;
    }

    /**
     * Get product entity link field
     *
     * @return string
     */
    private function getProductEntityLinkField()
    {
        if (!$this->productEntityLinkField) {
            $this->productEntityLinkField = $this->getMetadataPool()
                ->getMetadata(ProductInterface::class)
                ->getLinkField();
        }

        return $this->productEntityLinkField;
    }

    /**
     * Save prepared custom options
     *
     * @param array $products
     * @param array $options
     * @param array $titles
     * @param array $prices
     * @param array $types
     *
     * @return void
     */
    private function savePreparedCustomOptions(
        array $products,
        array $options,
        array $titles,
        array $prices,
        array $types
    ) {
        if ($this->getBehavior() == Import::BEHAVIOR_APPEND) {
            $this->_compareOptionsWithExisting($options, $titles, $prices, $types['values']);
            $this->prepareExistingOptionTypeIds($products);
            $this->restoreOriginalOptionTypeIds($types['values'], $types['prices'], $types['titles']);
        }

        $this->_saveOptions($options)
            ->_saveTitles($titles)
            ->_savePrices($prices)
            ->_saveSpecificTypeValues($types['values'])
            ->_saveSpecificTypePrices($types['prices'])
            ->_saveSpecificTypeTitles($types['titles'])
            ->_updateProducts($products);
    }
}
