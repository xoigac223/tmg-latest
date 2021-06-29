<?php

namespace Biztech\Productdesigner\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Psr\Log\LoggerInterface as Logger;

class ConfigObserver implements ObserverInterface
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @param Logger $logger
     */
    public function __construct(
        Logger $logger
    ) {
        $this->logger = $logger;
    }

    public function execute(EventObserver $observer)
    {
        
        $objectManagerConfig = \Magento\Framework\App\ObjectManager::getInstance();
        $config = $objectManagerConfig->create('Magento\Framework\App\Config\ScopeConfigInterface');
        $primaryColor = $config->getValue('productdesigner/themedesigner_general/primary_background');
        $secondaryColor = $config->getValue('productdesigner/themedesigner_general/secondary_background');
        
        header('Content-type: text/css');


        $layout = $this->_objectManager->create('Magento\Framework\View\LayoutInterface');
        $resultPage = $layout->createBlock('Biztech\Productdesigner\Block\Productdesigner');
        $css = $resultPage->setData(array("primary_background"=>$primaryColor,"secondary_background"=>$secondaryColor))->setTemplate('productdesigner/system/config/theme.phtml')->toHtml();

    }
}