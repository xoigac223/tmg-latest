<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExport\Model\Import;

use Magento\CustomerImportExport\Model\Import\Customer as MagentoCustomer;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use \Magento\ImportExport\Model\Import\AbstractEntity;
use Firebear\ImportExport\Model\Import;

class Customer extends MagentoCustomer
{
    use \Firebear\ImportExport\Traits\General;

    /**
     * @var LoggerInterface
     */
    private $_logger;

    /**
     * @var ConsoleOutput
     */
    protected $output;

    protected $_debugMode;

    protected $duplicateFields = [\Magento\CustomerImportExport\Model\Import\Customer::COLUMN_EMAIL];

    /**
     * @param Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\ImportExport\Model\ImportFactory $importFactory
     * @param ProcessingErrorAggregator $errorAggregator
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\ImportExport\Model\Export\Factory $collectionFactory
     * @param \Magento\CustomerImportExport\Model\ResourceModel\Import\Customer\StorageFactory $storageFactory
     * @param \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attrCollectionFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param ConsoleOutput $output
     * @param \Firebear\ImportExport\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\ImportExport\Model\ImportFactory $importFactory,
        ProcessingErrorAggregator $errorAggregator,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\ImportExport\Model\Export\Factory $collectionFactory,
        \Magento\CustomerImportExport\Model\ResourceModel\Import\Customer\StorageFactory $storageFactory,
        \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attrCollectionFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        ConsoleOutput $output,
        \Firebear\ImportExport\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct(
            $context->getStringUtils(),
            $scopeConfig,
            $importFactory,
            $context->getResourceHelper(),
            $context->getResource(),
            $errorAggregator,
            $storeManager,
            $collectionFactory,
            $context->getConfig(),
            $storageFactory,
            $attrCollectionFactory,
            $customerFactory,
            $data
        );
        $this->output = $output;
        $this->_logger = $context->getLogger();
        $this->_debugMode = $helper->getDebugMode();
        $this->_dataSourceModel = $context->getDataSourceModel();
    }

    /**
     * @return array
     */
    public function getAllFields()
    {
        $options = array_merge($this->getValidColumnNames(), $this->_specialAttributes);
        $options = array_merge($options, $this->_permanentAttributes);

        return array_unique($options);
    }

    protected function _importData()
    {
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $entitiesCreate = [];
            $entitiesUpdate = [];
            $entitiesDelete = [];
            $attributesToSave = [];

            foreach ($bunch as $rowNumber => $rowData) {
                $time = explode(" ", microtime());
                $startTime = $time[0] + $time[1];
                $email = $rowData['email'];
                $rowData = $this->joinIdenticalyData($rowData);
                $website = $rowData[self::COLUMN_WEBSITE];
                if (isset($this->_newCustomers[strtolower($rowData[self::COLUMN_EMAIL])][$website])) {
                    continue;
                }
                $rowData = $this->customChangeData($rowData);
                if (!$this->validateRow($rowData, $rowNumber)) {
                    $this->addLogWriteln(__('customer with email: %1 is not valided', $email), $this->output, 'info');
                    continue;
                }
                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNumber);
                    continue;
                }

                if (\Magento\ImportExport\Model\Import::BEHAVIOR_DELETE == $this->getBehavior($rowData)) {
                    $entitiesDelete[] = $this->_getCustomerId(
                        $rowData[self::COLUMN_EMAIL],
                        $rowData[self::COLUMN_WEBSITE]
                    );
                } elseif ($this->getBehavior($rowData) == \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE) {
                    $processedData = $this->_prepareDataForUpdate($rowData);
                    $entitiesCreate = array_merge($entitiesCreate, $processedData[self::ENTITIES_TO_CREATE_KEY]);
                    $entitiesUpdate = array_merge($entitiesUpdate, $processedData[self::ENTITIES_TO_UPDATE_KEY]);
                    foreach ($processedData[self::ATTRIBUTES_TO_SAVE_KEY] as $tableName => $customerAttributes) {
                        if (!isset($attributesToSave[$tableName])) {
                            $attributesToSave[$tableName] = [];
                        }
                        $attributesToSave[$tableName] =
                            array_diff_key(
                                $attributesToSave[$tableName],
                                $customerAttributes
                            ) + $customerAttributes;
                    }
                }
                $time = explode(" ", microtime());
                $endTime = $time[0] + $time[1];
                $totalTime = $endTime - $startTime;
                $totalTime = round($totalTime, 5);

