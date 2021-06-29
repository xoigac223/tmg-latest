<?php

namespace Mirasvit\Sorting\Factor;

use Magento\Framework\App\ResourceConnection;
use Mirasvit\Sorting\Api\Data\IndexInterface;
use Mirasvit\Sorting\Api\Data\RankingFactorInterface;

class Indexer
{
    private $resource;

    private $connection;

    private $rowPool = [];

    public function __construct(
        ResourceConnection $resource
    ) {
        $this->resource   = $resource;
        $this->connection = $resource->getConnection();
    }

    public function getResource()
    {
        return $this->resource;
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function delete(RankingFactorInterface $rankingFactor)
    {
        $this->connection->delete(
            $this->resource->getTableName(IndexInterface::TABLE_NAME),
            [IndexInterface::FACTOR_ID . ' = ' . $rankingFactor->getId()]
        );

        return true;
    }

    public function insertRow(RankingFactorInterface $rankingFactor, $productId, $value)
    {
        $this->connection->insert(
            $this->resource->getTableName(IndexInterface::TABLE_NAME),
            [
                IndexInterface::FACTOR_ID  => $rankingFactor->getId(),
                IndexInterface::PRODUCT_ID => $productId,
                IndexInterface::VALUE      => $value,
            ]
        );

        return true;
    }

    public function startIndexation()
    {

    }

    public function add(RankingFactorInterface $rankingFactor, $productId, $value)
    {
        $this->rowPool[] = [
            IndexInterface::FACTOR_ID  => $rankingFactor->getId(),
            IndexInterface::PRODUCT_ID => $productId,
            IndexInterface::VALUE      => $value,
        ];

        if (count($this->rowPool) > 1000) {
            $this->push();
        }
    }

    public function finishIndexation()
    {
        $this->push();
    }

    private function push()
    {
        if (!$this->rowPool) {
            return;
        }

        $this->connection->insertMultiple(
            $this->resource->getTableName(IndexInterface::TABLE_NAME),
            $this->rowPool
        );

        $this->rowPool = [];
    }
}