<?php

namespace Solwin\ProductVideo\Observer;

use Magento\Framework\Event\ObserverInterface;

class Productsavebefore implements ObserverInterface
{

    protected $_coreSession;

    public function __construct(
        \Magento\Framework\Session\SessionManagerInterface $coreSession
        ){
        $this->_coreSession = $coreSession;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->_coreSession->start();
        $_productvideo = $observer->getProduct()->getOrigData('productvideo');
        $this->_coreSession->setProductvideo($_productvideo);
    }
}
?>
