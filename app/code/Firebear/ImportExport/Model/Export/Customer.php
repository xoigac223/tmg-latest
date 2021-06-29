<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExport\Model\Export;

use Magento\Eav\Model\Entity\Collection\AbstractCollection;

class Customer extends \Magento\CustomerImportExport\Model\Export\Customer
{
    use \Firebear\ImportExport\Traits\Export\Entity;

    use \Firebear\ImportExport\Traits\General;

    /**
     * @return mixed
     */
    public function getFieldsForExport()
    {
        $validAttributeCodes = $this->_getExportAttributeCodes();

        return array_unique(array_merge($this->_permanentAttributes, $validAttributeCodes, ['password']));
    }

    /**
     * @param $item
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function exportItem($item)
    {
        $row = $this->_addAttributeValuesToRow($item);
        $row[self::COLUMN_WEBSITE] = $this->_websiteIdToCode[$item->getWebsiteId()];
        $row[self::COLUMN_STORE] = $this->_storeIdToCode[$item->getStoreId()];

        if ($row['gender'] == "0") {
            $row['gender'] = '';
        }
        if ($this->_parameters['enable_last_entity_id'] > 0) {
            $this->lastEntityId = $item->getEntityId();
        }

        $this->getWriter()->writeRow($this->changeRow($row));
    }

    public function export()
    {
        $entityCollection = $this->_getEntityCollection();
        if (isset($this->_parameters['last_entity_id'])
            && $this->_parameters['last_entity_id'] > 0
            && $this->_parameters['enable_last_entity_id'] > 0
        ) {
            $entityCollection->addFieldToFilter(
                'entity_id',
                ['gt' => $this->_parameters['last_entity_id']]
            );
        }
        $this->_prepareEntityCollection($entityCollection);
        $writer = $this->getWriter();

        // create export file
        $writer->setHeaderCols($this->_getHeaderColumns());
        $this->_exportCollectionByPages($entityCollection);

        return [$writer->getContents(), $entityCollection->getSize(), $this->lastEntityId];
    }

    /**
     * @return array
     */
    protected function _getHeaderColumns()
    {
        $validAttributeCodes = $this->_getExportAttributeCodes();
        $headers = array_merge($this->_permanentAttributes, $validAttributeCodes, ['password']);

        return $this->changeHeaders($headers);
    }
    
    /**
     * Apply filter to collection and add not skipped attributes to select
     *
     * @param AbstractCollection $collection
     * @return AbstractCollection
     */
    protected function _prepareEntityCollection(AbstractCollection $collection)
    {
        $this->filterEntityCollection($collection);
        $this->_addAttributesToCollection($collection);
        return $collection;
    }
}
