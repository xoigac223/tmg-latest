<?php
/**
 * Copyright © 2015 Biztech. All rights reserved.
 */

namespace Biztech\Productdesigner\Controller\Adminhtml\Colors;

class NewAction extends \Biztech\Productdesigner\Controller\Adminhtml\Colors
{

    public function execute()
    {
        $this->_forward('edit');
    }
}
