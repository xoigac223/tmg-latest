<?php

namespace Biztech\Productdesigner\Model\System\Config;

class Stockcolors extends \Magento\Framework\Model\AbstractModel {

    public function _construct() {
        $this->_init("productdesigner_configurableattributes", "product_stockcolor_id");
    }

}
