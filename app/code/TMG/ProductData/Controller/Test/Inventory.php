<?php

namespace TMG\ProductData\Controller\Test;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Action\Context;

use TMG\Base\Controller\Test;
use TMG\ProductData\Exception\InventoryServiceException;
use TMG\ProductData\Model\Service\Inventory as InventoryService;

class Inventory extends Test
{
    /**
     * @var InventoryService
     */
    protected $inventoryService;
    
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;
    
    public function __construct(
        Context $context,
        InventoryService $inventoryService,
        ProductRepositoryInterface $productRepository
    )
    {
        parent::__construct($context);
        $this->inventoryService = $inventoryService;
        $this->productRepository = $productRepository;
    }
    
    public function execute()
    {
        // Param
        if(!$sku = $this->getRequest()->getParam('sku',false)) {
            if(!$id = $this->getRequest()->getParam('id',false)) {
                echo 'NO ID OR SKU'; die();
            }
            $sku = $this->productRepository->getById($id)->getSku();
        }
        
        try {
            
            echo '<pre>';
            $stockData = $this->inventoryService
                ->getProductVariationsStock($sku);
            print_r("\nRESULT:\n");
            print_r($stockData);
            echo '</pre>';

        } catch ( InventoryServiceException $e) {
        
        
        
        } catch (\Exception $e) {

            print_r("\nERROR:\n");
            echo "<h3>{$e->getMessage()}</h3>";

            echo '<pre>';
            print_r("\nDEBUG:\n");
            print_r("\nREQUEST:\n");
            print_r($this->inventoryService->getRequestString(true,false));
            print_r("\nRESPONSE:\n");
            print_r($this->inventoryService->getResponseString(true,false));
            echo '</pre>';

        }
        
        
        die("\n" . __METHOD__ . "\n");
    }
}