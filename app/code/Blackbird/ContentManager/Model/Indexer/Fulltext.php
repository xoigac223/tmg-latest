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
namespace Blackbird\ContentManager\Model\Indexer;

use Blackbird\ContentManager\Model\Indexer\Fulltext\Action\FullFactory;
use Blackbird\ContentManager\Model\ResourceModel\Indexer\Fulltext as FulltextResource;
use Magento\Framework\Search\Request\Config as SearchRequestConfig;
use Magento\Framework\Search\Request\DimensionFactory;
use Magento\Store\Model\StoreManagerInterface;

class Fulltext implements \Magento\Framework\Indexer\ActionInterface, \Magento\Framework\Mview\ActionInterface
{
    /**
     * Indexer ID in configuration
     */
    const INDEXER_ID = 'blackbird_contenttype_fulltext';
    
    /**
     * @var Full
     */
    protected $_fullAction;
    
    /**
     * @var IndexerHandlerFactory
     */
    protected $_indexerHandlerFactory;
    
    /**
     * @var StoreManagerInterface 
     */
    protected $_storeManager;
    
    /**
     * @var DimensionFactory
     */
    protected $_dimensionFactory;
    
    /**
     * @var FulltextResource
     */
    protected $_fulltextResource;
    
    /**
     * @var SearchRequestConfig
     */
    protected $_searchRequestConfig;
    
    /**
     * @var array index structure
     */
    protected $data;
    
    /**
     * @param FullFactory $fullActionFactory
     * @param IndexerHandlerFactory $indexerHandlerFactory
     * @param StoreManagerInterface $storeManager
     * @param DimensionFactory $dimensionFactory
     * @param FulltextResource $fulltextResource
     * @param SearchRequestConfig $searchRequestConfig
     * @param array $data
     */
    public function __construct(
        FullFactory $fullActionFactory,
        IndexerHandlerFactory $indexerHandlerFactory,
        StoreManagerInterface $storeManager,
        DimensionFactory $dimensionFactory,
        FulltextResource $fulltextResource,
        SearchRequestConfig $searchRequestConfig,
        array $data
    ) {
        $this->_fullAction = $fullActionFactory->create(['data' => $data]);
        $this->_indexerHandlerFactory = $indexerHandlerFactory;
        $this->_storeManager = $storeManager;
        $this->_dimensionFactory = $dimensionFactory;
        $this->_fulltextResource = $fulltextResource;
        $this->_searchRequestConfig = $searchRequestConfig;
        $this->data = $data;
    }

    /**
     * Execute materialization on ids entities
     *
     * @param int[] $ids
     * @return void
     */
    public function execute($ids = null)
    {
        $storeIds = array_keys($this->_storeManager->getStores());
        $saveHandler = $this->createIndexerHandler();
        
        foreach ($storeIds as $storeId) {
            $dimension = $this->_dimensionFactory->create(['name' => 'scope', 'value' => $storeId]);
            $saveHandler->eraseIndex([$dimension], $ids);
            $saveHandler->saveIndex([$dimension], $this->_fullAction->rebuildStoreIndex($storeId, $ids));
        }
    }

    /**
     * Execute full indexation
     *
     * @return void
     */
    public function executeFull()
    {
        $this->execute();
        $this->_fulltextResource->resetSearchResults();
        $this->_searchRequestConfig->reset();
    }

    /**
     * Execute partial indexation by ID list
     *
     * @param int[] $ids
     * @return void
     */
    public function executeList(array $ids)
    {
        $this->execute($ids);
    }

    /**
     * Execute partial indexation by ID
     *
     * @param int $id
     * @return void
     */
    public function executeRow($id)
    {
        $this->execute([$id]);
    }
    
    /**
     * @return IndexerHandler
     */
    protected function createIndexerHandler()
    {
        return $this->_indexerHandlerFactory->create(['data' => $this->data]);
    }
}
