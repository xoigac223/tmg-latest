<?php
/**
 * Copyright (c) 2018. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Firebear\ImportExport\Model\Export;

use Firebear\ImportExport\Helper\Data;
use Firebear\ImportExport\Model\Source\Factory;
use Magento\Cms\Api\Data\BlockInterface;
use Magento\Cms\Model\ResourceModel\Block\CollectionFactory;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\ResourceModel\Entity\Type\CollectionFactory as TypeCollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\ImportExport\Model\Export\Entity\AbstractEntity;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Class CmsBlock
 * @package Firebear\ImportExport\Model\Export
 */
class CmsBlock extends AbstractEntity
{
    use \Firebear\ImportExport\Traits\Export\Entity;

    use \Firebear\ImportExport\Traits\General;

    /** @var CollectionFactory */
    protected $entityCollectionFactory;

    /**
     * @var TypeCollectionFactory
     */
    protected $typeCollection;
    /** @var LoggerInterface */
    protected $_logger;

    /**
     * @var \Firebear\ImportExport\Model\Source\Factory
     */
    protected $createFactory;

    protected $headerColumns = [];

    protected $blockFields = [
        BlockInterface::BLOCK_ID,
        BlockInterface::CONTENT,
        BlockInterface::CREATION_TIME,
        BlockInterface::IDENTIFIER,
        BlockInterface::IS_ACTIVE,
        BlockInterface::TITLE,
        BlockInterface::UPDATE_TIME
    ];

    protected $fieldsMap = [];

    /**
     * Items per page for collection limitation
     *
     * @var int|null
     */
    protected $itemsPerPage = null;

    /**
     * @var Store
     */
    protected $store;

    /**
     * @var \Firebear\ImportExport\Helper\Data
     */
    protected $helper;


    /**
     * CmsBlock constructor.
     * @param TimezoneInterface $localeDate
     * @param Config $config
     * @param ResourceConnection $resource
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     * @param Factory $createFactory
     * @param Data $helper
     * @param CollectionFactory $collectionFactory
     * @param TypeCollectionFactory $typeCollection
     * @param Store $store
     * @param ConsoleOutput $output
     */
    public function __construct(
        TimezoneInterface $localeDate,
        Config $config,
        ResourceConnection $resource,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger,
        Factory $createFactory,
        Data $helper,
        CollectionFactory $collectionFactory,
        TypeCollectionFactory $typeCollection,
        Store $store,
        ConsoleOutput $output
    ) {
        $this->_logger = $logger;
        $this->createFactory = $createFactory;
        $this->helper = $helper;
        $this->output = $output;
        $this->_debugMode = $helper->getDebugMode();
        $this->entityCollectionFactory = $collectionFactory;
        $this->store = $store;
        $this->typeCollection = $typeCollection;
        parent::__construct($localeDate, $config, $resource, $storeManager);
    }

    /**
     * @return array|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function export()
    {
        set_time_limit(0);

        $writer = $this->getWriter();
        $page = 0;
        $counts = 0;
        while (true) {
            ++$page;
            $entityCollection = $this->_getEntityCollection(true);
            $entityCollection->setOrder('block_id', 'asc');
            if (isset($this->_parameters['last_entity_id'])
                && $this->_parameters['last_entity_id'] > 0
                && $this->_parameters['enable_last_entity_id'] > 0
            ) {
                $entityCollection->addFieldToFilter(
                    BlockInterface::BLOCK_ID,
                    ['gt' => $this->_parameters['last_entity_id']]
                );
            }
            $this->paginateCollection($page, $this->getItemsPerPage());
            if ($entityCollection->count() == 0) {
                break;
            }
            $exportData = $this->getExportData();
            if ($page == 1) {
                $writer->setHeaderCols($this->_getHeaderColumns());
            }
            foreach ($exportData as $dataRow) {
                if ($this->_parameters['enable_last_entity_id'] > 0) {
                    $this->lastEntityId = $dataRow[BlockInterface::BLOCK_ID];
                }
                $dd = $this->_customFieldsMapping($dataRow);
                $writer->writeRow($dd);
                $counts++;
            }

            if ($entityCollection->getCurPage() >= $entityCollection->getLastPageNumber()) {
                break;
            }
        }

        return [$writer->getContents(), $counts, $this->lastEntityId];
    }

    /**
     * @param bool $resetCollection
     * @return \Magento\Framework\Data\Collection\AbstractDb|void
     */
    protected function _getEntityCollection($resetCollection = false)
    {
        if ($resetCollection || empty($this->entityCollection)) {
            $this->entityCollection = $this->entityCollectionFactory->create();
        }

        return $this->entityCollection;
    }

    /**
     * @param $page
     * @param $pageSize
     */
    protected function paginateCollection($page, $pageSize)
    {
        $this->_getEntityCollection()
            ->setCurPage($page)
            ->setPageSize($pageSize);
    }

