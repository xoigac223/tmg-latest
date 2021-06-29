<?php

namespace Mirasvit\Sorting\Ui\Preview;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Mirasvit\Sorting\Api\Data\CriterionInterface;
use Mirasvit\Sorting\Api\Data\IndexInterface;
use Mirasvit\Sorting\Api\Data\RankingFactorInterface;
use Mirasvit\Sorting\Api\Repository\CriterionRepositoryInterface;
use Mirasvit\Sorting\Api\Repository\RankingFactorRepositoryInterface;
use Mirasvit\Sorting\Service\CriteriaApplierService;

class DataProvider extends ProductDataProvider
{
    private $repository;

    private $productCollectionFactory;

    private $criteriaApplierService;

    private $context;

    private $resource;

    private $rankingFactorRepository;

    private $criterionRepository;

    public function __construct(
        CriterionRepositoryInterface $repository,
        ProductCollectionFactory $productCollectionFactory,
        CriteriaApplierService $criteriaApplierService,
        ResourceConnection $resource,
        RankingFactorRepositoryInterface $rankingFactorRepository,
        CriterionRepositoryInterface $criterionRepository,
        ContextInterface $context,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {

        $this->repository               = $repository;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->criteriaApplierService   = $criteriaApplierService;
        $this->context                  = $context;
        $this->resource                 = $resource;
        $this->rankingFactorRepository  = $rankingFactorRepository;
        $this->criterionRepository      = $criterionRepository;

        parent::__construct($name, $primaryFieldName, $requestFieldName, $productCollectionFactory, [], [], $meta, $data);
    }

    public function getData()
    {
        $data = false;
        if ($this->context->getRequestParam('criterion')) {
            $data = $this->applyCriterion();
        }

        if ($this->context->getRequestParam('rankingFactor')) {
            $data = $this->applyRankingFactor();
        }

        return $data ? $data : parent::getData();
    }

    private function applyCriterion()
    {
        $arCriterion = $this->context->getRequestParam('criterion');

        if (!isset($arCriterion[CriterionInterface::CONDITIONS])) {
            return parent::getData();
        }

        $conditions = $arCriterion[CriterionInterface::CONDITIONS];

        if (!is_array($conditions)) {
            return parent::getData();
        }

        $ids = [];

        $rankingFactors = $this->rankingFactorRepository->getCollection();
        $rankingFactors->addFieldToFilter(RankingFactorInterface::IS_ACTIVE, 1)
            ->addFieldToFilter(RankingFactorInterface::IS_GLOBAL, 1);
        foreach ($rankingFactors as $factor) {
            $ids[] = $factor->getId();
        }

        foreach ($conditions as $node) {
            foreach ($node as $condition) {
                if ($condition[CriterionInterface::CONDITION_SORT_BY] == CriterionInterface::CONDITION_SORT_BY_RANKING_FACTOR) {
                    $ids[] = $condition[CriterionInterface::CONDITION_RANKING_FACTOR];
                }
            }
        }

        $criterion = $this->criterionRepository->create();
        $criterion->setConditions($conditions);

        $this->criteriaApplierService->applyGlobalRankingFactors($this->getCollection());
        $this->criteriaApplierService->applyCriterion($criterion, $this->getCollection());

        # prevent "random" sorting, if scores are same
        $this->getCollection()->setOrder('entity_id');

        //        echo $this->getCollection()->getSelect();
        //        die();

        $data = parent::getData();
        $data = $this->addRankingFactorsToData($data, $ids);

        return $data;
    }

    private function applyRankingFactor()
    {
        $arRankingFactor = $this->context->getRequestParam('rankingFactor');

        if (!isset($arRankingFactor[RankingFactorInterface::ID])) {
            return parent::getData();
        }

        $id = $arRankingFactor[RankingFactorInterface::ID];

        if (!$id) {
            return parent::getData();
        }

        $criterion = $this->criterionRepository->create();
        $criterion->setConditions([
            [
                [
                    CriterionInterface::CONDITION_SORT_BY        => CriterionInterface::CONDITION_SORT_BY_RANKING_FACTOR,
                    CriterionInterface::CONDITION_RANKING_FACTOR => $id,
                    CriterionInterface::CONDITION_DIRECTION      => 'desc',
                    CriterionInterface::CONDITION_WEIGHT         => $arRankingFactor['weight'],
                ],
            ],
        ]);

        $this->criteriaApplierService->applyCriterion($criterion, $this->getCollection());

        # prevent "random" sorting, if scores are same
        $this->getCollection()->setOrder('entity_id');

        $data = parent::getData();

        $data = $this->addRankingFactorsToData($data, [$id]);

        return $data;
    }

    private function addRankingFactorsToData($data, $ids = [])
    {
        $items = [];
        foreach ($data['items'] as $item) {
            $id         = $item['entity_id'];
            $items[$id] = $item;
        }

        $select = $this->resource->getConnection()->select()
            ->from(
                $this->resource->getTableName(IndexInterface::TABLE_NAME)
            )->where('product_id IN(?)', array_keys($items));

        $factors = $this->rankingFactorRepository->getCollection();
        if (count($ids)) {
            $factors->addFieldToFilter(RankingFactorInterface::ID, $ids);
        }

        $labels = [];
        foreach ($factors as $factor) {
            $labels[$factor->getId()] = $factor->getName();
        }

        foreach (array_keys($items) as $id) {
            foreach ($labels as $fId => $label) {
                $items[$id]['factors'][$fId] = [
                    'label' => $label,
                    'value' => 0,
                ];
            }
        }

        foreach ($this->resource->getConnection()->fetchAll($select) as $row) {
            $productId = $row['product_id'];
            $factorId  = $row['factor_id'];

            if (!isset($labels[$factorId])) {
                continue;
            }

            $items[$productId]['factors'][$factorId] = [
                'label' => $labels[$factorId],
                'value' => $row['value'],
            ];
        }

        foreach (array_keys($items) as $id) {
            $items[$id]['factors'] = array_values($items[$id]['factors']);
        }

        $data['items'] = array_values($items);

        return $data;
    }

    public function addOrder($field, $direction)
    {
        return $this;
    }

    public function getCollection()
    {
        /** @var \Magento\Eav\Model\Entity\Collection\AbstractCollection $collection */
        $collection = parent::getCollection();
        $collection->addFieldToSelect('status');

        return $collection;
    }
}
