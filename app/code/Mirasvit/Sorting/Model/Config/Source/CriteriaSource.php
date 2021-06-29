<?php

namespace Mirasvit\Sorting\Model\Config\Source;


use Magento\Framework\Option\ArrayInterface;
use Mirasvit\Sorting\Api\Data\CriterionInterface;
use Mirasvit\Sorting\Api\Repository\CriterionRepositoryInterface;
use Mirasvit\Sorting\Model\Config;
use Mirasvit\Sorting\Service\CriteriaManagementService;

class CriteriaSource implements ArrayInterface
{
    /**
     * @var CriterionRepositoryInterface
     */
    private $criterionRepository;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var CriteriaManagementService
     */
    private $criteriaManagement;


    public function __construct(
        CriteriaManagementService $criteriaManagement,
        CriterionRepositoryInterface $criterionRepository,
        Config $config
    ) {
        $this->criteriaManagement  = $criteriaManagement;
        $this->criterionRepository = $criterionRepository;
        $this->config              = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->toArray() as $code => $label) {
            $options[] = ['value' => $code, 'label' => $label];
        }

        return $options;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $options         = [];
        $defaultCriteria = $this->criteriaManagement->getDefaultCriteria();

        $collection = $this->criterionRepository->getCollection();
        $collection->addFieldToFilter(CriterionInterface::IS_ACTIVE, 1)
            ->setOrder(CriterionInterface::POSITION);

        foreach ($collection as $criterion) {
            $options[$criterion->getCode()] = $criterion->getName();
        }

        // if criteria not configured yet - use default sort by options
        if (!$options) {
            $options = $defaultCriteria;
        }

        return $options;
    }
}
