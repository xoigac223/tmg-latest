<?php

namespace Themagnet\Productimport\Controller\Adminhtml\System\Config;

use \Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\Controller\ResultFactory;

class Connect extends \Magento\Backend\App\Action
{
    
    protected $_logger;
    protected $_ftp;
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Themagnet\Productimport\Helper\Data $helper,
        \Themagnet\Productimport\Model\Ftpfiles $ftp
    ) {
        $this->_logger = $logger;
        $this->_ftp = $ftp;
        parent::__construct($context);
        $this->_helper = $helper;
    }

    public function execute()
    {
        $connection = $this->_ftp->getFtpConnection();
        $result = array();
        if(isset($connection['error'])){
            $result = array('error'=>$connection['error']);
        }else{
            $result = array('success'=>1);
        }
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($result);
        return $resultJson;
    }
}