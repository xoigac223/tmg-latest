<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Blackbird\ContentManager\Model;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;

/**
 * Attribute Metadata data provider class
 */
class AttributeMetadataDataProvider
{
    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;

    /**
     * @var \Magento\Store\Model\StoreManager
     */
    protected $_storeManager;

    /**
     * Initialize data provider with data source
     *
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Store\Model\StoreManager $storeManager
     */
    public function __construct(
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Store\Model\StoreManager $storeManager
    ) {
        $this->_eavConfig = $eavConfig;
        $this->_storeManager = $storeManager;
    }

    /**
     * Get attribute model for a given entity type and code
     *
     * @param string $entityType
     * @param string $attributeCode
     * @return false|AbstractAttribute
     */
    public function getAttribute($entityType, $attributeCode)
    {
        return $this->_eavConfig->getAttribute($entityType, $attributeCode);
    }

    /**
     * Get all attribute codes for a given entity type and attribute set
     *
     * @param string $entityType
     * @param int $attributeSetId
     * @param string|null $storeId
     * @return array Attribute codes
     */
    public function getAllAttributeCodes($entityType, $attributeSetId = 0, $storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->_storeManager->getStore()->getId();
        }
        $object = new \Magento\Framework\DataObject(
            [
                'store_id' => $storeId,
                'attribute_set_id' => $attributeSetId,
            ]
        );
        return $this->_eavConfig->getEntityAttributeCodes($entityType, $object);
    }
}
