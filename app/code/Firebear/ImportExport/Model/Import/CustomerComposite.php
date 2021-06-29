<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExport\Model\Import;

use Magento\CustomerImportExport\Model\Import\CustomerComposite as MagentoCustomer;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use \Magento\ImportExport\Model\Import\AbstractEntity;

class CustomerComposite extends MagentoCustomer
{
    use \Firebear\ImportExport\Traits\General;

    protected $specialFields = [
        'reward_update_notification',
        'reward_warning_notification'
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

    protected $duplicateFields = [\Magento\CustomerImportExport\Model\Import\Customer::COLUMN_EMAIL];

    protected $_customerEntity;

    protected $_addressAttributes = [
        'increment_id'
    ];

    /**
     * @param Context                                                                                $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface                                     $scopeConfig
     * @param \Magento\ImportExport\Model\ImportFactory                                              $importFactory
     * @param ProcessingErrorAggregator                                                              $errorAggregator
     * @param \Magento\CustomerImportExport\Model\ResourceModel\Import\CustomerComposite\DataFactory $dataFactory
     * @param \Magento\CustomerImportExport\Model\Import\CustomerFactory                             $customerFactory
     * @param \Magento\CustomerImportExport\Model\Import\AddressFactory                              $addressFactory
     * @param ConsoleOutput                                                                          $output
     * @param \Firebear\ImportExport\Helper\Data                                                     $helper
     * @param \Firebear\ImportExport\Model\Import\CustomerFactory                                    $fireImportCustomer
     * @param AddressFactory                                                                         $fireImportAddress
     * @param \Firebear\ImportExport\Model\ResourceModel\Import\CustomerComposite\DataFactory        $importFireData
     * @param array                                                                                  $data
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\ImportExport\Model\ImportFactory $importFactory,
        ProcessingErrorAggregator $errorAggregator,
        \Magento\CustomerImportExport\Model\ResourceModel\Import\CustomerComposite\DataFactory $dataFactory,
        \Magento\CustomerImportExport\Model\Import\CustomerFactory $customerFactory,
        \Magento\CustomerImportExport\Model\Import\AddressFactory $addressFactory,
        ConsoleOutput $output,
        \Firebear\ImportExport\Helper\Data $helper,
        \Firebear\ImportExport\Model\Import\CustomerFactory $fireImportCustomer,
        \Firebear\ImportExport\Model\Import\AddressFactory $fireImportAddress,
        \Firebear\ImportExport\Model\ResourceModel\Import\CustomerComposite\DataFactory $importFireData,
        array $data = []
    ) {
        parent::__construct(
            $context->getStringUtils(),
            $scopeConfig,
            $importFactory,
            $context->getResourceHelper(),
            $context->getResource(),
            $errorAggregator,
            $dataFactory,
            $customerFactory,
            $addressFactory,
            $data
        );
        $this->output = $output;
        $this->_logger = $context->getLogger();
        $this->_debugMode = $helper->getDebugMode();
        $this->_customerEntity = $fireImportCustomer->create(['data' => $data]);

        // Exclude common fields in customer and address tables
        $customerAttributes = array_filter($this->_customerAttributes, function ($value) {
            return !in_array($value, ['firstname', 'lastname']);
        });

        // address entity stuff
        $data['data_source_model'] = $importFireData->create(
            [
                'arguments' => [
                    'entity_type' => 'address',
                    'customer_attributes' => $customerAttributes,
                ],
            ]
        );
        $this->_addressEntity = $fireImportAddress->create(['data' => $data]);
        unset($data['data_source_model']);
        $this->_dataSourceModel = $importFireData->create();
    }

    /**
     * @return array
     */
    public function getAllFields()
    {
        $options = array_merge($this->getValidColumnNames(), $this->_specialAttributes);
        $options = array_merge($options, $this->_permanentAttributes);
        $options = array_merge($options, $this->specialFields);

        return array_unique($options);
    }

    public function setLogger($logger)
    {
        $this->_logger = $logger;
        $this->_customerEntity->setLogger($logger);
    }

    /**
     * Import data rows
     *
     * @return bool
     */
    protected function _importData()
    {
        $this->_customerEntity->setDataSourceData(
            $this->_dataSourceModel->getFile(),
            $this->_dataSourceModel->getJob(),
            $this->_dataSourceModel->getOffset()
        );
        $result = $this->_customerEntity->importData();
        if ($this->getBehavior() != \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE) {
            $this->_addressEntity->setDataSourceData(
                $this->_dataSourceModel->getFile(),
                $this->_dataSourceModel->getJob(),
                $this->_dataSourceModel->getOffset()
            );
            return $result && $this->_addressEntity->setCustomerAttributes($this->_customerAttributes)->importData();
        }

        return $result;
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
        $prevData = [];
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

                if (!empty($prevData) && (!isset($rowData['email']) || empty($rowData['email']))) {
                    $rowData = array_merge($prevData, $this->deleteEmpty($rowData));
                }

                $prevData = $rowData;

                if (!$valid) {
                    $this->_processedRowsCount++;
                    $source->next();
                    continue;
                }

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
                //   $platformModel = $this->helper->getPlatformModel($this->_parameters['platforms']);

                // $rowData = $platformModel->prepareRow($rowData);
                $this->_processedRowsCount++;
                $source->next();
            }
        }
        return $this;
    }

    protected function deleteEmpty($array)
    {
        if (isset($array['sku'])) {
            unset($array['sku']);
        }
        $newElement = [];
        foreach ($array as $key => $element) {
            if (strlen($element)) {
                $newElement[$key] = $element;
            }
        }

        return $newElement;
    }
}
