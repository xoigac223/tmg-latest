<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExport\Model;

use Firebear\ImportExport\Api\Data\ImportInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * ImportExport job model
 *
 */
class Job extends AbstractModel implements ImportInterface
{
    const CACHE_TAG = 'import_job';

    /**#@-*/
    /**
     * @var string
     */
    protected $_cacheTag = 'importexport_job';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'importexport_job';

    /**
     * Behavior Form Fields
     *
     * @var array
     */
    protected $behaviorFields = [
        'validation_strategy',
        'use_api',
//        'type_file',
        'allowed_error_count',
        '_import_field_separator',
        '_import_multiple_value_separator',
        'category_levels_separator',
        'categories_separator',
        'image_import_source'
    ];

    /**
     * @var ResourceModel\Job\Mapping\CollectionFactory
     */
    protected $collectionMapsFactory;

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Firebear\ImportExport\Model\ResourceModel\Job');
    }

    /**
     * Job constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param ResourceModel\Job\Mapping\CollectionFactory $collectionMapsFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Firebear\ImportExport\Model\ResourceModel\Job\Mapping\CollectionFactory $collectionMapsFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        if (isset($data['behaviorFields'])) {
            $this->setBehaviorFields($data['behaviorFields']);
        }
        $this->collectionMapsFactory = $collectionMapsFactory;
    }

    public function setBehaviorFields($data)
    {
        if (is_array($data)) {
            $this->behaviorFields = array_merge($this->behaviorFields, $data);
        } elseif (is_string($data)) {
            $this->behaviorFields[] = $data;
        }
    }

    /**
     * Retrieve block id
     *
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * Retrieve block title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * Is active
     *
     * @return int
     */
    public function getIsActive()
    {
        return $this->getData(self::IS_ACTIVE);
    }

    /**
     * @return string|null
     */
    public function getCron()
    {
        return $this->getData(self::CRON);
    }

    /**
     * @return string
     */
    public function getFrequency()
    {
        return $this->getData(self::FREQUENCY);
    }

    /**
     * @return string
     */
    public function getEntity()
    {
        return $this->getData(self::ENTITY);
    }

    /**
     * @return string
     */
    public function getBehaviorData()
    {
        return $this->getData(self::BEHAVIOR_DATA);
    }

    /**
     * @return string
     */
    public function getImportSource()
    {
        return $this->getData(self::IMPORT_SOURCE);
    }

    /**
     * @return string
     */
    public function getSourceData()
    {
        return $this->getData(self::SOURCE_DATA);
    }

    /**
     * @return string|null
     */
    public function getFileUpdatedAt()
    {
        return $this->getData(self::FILE_UPDATED_AT);
    }

    /**
     * Prepare block's statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }

    /**
     * Get available behavior form fields
     *
     * @return array
     */
    public function getBehaviorFormFields()
    {
        return $this->behaviorFields;
    }

    /**
     * Get frequency modes
     *
     * @return array
     */
    public function getExtendedFrequencyModes()
    {
        return [
            self::FREQUENCY_NONE => [
                'title' => __('None'),
                'label' => __('None (manual run only)'),
                'value' => self::FREQUENCY_NONE,
                'expr' => '',
            ],
            self::FREQUENCY_MINUTE => [
                'title' => __('Minute'),
                'label' => __('Every minute'),
                'value' => self::FREQUENCY_MINUTE,
                'expr' => '*/1 * * * *',
            ],
            self::FREQUENCY_HOUR => [
                'title' => __('Hour'),
                'label' => __('Every hour'),
                'value' => self::FREQUENCY_HOUR,
                'expr' => '0 * * * *',
            ],
            self::FREQUENCY_DAY => [
                'title' => __('Day'),
                'label' => __('Every day at 3:00am'),
                'value' => self::FREQUENCY_DAY,
                'expr' => '0 3 * * *',
            ],
            self::FREQUENCY_WEEK => [
                'title' => __('Week'),
                'label' => __('Every Monday at 3:00am'),
                'value' => self::FREQUENCY_WEEK,
                'expr' => '0 3 * * 1',
            ],
            self::FREQUENCY_MONTH => [
                'title' => __('Month'),
                'label' => __('Every 1st day of month at 3:00am'),
                'value' => self::FREQUENCY_MONTH,
                'expr' => '0 3 1 * *',
            ],
            self::FREQUENCY_CUSTOM => [
                'title' => __('Custom'),
                'label' => __('Custom'),
                'value' => self::FREQUENCY_CUSTOM,
                'expr' => '* * * * *',
                'custom' => true
            ]
        ];
    }

    /**
     * @return mixed
     */
    public function getMap()
    {
        if ($this->getData(self::MAP) == null) {
            $this->setData(
                self::MAP,
                $this->getMapsCollection()->getItems()
            );
        }
        return $this->getData(self::MAP);
    }

    public function getXslt()
    {
        return $this->getData(self::XSLT);
    }

    /**
     * @return mixed
     */
    public function getMapsCollection()
    {
        $collection = $this->collectionMapsFactory->create()->addFieldToFilter('job_id', $this->getId());

        return $collection;
    }

    /**
     * Set ID
     *
     * @param int $entityId
     * @return Job
     */
    public function setId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Job
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * Set is active
     *
     * @param bool|int $isActive
     * @return Job
     */
    public function setIsActive($isActive)
    {
        return $this->setData(self::IS_ACTIVE, $isActive);
    }

    /**
     * Set cron
     *
     * @param string|null $cron
     * @return Job
     */
    public function setCron($cron)
    {
        return $this->setData(self::CRON, $cron);
    }

    /**
     * Set frequency
     *
     * @param string $frequency
     * @return Job
     */
    public function setFrequency($frequency)
    {
        return $this->setData(self::FREQUENCY, $frequency);
    }

    /**
     * Set entity
     *
     * @param string $entity
     * @return Job
     */
    public function setEntity($entity)
    {
        return $this->setData(self::ENTITY, $entity);
    }

    /**
     * Set behavior data
     *
     * @param string $behaviorData
     * @return Job
     */
    public function setBehaviorData($behaviorData)
    {
        return $this->setData(self::BEHAVIOR_DATA, $behaviorData);
    }

    /**
     * Set import source
     *
     * @param string $importSource
     * @return Job
     */
    public function setImportSource($importSource)
    {
        return $this->setData(self::IMPORT_SOURCE, $importSource);
    }

    /**
     * Set source data
     *
     * @param string $sourceData
     * @return Job
     */
    public function setSourceData($sourceData)
    {
        return $this->setData(self::SOURCE_DATA, $sourceData);
    }

    /**
     * Set file updated at
     *
     * @param string $updatedAt
     * @return Job
     */
    public function setFileUpdatedAt($updatedAt)
    {
        return $this->setData(self::FILE_UPDATED_AT, $updatedAt);
    }

    /**
     * @param $maps
     *
     * @return mixed
     */
    public function setMaps($maps)
    {
        return $this->setData(self::MAP, $maps);
    }

    /**
     * @param Job\Mapping $map
     *
     * @return $this
     */
    public function addMap(\Firebear\ImportExport\Model\Job\Mapping $map)
    {
        if (!$map->getId()) {
            $this->setMaps(array_merge($this->getMap(), [$map]));
        }

        return $this;
    }

    /**
     * @param null $mapId
     *
     * @return $this
     */
    public function deleteMap($mapId = null)
    {
        $maps = $this->collectionMapsFactory->create()->addFieldToFilter('job_id', $this->getId());

        if ($mapId) {
            $maps = $maps->addFieldToFilter('entity_id', $mapId);
        }

        foreach ($maps as $item) {
            $item->delete();
        }
        $this->setData(
            self::MAP,
            $this->getMapsCollection()->getItems()
        );

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getMapping()
    {
        return $this->getData(self::MAPPING);
    }

    /**
     * {@inheritdoc}
     */
    public function setMapping($mapping)
    {
        return $this->setData(self::MAPPING, $mapping);
    }

    /**
     * {@inheritdoc}
     */
    public function getPriceRules()
    {
        return $this->getData(self::PRICE_RULES);
    }

    /**
     * {@inheritdoc}
     */
    public function setPriceRules($priceRules)
    {
        return $this->setData(self::PRICE_RULES, $priceRules);
    }

    /**
     * @param $xslt
     * @return $this
     */
    public function setXslt($xslt)
    {
        return $this->setData(self::XSLT, $xslt);
    }
}
