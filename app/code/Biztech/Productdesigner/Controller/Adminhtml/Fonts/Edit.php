<?php

/**
 * Copyright © 2015 Biztech. All rights reserved.
 */

namespace Biztech\Productdesigner\Controller\Adminhtml\Fonts;

class Edit extends \Biztech\Productdesigner\Controller\Adminhtml\Fonts {

    public function execute() {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set((__('Manage Fonts')));
        $this->_initAction();
        $this->_view->renderLayout();
    }

}