    /**
     * @return int|null
     */
    protected function getItemsPerPage()
    {
        if ($this->itemsPerPage === null) {
            $memoryLimitConfigValue = trim(ini_get('memory_limit'));
            $lastMemoryLimitLetter = strtolower($memoryLimitConfigValue[strlen($memoryLimitConfigValue) - 1]);
            $memoryLimit = (int)$memoryLimitConfigValue;
            switch ($lastMemoryLimitLetter) {
                case 'g':
                    $memoryLimit *= 1024;
                //next
                case 'm':
                    $memoryLimit *= 1024;
                //next
                case 'k':
                    $memoryLimit *= 1024;
                    break;
                default:
                    $memoryLimit = 250000000;
            }

            $memoryPerProduct = 500000;
            $memoryUsagePercent = 0.8;
            $minProductsLimit = 500;
            $maxProductsLimit = 5000;

            $this->itemsPerPage = intval(
                ($memoryLimit * $memoryUsagePercent - memory_get_usage(true)) / $memoryPerProduct
            );
            if ($this->itemsPerPage < $minProductsLimit) {
                $this->itemsPerPage = $minProductsLimit;
            }
            if ($this->itemsPerPage > $maxProductsLimit) {
                $this->itemsPerPage = $maxProductsLimit;
            }
        }

        return $this->itemsPerPage;
    }

    /**
     * @return array
     */
    protected function getExportData()
    {
        $exportData = [];
        try {
            $rawData = $this->collectRawData();

            foreach ($rawData as $blockId => $dataRow) {
                $exportData[] = $dataRow;
            }
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }
        $newData = $this->changeData($exportData, BlockInterface::BLOCK_ID);

        $this->headerColumns = $this->changeHeaders($this->headerColumns);


        return $newData;
    }

    /**
     * @return array
     */
    protected function collectRawData()
    {
        $data = [];
        $collection = $this->_getEntityCollection();

        foreach ($collection as $itemId => $item) {
            $stores = [];
            $data[$itemId] = $item->getData();
            $itemStores = $item->getStores();
            if (!is_array($itemStores)) {
                $itemStores = [$itemStores];
            }
            foreach ($itemStores as $storeId) {
                $store = $this->store->load($storeId);
                if ($store->getCode() === 'admin') {
                    array_push($stores, 'All');
                } else {
                    array_push($stores, $store->getCode());
                }
            }
            $data[$itemId]['store_view_code'] = implode(',', $stores);
        }
        return $data;
    }

    /**
     * Get header columns
     *
     * @return string[]
     */
    protected function _getHeaderColumns()
    {
        $headers = array_merge(
            $this->blockFields,
            ['store_view_code']
        );

        return $this->changeHeaders($headers);
    }

    /**
     * @param $rowData
     * @return array
     */
    protected function _customFieldsMapping($rowData)
    {
        $headerColumns = $this->_getHeaderColumns();

        foreach ($this->fieldsMap as $systemFieldName => $fileFieldName) {
            if (isset($rowData[$systemFieldName])) {
                $rowData[$fileFieldName] = $rowData[$systemFieldName];
                unset($rowData[$systemFieldName]);
            }
        }

        if (count($headerColumns) != count(array_keys($rowData))) {
            $newData = [];
            foreach ($headerColumns as $code) {
                if (!isset($rowData[$code])) {
                    $newData[$code] = '';
                } else {
                    $newData[$code] = $rowData[$code];
                }
            }
            $rowData = $newData;
        }

        return $rowData;
    }

    /**
     * @return array
     */
    public function getFieldsForFilter()
    {
        $options = [];
        foreach ($this->blockFields as $blockField) {
            $options[] = [
                'label' => $blockField,
                'value' => $blockField
            ];
        }
        return [$this->getEntityTypeCode() => $options];
    }

    /**
     * EAV entity type code getter.
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        return 'cms_block';
    }

    /**
     * @return mixed
     */
    public function getFieldsForExport()
    {
        return $this->blockFields;
    }

    /**
     * @return array|\Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection
     */
    public function getAttributeCollection()
    {
        return [];
    }

    public function getFieldColumns()
    {
        $options = [];
        $model = $this->createFactory->create('\Magento\Cms\Model\Block');
        $fields = $this->describeTable($model);
        $mergeFields = [];
        foreach ($fields as $key => $field) {
            $type = $this->helper->convertTypesTables($field['DATA_TYPE']);
            $select = [];
            if (isset($mergeFields[$key])) {
                if (!$mergeFields[$key]['delete']) {
                    $type = $mergeFields[$key]['type'];
                    $select = $mergeFields[$key]['options'];
                }
            }
            $options['cms_block'][] = ['field' => $key, 'type' => $type, 'select' => $select];
        }

        return $options;
    }

    protected function describeTable($model = null)
    {
        $resource = $model->getResource();
        $table = $resource->getMainTable();
        $fields = $resource->getConnection()->describeTable($table);

        return $fields;
    }
}
