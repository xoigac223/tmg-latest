<?php
/**
 * Copyright © 2015 Biztech. All rights reserved.
 */

namespace Biztech\Productdesigner\Controller\Adminhtml\Fonts;

class NewAction extends \Biztech\Productdesigner\Controller\Adminhtml\Fonts
{

    public function execute()
    {	
    	
        $this->_forward('edit');
    }
}
