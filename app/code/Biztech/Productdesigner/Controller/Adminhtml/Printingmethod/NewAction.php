<?php
/**
 * Copyright © 2015 Biztech. All rights reserved.
 */

namespace Biztech\Productdesigner\Controller\Adminhtml\Printingmethod;

class NewAction extends \Biztech\Productdesigner\Controller\Adminhtml\Printingmethod
{

    public function execute()
    {
        $this->_forward('edit');
    }
}
