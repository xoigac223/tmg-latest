<?php
/**
 * Copyright © 2015 Biztech. All rights reserved.
 */

namespace Biztech\Productdesigner\Controller\Adminhtml\Pcolor;

class NewAction extends \Biztech\Productdesigner\Controller\Adminhtml\Pcolor
{

    public function execute()
    {
        $this->_forward('edit');
    }
}
