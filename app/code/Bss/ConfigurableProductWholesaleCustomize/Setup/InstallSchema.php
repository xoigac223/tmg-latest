<?php
namespace Bss\ConfigurableProductWholesaleCustomize\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
    private $eavSetupFactory;
 
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY,'qty_order_sample');

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'qty_order_sample',
            [
                'group' => 'General',
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Qty Order Sample',
                'input' => 'text',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'visible'           => true,
                'required'          => false,
                'user_defined'      => 1,
                'searchable'        => false,
                'filterable'        => false,
                'comparable'        => false,
                'visible_on_front'  => 1,
                'apply_to'          => 'configurable',
            ]
        );
        $setup->endSetup();
    }
}