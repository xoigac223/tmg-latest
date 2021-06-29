<?php

namespace Mirasvit\Sorting\Service;

use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Select;
use Mirasvit\Sorting\Api\Data\CriterionInterface;
use Mirasvit\Sorting\Api\Data\IndexInterface;
use Mirasvit\Sorting\Api\Data\RankingFactorInterface;
use Mirasvit\Sorting\Api\Repository\RankingFactorRepositoryInterface;

class CriteriaApplierService
{
    private $rankingFactorRepository;

    private $resource;

    private $level = 0;

    public function __construct(
        RankingFactorRepositoryInterface $rankingFactorRepository,
        ResourceConnection $resource
    ) {
        $this->rankingFactorRepository = $rankingFactorRepository;
        $this->resource                = $resource;
    }

    public function applyGlobalRankingFactors(AbstractCollection $collection)
    {
        $rankingFactors = $this->rankingFactorRepository->getCollection();
        $rankingFactors->addFieldToFilter(RankingFactorInterface::IS_ACTIVE, 1)
            ->addFieldToFilter(RankingFactorInterface::IS_GLOBAL, 1);

        $table = $this->summarize($collection->getSelect(), $rankingFactors->getItems());

        $collection = $this->join($collection, $table, 'desc', 'global');

        return $collection;
    }

    /**
     * @inheritdoc
     */
    public function applyCriterion(CriterionInterface $criterion, AbstractCollection $collection, $dir = null)
    {
        $conditions = $criterion->getConditions();
        $useAttrDir = true; // we can change direction only once for the top-level condition
        $useFactDir = true;

        foreach ($conditions as $conditionNode) {
            $factorPool = [];
            foreach ($conditionNode as $condition) {
                if ($condition[CriterionInterface::CONDITION_SORT_BY] == CriterionInterface::CONDITION_SORT_BY_ATTRIBUTE) {
                    $attribute = $condition[CriterionInterface::CONDITION_SORT_BY_ATTRIBUTE];
                    $dir       = $useAttrDir ? $dir : $condition[CriterionInterface::CONDITION_DIRECTION];

                    $collection->setOrder($attribute, $dir);

                    if ($useAttrDir) {
                        $useAttrDir = $useFactDir = false;
                    }
                } else {
                    $factorPool[] = $condition;
                    $useAttrDir   = false;
                }
            }

            $this->applyFactorConditions($collection, $factorPool, $useFactDir ? $dir : null);
        }

        return $collection;
    }

    private function applyFactorConditions(AbstractCollection $collection, $conditions, $dir)
    {
        if (!count($conditions)) {
            return $collection;
        }

        $ids = [];
        foreach ($conditions as $condition) {
            $id = $condition[CriterionInterface::CONDITION_RANKING_FACTOR];

            if (isset($condition[CriterionInterface::CONDITION_WEIGHT])) {
                $ids[$id] = $condition[CriterionInterface::CONDITION_WEIGHT];
            } else {
                $ids[$id] = 1;
            }

            if (!$dir || count($ids) > 1) { // we can change direction only for top-level factor
                $dir = $condition[CriterionInterface::CONDITION_DIRECTION];
            }
        }

        $rankingFactors = $this->rankingFactorRepository->getCollection();
        $rankingFactors->addFieldToFilter(RankingFactorInterface::IS_ACTIVE, 1)
            ->addFieldToFilter(RankingFactorInterface::ID, array_keys($ids));

        foreach ($rankingFactors as $factor) {
            $factor->setWeight($ids[$factor->getId()]);
        }

        $table = $this->summarize($collection->getSelect(), $rankingFactors->getItems());

        $collection = $this->join($collection, $table, $dir);

        return $collection;
    }

    /**
     * @param Select                   $select
     * @param RankingFactorInterface[] $rankingFactors
     *
     * @return Table|false
     */
    private function summarize(Select $select, array $rankingFactors)
    {
        if (!count($rankingFactors)) {
            return false;
        }

        $select = $this->resource->getConnection()->select()
            ->from(
                ['e' => $this->resource->getTableName('catalog_product_entity')],
                ['entity_id']
            );

        $weights = [];

        foreach ($rankingFactors as $rankingFactor) {
            $weights[$rankingFactor->getId()] = intval($rankingFactor->getWeight());

            if ($weights[$rankingFactor->getId()] === 0) {
                $weights[$rankingFactor->getId()] = 1;
            }
        }

        $alias = str_replace('.', '_', uniqid('mst_sorting_', true));

        $cases = [];
        foreach ($weights as $id => $weight) {
            $cases[] = 'WHEN ' . $id . ' THEN ' . $alias . '.value * ' . $weight;
        }
        $cases = 'CASE ' . $alias . '.factor_id ' . implode(' ', $cases) . ' ELSE 0 END';

        $select->joinInner(
            [$alias => $this->resource->getTableName(IndexInterface::TABLE_NAME)],
            $alias . '.product_id = e.entity_id',
            ['score' => 'SUM(' . $cases . ')']
        )->group('e.entity_id');

        $table = $this->createTemporaryTable();

        $this->resource->getConnection()
            ->query($this->resource->getConnection()->insertFromSelect($select, $table->getName()));

        return $table;
    }

    /**
     * @param AbstractCollection $collection
     * @param Table|false        $table
     * @param string             $dir
     * @param string|null        $suffix
     *
     * @return AbstractCollection
     */
    private function join(AbstractCollection $collection, $table, $dir, $suffix = null)
    {
        if (!$table) {
            return $collection;
        }

        if (!$suffix) {
            $suffix = $this->level;
            $this->level++;
        }

        $alias = str_replace('.', '_', uniqid('mst_sorting_', true));

        $collection->getSelect()->joinLeft(
            [$alias => $table->getName()],
            $alias . '.sorting_entity_id = e.entity_id',
            ['sorting_score_' . $suffix => $alias . '.sorting_score']
        )->order($alias . '.sorting_score ' . $dir);

        return $collection;
    }

    /**
     * Create temporary table for search select results.
     * @return Table
     * @throws \Zend_Db_Exception
     */
    private function createTemporaryTable()
    {
        $connection = $this->resource->getConnection();
        $tableName  = $this->resource->getTableName(str_replace('.', '_', uniqid('mst_sorting_', true)));
        $table      = $connection->newTable($tableName);
        $connection->dropTemporaryTable($table->getName());

        $table->addColumn(
            'sorting_entity_id',
            Table::TYPE_INTEGER,
            10,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity ID'
        );
        $table->addColumn(
            'sorting_score',
            Table::TYPE_INTEGER,
            32,
            ['nullable' => false],
            'Score'
        );

        $table->setOption('type', 'memory');
        $connection->createTemporaryTable($table);

        //        $connection->createTable($table);
        return $table;
    }
}
