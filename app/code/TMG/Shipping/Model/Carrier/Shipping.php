<?php


namespace TMG\Shipping\Model\Carrier;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Quote\Model\Quote\Address\RateResult\Method as ResultMethod;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result as RateResult;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use TMG\Shipping\Helper\Config as ConfigHelper;
use TMG\Shipping\Model\Api\FreightEstimates;

class Shipping extends AbstractCarrier implements CarrierInterface
{
    const CARRIER_CODE = 'tmgshipping';
    
    /**
     * @var string
     */
    protected $_code = self::CARRIER_CODE;
    
    /**
     * @var ConfigHelper
     */
    protected $configHelper;
    
    /**
     * @var FreightEstimates
     */
    protected $freightEstimates;
    
    /**
     * @var MethodFactory
     */
    protected $rateMethodFactory;
    
    /**
     * @var ResultFactory
     */
    protected $rateResultFactory;
    
    /**
     * @var array
     */
    protected $availableRates;
    
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        // Custom
        ConfigHelper $configHelper,
        FreightEstimates $freightEstimates,
        MethodFactory $rateMethodFactory,
        ResultFactory $rateResultFactory,
        array $data = []
    ){
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
        // Custom
        $this->configHelper = $configHelper;
        $this->freightEstimates = $freightEstimates;
        $this->rateMethodFactory = $rateMethodFactory;
        $this->rateResultFactory = $rateResultFactory;
    }
    
    public function getAllowedMethods()
    {
        return [self::CARRIER_CODE => $this->getConfigData('name')];
    }

    public function getAvailableRates()
    {
        if(!$this->availableRates) {
            $this->availableRates = $this->freightEstimates->getAvailableRates();
        }
        return $this->availableRates;
    }
    
    public function collectRates(RateRequest $request)
    {
        /** @var RateResult $result */
        $result = $this->rateResultFactory->create();
        
        try {
            
            foreach ($this->getAvailableRates() as $rateCode => $rate) {
                
                /** @var ResultMethod $method */
                $method = $this->rateMethodFactory->create();
                
                $method->setCarrier($this->_code);
                //$method->setCarrierTitle($rate['carrier_title']);

                $method->setCarrierTitle($this->getConfigData('title'));
                
                $method->setMethod($rateCode);
                $method->setMethodTitle($rate['title']);
                
                $method->setPrice($rate['price']);
                $method->setCost($rate['price']);
                
                $result->append($method);
            }
        
        } catch (\Exception $e) {
//throw new CouldNotSaveException(__('Cannot set shipping method. %1', $e->getMessage()));
            /** @var \Magento\Quote\Model\Quote\Address\RateResult\Error $error */
            $error = $this->_rateErrorFactory->create(
                [
                    'data' => [
                        'carrier' => $this->_code,
                        'carrier_title' => $this->getConfigData('title'),
                        'error_message' => $this->getConfigData('default_error_msg'),
                    ],
                ]
            );
            $result->append($error);
            
        }
        
        return $result;
    }
    
    public function isTrackingAvailable()
    {
        return true;
        // TODO: Implement isTrackingAvailable() method.
    }
    
}
