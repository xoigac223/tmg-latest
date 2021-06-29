<?php

namespace Shreeji\Duplicateimage\Model;

use Magento\Framework\Model\AbstractModel;

class Duplicateimage extends AbstractModel {

    /**
     * Initialize resources
     *
     * @return void
     */
    protected function _construct() {
        $this->_init('Shreeji\Duplicateimage\Model\ResourceModel\Duplicateimage');
    }

}
