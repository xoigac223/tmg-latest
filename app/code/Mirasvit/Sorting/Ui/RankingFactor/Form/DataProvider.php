<?php

namespace Mirasvit\Sorting\Ui\RankingFactor\Form;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Mirasvit\Sorting\Api\Data\RankingFactorInterface;
use Mirasvit\Sorting\Api\Repository\RankingFactorRepositoryInterface;
use Magento\Framework\App\ObjectManager;

class DataProvider extends AbstractDataProvider
{
    private $repository;

    private $context;

    private $uiComponentFactory;

    /**
     * @var ModifierInterface[]
     */
    private $modifierPool;

    public function __construct(
        RankingFactorRepositoryInterface $repository,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = [],
        array $modifier = []
    ) {
        $this->repository         = $repository;
        $this->collection         = $this->repository->getCollection();
        $this->context            = $context;
        $this->uiComponentFactory = $uiComponentFactory;

        $this->modifierPool = $modifier;

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getConfigData()
    {
        $data = parent::getConfigData();

        foreach ($this->repository->getFactors() as $code => $factor) {
            $data['notes'][$code] = $factor->getDescription();
        }

        return $data;
    }

    public function getMeta()
    {
        $meta = parent::getMeta();

        $model = $this->getModel();
        if (!$model) {
            return $meta;
        }

        $factor = $this->repository->getFactor($model->getType());
        if (!$factor) {
            return $meta;
        }

        $uiComponent = $factor->getUiComponent();
        if (!$uiComponent) {
            return $meta;
        }

        $component = $this->uiComponentFactory->create($uiComponent);

        $meta = $this->prepareComponent($component)['children'];

        return $meta;
    }

    /**
     * @param UiComponentInterface $component
     *
     * @return array
     */
    protected function prepareComponent(UiComponentInterface $component)
    {
        $data = [];
        foreach ($component->getChildComponents() as $name => $child) {
            $data['children'][$name] = $this->prepareComponent($child);
        }

        $data['arguments']['data']  = $component->getData();
        $data['arguments']['block'] = $component->getBlock();

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

            $data[RankingFactorInterface::CONFIG]      = $model->getConfig();
            $data[RankingFactorInterface::CONFIG]['_'] = 1;

            foreach ($this->modifierPool as $type => $modifier) {
                if ($type === $model->getType()) {
                    $data = $modifier->modifyData($data);
                }
            }
            $result[$model->getId()] = $data;
        }

        return $result;
    }

    /**
     * @return false|\Mirasvit\Sorting\Api\Data\RankingFactorInterface
     */
    private function getModel()
    {
        $id = $this->context->getRequestParam($this->getRequestFieldName(), null);

        return $id ? $this->repository->get($id) : false;
    }
}
