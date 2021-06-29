<?php

namespace Mirasvit\Sorting\Model\Config\Source;

use Magento\Catalog\Model\Config;
use Magento\Framework\Option\ArrayInterface;

class SortByAttributeSource implements ArrayInterface
{
    private $config;

    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    public function toOptionArray()
    {
        $result = [];

        /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
        foreach ($this->config->getAttributesUsedForSortBy() as $attribute) {
            $result[] = [
                'label' => $attribute->getStoreLabel(),
                'value' => $attribute->getAttributeCode(),
            ];
        }

        return $result;
    }
}