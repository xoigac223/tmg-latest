<?php

namespace Mirasvit\Sorting\Model\Config\Source\Factor\Attribute;

use Magento\Eav\Model\Entity;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection;
use Magento\Framework\Option\ArrayInterface;

class AttributeSource implements ArrayInterface
{
    private $eavEntity;

    private $attributeCollection;

    public function __construct(
        Entity $eavEntity,
        Collection $attributeCollection
    ) {
        $this->eavEntity           = $eavEntity;
        $this->attributeCollection = $attributeCollection;
    }

    public function toOptionArray()
    {
        $entityTypeId = $this->eavEntity->setType('catalog_product')->getTypeId();

        $attributes = $this->attributeCollection->setEntityTypeFilter($entityTypeId)
            ->addFieldToFilter('frontend_input', ['select', 'multiselect']);

        $result = [];

        /** @var \Magento\Eav\Model\Entity\Attribute $attribute */
        foreach ($attributes as $attribute) {
            if ($attribute->getStoreLabel()) {
                $result[] = [
                    'label' => $attribute->getStoreLabel(),
                    'value' => $attribute->getAttributeCode(),
                ];
            }
        }

        return $result;
    }
}

