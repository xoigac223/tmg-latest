<?php

namespace Mirasvit\Sorting\Factor;

use Mirasvit\Sorting\Api\Data\IndexInterface;
use Mirasvit\Sorting\Api\Data\RankingFactorInterface;

class DateFactor implements FactorInterface
{
    const DATE_FIELD = 'date_field';
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
        return 'Date';
    }

    public function getDescription()
    {
        return '';
    }

    public function getUiComponent()
    {
        return 'sorting_factor_date';
    }

    public function reindexAll(RankingFactorInterface $rankingFactor)
    {
        $dateField = $rankingFactor->getConfigData(self::DATE_FIELD, 'created_at');
        $zeroPoint = $rankingFactor->getConfigData(self::ZERO_POINT, 60);

        $this->indexer->delete($rankingFactor);

        $resource   = $this->indexer->getResource();
        $connection = $this->indexer->getConnection();

        $select = $connection->select();
        $select->from(
            ['e' => $resource->getTableName('catalog_product_entity')],
            ['entity_id', $dateField]
        );

        $stmt = $connection->query($select);

        $this->indexer->startIndexation();

        while ($row = $stmt->fetch()) {
            $createdAt = $row[$dateField];
            $days      = $this->getDaysDiff($createdAt);

            $value = 0;
            if ($zeroPoint > $days) {
                $value = ($zeroPoint - $days) / $zeroPoint * IndexInterface::MAX;
            }

            $this->indexer->add($rankingFactor, $row['entity_id'], $value);
        }

        $this->indexer->finishIndexation();
    }

    private function getDaysDiff($date)
    {
        return ceil((time() - strtotime($date)) / 60 / 60 / 24);
    }
}