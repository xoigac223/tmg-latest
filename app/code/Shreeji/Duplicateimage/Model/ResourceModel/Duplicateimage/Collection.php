<?php

namespace Shreeji\Duplicateimage\Model\ResourceModel\Duplicateimage;

use \Shreeji\Duplicateimage\Model\ResourceModel\AbstractCollection;

/**
 * ZIP code collection
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'manageimage_id';

    /**
     * Load data for preview flag
     *
     * @var bool
     */
    protected $_previewFlag;

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Shreeji\Duplicateimage\Model\Duplicateimage', 'Shreeji\Duplicateimage\Model\ResourceModel\Duplicateimage');        
    }    
}
