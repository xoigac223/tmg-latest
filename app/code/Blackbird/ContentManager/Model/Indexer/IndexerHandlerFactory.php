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

use Magento\Framework\Indexer\SaveHandler\IndexerInterface;
use Magento\Framework\ObjectManagerInterface;

class IndexerHandlerFactory
{
    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    protected $_objectManager = null;

    /**
     * Factory Constructor
     * 
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create indexer handler
     *
     * @param array $data
     * @return IndexerInterface
     */
    public function create(array $data = [])
    {
        $indexer = $this->_objectManager->create(IndexerHandler::class, $data);

        if (!$indexer instanceof IndexerInterface) {
            throw new \InvalidArgumentException($indexer . ' doesn\'t implement \Magento\Framework\IndexerInterface');
        }

        if ($indexer && !$indexer->isAvailable()) {
            throw new \LogicException(
                'Indexer handler is not available: ' . $indexer
            );
        }
        return $indexer;
    }
}
