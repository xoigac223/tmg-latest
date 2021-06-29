<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Biztech\Productdesigner\Controller\Adminhtml\Designtemplatecategory;

class TemplatecategoryGrid extends \Biztech\Productdesigner\Controller\Adminhtml\Designtemplatecategory {

    public function execute() {
        $resultLayout = $this->resultLayoutFactory->create();

        $resultLayout->getLayout()->getBlock('templatecategory.grid')->setInProducts($this->getRequest()->getPost('templates', null));
        return $resultLayout;
    }

}
