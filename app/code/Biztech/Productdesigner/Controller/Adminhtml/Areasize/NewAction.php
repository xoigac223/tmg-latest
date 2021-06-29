<?php
/**
 * Copyright © 2015 Biztech. All rights reserved.
 */

namespace Biztech\Productdesigner\Controller\Adminhtml\Areasize;

class NewAction extends \Biztech\Productdesigner\Controller\Adminhtml\Areasize
{

    public function execute()
    {
        $this->_forward('edit');
    }
}
