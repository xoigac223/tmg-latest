<?php

namespace Mirasvit\Sorting\Factor;

use Mirasvit\Sorting\Api\Data\RankingFactorInterface;

class BestsellerFactor implements FactorInterface
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
        return 'Bestsellers';
    }

    public function getDescription()
    {
        return '';
    }

    public function getUiComponent()
    {
        return 'sorting_factor_bestseller';
    }

    public function reindexAll(RankingFactorInterface $rankingFactor)
    {
        $this->indexer->delete($rankingFactor);

        $resource   = $this->indexer->getResource();
        $connection = $this->indexer->getConnection();

        $zeroPoint = $rankingFactor->getConfigData(self::ZERO_POINT, 60);

        $date = date('Y-m-d', strtotime('-' . $zeroPoint . ' day', time()));

        $select = $connection->select();

        $select->from(
            $resource->getTableName('sales_order_item'), [
                'product_id',
                'value' => new \Zend_Db_Expr('SUM(qty_ordered)'),
            ]
        )
            ->where('created_at >= ?', $date)
            ->group('product_id');

        $minMaxSelect = $connection->select()
            ->from(
                $select,
                [
                    'max' => 'MAX(value)',
                    'min' => 'MIN(value)',
                ]
            );

        $minMax = $connection->fetchRow($minMaxSelect);

        $max = $minMax['max'];

        $stmt = $connection->query($select);

        while ($row = $stmt->fetch()) {
            $value = $row['value'];

            $value = $value / $max * 100;

            $this->indexer->insertRow($rankingFactor, $row['product_id'], $value);
        }
    }
}