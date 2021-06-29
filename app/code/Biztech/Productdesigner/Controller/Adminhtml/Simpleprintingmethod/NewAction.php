<?php
/**
 * Copyright © 2015 Biztech. All rights reserved.
 */

namespace Biztech\Productdesigner\Controller\Adminhtml\Simpleprintingmethod;

class NewAction extends \Biztech\Productdesigner\Controller\Adminhtml\Simpleprintingmethod
{

    public function execute()
    {
        $this->_forward('edit');
    }
}
