<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Biztech\Productdesigner\Controller\Adminhtml\Product;

class Designtemplate extends \Magento\Backend\App\Action {

    protected $resultLayoutFactory;
    
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory        
    ) {
        
        parent::__construct($context);       
        $this->resultLayoutFactory = $resultLayoutFactory;
    }
    
    public function execute() {
        $resultLayout = $this->resultLayoutFactory->create();
        $resultLayout->getLayout()->getBlock('templatecategory.grid')->setProducts($this->getRequest()->getPost('products', null));
        return $resultLayout;
    }

}
