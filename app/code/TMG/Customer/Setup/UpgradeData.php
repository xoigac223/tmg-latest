<?php

namespace TMG\Customer\Setup;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use MauroNigrele\SetupTools\Model\Setup\CatalogInstaller;
use MauroNigrele\SetupTools\Model\Setup\CmsInstaller;
use MauroNigrele\SetupTools\Model\Setup\CustomerInstaller;
use MauroNigrele\SetupTools\Model\Setup\Installer;
use MauroNigrele\SetupTools\Model\Setup\SalesInstaller;
use MauroNigrele\SetupTools\Model\Setup\StoreInstaller;
use Psr\Log\LoggerInterface;

use TMG\Customer\Helper\Customer as CustomerHelper;
use TMG\Customer\Helper\Config as ConfigHelper;

class UpgradeData extends Installer implements UpgradeDataInterface
{
    
    public function __construct(
        ObjectManagerInterface $objectManager,
        Registry $registry,
        LoggerInterface $logger,
        ScopeConfigInterface $config,
        WriterInterface $configWriter,
        CatalogInstaller $catalogInstaller,
        CmsInstaller $cmsInstaller,
        CustomerInstaller $customerInstaller,
        SalesInstaller $salesInstaller,
        StoreInstaller $storeInstaller
    ){
        parent::__construct($objectManager, $registry, $logger, $config, $configWriter, $catalogInstaller,
            $cmsInstaller, $customerInstaller, $salesInstaller, $storeInstaller);
    }
    
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->setModuleDataSetup($setup)
            ->allowRemoveAction()
            ->setAreaCode('adminhtml');
    
        if (version_compare($context->getVersion(), '1.0.0') < 0) {
            // Cleanup
            // Attributes Install
            $this->installCustomerAttributes()
                ->installAddressAttributes();
        }
        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            $this->getCustomerInstaller()->deleteAllCustomers();
        }
        
    }
    
    /**
     * @var CustomerHelper
     */
    protected $customerHelper;
    
    /**
     * @var ConfigHelper
     */
    protected $configHelper;
    
    /**
     * @return mixed|CustomerHelper
     */
    public function getCustomerHelper()
    {
        if(!isset($this->customerHelper)) {
            $this->customerHelper = $this->objectManager->get(CustomerHelper::class);
        }
        return $this->customerHelper;
    }
    
    /**
     * @return mixed|ConfigHelper
     */
    public function getConfigHelper()
    {
        if(!isset($this->configHelper)) {
            $this->configHelper = $this->objectManager->get(ConfigHelper::class);
        }
        return $this->configHelper;
    }
    
    public function installCustomerAttributes()
    {
        $attributesSkeleton = [
            'type'              => 'varchar',
            'input'             => 'text',
            'required'          => false,
            'visible'           => true,
            'position'          => 1000,
            'user_defined'      => 0,
            'system'            => 0,
        ];
        
//        var_dump($this->getConfigHelper()->getCustomCustomerAttributesConfig()); die();
        
        foreach ($this->getConfigHelper()->getCustomCustomerAttributesConfig() as $attributeCode => $attributeData) {
            
//            var_dump($attributeCode);
//            continue;
            
            try {
                $data = array_merge($attributesSkeleton,$attributeData);
                $this->getCustomerInstaller()
                    ->addCustomerAttribute($attributeCode,$data);
            } catch (\Exception $e) {
                echo "\n Error creating Customer Attribute: $attributeCode\n";
                $this->logger->critical($e);
                die();
//                throw $e;
            }
        }
        
        return $this;
        
    }
    
    public function installAddressAttributes()
    {
        $attributesSkeleton = [
            'type'              => 'varchar',
            'input'             => 'text',
            'required'          => false,
            'visible'           => true,
            'position'          => 1000,
            'user_defined'      => 0,
            'system'            => 0,
        ];
        
        foreach ($this->getConfigHelper()->getCustomAddressAttributesConfig() as $attributeCode => $attributeData) {
    
//            var_dump($attributeCode);
//            continue;
    
            try {
                $data = array_merge($attributesSkeleton,$attributeData);
                $this->getCustomerInstaller()
                    ->addAddressAttribute($attributeCode,$data);
            } catch (\Exception $e) {
                $message = "\n Error creating Address Attribute: $attributeCode > " . $e->getMessage() . "\n";
                echo $message;
                die();
                
                
                
                
                
                
                
                
                
                
                
                
                
                
                $this->logger->critical($e);
//                throw $e;
            }
            
        }
        
        return $this;
        
    }
}