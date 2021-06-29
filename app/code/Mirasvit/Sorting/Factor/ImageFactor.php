<?php

namespace Mirasvit\Sorting\Factor;

use Mirasvit\Sorting\Api\Data\IndexInterface;
use Mirasvit\Sorting\Api\Data\RankingFactorInterface;

class ImageFactor implements FactorInterface
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
        return 'Image';
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
        )->group('entity_id');

        $attribute = $this->context->eavConfig->getAttribute(4, 'image');

        $conditions = [
            'eav.attribute_id = ' . $attribute->getId(),
            'eav.entity_id = e.entity_id',
        ];

        $select->joinLeft(
            ['eav' => $attribute->getBackend()->getTable()],
            implode(' AND ', $conditions),
            ['value']
        );

        $stmt = $connection->query($select);

        $this->indexer->startIndexation();

        while ($row = $stmt->fetch()) {
            $image = $row['value'];
            $value = IndexInterface::MAX;

            if (!$image || $image == 'no_selection') {
                $value = IndexInterface::MIN;
            }

            $this->indexer->add($rankingFactor, $row['entity_id'], $value);
        }

        $this->indexer->finishIndexation();
    }
}