<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExport\Model\Import\Product\Type;

use Magento\CatalogImportExport\Model\Import\Product as ImportProduct;
use Magento\Framework\App\ObjectManager;

class Bundle extends \Magento\BundleImportExport\Model\Import\Product\Type\Bundle
{
    protected $resource;

    /**
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $attrSetColFac
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $prodAttrColFac
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param array $params
     * @param \Magento\Framework\EntityManager\MetadataPool|null $metadataPool
     */
    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $attrSetColFac,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $prodAttrColFac,
        \Magento\Framework\App\ResourceConnection $resource,
        array $params
    ) {
        parent::__construct($attrSetColFac, $prodAttrColFac, $resource, $params);
        $this->resource = $resource;
    }

    /**
     * Insert selections.
     *
     * @return \Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType
     */
    protected function insertSelections()
    {
        $selectionTable = $this->_resource->getTableName('catalog_product_bundle_selection');
        $selections = [];
        foreach ($this->_cachedOptions as $productId => $options) {
            foreach ($options as $option) {
                $index = 0;
                foreach ($option['selections'] as $selection) {
                    if (isset($selection['position'])) {
                        $index = $selection['position'];
                    }
                    if ($tmpArray = $this->populateSelectionTemplate(
                        $selection,
                        $option['option_id'],
                        $productId,
                        $index
                    )) {
                        $selections[] = $tmpArray;
                        $index++;
                    }
                }
            }
        }
        if (!empty($selections)) {
            $this->connection->insertOnDuplicate(
                $selectionTable,
                $selections,
                [
                    'selection_id',
                    'product_id',
                    'position',
                    'is_default',
                    'selection_price_type',
                    'selection_price_value',
                    'selection_qty',
                    'selection_can_change_qty'
                ]
            );
			$this->saveCatalogProductRelation($selections);
        }
        return $this;
    }

    /**
     * Insert data to catalog_product_relation table
     * Solve problem: bundle products always show out of stock in front-end
     */
    protected function saveCatalogProductRelation($selections)
    {
        if (!empty($selections)) {
            $catalogProductRelations = [];
            foreach ($selections as $selection) {
                $catalogProductRelations[] = [
                    'parent_id' => $selection['parent_product_id'],
                    'child_id' => $selection['product_id']
                ];
            }
            $this->resource->getConnection()->insertOnDuplicate(
                $this->resource->getTableName('catalog_product_relation'),
                $catalogProductRelations,
                [
                    'parent_id',
                    'child_id',
                ]
            );
        }
    }
}
