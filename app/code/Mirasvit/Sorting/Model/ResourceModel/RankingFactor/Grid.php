<?php

namespace Mirasvit\Sorting\Model\ResourceModel\RankingFactor;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Mirasvit\Sorting\Api\Data\RankingFactorInterface;
use Psr\Log\LoggerInterface as Logger;

class Grid extends SearchResult
{
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable = RankingFactorInterface::TABLE_NAME,
        $resourceModel = \Mirasvit\Sorting\Model\ResourceModel\RankingFactor::class
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }
}
