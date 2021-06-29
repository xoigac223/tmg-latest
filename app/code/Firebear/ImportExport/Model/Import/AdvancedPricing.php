<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExport\Model\Import;

use Firebear\ImportExport\Helper\Data as ImportExportHelper;
use Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing\Validator;
use Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing\Validator\Website as WebsiteValidator;
use Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing\Validator\TierPrice as TierPriceValidator;
use Magento\Catalog\Helper\Data as CatalogHelper;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface;
use Magento\CatalogImportExport\Model\Import\Product\StoreResolver;
use Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceModelFactory;
use Magento\Framework\App\ProductMetadata;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\ConsoleOutput;

class AdvancedPricing extends \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing
{
    use \Firebear\ImportExport\Traits\General;

    const TIER_PRICE_TYPE_FIXED = 'Fixed';

    const TIER_PRICE_TYPE_PERCENT = 'Discount';

    /**
     * @var array
     */
    protected $checkDuplicates = [];

    /**
     * @var array
     */
    protected $messageTemplates = [
        RowValidatorInterface::ERROR_DUPLICATE_UNIQUE_ATTRIBUTE => 'Duplicate unique attribute'
    ];

    /**
     * @var LoggerInterface
     */
    private $_logger;

    /**
     * @var ConsoleOutput
     */
    protected $output;

    protected $_debugMode;

    private $productEntityLinkField;

    protected $entityProducts;

    /**
     * @var ProductMetadata
     */
    protected $productMetadata;

    /**
     * @param Context $context
     * @param ProcessingErrorAggregator $errorAggregator
     * @param DateTime $dateTime
     * @param ResourceModelFactory $resourceFactory
     * @param ProductModel $productModel
     * @param CatalogHelper $catalogHelper
     * @param StoreResolver $storeResolver
     * @param Product $importProduct
     * @param Validator $validator
     * @param WebsiteValidator $websiteValidator
     * @param TierPriceValidator $tierPriceValidator
     * @param ConsoleOutput $output
     * @param ImportExportHelper $helper
     * @param ProductMetadata $productMetadata
     */
    public function __construct(
        Context $context,
        ProcessingErrorAggregator $errorAggregator,
        DateTime $dateTime,
        ResourceModelFactory $resourceFactory,
        ProductModel $productModel,
        CatalogHelper $catalogHelper,
        StoreResolver $storeResolver,
        Product $importProduct,
        Validator $validator,
        WebsiteValidator $websiteValidator,
        TierPriceValidator $tierPriceValidator,
        ConsoleOutput $output,
        ImportExportHelper $helper,
        ProductMetadata $productMetadata
    ) {
        parent::__construct(
            $context->getJsonHelper(),
            $context->getImportExportData(),
            $context->getDataSourceModel(),
            $context->getConfig(),
            $context->getResource(),
            $context->getResourceHelper(),
            $context->getStringUtils(),
            $errorAggregator,
            $dateTime,
            $resourceFactory,
            $productModel,
            $catalogHelper,
            $storeResolver,
            $importProduct,
            $validator,
            $websiteValidator,
            $tierPriceValidator
        );

        $this->productMetadata = $productMetadata;
        $this->_logger = $context->getLogger();
        $this->output = $output;
        $this->_debugMode = $helper->getDebugMode();

        foreach ($this->messageTemplates as $errorCode => $message) {
            $this->addMessageTemplate($errorCode, $message);
        }
    }

    public function validateRow(array $rowData, $rowNum)
    {
        if (!isset($this->_validatedRows[$rowNum])) {
            $this->_processedRowsCount++;
            $this->_processedEntitiesCount++;

            if (parent::validateRow($rowData, $rowNum)) {
                $sku = $rowData[static::COL_SKU];
                $website = $rowData[static::COL_TIER_PRICE_WEBSITE];
                $group = $rowData[static::COL_TIER_PRICE_CUSTOMER_GROUP];
                $qty = $rowData[static::COL_TIER_PRICE_QTY];

                if (isset($this->checkDuplicates[$sku][$website][$group][$qty])) {
                    $this->addRowError(
                        RowValidatorInterface::ERROR_DUPLICATE_UNIQUE_ATTRIBUTE,
                        $rowNum
                    );
                }
                $this->checkDuplicates[$sku][$website][$group][$qty] = true;
            }
        }

        return !$this->getErrorAggregator()->isRowInvalid($rowNum);
    }

