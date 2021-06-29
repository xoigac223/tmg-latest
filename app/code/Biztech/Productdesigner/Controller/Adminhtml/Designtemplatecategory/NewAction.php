<?php
/**
 * Copyright © 2015 Biztech. All rights reserved.
 */

namespace Biztech\Productdesigner\Controller\Adminhtml\Designtemplatecategory;

class NewAction extends \Biztech\Productdesigner\Controller\Adminhtml\Designtemplatecategory
{

    public function execute()
    {
        $this->_forward('edit');
    }
}
