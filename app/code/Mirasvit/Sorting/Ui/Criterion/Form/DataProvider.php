<?php

namespace Mirasvit\Sorting\Ui\Criterion\Form;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Mirasvit\Sorting\Api\Data\CriterionInterface;
use Mirasvit\Sorting\Api\Repository\CriterionRepositoryInterface;
use Mirasvit\Sorting\Model\Config\Source\SortByAttributeSource;
use Mirasvit\Sorting\Model\Config\Source\SortByRankingFactorSource;
use Mirasvit\Sorting\Model\Config\Source\SortBySource;
use Mirasvit\Sorting\Model\Config\Source\SortDirectionSource;

class DataProvider extends AbstractDataProvider
{
    private $repository;

    private $context;

    private $uiComponentFactory;

    private $sortBySource;

    private $sortByAttributeSource;

    private $sortByRankingFactorSource;

    private $sortDirectionSource;

    public function __construct(
        CriterionRepositoryInterface $repository,
        SortBySource $sortBySource,
        SortByAttributeSource $sortByAttributeSource,
        SortByRankingFactorSource $sortByRankingFactorSource,
        SortDirectionSource $sortDirectionSource,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        $this->repository         = $repository;
        $this->collection         = $this->repository->getCollection();
        $this->context            = $context;
        $this->uiComponentFactory = $uiComponentFactory;

        $this->sortBySource              = $sortBySource;
        $this->sortByAttributeSource     = $sortByAttributeSource;
        $this->sortByRankingFactorSource = $sortByRankingFactorSource;
        $this->sortDirectionSource       = $sortDirectionSource;

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getConfigData()
    {
        $data = parent::getConfigData();

        $data['sortBySource']              = $this->sortBySource->toOptionArray();
        $data['sortByAttributeSource']     = $this->sortByAttributeSource->toOptionArray();
        $data['sortByRankingFactorSource'] = $this->sortByRankingFactorSource->toOptionArray();
        $data['sortDirectionSource']       = $this->sortDirectionSource->toOptionArray();

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $result = [];

        $model = $this->getModel();

        if ($model) {
            $data = $model->getData();

            $data[CriterionInterface::CONDITIONS] = $model->getConditions();
            //print_r($data);die();
            $result[$model->getId()] = $data;
        }

        return $result;
    }

    /**
     * @return false|\Mirasvit\Sorting\Api\Data\CriterionInterface
     */
    private function getModel()
    {
        $id = $this->context->getRequestParam($this->getRequestFieldName(), null);

        return $id ? $this->repository->get($id) : false;
    }
}
