<?php

namespace Mirasvit\Sorting\Factor;

use Magento\Catalog\Model\ProductFactory as ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\Model\ResourceModel\Iterator;
use Mirasvit\Sorting\Api\Data\IndexInterface;
use Mirasvit\Sorting\Api\Data\RankingFactorInterface;

class RuleFactor implements FactorInterface
{
    const RULE = 'rule';

    private $productIds = [];

    private $ruleFactory;

    private $productCollectionFactory;

    private $iterator;

    private $productFactory;

    private $context;

    private $indexer;

    public function __construct(
        ProductRule\RuleFactory $ruleFactory,
        ProductCollectionFactory $productCollectionFactory,
        ProductFactory $productFactory,
        Iterator $iterator,
        Context $context,
        Indexer $indexer
    ) {
        $this->ruleFactory              = $ruleFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->iterator                 = $iterator;
        $this->productFactory           = $productFactory;
        $this->context                  = $context;
        $this->indexer                  = $indexer;
    }

    public function getName()
    {
        return 'Rule';
    }

    public function getDescription()
    {
        return '';
    }

    public function getUiComponent()
    {
        return 'sorting_factor_rule';
    }

    /**
     * @param RankingFactorInterface $rankingFactor
     *
     * @return ProductRule\Rule
     */
    public function getRule(RankingFactorInterface $rankingFactor)
    {
        $ruleData = $rankingFactor->getConfigData(self::RULE, []);

        $model = $this->ruleFactory->create();

        $model->loadPost($ruleData);

        return $model;
    }

    public function reindexAll(RankingFactorInterface $rankingFactor)
    {
        $this->indexer->delete($rankingFactor);

        $rule = $this->getRule($rankingFactor);


        $productCollection = $this->productCollectionFactory->create();

        $rule->getConditions()->collectValidatedAttributes($productCollection);

        $this->iterator->walk(
            $productCollection->getSelect(),
            [[$this, 'callbackValidateProduct']],
            [
                'product' => $this->productFactory->create(),
                'rule'    => $rule,
            ]
        );

        $this->indexer->startIndexation();

        foreach ($this->productIds as $id => $value) {
            $this->indexer->add(
                $rankingFactor,
                $id,
                $value ? IndexInterface::MAX : 0
            );
        }

        $this->indexer->finishIndexation();
    }

    /**
     * Callback function for product matching
     *
     * @param array $args
     *
     * @return void
     */
    public function callbackValidateProduct($args)
    {
        $product = clone $args['product'];
        $product->setData($args['row']);

        $rule = $args['rule'];

        $this->productIds[$product->getId()] = $rule->getConditions()->validate($product);
    }
}