<?php
/**
 * Copyright © 2015 Biztech. All rights reserved.
 */

namespace Biztech\Productdesigner\Controller\Adminhtml\Quotes;

class NewAction extends \Biztech\Productdesigner\Controller\Adminhtml\Quotes
{

    public function execute()
    {     	   
        $this->_forward('edit');
    }
}
