<?php

namespace Mirasvit\Sorting\Factor;

use Mirasvit\Sorting\Api\Data\IndexInterface;
use Mirasvit\Sorting\Api\Data\RankingFactorInterface;

class SpvFactor implements FactorInterface
{
    const ZERO_POINT = 'zero_point';

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
        return 'SPV';
    }

    public function getDescription()
    {
        return 'SPV = Sales Pre View = Number of Sales / Number of Views';
    }

    public function getUiComponent()
    {
        return 'sorting_factor_spv';
    }

    public function reindexAll(RankingFactorInterface $rankingFactor)
    {
        $this->indexer->delete($rankingFactor);

        $zeroPoint = $rankingFactor->getConfigData(self::ZERO_POINT, 365);

        $date = date('Y-m-d', strtotime('-' . $zeroPoint . ' day', time()));

        $resource   = $this->indexer->getResource();
        $connection = $this->indexer->getConnection();

        $select = $connection->select()->from($resource->getTableName('report_viewed_product_index'), [
            'product_id',
            'value' => new \Zend_Db_Expr('COUNT(index_id)'),
        ])
            ->where('added_at >= ?', $date)
            ->group('product_id');
        $views  = $connection->fetchPairs($select);

        $select = $connection->select()->from($resource->getTableName('sales_order_item'), [
            'product_id',
            'value' => new \Zend_Db_Expr('SUM(qty_ordered)'),
        ])
            ->where('created_at >= ?', $date)
            ->group('product_id');
        $sales  = $connection->fetchPairs($select);

        $this->indexer->startIndexation();

        foreach ($sales as $productId => $nSales) {
            $nViews = isset($views[$productId]) ? $views[$productId] : 0;

            if ($nViews > $nSales) {
                $value = $nSales / $nViews * IndexInterface::MAX;
            } else {
                $value = 0;
            }

            $this->indexer->add($rankingFactor, $productId, $value);
        }

        $this->indexer->finishIndexation();
    }
}