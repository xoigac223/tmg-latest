<?php

namespace Mirasvit\Sorting\Service;


use Magento\Catalog\Model\Config as CatalogConfig;
use Mirasvit\Sorting\Api\Data\CriterionInterface;
use Mirasvit\Sorting\Api\Repository\CriterionRepositoryInterface;
use Mirasvit\Sorting\Model\Config;

class CriteriaManagementService
{
    const DEFAULT_DIRECTION = 'asc';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var CatalogConfig
     */
    private $catalogConfig;

    private $criterionRepository;

    public function __construct(
        CatalogConfig $catalogConfig,
        Config $config,
        CriterionRepositoryInterface $criterionRepository
    ) {
        $this->config              = $config;
        $this->catalogConfig       = $catalogConfig;
        $this->criterionRepository = $criterionRepository;
    }

    /**
     * @return false|CriterionInterface
     */
    public function getDefaultCriterion()
    {
        /** @var CriterionInterface $criterion */
        $criterion = $this->criterionRepository->getCollection()
            ->addFieldToFilter(CriterionInterface::IS_ACTIVE, 1)
            ->addFieldToFilter(CriterionInterface::IS_DEFAULT, 1)
            ->getFirstItem();

        return $criterion->getId() ? $criterion : false;
    }

    public function getDefaultDirection(CriterionInterface $criterion)
    {
        foreach ($criterion->getConditions() as $node) {
            foreach ($node as $condition) {
                return $condition[CriterionInterface::CONDITION_DIRECTION];
            }
        }

        return 'asc';
    }

    /**
     * @inheritdoc
     */
    public function getDefaultCriteria()
    {
        $options = ['position' => __('Position')];
        foreach ($this->catalogConfig->getAttributesUsedForSortBy() as $attribute) {
            /* @var $attribute \Magento\Eav\Model\Entity\Attribute\AbstractAttribute */
            $options[$attribute->getAttributeCode()] = $attribute->getStoreLabel();
        }

        return $options;
    }
}
