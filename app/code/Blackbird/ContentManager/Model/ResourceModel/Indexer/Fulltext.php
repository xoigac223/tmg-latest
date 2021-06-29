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
namespace Blackbird\ContentManager\Model\ResourceModel\Indexer;

/**
 * ContentManager Fulltext Index resource model
 */
class Fulltext extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        $connectionName = null
    ) {
        $this->_eventManager = $eventManager;
        parent::__construct($context, $connectionName);
    }

    /**
     * Init resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('blackbird_contenttype_fulltext', 'entity_id');
    }

    /**
     * Reset search results
     *
     * @return $this
     */
    public function resetSearchResults()
    {
        $connection = $this->getConnection();
        $connection->update($this->getTable('search_query'), ['is_processed' => 0], ['is_processed != 0']);
        $this->_eventManager->dispatch('contentmanager_reset_search_result');
        return $this;
    }
}
