<?php
/**
 * Copyright © 2015 Biztech. All rights reserved.
 */

namespace Biztech\Productdesigner\Controller\Adminhtml\Masking;

class NewAction extends \Biztech\Productdesigner\Controller\Adminhtml\Masking
{

    public function execute()
    {
        $this->_forward('edit');
    }
}
