<?php

namespace Mirasvit\Sorting\Factor;

use Mirasvit\Sorting\Api\Data\IndexInterface;
use Mirasvit\Sorting\Api\Data\RankingFactorInterface;

class StockFactor implements FactorInterface
{
    private $context;

    private $indexer;

    public function __construct(
        Context $context,
        Indexer $indexer
    ) {
        $this->context = $context;
        $this->indexer = $indexer;
    }

    public function getName()
    {
        return 'Stock Status';
    }

    public function getDescription()
    {
        return '';
    }

    public function getUiComponent()
    {
        return false;
    }

    public function reindexAll(RankingFactorInterface $rankingFactor)
    {
        $this->indexer->delete($rankingFactor);

        $resource   = $this->indexer->getResource();
        $connection = $this->indexer->getConnection();

        $select = $connection->select();
        $select->from(
            ['e' => $resource->getTableName('catalog_product_entity')],
            ['entity_id']
        )->joinInner(
            ['stock' => $resource->getTableName('cataloginventory_stock_status')],
            'stock.product_id = e.entity_id',
            ['value' => 'stock_status']
        )->group('e.entity_id');

        $stmt = $connection->query($select);

        $this->indexer->startIndexation();

        while ($row = $stmt->fetch()) {
            $value = $row['value'] ? IndexInterface::MAX : IndexInterface::MIN;

            $this->indexer->add($rankingFactor, $row['entity_id'], $value);
        }

        $this->indexer->finishIndexation();
    }
}