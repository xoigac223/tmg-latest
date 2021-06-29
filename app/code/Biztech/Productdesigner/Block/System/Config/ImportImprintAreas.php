<?php

/**
 * Copyright © 2017-2018 AppJetty. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Biztech\Productdesigner\Block\System\Config;

class ImportImprintAreas extends \Magento\Config\Model\Config\Backend\File {
    /**
     * @return string[]
     */
    public function _getAllowedExtensions() {
        return ['xml'];
    }

}