                $this->addLogWriteln(
                    __('customer with email: %1 .... %2s', $email, $totalTime),
                    $this->output,
                    'info'
                );
            }
            $this->updateItemsCounterStats($entitiesCreate, $entitiesUpdate, $entitiesDelete);
            /**
             * Save prepared data
             */
            if ($entitiesCreate || $entitiesUpdate) {
                $this->_saveCustomerEntities($entitiesCreate, $entitiesUpdate);
            }
            if ($attributesToSave) {
                $this->_saveCustomerAttributes($attributesToSave);
            }
            if ($entitiesDelete) {
                $this->_deleteCustomerEntities($entitiesDelete);
            }
        }

        return true;
    }

    public function _saveCustomerEntities(array $entitiesToCreate, array $entitiesToUpdate)
    {
        return parent::_saveCustomerEntities($entitiesToCreate, $entitiesToUpdate); // TODO: Change the autogenerated stub
    }

    public function _saveCustomerAttributes(array $attributesData)
    {
        return parent::_saveCustomerAttributes($attributesData); // TODO: Change the autogenerated stub
    }

    public function _deleteCustomerEntities(array $entitiesToDelete)
    {
        return parent::_deleteCustomerEntities($entitiesToDelete); // TODO: Change the autogenerated stub
    }

    public function _prepareDataForUpdate(array $rowData)
    {
        return parent::_prepareDataForUpdate($rowData); // TODO: Change the autogenerated stub
    }

    protected function _saveValidatedBunches()
    {
        $source = $this->getSource();
        $bunchRows = [];
        $startNewBunch = false;

        $source->rewind();
        $this->_dataSourceModel->cleanBunches();
        $masterAttributeCode = $this->getMasterAttributeCode();
        $file = null;
        $jobId = null;
        if (isset($this->_parameters['file'])) {
            $file = $this->_parameters['file'];
        }
        if (isset($this->_parameters['job_id'])) {
            $jobId = $this->_parameters['job_id'];
        }
        while ($source->valid() || count($bunchRows) || isset($entityGroup)) {
            if ($startNewBunch || !$source->valid()) {
                /* If the end approached add last validated entity group to the bunch */
                if (!$source->valid() && isset($entityGroup)) {
                    foreach ($entityGroup as $key => $value) {
                        $bunchRows[$key] = $value;
                    }
                    unset($entityGroup);
                }
                $this->_dataSourceModel->saveBunches(
                    $this->getEntityTypeCode(),
                    $this->getBehavior(),
                    $jobId,
                    $file,
                    $bunchRows
                );

                $bunchRows = [];
                $startNewBunch = false;
            }
            if ($source->valid()) {
                $valid = true;
                try {
                    $rowData = $source->current();
                    foreach ($rowData as $attrName => $element) {
                        if (!mb_check_encoding($element, 'UTF-8')) {
                            $valid = false;
                            $this->addRowError(
                                AbstractEntity::ERROR_CODE_ILLEGAL_CHARACTERS,
                                $this->_processedRowsCount,
                                $attrName
                            );
                        }
                    }
                } catch (\InvalidArgumentException $e) {
                    $valid = false;
                    $this->addRowError($e->getMessage(), $this->_processedRowsCount);
                }
                if (!$valid) {
                    $this->_processedRowsCount++;
                    $source->next();
                    continue;
                }
                $rowData = $this->customBunchesData($rowData);
                if (isset($rowData[$masterAttributeCode]) && trim($rowData[$masterAttributeCode])) {
                    /* Add entity group that passed validation to bunch */
                    if (isset($entityGroup)) {
                        foreach ($entityGroup as $key => $value) {
                            $bunchRows[$key] = $value;
                        }
                        $productDataSize = strlen(\Zend\Serializer\Serializer::serialize($bunchRows));

                        /* Check if the new bunch should be started */
                        $isBunchSizeExceeded = ($this->_bunchSize > 0 && count($bunchRows) >= $this->_bunchSize);
                        $startNewBunch = $productDataSize >= $this->_maxDataSize || $isBunchSizeExceeded;
                    }

                    /* And start a new one */
                    $entityGroup = [];
                }

                if (isset($entityGroup) && $this->validateRow($rowData, $source->key())) {
                    /* Add row to entity group */
                    $entityGroup[$source->key()] = $this->_prepareRowForDb($rowData);
                } elseif (isset($entityGroup)) {
                    /* In case validation of one line of the group fails kill the entire group */
                    unset($entityGroup);
                }

                $this->_processedRowsCount++;
                $source->next();
            }
        }
        return $this;
    }

    protected function _validateRowForUpdate(array $rowData, $rowNum)
    {
        if ($this->_checkUniqueKey($rowData, $rowNum)) {
            $email = strtolower($rowData[self::COLUMN_EMAIL]);
            $website = $rowData[self::COLUMN_WEBSITE];
            $this->_newCustomers[$email][$website] = false;

            if (!empty($rowData[self::COLUMN_STORE]) && !isset($this->_storeCodeToId[$rowData[self::COLUMN_STORE]])) {
                $this->addRowError(self::ERROR_INVALID_STORE, $rowNum);
            }
            if (isset($rowData['password']) && strlen($rowData['password'])
                && $this->string->strlen($rowData['password']) < self::MIN_PASSWORD_LENGTH
            ) {
                $this->addRowError(self::ERROR_PASSWORD_LENGTH, $rowNum);
            }
            foreach ($this->_attributes as $attributeCode => $attributeData) {
                if (in_array($attributeCode, $this->_ignoredAttributes)) {
                    continue;
                }
                if (isset($rowData[$attributeCode]) && strlen($rowData[$attributeCode])) {
                    $this->isAttributeValid(
                        $attributeCode,
                        $attributeData,
                        $rowData,
                        $rowNum,
                        isset($this->_parameters[Import::FIELD_FIELD_MULTIPLE_VALUE_SEPARATOR])
                            ? $this->_parameters[Import::FIELD_FIELD_MULTIPLE_VALUE_SEPARATOR]
                            : Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR
                    );
                } elseif ($attributeData['is_required'] && !$this->_getCustomerId($email, $website)) {
                    $this->addRowError(self::ERROR_VALUE_IS_REQUIRED, $rowNum, $attributeCode);
                }
            }
        }
    }
}
