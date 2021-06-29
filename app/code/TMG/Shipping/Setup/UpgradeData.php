<?php

namespace TMG\Shipping\Setup;

use Magento\Eav\Setup\EavSetupFactory;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

use Magento\Quote\Setup\QuoteSetup;
use Magento\Sales\Setup\SalesSetup;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Quote\Setup\QuoteSetupFactory;

use TMG\Shipping\Helper\Config as ConfigHelper;

class UpgradeData implements UpgradeDataInterface
{
 
    protected $quoteSetupFactory;
    
    protected $salesSetupFactory;
    
    protected $configHelper;
    
    public function __construct(
        QuoteSetupFactory $quoteSetupFactory,
        SalesSetupFactory $salesSetupFactory,
        ConfigHelper $configHelper
    )
    {
        $this->quoteSetupFactory = $quoteSetupFactory;
        $this->salesSetupFactory = $salesSetupFactory;
        $this->configHelper = $configHelper;
    }
    
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        
        if (version_compare($context->getVersion(), '1.0.1') <= 0) {
            
            /** @var QuoteSetup $quoteSetup */
            $quoteSetup = $this->quoteSetupFactory->create(['setup' => $setup]);
            /** @var SalesSetup $salesSetup */
            $salesSetup = $this->salesSetupFactory->create(['setup' => $setup]);
    
            $attributeOptions = ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'visible' => false, 'required' => false];
        
            foreach ($this->configHelper->getItemAttributes() as $code) {
                $quoteSetup->addAttribute('quote_item', $code, $attributeOptions);
                $salesSetup->addAttribute('order_item', $code, $attributeOptions);
            }
            
        }
    
        if (version_compare($context->getVersion(), '1.1.0') <= 0) {
            $setup->getConnection()->dropColumn($setup->getTable('quote_item'), ConfigHelper::ITEM_ATTRIBUTE_PRICE_KEY);
            $setup->getConnection()->dropColumn($setup->getTable('sales_order_item'), ConfigHelper::ITEM_ATTRIBUTE_PRICE_KEY);
        }
        
    }
}
