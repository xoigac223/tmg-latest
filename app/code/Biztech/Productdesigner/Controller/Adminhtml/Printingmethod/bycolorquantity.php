<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Biztech\Productdesigner\Controller\Adminhtml\Printingmethod;

class bycolorquantity extends \Biztech\Productdesigner\Controller\Adminhtml\Printingmethod {

    public function execute() {
    		
        $resultLayout = $this->resultLayoutFactory->create();
        $resultLayout->getLayout()->getBlock('printing.grid')->setProducts($this->getRequest()->getPost('products', null));
        return $resultLayout;
    }

}
