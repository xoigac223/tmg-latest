<?php

namespace Mirasvit\Sorting\Ui\RankingFactor\Form\Modifier;

use Magento\Catalog\Model\Product\AttributeSet\Options as AttributeSetOptions;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Mirasvit\Sorting\Api\Data\RankingFactorInterface;
use Mirasvit\Sorting\Factor\AttributeSetFactor;

class AttributeSetModifier implements ModifierInterface
{
    use MappingTrait;

    private $options;

    public function __construct(
        AttributeSetOptions $options
    ) {
        $this->options = $options;
    }

    public function modifyData(array $data)
    {
        $mapping = isset($data[RankingFactorInterface::CONFIG][AttributeSetFactor::MAPPING])
            ? $data[RankingFactorInterface::CONFIG][AttributeSetFactor::MAPPING]
            : [];

        $mapping = $this->sync($this->options->toOptionArray(), $mapping);

        $data[RankingFactorInterface::CONFIG][AttributeSetFactor::MAPPING] = $mapping;

        return $data;
    }

    public function modifyMeta(array $meta)
    {
        return $meta;
    }
}
