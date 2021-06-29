<?php
/**
 * Copyright © 2015 Biztech. All rights reserved.
 */

namespace Biztech\Productdesigner\Controller\Adminhtml\Quotescategory;

class NewAction extends \Biztech\Productdesigner\Controller\Adminhtml\Quotescategory
{

    public function execute()
    {
        $this->_forward('edit');
    }
}
