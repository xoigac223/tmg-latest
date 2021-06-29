<?php

namespace Mirasvit\Sorting\Ui\RankingFactor\Form\Modifier;

use Magento\Eav\Model\AttributeRepository;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Mirasvit\Sorting\Api\Data\RankingFactorInterface;
use Mirasvit\Sorting\Factor\AttributeFactor;

class AttributeModifier implements ModifierInterface
{
    use MappingTrait;

    private $repository;

    public function __construct(
        AttributeRepository $repository
    ) {
        $this->repository = $repository;
    }

    public function modifyData(array $data)
    {
        $code = isset($data[RankingFactorInterface::CONFIG][AttributeFactor::ATTRIBUTE])
            ? $data[RankingFactorInterface::CONFIG][AttributeFactor::ATTRIBUTE]
            : false;

        if (!$code) {
            $data[RankingFactorInterface::CONFIG][AttributeFactor::MAPPING] = [];

            return $data;
        }

        $attribute = $this->repository->get('catalog_product', $code);

        $mapping = isset($data[RankingFactorInterface::CONFIG][AttributeFactor::MAPPING])
            ? $data[RankingFactorInterface::CONFIG][AttributeFactor::MAPPING]
            : [];

        $options = [];

        /** @var \Magento\Eav\Model\Entity\Attribute\Option $option */
        foreach ($attribute->getOptions() as $option) {
            $label = trim($option->getLabel());
            if (!$label) {
                continue;
            }

            $options[] = [
                'label' => $label,
                'value' => $option->getValue(),
            ];
        }

        $mapping = $this->sync($options, $mapping);

        $data[RankingFactorInterface::CONFIG][AttributeFactor::MAPPING] = $mapping;

        return $data;
    }

    public function modifyMeta(array $meta)
    {
        return $meta;
    }
}
