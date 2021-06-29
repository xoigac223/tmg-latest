<?php

namespace TMG\Base\Model\Soap;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\State as AppState;


class Client extends DataObject
{
   
    /**
     * @var array
     */
    protected $logColors = [
        'req_header' => "\033[1;92m",
        'req_body' => "\033[0;92m",
        'res_header' => "\033[1;33m",
        'res_body' => "\033[0;93m",
        'reset' => "\033[0m",
    ];
    
    /**
     * @var Zend_Soap_Client | Zend_Soap_Client_DotNet | Zend_Soap_Client_Local
     *
     * @see http://framework.zend.com/manual/1.12/en/zend.soap.client.html
     */
    protected $soapClient;
    
    /**
     * Soap Client Tipe
     *
     * Available Types:
     * 'standar':        SoapClient
     * 'standar-wcf':    SoapClient
     * 'zend-standard':  Zend_Soap_Client
     * 'zend-local':     Zend_Soap_Client_Local
     * 'zend-dot-net':   Zend_Soap_Client_DotNet
     *
     * @var string
     * @see http://framework.zend.com/manual/1.12/en/zend.soap.client.html
     *
     */
    protected $soapClientType = 'standard';
    
    /**
     * @var LoggerInterface
     */
    protected $logger;
    
    /**
     * @var AppState
     */
    protected $state;
    
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    
    /**
     * @var string
     */
    protected $xmlConfigPathApiUser = 'tmg_base/api_global/api_user';
    
    /**
     * @var string
     */
    protected $xmlConfigPathApiPass = 'tmg_base/api_global/api_pass';
    
    /**
     * @var string
     */
    protected $xmlConfigPathErrorMessage = 'tmg_base/api_global/error_msg';
    
    /**
     * @var string
     */
    protected $xmlConfigPathSoapClientOptions = 'tmg_base/api_global/soap_options';
    
    /**
     * Config path node to wsdl url configuration (must be implemented in child class)
     * @var string
     */
    protected $xmlConfigPathSoapWsdlUrl;
    
    /**
     * @var CacheInterface
     */
    protected $cache;
    
    /**
     * @var int
     */
    protected $cacheLifetime = 3600;
    
