<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExport\Traits\Import\Product;

trait Type
{
    /**
     * Attach Attributes By Id
     *
     * @param string $attributeSetName
     * @param array $attributeIds
     * @return void
     */
    protected function attachAttributesById($attributeSetName, $attributeIds)
    {
        foreach ($this->_prodAttrColFac->create()->addFieldToFilter(
            ['main_table.attribute_id', 'main_table.attribute_code'],
            [
                ['in' => $attributeIds],
                ['in' => $this->_forcedAttributesCodes]
            ]
        ) as $attribute) {
            $attributeId = $attribute->getId();
            $attributeCode = $attribute->getAttributeCode();

            if ($attribute->getIsVisible() || in_array($attributeCode, $this->_forcedAttributesCodes)) {
                if (!isset(self::$commonAttributesCache[$attributeId])) {
                    self::$commonAttributesCache[$attributeId] = [
                        'id' => $attributeId,
                        'code' => $attributeCode,
                        'is_user_defined' => $attribute->getIsUserDefined(),
                        'is_global' => $attribute->getIsGlobal(),
                        'is_required' => $attribute->getIsRequired(),
                        'is_unique' => $attribute->getIsUnique(),
                        'frontend_label' => $attribute->getFrontendLabel(),
                        'is_static' => $attribute->isStatic(),
                        'apply_to' => $attribute->getApplyTo(),
                        'type' => \Magento\ImportExport\Model\Import::getAttributeType($attribute),
                        'options' => $this->_entityModel->getAttributeOptions(
                            $attribute,
                            $this->_indexValueAttributes
                        ),
                        'default_value' => strlen(
                            $attribute->getDefaultValue()
                        ) ? $attribute->getDefaultValue() : null,
                    ];
                }

                self::$attributeCodeToId[$attributeCode] = $attributeId;
                $this->_addAttributeParams(
                    $attributeSetName,
                    self::$commonAttributesCache[$attributeId],
                    $attribute
                );
            }
        }
    }
}
