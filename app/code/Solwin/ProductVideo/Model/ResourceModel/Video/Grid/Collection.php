<?php
/**
 * Solwin Infotech
 * Solwin Advanced Product Video Extension
 *
 * @category   Solwin
 * @package    Solwin_ProductVideo
 * @copyright  Copyright © 2006-2016 Solwin (https://www.solwininfotech.com)
 * @license    https://www.solwininfotech.com/magento-extension-license/ 
 */
namespace Solwin\ProductVideo\Model\ResourceModel\Video\Grid;

use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;

class Collection
extends \Solwin\ProductVideo\Model\ResourceModel\Video\Collection
implements \Magento\Framework\Api\Search\SearchResultInterface
{
    /**
     * Aggregations
     * 
     * @var \Magento\Framework\Search\AggregationInterface
     */
    protected $_aggregations;
    
    /**
     * Define resource model
     *
     * @return void
     */
      /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * constructor
     * 
     * @param EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param $mainTable
     * @param $eventPrefix
     * @param $eventObject
     * @param $resourceModel
     * @param $model
     * @param $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        $mainTable,
        $eventPrefix,
        $eventObject,
        $resourceModel,
        $model = 'Magento\Framework\View\Element\UiComponent\DataProvider\Document',
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct(
                $entityFactory, 
                $logger, 
                $fetchStrategy, 
                $eventManager, 
                $connection, 
                $resource);
        $this->_eventPrefix = $eventPrefix;
        $this->_eventObject = $eventObject;
        $this->_init($model, $resourceModel);
        $this->setMainTable($mainTable);
        $this->_storeManager = $storeManager;
    }


    /**
     * @return \Magento\Framework\Search\AggregationInterface
     */
    public function getAggregations()
    {
        return $this->_aggregations;
    }

    /**
     * @param \Magento\Framework\Search\AggregationInterface $aggregations
     * @return $this
     */
    public function setAggregations($aggregations)
    {
        $this->_aggregations = $aggregations;
    }

    /**
     * Get search criteria.
     *
     * @return \Magento\Framework\Api\SearchCriteriaInterface|null
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * Set search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setSearchCriteria(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null
    ) {
        return $this;
    }

    /**
     * Get total count.
     *
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * Set total count.
     *
     * @param int $totalCount
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * Set items list.
     *
     * @param \Magento\Framework\Api\ExtensibleDataInterface[] $items
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setItems(array $items = null)
    {
        return $this;
    }
    
    /**
     * Perform operations after collection load
     *
     * @return $this
     */
    protected function _afterLoad()
    {   
        $items = $this->getColumnValues('video_id');
        if (count($items)) {
            $connection = $this->getConnection();
            $select = $connection->select()
                    ->from([
                        'cps' => $this->getTable(
                                'solwin_productvideo_video_store'
                                )
                            ])
                ->where('cps.video_id IN (?)', $items);
            $result = $connection->fetchPairs($select);

            if ($result) {
                foreach ($this as $item) {
                    $videoId = $item->getData('video_id');
                    if (!isset($result[$videoId])) {
                        continue;
                    }
                    if ($result[$videoId] == 0) {
                        $stores = $this->_storeManager->getStores(false, true);
                        $storeId = current($stores)->getId();
                        $storeCode = key($stores);
                    } else {
                        $storeId = $result[$item->getData('video_id')];
                        $storeCode = $this->_storeManager
                                ->getStore($storeId)
                                ->getCode();
                    }
                    $item->setData('_first_store_id', $storeId);
                    $item->setData('store_code', $storeCode);
                    $item->setData('store_id', [$result[$videoId]]);
                }
            }

        }

        $this->_previewFlag = false;
        return parent::_afterLoad();
    }
}