<?php

namespace Mirasvit\Sorting\Factor;

use Mirasvit\Sorting\Api\Data\RankingFactorInterface;

class RatingFactor implements FactorInterface
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
        return 'Product Rating';
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
            ['rating' => $resource->getTableName('rating_option_vote_aggregated')],
            'rating.entity_pk_value = e.entity_id',
            ['value' => new \Zend_Db_Expr('AVG(rating.percent)')]
        )->group('e.entity_id');

        $stmt = $connection->query($select);

        while ($row = $stmt->fetch()) {
            $value = $row['value'];

            $this->indexer->insertRow($rankingFactor, $row['entity_id'], $value);
        }
    }

    private function getDaysDiff($date)
    {
        return ceil((time() - strtotime($date)) / 60 / 60 / 24);
    }
}