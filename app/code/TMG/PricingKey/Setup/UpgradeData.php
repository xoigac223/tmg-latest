<?php

namespace TMG\PricingKey\Setup;

use Magento\Eav\Setup\EavSetupFactory;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

use Magento\Quote\Setup\QuoteSetup;
use Magento\Sales\Setup\SalesSetup;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Quote\Setup\QuoteSetupFactory;

use TMG\PricingKey\Helper\PricingKey as PricingKeyHelper;

class UpgradeData implements UpgradeDataInterface
{
 
    protected $quoteSetupFactory;
    
    protected $salesSetupFactory;
    
    protected $pricingKeyHelper;
    
    public function __construct(
        QuoteSetupFactory $quoteSetupFactory,
        SalesSetupFactory $salesSetupFactory,
        PricingKeyHelper $pricingKeyHelper
    )
    {
        $this->quoteSetupFactory = $quoteSetupFactory;
        $this->salesSetupFactory = $salesSetupFactory;
        $this->pricingKeyHelper = $pricingKeyHelper;
    }
    
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        
        if (version_compare($context->getVersion(), '1.1.0') <= 0) {
            /** @var QuoteSetup $quoteSetup */
            $quoteSetup = $this->quoteSetupFactory->create(['setup' => $setup]);
            /** @var SalesSetup $salesSetup */
            $salesSetup = $this->salesSetupFactory->create(['setup' => $setup]);
            $attributeOptions = ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'visible' => false, 'required' => false];
            $quoteSetup->addAttribute('quote_item', PricingKeyHelper::ITEM_ATTRIBUTE_PRICING_KEY, $attributeOptions);
            $salesSetup->addAttribute('order_item', PricingKeyHelper::ITEM_ATTRIBUTE_PRICING_KEY, $attributeOptions);
        }
        
    }
}
