<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 4/9/18
 * Time: 12:43
 */

namespace TMG\ProductData\Plugin\ConfigurableProduct\Block\Product\View\Type;

use \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable;
use Magento\Framework\Json\DecoderInterface;
use Magento\Framework\Json\EncoderInterface;
use Psr\Log\LoggerInterface;
use TMG\ProductData\Exception\InventoryServiceException;
use TMG\ProductData\Model\Service\Inventory as InventoryService;

class ConfigurablePlugin
{
    /**
     * @var LoggerInterface
     */
    protected $logger;
    
    /**
     * @var EncoderInterface
     */
    protected $jsonEncoder;
    
    /**
     * @var DecoderInterface
     */
    protected $jsonDecoder;

    protected $customerHelper;

    protected $custSession;

    protected $customerRepository;
    
    /**
     * @var
     */
    public $inventoryService;
    
    public function __construct(
        EncoderInterface $jsonEncoder,
        DecoderInterface $jsonDecoder,
        InventoryService $inventoryService,
        LoggerInterface $logger,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository ,
        \Magento\Customer\Model\Session $custSession,
        \TMG\Customer\Helper\Customer $customerHelper,
        \Magento\Framework\App\Request\Http $request
    )
    {
        $this->jsonEncoder          = $jsonEncoder;
        $this->jsonDecoder          = $jsonDecoder;
        $this->inventoryService     = $inventoryService;
        $this->logger               = $logger;
        $this->customerHelper       = $customerHelper;
        $this->custSession          = $custSession; 
        $this->customerRepository   = $customerRepository; 
        $this->_request             = $request;
    }
    
    
    public function afterGetJsonConfig(Configurable $subject, $result)
    {
        $inventoryData = [];
        $skuMapping = [];
        
        // Product
        $product = $subject->getProduct();
        if(!$sku = $product->getSku()) {
            // No Product
            return $result;
        }
        if ($this->_request->getFullActionName() == 'catalog_category_view' || $this->_request->getFullActionName() == 'catalogsearch_result_index') {
            return $result;
        }
        $config = $this->jsonDecoder->decode($result);
        // Loading Data
        $tmg_encrypt_account                = false;
        if($this->custSession->isLoggedIn()) 
        {
            try 
            {
                $inventoryData = $this->inventoryService
                    ->getProductVariationsStock($sku);
                // SKU Mapping
                $allowedProducts = $subject->getAllowProducts();
                foreach ($allowedProducts as $allowedProduct) 
                {
                    $skuMapping[$allowedProduct->getId()] = $allowedProduct->getSku();
                }                
                $config['tmg_sku_mapping']          = $skuMapping;
                $config['tmg_inventory']            = $inventoryData;
                $customerId                         = $this->custSession->getCustomerId();
                if($customerId){
                    $customer                       = $this->customerRepository->getById($customerId);
                    $encrypt_account                = $customer->getCustomAttribute('tmg_encrypt_account');
                    if($encrypt_account)
                    {
                        $tmg_encrypt_account        = true;
                    }
                }
            } catch (InventoryServiceException $e) {
                $this->logger->critical($e);
            } catch (\Exception $e) {
                $this->logger->critical($e);
            }
        } 
        else 
        {
            $tmg_encrypt_account                = false;    
        }
        // Inventory Data        
        $config['tmg_inventory_visible']    = $tmg_encrypt_account;                
        return $this->jsonEncoder->encode($config);        
    }
    
}