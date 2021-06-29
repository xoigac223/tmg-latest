<?php
namespace Themagnet\Orderstatus\Model\Api;

class Soap extends \Magento\Framework\Model\AbstractModel
{
    protected $_importlogger;
    protected $_helper;
    protected $_api;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Themagnet\Orderstatus\Helper\Data $helper,
        \Themagnet\Orderstatus\Model\Logger $logger,
        array $data = array()
    ) {
        $this->_importlogger = $logger;
        $this->_helper = $helper;
        parent::__construct($context, $registry);
    }

    protected function getSoapInit()
    {
        
        try {
            $url = $this->_helper->getWsdlUrl();
            $options = array(
                    'exceptions'=>true,
                    'trace'=>1
            );
            $this->_api = new \SoapClient($url, $options);
        } catch (\SoapFault $fault) {
            trigger_error("SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})", E_USER_ERROR);
        }
    }

    protected function getSoapCall($param)
    {
        $this->getSoapInit();
        $parameter = array('svcUser'=>$this->_helper->getSvcUser(),
                           'svcPassword'=>$this->_helper->getSvcPassword(),
                           'Account'=>$param['Account'],
                           'req'=>array('QueryType'=>$param['QueryType'],'ReferenceNumber'=>$param['ReferenceNumber'])
                          );
        $request = $this->_api->getOrderStatus($parameter);
        return $this->processResponce($request);
    }

    public function getOrderStatus($param)
    {
        //$param = $this->_helper->getReq();
        return $this->getSoapCall($param);
    }

    public function processResponce($request)
    {
        if($request){
            $reponse = json_encode($request);
            $response = json_decode($reponse, true);
            if(isset($response['getOrderStatusResult']['ErrorMessage']) && $response['getOrderStatusResult']['ErrorMessage'] !='')
            {
                $this->_importlogger->debugLog($response['getOrderStatusResult']['ErrorMessage']);
            }
            return $response;
        }
        return array();
    }
}
