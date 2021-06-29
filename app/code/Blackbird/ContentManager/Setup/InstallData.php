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

namespace Blackbird\ContentManager\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Indexer\IndexerInterfaceFactory;
use Blackbird\ContentManager\Model\Indexer\Fulltext;

class InstallData implements InstallDataInterface
{
    /**
     * Content setup factory
     *
     * @var ContentSetupFactory
     */
    protected $_contentSetupFactory;
    
    /**
     * @var IndexerInterfaceFactory
     */
    protected $_indexerFactory;

    /**
     * @param \Magento\Framework\Indexer\IndexerInterfaceFactory $indexerFactory
     * @param \Blackbird\ContentManager\Setup\ContentSetupFactory $contentSetupFactory
     */
    public function __construct(
        IndexerInterfaceFactory $indexerFactory,
        ContentSetupFactory $contentSetupFactory
    ) {
        $this->_indexerFactory = $indexerFactory;
        $this->_contentSetupFactory = $contentSetupFactory;
    }
    
    /**
     * Installs entities for the module
     * 
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var ContentSetup $contentSetup */
        $contentSetup = $this->_contentSetupFactory->create(['setup' => $setup]);
        
        // Install defaults entities
        $contentSetup->installEntities();
    }
    
    /**
     * @param string $indexerId
     * @return \Magento\Framework\Indexer\IndexerInterface
     */
    protected function getIndexer($indexerId)
    {
        return $this->_indexerFactory->create()->load($indexerId);
    }
}