    /**
     * @param array $prices
     * @param string $table
     * @return $this
     */
    protected function processCountExistingPrices($prices, $table)
    {
        $tableName = $this->_resourceFactory->create()->getTable($table);
        $productEntityLinkField = $this->getProductEntityLinkField();
        $existingPrices = $this->_connection->fetchAssoc(
            $this->_connection->select()->from(
                $tableName,
                ['value_id', $productEntityLinkField, 'all_groups', 'customer_group_id']
            )->where($productEntityLinkField . ' in(?)', $this->getEntity($productEntityLinkField))
        );

        $oldSkus = $this->retrieveOldSkus();
        foreach ($existingPrices as $existingPrice) {
            foreach ($oldSkus as $sku => $productId) {
                if ($existingPrice[$productEntityLinkField] == $productId && isset($prices[$sku])) {
                    $this->incrementCounterUpdated($prices[$sku], $existingPrice);
                }
            }
        }

        return $this;
    }

    /**
     * @return string
     */
    private function getProductEntityLinkField()
    {
        if (!$this->productEntityLinkField) {
            $this->productEntityLinkField = $this->getMetadataPool()
                ->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class)
                ->getLinkField();
        }

        return $this->productEntityLinkField;
    }

    /**
     * @return $this
     */
    protected function saveAndReplaceAdvancedPrices()
    {
        $behavior = $this->getBehavior();
        if (\Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE == $behavior) {
            $this->_cachedSkuToDelete = null;
        }
        $listSku = [];
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $tierPrices = [];
            foreach ($bunch as $rowNum => $rowData) {
                $rowData = $this->joinIdenticalyData($rowData);
                if (!$this->validateRow($rowData, $rowNum)) {
                    $this->addLogWriteln(
                        __('price from sku: %1 is not validated', $rowData[self::COL_SKU]),
                        $this->output,
                        'info'
                    );
                    continue;
                }
                $time = explode(" ", microtime());
                $startTime = $time[0] + $time[1];
                $sku = $rowData[self::COL_SKU];
                $rowData = $this->customChangeData($rowData);
                if (!$this->validateRow($rowData, $rowNum)) {
                    $this->addRowError(ValidatorInterface::ERROR_SKU_IS_EMPTY, $rowNum);
                    continue;
                }
                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                    continue;
                }
                $rowSku = $rowData[self::COL_SKU];
                $listSku[] = $rowSku;
                if (!empty($rowData[self::COL_TIER_PRICE_WEBSITE])) {
                    $array = [
                        'all_groups' => $rowData[self::COL_TIER_PRICE_CUSTOMER_GROUP] == self::VALUE_ALL_GROUPS,
                        'customer_group_id' => $this->getCustomerGroupId(
                            $rowData[self::COL_TIER_PRICE_CUSTOMER_GROUP]
                        ),
                        'qty' => $rowData[self::COL_TIER_PRICE_QTY],
                        'value' => $rowData[self::COL_TIER_PRICE],
                        'website_id' => $this->getWebsiteId($rowData[self::COL_TIER_PRICE_WEBSITE])
                    ];
                    if (strpos($this->productMetadata->getVersion(), '2.2') !== false) {
                        if (isset($rowData[self::COL_TIER_PRICE_TYPE])) {
                            $array['value'] = $rowData[self::COL_TIER_PRICE_TYPE] === self::TIER_PRICE_TYPE_FIXED
                                ? $rowData[self::COL_TIER_PRICE] : 0;
                            $array['percentage_value'] = $rowData[self::COL_TIER_PRICE_TYPE] === self::TIER_PRICE_TYPE_PERCENT
                                ? $rowData[self::COL_TIER_PRICE] : null;
                        }
                    }
                    $tierPrices[$rowSku][] = $array;
                }
                $time = explode(" ", microtime());
                $endTime = $time[0] + $time[1];
                $totalTime = $endTime - $startTime;
                $totalTime = round($totalTime, 5);
                $this->addLogWriteln(__('price from sku: %1 .... %2s', $sku, $totalTime), $this->output, 'info');
            }
            $this->getEntities($listSku);
            if ($behavior == \Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE) {
                if ($listSku) {
                    $this->processCountNewPrices($tierPrices);
                    if ($this->deleteProductTierPrices(array_unique($listSku), self::TABLE_TIER_PRICE)) {
                        $this->saveProductPrices($tierPrices, self::TABLE_TIER_PRICE);
                        $this->setUpdatedAt($listSku);
                    }
                }
            } elseif ($behavior == \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND) {
                $this->processCountExistingPrices($tierPrices, self::TABLE_TIER_PRICE)
                    ->processCountNewPrices($tierPrices);
                $this->saveProductPrices($tierPrices, self::TABLE_TIER_PRICE);

                if ($listSku) {
                    $this->setUpdatedAt($listSku);
                }
            }
        }

        return $this;
    }

    /**
     * @param $listSku
     */
    protected function getEntities($listSku)
    {
        $this->entityProducts = $this->_connection->fetchAll(
            $this->_connection->select()->from(
                $this->_catalogProductEntity,
                ['sku', $this->getProductEntityLinkField()]
            )->where('sku in(?)', $listSku)
        );
    }

    /**
     * @param $field
     * @return array
     */
    protected function getEntity($field)
    {
        $array = [];
        if (!empty($this->entityProducts)) {
            foreach ($this->entityProducts as $value) {
                $array[] = $value[$field];
            }
        }

        return $array;
    }

    /**
     * @return array
     */
    protected function retrieveOldSkus()
    {
        $select = $this->_connection->select()->from(
            $this->_catalogProductEntity,
            ['sku', $this->getProductEntityLinkField()]
        );
        if ($skus = $this->getEntity('sku')) {
            $select->where('sku in(?)', $this->getEntity('sku'));
        }
        $this->_oldSkus = $this->_connection->fetchPairs(
            $select
        );
        return $this->_oldSkus;
    }

    protected function _saveValidatedBunches()
    {
        $source = $this->_getSource();
        $currentDataSize = 0;
        $bunchRows = [];
        $startNewBunch = false;
        $nextRowBackup = [];
        $maxDataSize = $this->_resourceHelper->getMaxDataSize();
        $bunchSize = $this->_importExportData->getBunchSize();

        $source->rewind();
        $this->_dataSourceModel->cleanBunches();
        $file = null;
        $jobId = null;
        if (isset($this->_parameters['file'])) {
            $file = $this->_parameters['file'];
        }
        if (isset($this->_parameters['job_id'])) {
            $jobId = $this->_parameters['job_id'];
        }

        while ($source->valid() || $bunchRows) {
            if ($startNewBunch || !$source->valid()) {
                $this->_dataSourceModel->saveBunches(
                    $this->getEntityTypeCode(),
                    $this->getBehavior(),
                    $jobId,
                    $file,
                    $bunchRows
                );
                $bunchRows = $nextRowBackup;
                $currentDataSize = strlen(\Zend\Serializer\Serializer::serialize($bunchRows));
                $startNewBunch = false;
                $nextRowBackup = [];
            }
            if ($source->valid()) {
                try {
                    $rowData = $source->current();
                } catch (\InvalidArgumentException $e) {
                    $this->addRowError($e->getMessage(), $this->_processedRowsCount);
                    $this->_processedRowsCount++;
                    $source->next();
                    continue;
                }

                $this->_processedRowsCount++;
                $rowData = $this->customBunchesData($rowData);
                $rowSize = strlen($this->jsonHelper->jsonEncode($rowData));

                $isBunchSizeExceeded = $bunchSize > 0 && count($bunchRows) >= $bunchSize;

                if ($currentDataSize + $rowSize >= $maxDataSize || $isBunchSizeExceeded) {
                    $startNewBunch = true;
                    $nextRowBackup = [$source->key() => $rowData];
                } else {
                    $bunchRows[$source->key()] = $rowData;
                    $currentDataSize += $rowSize;
                }

                $source->next();
            }
        }

        return $this;
    }

    /**
     * Retrieve All Fields Source
     *
     * @return array
     */
    public function getAllFields()
    {
        return $this->validColumnNames;
    }
}
