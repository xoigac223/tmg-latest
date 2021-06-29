<?php
/**
 * Copyright © 2015 Biztech. All rights reserved.
 */

namespace Biztech\Productdesigner\Controller\Adminhtml\Side;

class NewAction extends \Biztech\Productdesigner\Controller\Adminhtml\Side
{

    public function execute()
    {
        $this->_forward('edit');
    }
}
