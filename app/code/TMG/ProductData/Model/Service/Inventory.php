<?php

namespace TMG\ProductData\Model\Service;

use TMG\Base\Model\Soap\Client;
use TMG\ProductData\Exception\InventoryServiceException;

class Inventory extends Client
{
    
    protected $xmlConfigPathSoapWsdlUrl = 'tmg_product_data/inventory_service/wsdl_url';
    
    protected $xmlConfigPathWsVersion = 'tmg_product_data/inventory_service/ws_version';

    public function getWsVersion()
    {
        return $this->scopeConfig->getValue($this->xmlConfigPathWsVersion);
    }
    
    /**
     * @param $sku
     * @return array
     */
    public function getProductVariationsStock($sku)
    {
        $result = [];
        foreach($this->doGetInventoryLevelsRequest($sku) as $id => $data) {
            
            $this->logger->warning($sku);
            $this->logger->warning(print_r($data,true));
//            $result[$id] = isset($data['quantityAvailable']) ? $data['quantityAvailable'] : 0;
//            if ($id == $sku) {
//                $result['all'] = isset($data['quantityAvailable']) ? $data['quantityAvailable'] : 0;;
//            }
            
            $msg = (isset($data['customProductMessage'])) ? $data['customProductMessage']: '';
            $qty = (isset($data['quantityAvailable'])) ? $data['quantityAvailable'] : 0;
            $result[$id] = [
                'qty' => $qty == 0? $msg: $qty,
                'message' => ''
            ];
            
            if ($id == $sku) {
                $result['all'] = [
                    'qty' => $qty == 0? $msg: $qty,
                    'message' => ''
                ];
            }
        }
        return $result;
    }



    /**
     * @param $sku
     * @return array
     */
    public function getProductVariationsSimpleStock($sku)
    {
        $result = [];
        foreach($this->doGetInventorySimpleLevelsRequest($sku) as $id => $data) {
            return $data && isset($data->quantityAvailable) ? $data->quantityAvailable : 0;
        }
        return -1;
    }

    public function doGetInventorySimpleLevelsRequest($sku)
    {
        $data = [
            'wsVersion' => (string)$this->getWsVersion(),
            'id' => $this->getApiUser(),
            'password' => $this->getApiPass(),
            'productID' => $sku,
        ];

        // Cache Implementation
        $cacheKey = $this->getCacheRequestKey('GetInventoryLevels',$data);

        if(!$result = $this->getCacheRequest($cacheKey)) {
            // Load & Save to cache
            $result = $this->parseGetInventorySimpleLevelsResponse($this->call('GetInventoryLevels',$data));
            $this->saveCacheRequest($cacheKey,$result);
        }

        return $result;

    }
    /**
     * @param $rawResponse
     * @return mixed
     * @throws InventoryServiceException
     */
    public function parseGetInventorySimpleLevelsResponse($rawResponse)
    {
        $response = [];
        if(empty($rawResponse->ProductCompanionInventoryArray) && empty($rawResponse->ProductVariationInventoryArray)) {
            $message = $this->isDebugMode() ? 'Invalid Response Data - ProductCompanionInventoryArray not present.' : $this->getDefaultErrorMessage();
            //throw new InventoryServiceException($message);
            return $response;
        }
        if(empty($rawResponse->ProductCompanionInventoryArray->ProductCompanionInventory) && empty($rawResponse->ProductVariationInventoryArray->ProductVariationInventory)) {
            $message = $this->isDebugMode() ? 'Invalid Response Data - ProductCompanionInventory not present.' : $this->getDefaultErrorMessage();
            //throw new InventoryServiceException($message);
            return $response;
        }

        $variationInventory = isset($rawResponse->ProductCompanionInventoryArray->ProductCompanionInventory) ? $rawResponse->ProductCompanionInventoryArray->ProductCompanionInventory : $rawResponse->ProductVariationInventoryArray->ProductVariationInventory;
        // HANDLE A RESPONSE IS SINGLE VARIATION
        if(is_object($variationInventory)) {
            $variationInventory = [$variationInventory];
        }

        return $variationInventory;
    }
    
    public function doGetInventoryLevelsRequest($sku)
    {
        $data = [
            'wsVersion' => (string)$this->getWsVersion(),
            'id' => $this->getApiUser(),
            'password' => $this->getApiPass(),
            'productID' => $sku,
        ];
        
        // Cache Implementation
        $cacheKey = $this->getCacheRequestKey('GetInventoryLevels',$data);
    
        if(!$result = $this->getCacheRequest($cacheKey)) {
            // Load & Save to cache
            $result = $this->parseGetInventoryLevelsResponse($this->call('GetInventoryLevels',$data));
            $this->saveCacheRequest($cacheKey,$result);
        }
    
        return $result;
        
    }
    
    /**
     * @param $rawResponse
     * @return mixed
     * @throws InventoryServiceException
     */
    public function parseGetInventoryLevelsResponse($rawResponse)
    {
        $response = [];

        if(!empty($rawResponse->errorMessage)) {
            $ex = new \Exception($rawResponse->errorMessage);
            $this->logException($ex);
            $message = $this->isDebugMode() ? $ex->getMessage() : $this->getDefaultErrorMessage();
            throw new InventoryServiceException($message,'001',$ex);
        }
        
        if(empty($rawResponse->ProductVariationInventoryArray)) {
            $message = $this->isDebugMode() ? 'Invalid Response Data - ProductVariationInventoryArray not present.' : $this->getDefaultErrorMessage();
            throw new InventoryServiceException($message);
        }
        if(empty($rawResponse->ProductVariationInventoryArray->ProductVariationInventory)) {
            $message = $this->isDebugMode() ? 'Invalid Response Data - ProductVariationInventory not present.' : $this->getDefaultErrorMessage();
            throw new InventoryServiceException($message);
        }

        $variationInventory = $rawResponse->ProductVariationInventoryArray->ProductVariationInventory;
        // HANDLE A RESPONSE IS SINGLE VARIATION
        if(is_object($variationInventory)) {
            $variationInventory = [$variationInventory];
        }
        foreach($variationInventory  as $stockData) {
//            var_dump($variationInventory);
            $response[$stockData->partID] = (array)$stockData;
        }
        
        return $response;
    }
    
}