    /**
     * @var array
     */
    protected $cacheTags = [
        'WEBSERVICE',
        'INTEGRATION',
        'INTEGRATION_API_CONFIG',
    ];
    
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger,
        AppState $state,
        CacheInterface $cache,
        array $data = []
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->state = $state;
        $this->logger = $logger;
        $this->cache = $cache;
        parent::__construct($data);
    }
    
    /******************************************************************************************************************/
    /************************************************************************************************ CONFIG STUFF ****/
    /******************************************************************************************************************/
    
    public function getApiUser()
    {
        return $this->scopeConfig->getValue($this->xmlConfigPathApiUser);
    }
    
    public function getApiPass()
    {
        return $this->scopeConfig->getValue($this->xmlConfigPathApiPass);
    }
    
    public function getDefaultErrorMessage()
    {
        return $this->scopeConfig->getValue($this->xmlConfigPathErrorMessage);
    }
    
    /**
     * @return bool
     */
    public function isDebugMode()
    {
        return $this->state->getMode() === AppState::MODE_DEVELOPER;
    }
    
    public function isStandard()
    {
        return ($this->getClient() instanceof \SoapClient);
    }
    
    /**
     * @param $method
     * @param $params
     * @return mixed|null
     * @throws \Exception
     */
    public function call($method,$params)
    {
        $result = null;
        try {
            
            $result = call_user_func(array($this->getClient(),$method),$params);
            
            // Common Parser
            $result = $this->parseSoapResponse($result);
            
            // Log
            $this->logRequest()
                ->logResponse();
            
        } catch (\SoapFault $e) {
            $this->logSoapFault($e);
        } catch (\Zend_Soap_Client_Exception $e) {
            $this->logSoapException($e);
        } catch (\Exception $e) {
            $this->logException($e);
        }
        
        return $result;
        
    }
    
    /**
     * Common Parser
     *
     * @param mixed $response
     * @return mixed
     */
    protected function parseSoapResponse($response)
    {
        return $response;
    }
    
    /**
     * @return mixed
     * @throws LocalizedException
     */
    protected function getWsdlUrl()
    {
        $url = $this->scopeConfig->getValue($this->xmlConfigPathSoapWsdlUrl);
        if (!$url) {
            throw new LocalizedException(__('WSDL not set.'));
        }
        return $url;
    }
    
    /**
     * Return an array with soap client configured options
     * @return array
     */
    protected function getSoapClientOptions()
    {
        $options = $this->scopeConfig->getValue($this->xmlConfigPathSoapClientOptions);
        $options['trace'] = $this->isDebugMode();
        return (is_array($options)) ? $options : [];
    }
    
    /**
     * Returns the soap client type
     * @return string
     */
    protected function getSoapClientType()
    {
        return $this->soapClientType;
    }
    
    protected function getClient()
    {
        if (!$this->soapClient) {
            
            $wsdl = $this->getWsdlUrl();
            $type = $this->getSoapClientType();
            $options = $this->getSoapClientOptions();
            
            switch($type) {
                case 'standard':
                    // Standard Clientxx
                    if (!isset($options['trace'])) {
                        $options['trace'] = true;
                    }
                    $this->soapClient = new \SoapClient($wsdl,$options);
                    break;
                // Basic Client
                case 'zend':
                    $this->soapClient = new \Zend_Soap_Client($wsdl,$options);
                    break;
                // Local Server Client
                case 'zend-local':
                    $this->soapClient = new \Zend_Soap_Client_Local($wsdl,$options);
                    break;
                // ASPX Server Client
                case 'zend-dot-net':
                    $this->soapClient = new \Zend_Soap_Client_DotNet($wsdl,$options);
                    break;
                default:
                    throw new LocalizedException(__('Invalid Soap Client Type: "%1".', $type));
                    break;
            }
            
            // Debug Options
            if ($this->isDebugMode() && $this->soapClient instanceof Zend_Soap_Client) {
                $this->soapClient
                    ->setWsdlCache(null);
            }
        }
        return $this->soapClient;
    }
    
   
    /******************************************************************************************************************/
    /**************************************************************************************** CACHE IMPLEMENTATION ****/
    /******************************************************************************************************************/
    
    /**
     * @param $key
     * @param $data
     * @return $this
     */
    protected function saveCacheRequest($key,$data)
    {
        $this->cache->remove($key);
        $this->cache->save(
            serialize($data),
            $key,
            $this->getCacheTags(),
            $this->getCacheLifetime()
        );
        return $this;
    }
    
    protected function getCacheRequest($key)
    {
        if(isset($this->requests[$key])) {
            return $this->requests[$key];
        }
        if($request = $this->cache->load($key)) {
            $this->requests[$key] = unserialize($request);
            return $this->requests[$key];
        }
        return null;
    }
    
    protected function getCacheRequestKey($method, $params)
    {
        $key = md5(serialize($params) . $method);
        return $key;
    }
    
    protected function getCacheTags()
    {
        return $this->cacheTags;
    }
    
    protected function getCacheLifetime()
    {
        return $this->cacheLifetime;
    }
    
    
    /******************************************************************************************************************/
    /************************************************************************************** DEBUG & TRACING STUFF  ****/
    /******************************************************************************************************************/
    
    /**
     * @return $this
     */
    protected function logRequest()
    {
        $this->logger->debug($this->getRequestString());
        return $this;
    }
    
    /**
     * @return $this
     */
    protected function logResponse()
    {
        $this->logger->debug($this->getResponseString());
        return $this;
    }
    
    /**
     * @param \Exception $e
     * @return $this
     * @throws \Exception
     */
    protected function logException(\Exception $e)
    {
        $this->errorHandler($e);
        return $this;
    }
    
    /**
     * @param \SoapFault $fault
     * @return $this
     * @throws \Exception
     */
    protected function logSoapFault(\SoapFault $fault)
    {
        $this->errorHandler($fault);
        return $this;
    }
    
    /**
     * @param \Zend_Soap_Client_Exception $e
     * @return $this
     * @throws \Exception
     */
    protected function logSoapException(\Zend_Soap_Client_Exception $e)
    {
        $this->errorHandler($e);
        return $this;
    }
    
    /**
     * @param \Exception $e
     * @return $this
     * @throws \Exception
     */
    protected function errorHandler(\Exception $e)
    {
        if ($this->soapClient) {
            $this->logRequest()
                ->logResponse();
        }
        if ($this->isDebugMode()) {
            throw $e;
        }
        // Standard
        $this->logger->critical($e);
        return $this;
    }
    
    
    public function getRequestString($forHtml = false, $formatted = true)
    {
        $headers = ($this->isStandard()) ? $this->getClient()->__getLastRequestHeaders()
            : $this->getClient()->getLastRequestHeaders();
        
        $xml = ($this->isStandard()) ? $this->getClient()->__getLastRequest()
            : $this->getClient()->getLastRequest();
        
        $result = str_repeat('-', 70);
        $result .= $this->logColors['req_header'];
        $result .= "\nREQUEST HEADER:\n";
        $result .= (!$formatted) ? $headers : $this->xmlFormat($headers);
        $result .= $this->logColors['req_body'];
        $result .= "\n\nREQUEST BODY:\n";
        $result .= (!$formatted) ? $xml : $this->xmlFormat($xml);
        $result .= $this->logColors['reset'];
        
        if($forHtml) {
            $result = nl2br($result);
        }
        return $result;
    }
    
    public function getResponseString($forHtml = false, $formatted = true)
    {
        $headers = ($this->isStandard()) ? $this->getClient()->__getLastResponseHeaders()
            : $this->getClient()->getLastResponseHeaders();
        
        $xml = ($this->isStandard()) ? $this->getClient()->__getLastResponse()
            : $this->getClient()->getLastResponse();
        
        $result = str_repeat('-', 70);
        $result .= $this->logColors['res_header'];
        $result .= "\nRESPONSE HEADER:\n";
        $result .= (!$formatted) ? $headers : $this->xmlFormat($headers);
        $result .= $this->logColors['res_body'];
        $result .= "\n\nRESPONSE BODY:\n";
        $result .= (!$formatted) ? $xml : $this->xmlFormat($xml);
        $result .= $this->logColors['reset'];
        
        if($forHtml) {
            $result = nl2br($result);
        }
        return $result;
    }
    
    public function xmlFormat($xmlString)
    {
        $outputString = "";
        $previousBitIsCloseTag = false;
        $previousBitIsSimplifiedTag = false;
        $indentLevel = 0;
        $bits = explode("<", $xmlString);
        $prefix = '';
        foreach ($bits as $bit) {
            $bit = trim($bit);
            if (!empty($bit)) {
                if ($bit[0] == "/") {
                    $isCloseTag = true;
                } else {
                    $isCloseTag = false;
                }
                if (strstr($bit, "/>")) {
                    $prefix = "\n" . str_repeat("  ", $indentLevel);
                    $previousBitIsSimplifiedTag = true;
                } else {
                    if (!$previousBitIsCloseTag and $isCloseTag) {
                        if ($previousBitIsSimplifiedTag) {
                            $indentLevel--;
                            $prefix = "\n" . str_repeat(" ", $indentLevel);
                        } else {
                            $prefix = "";
                            $indentLevel--;
                        }
                    }
                    if ($previousBitIsCloseTag and !$isCloseTag) {
                        $prefix = "\n" . str_repeat("  ", $indentLevel);
                        $indentLevel++;
                    }
                    if ($previousBitIsCloseTag and $isCloseTag) {
                        $indentLevel--;
                        $prefix = "\n" . str_repeat("  ", $indentLevel);
                    }
                    if (!$previousBitIsCloseTag and !$isCloseTag) { {
                        $prefix = "\n" . str_repeat("  ", $indentLevel);
                        $indentLevel++;
                    }
                    }
                    $previousBitIsSimplifiedTag = false;
                }
                $outputString .= $prefix . "<" . $bit;
                $previousBitIsCloseTag = $isCloseTag;
            }
        }
        return $outputString;
    }
    
    
}