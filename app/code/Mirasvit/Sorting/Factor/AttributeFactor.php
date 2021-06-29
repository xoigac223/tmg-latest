<?php

namespace Mirasvit\Sorting\Factor;

use Mirasvit\Sorting\Api\Data\RankingFactorInterface;

class AttributeFactor implements FactorInterface
{
    use MappingTrait;

    const ATTRIBUTE = 'attribute';
    const MAPPING   = 'mapping';

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
        return 'Attribute';
    }

    public function getDescription()
    {
        return '';
    }

    public function getUiComponent()
    {
        return 'sorting_factor_attribute';
    }

    public function reindexAll(RankingFactorInterface $rankingFactor)
    {
        $attribute = $rankingFactor->getConfigData(self::ATTRIBUTE);
        $mapping   = $rankingFactor->getConfigData(self::MAPPING, []);

        $this->indexer->delete($rankingFactor);

        $resource   = $this->indexer->getResource();
        $connection = $this->indexer->getConnection();

        $select = $connection->select();
        $select->from(
            ['e' => $resource->getTableName('catalog_product_entity')],
            ['entity_id']
        );

        $attrModel = $this->context->eavConfig->getAttribute(4, $attribute);

        $select->joinLeft(
            ['eav' => $attrModel->getBackend()->getTable()],
            implode(' AND ', [
                'eav.attribute_id = ' . $attrModel->getId(),
                'eav.entity_id = e.entity_id',
            ]),
            ['value']
        );

        $stmt = $connection->query($select);

        $this->indexer->startIndexation();

        while ($row = $stmt->fetch()) {
            $value = $this->getValue($mapping, $row['value']);

            $this->indexer->add($rankingFactor, $row['entity_id'], $value);
        }

        $this->indexer->finishIndexation();
    }
}