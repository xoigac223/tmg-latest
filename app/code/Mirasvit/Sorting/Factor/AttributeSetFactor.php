<?php

namespace Mirasvit\Sorting\Factor;

use Mirasvit\Sorting\Api\Data\IndexInterface;
use Mirasvit\Sorting\Api\Data\RankingFactorInterface;

class AttributeSetFactor implements FactorInterface
{
    use MappingTrait;

    const MAPPING = 'mapping';

    private $indexer;

    public function __construct(
        Indexer $indexer
    ) {
        $this->indexer = $indexer;
    }

    public function getName()
    {
        return 'Attribute Set';
    }

    public function getDescription()
    {
        return '';
    }

    public function getUiComponent()
    {
        return 'sorting_factor_attributeSet';
    }

    public function reindexAll(RankingFactorInterface $rankingFactor)
    {
        $mapping = $rankingFactor->getConfigData(self::MAPPING, []);

        $this->indexer->delete($rankingFactor);

        $resource   = $this->indexer->getResource();
        $connection = $this->indexer->getConnection();

        $select = $connection->select();
        $select->from(
            ['e' => $resource->getTableName('catalog_product_entity')],
            ['entity_id', 'attribute_set_id']
        );

        $stmt = $connection->query($select);

        $this->indexer->startIndexation();

        while ($row = $stmt->fetch()) {
            $value = $this->getValue($mapping, $row['attribute_set_id']);

            $this->indexer->add($rankingFactor, $row['entity_id'], $value);
        }

        $this->indexer->finishIndexation();
    }
}