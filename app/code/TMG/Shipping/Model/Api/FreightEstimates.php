<?php

namespace TMG\Shipping\Model\Api;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\ConfigurableProduct\Model\Product\Type\ConfigurableFactory;
use Magento\Checkout\Model\Session as CheckoutSession;

use Psr\Log\LoggerInterface;

use TMG\Base\Model\Soap\Client as SoapClient;
use TMG\Shipping\Helper\Config as ConfigHelper;
use TMG\Shipping\Model\NoRatesException;
use TMG\PricingKey\Helper\PricingKey as PricingKeyHelper;


class FreightEstimates extends SoapClient
{
    protected $xmlConfigPathSoapClientOptions = 'tmg_shipping/freight_estimates/soap_options';
    
    protected $xmlConfigPathSoapWsdlUrl = 'tmg_shipping/freight_estimates/wsdl_url';
    
    protected $xmlConfigPathDebugMode = 'tmg_shipping/freight_estimates/debug_mode';
    
    protected $xmlConfigPathErrorMessage = 'carriers/tmgshipping/default_error_msg';
    
    /**
     * @var ConfigHelper
     */
    protected $configHelper;
    
    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;
    
    /**
     * @var CustomerRepository
     */
    protected $customerRepository;
    
    
    
    protected $allowedProviders = [
        'UPS',
        'FedEx',
    ];
    
