<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Biztech\Productdesigner\Observer;

use Magento\Framework\Event\ObserverInterface;

class ChangeTemplateObserver implements ObserverInterface {

    /**
     * @param mixed $observer
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {
        $observer->getBlock()->setTemplate('Biztech_Productdesigner::helper/gallery.phtml');
    }

}
