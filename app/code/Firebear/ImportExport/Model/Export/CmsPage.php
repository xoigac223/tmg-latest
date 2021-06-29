<?php
/**
 * Copyright (c) 2018. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Firebear\ImportExport\Model\Export;

use Firebear\ImportExport\Helper\Data;
use Firebear\ImportExport\Model\Source\Factory;
use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory;
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
 * Class CmsPage
 * @package Firebear\ImportExport\Model\Export
 */
class CmsPage extends AbstractEntity
{
    use \Firebear\ImportExport\Traits\Export\Entity;

    use \Firebear\ImportExport\Traits\General;

    /** @var CollectionFactory */
    protected $entityCollectionFactory;
    /** @var LoggerInterface */
    protected $_logger;

    /**
     * @var TypeCollectionFactory
     */
    protected $typeCollection;

    protected $headerColumns = [];

    protected $pageFields = [
        PageInterface::PAGE_ID,
        PageInterface::IDENTIFIER,
        PageInterface::TITLE,
        PageInterface::PAGE_LAYOUT,
        PageInterface::META_TITLE,
        PageInterface::META_KEYWORDS,
        PageInterface::META_DESCRIPTION,
        PageInterface::CONTENT_HEADING,
        PageInterface::CONTENT,
        PageInterface::CREATION_TIME,
        PageInterface::UPDATE_TIME,
        PageInterface::SORT_ORDER,
        PageInterface::LAYOUT_UPDATE_XML,
        PageInterface::CUSTOM_THEME,
        PageInterface::CUSTOM_ROOT_TEMPLATE,
        PageInterface::CUSTOM_LAYOUT_UPDATE_XML,
        PageInterface::CUSTOM_THEME_FROM,
        PageInterface::CUSTOM_THEME_TO,
        PageInterface::IS_ACTIVE,
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
     * CmsPage constructor.
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
        $this->typeCollection = $typeCollection;
        $this->store = $store;
        parent::__construct($localeDate, $config, $resource, $storeManager);
    }

    /**
     * Export process.
     *
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
            $entityCollection->setOrder('page_id', 'asc');
            if (isset($this->_parameters['last_entity_id'])
                && $this->_parameters['last_entity_id'] > 0
                && $this->_parameters['enable_last_entity_id'] > 0
            ) {
                $entityCollection->addFieldToFilter(
                    PageInterface::PAGE_ID,
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
                    $this->lastEntityId = $dataRow[PageInterface::PAGE_ID];
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
     * Get entity collection
     *
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

            foreach ($rawData as $productId => $dataRow) {
                $exportData[] = $dataRow;
            }
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }
        $newData = $this->changeData($exportData, PageInterface::PAGE_ID);

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
            foreach ($item->getStores() as $storeId) {
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
            $this->pageFields,
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
        foreach ($this->pageFields as $pageField) {
            $options[] = [
                'label' => $pageField,
                'value' => $pageField
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
        return 'cms_page';
    }

    /**
     * @return array
     */
    public function getFieldsForExport()
    {
        return $this->pageFields;
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
            $options['cms_page'][] = ['field' => $key, 'type' => $type, 'select' => $select];
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