    /**
     * @var array
     */
    protected $requests = [];
    
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger,
        AppState $state,
        ConfigHelper $configHelper,
        CacheInterface $cache,
        CheckoutSession $checkoutSession,
        CustomerRepository $customerRepository,
        array $data = []
    ){
        parent::__construct($scopeConfig, $logger, $state, $cache, $data);
        
        // CONFIG
        $this->configHelper = $configHelper;
        // SESSION
        $this->checkoutSession = $checkoutSession;
        // CUSTOMER
        $this->customerRepository = $customerRepository;
    }
    
    /**
     * @return ConfigHelper
     */
    public function getConfigHelper()
    {
        return $this->configHelper;
    }
    
    /**
     * @return Quote
     */
    public function getQuote()
    {
        return $this->checkoutSession->getQuote();
    }
    
    public function isDebugMode()
    {
        return (bool)$this->scopeConfig->getValue($this->xmlConfigPathDebugMode);
    }
    
    /******************************************************************************************************************/
    /************************************************************************************************** SOAP STUFF ****/
    /******************************************************************************************************************/
    
    /**
     * @param $params
     * @return array|mixed|null|string
     * @throws LocalizedException
     * @throws \Exception
     */
    public function doGetFreightDataRequest($params)
    {
        // Format Order
        $data = [
            'req' => [
                'DestinationAddress' => $params['DestinationAddress'],
                'Id' => $this->getApiUser(),
                'Item' => $params['Item'],
                'Password' => $this->getApiPass(),
                'Provider' =>  $params['Provider'],
            ]
        ];
        
        // Cache Implementation
        $cacheKey = $this->getCacheRequestKey('GetFreightData',$data);
    
        if(!$result = $this->getCacheRequest($cacheKey)) {
            // Load & Save to cache
            $result = $this->parseGetFreightDataResponse($this->call('GetFreightData',$data));
            $this->saveCacheRequest($cacheKey,$result);
        }
        
        return $result;
        
    }
    
    /**
     * @param $rawResponse
     * @return array
     * @throws LocalizedException
     */
    protected function parseGetFreightDataResponse($rawResponse)
    {
        $result = [];
        $response = $rawResponse->GetFreightDataResult;
        $freightEstimate = $response->FreightEstimate;
        
        // Error Case
        if($freightEstimate === null) {
            // Log
            $ex = new LocalizedException(__($response->ErrorMessage . ' | ' . $response->ErrorMessageDetail));
            $this->logger->critical($ex);
            // Redefine for User Message
            if(!$this->isDebugMode()) {
                $ex = new LocalizedException(__($this->getDefaultErrorMessage()));
            }
            throw $ex;
        }
        
        // Success Case
        foreach ($freightEstimate->FreightEstimate as $row) {
            $result[] = (array)$row;
        }
        
        return $result;
        
    }
    
    
    /******************************************************************************************************************/
    /************************************************************************************************* QUOTE STUFF ****/
    /******************************************************************************************************************/
    
    /**
     * @param Quote|null $quote
     * @param bool $forceReload
     * @return array
     * @throws LocalizedException
     * @throws NoRatesException
     * @throws \Exception
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAvailableRates(Quote $quote = null, $forceReload = false)
    {
        $result = [];
        $rates = [];
        $params = [];
        
        $quote = ($quote) ?: $this->getQuote();
        
        // Validate Items
        if(!$items = $this->getShippableItems($quote)) {
            throw new NoRatesException(__('Cart is Empty'));
        }
        
        // Validate Address
        if(!$addressData = $this->getDestinationAddressParams($quote)) {
            throw new NoRatesException(__('No Address Data Available'));
        }
        
        // Address
        $params['DestinationAddress'] = $addressData;
        
        // Providers LOOP
        foreach ($this->allowedProviders as $provider) {
            
            $params['Provider'] = [
                'string' => $provider
            ];
            
            // Items LOOP
            foreach ($this->getShippableItems($quote) as $item) {
                $params['Item'] = [
                    'ItemCode' => $item->getData(ConfigHelper::ITEM_ATTRIBUTE_ITEM_CODE),
                    'Quantity' => $item->getQty(),
                    'Thickness' => $item->getData(PricingKeyHelper::ITEM_ATTRIBUTE_PRICING_KEY),
                ];
                $rates[$provider][$item->getId()] = $this->doGetFreightDataRequest($params);
            }
            
            $result = $this->prepareTotalRates($rates);
            
        }
        
        return $result;
        
    }
    
    public function getNbAvailableRates($productSku, $productQty, $countryId, $regionId, $postCode,$thickness='')
    {
        $result = [];
        $rates  = [];
        $params = [];
        // Validate Address
        if (!$addressData = $this->getDestinationNbAddressParams($countryId,$postCode, $regionId)) {
            throw new NoRatesException(__('No Address Data Available'));
        }
        // Address
        $params['DestinationAddress'] = $addressData;

        // Providers LOOP
        foreach ($this->allowedProviders as $provider) {
            $params['Provider'] = [
                'string' => $provider
            ];
            // Items LOOP
            $params['Item'] = [
                'ItemCode' => $productSku,
                'Quantity' => $productQty,
                'Thickness' => $thickness,
            ];
            $result[$provider] = $this->doGetFreightDataRequest($params);
        }
        return $result;

    }

    /**
     * @param Quote $quote
     * @return array
     */
    protected function getShippableItems(Quote $quote)
    {
        return $quote->getAllVisibleItems();
    }
    
    /**
     * @param $rateData
     * @return string
     */
    public function getRateCode($rateData)
    {
        return mb_strtolower($rateData['Carrier']) . '---' . str_replace([' ','_'],'-', mb_strtolower($rateData['RateName']));
    }
    
    /**
     * @param $rateData
     * @return string
     */
    public function getRateTitle($rateData)
    {
        $title = ucwords(str_replace(['-','_','ups','fedex'],' ', mb_strtolower($rateData['RateDescription'])));
        return $rateData['Carrier'] . ' - ' . $title;
    }
    
    /**
     * @param Quote $quote
     * @return array
     * @throws LocalizedException
     * @throws NoRatesException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getDestinationAddressParams(Quote $quote)
    {
        $params = [];
        $address = $quote->getShippingAddress();
        $isCustomerAddress = false;
        
        // No Shipping Address Selected
        if(!$address->getPostcode() || !$address->getStreet()) {
            
            $address = null;
            
            // Get From Customer Default Shipping Address
            if ($customerId = $quote->getCustomerId()) {
                // Customer Case
                $customer = $this->customerRepository->getById($customerId);
                foreach ($customer->getAddresses() as $customerAddress) {
                    if ($customerAddress->getId() == $customer->getDefaultShipping()) {
                        $isCustomerAddress = true;
                        $address = $customerAddress;
                        break;
                    }
                }
            }
            
            if (null === $address) {
                throw new NoRatesException(__('No Shipping Address Selected'));
            }
            
        }
        
        // Street parsing
        // @todo - Check Max Length
        $streetLineId = 1;
        $streetMaxLines = 2;
        foreach ($address->getStreet() as $streetLine) {
            if($streetLineId > $streetMaxLines) {
                break;
            }
            $params['Address' . $streetLineId ] = $streetLine;
            $streetLineId++;
        }
        
        $params = array_merge($params, [
            'City' => $address->getCity(),
            'Country' => $address->getCountryId(),
            'Zip' => $address->getPostcode(),
            'State' => ($isCustomerAddress) ? $address->getRegion()->getRegionCode() : $address->getRegionCode(),
        ]);
        
        return $params;
    }

    protected function getDestinationNbAddressParams($countryId, $postCode, $regionId)
    {
        $params = [
            'City' => 'United States',
            'Country' => $countryId,
            'Zip' => $postCode,
            'State' => $regionId
        ];
        return $params;
    }
    
    /**
     * @param $singleRates
     * @return array
     */
    public function prepareTotalRates($singleRates)
    {
        
        $itemsCount = count($this->getShippableItems($this->getQuote()));
        $rates = [];
        
        // Prepare Items
        foreach ($singleRates as $provider => $item ) {
            
            foreach ($item as $itemId => $singleRates ) {
                foreach ($singleRates as $singleRate ) {
                    
                    $code = $this->getRateCode($singleRate);
                    $title = $this->getRateTitle($singleRate);
                    
                    // Preset
                    if(!isset($rates[$code])) {
                        $rates[$code] = [
                            'carrier_title' => $provider,
                            'code' => $code,
                            'title' => $title,
                            'price' => 0,
                            'weight' => 0,
                            'item_rates' => []
                        ];
                    }
                    
                    // Add - Single Data
                    $rates[$code]['item_rates'][$itemId] = $singleRate;
                    $rates[$code]['price'] += $singleRate['ListCharge'];
                    $rates[$code]['weight'] += $singleRate['BillingWeight'];
                    
                }
            }
            
        }
        
        // PURGE NON COMMON METHODS
        foreach ($rates as $rateCode => $rateData) {
            if(count($rateData['item_rates']) < $itemsCount) {
                unset($rates[$rateCode]);
            }
        }
        
        return $rates;
        
    }
    
    
    /******************************************************************************************************************/
    /************************************************************************************************ HELPER STUFF ****/
    /******************************************************************************************************************/
    
    /**
     * @param $code
     * @param Quote|null $quote
     * @return null
     * @throws LocalizedException
     * @throws NoRatesException
     * @throws \Exception
     */
    public function setShippingRateToItems($code, Quote $quote = null)
    {
        $quote = $quote ?: $this->getQuote();
        $rates = $this->getAvailableRates($quote);
        
        // Code Validation
        if(!isset($rates[$code])) {
            return;
        }
        
        $rate = $rates[$code];
        foreach ($this->getShippableItems($quote) as $item) {
            
            /** @var $item QuoteItem */
            if(!isset($rate['item_rates'][$item->getId()])) {
                continue;
            }
            $item->setData(ConfigHelper::ITEM_ATTRIBUTE_SHIPPING_RATE, serialize($rate['item_rates'][$item->getId()]))
                // Save?
                ->save();
        }
    }
    
    /**
     * @param $code
     * @return bool
     */
    public function isInternalCode($code)
    {
        $parts = explode('---',$code);
        if(count($parts) > 1) {
            foreach ($this->allowedProviders as $provider) {
                if(strtolower($parts[0]) == strtolower($provider)) {
                    return true;
                }
            }
        }
        return false;
    }
    
    
    
}