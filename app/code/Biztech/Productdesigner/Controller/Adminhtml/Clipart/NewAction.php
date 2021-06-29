<?php
/**
 * Copyright © 2015 Biztech. All rights reserved.
 */

namespace Biztech\Productdesigner\Controller\Adminhtml\Clipart;

class NewAction extends \Biztech\Productdesigner\Controller\Adminhtml\Clipart
{

    public function execute()
    {
        $this->_forward('edit');
    }
}
