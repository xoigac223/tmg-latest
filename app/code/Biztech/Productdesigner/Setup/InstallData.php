<?php

namespace Biztech\Productdesigner\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Directory\Helper\Data;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface {

    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;
    protected $_resource;
    private $directoryData;

    /**
     * Init
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
    EavSetupFactory $eavSetupFactory, Data $directoryData, \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->_resource = $resource;
        $this->directoryData = $directoryData;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context) {

        $installer = $setup;
        $installer->startSetup();

        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY, 'default_color', [
            'type' => 'int',
            'backend' => '',
            'frontend' => '',
            'label' => 'Default Color',
            'input' => 'select',
            'visible' => true,
            'required' => false,
            'source' => 'Biztech\Productdesigner\Model\Entity\Attribute\Source\Defaultcolor',
            'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_GLOBAL,
                ]
        );
        $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY, 'enable_product_designer', [
            'type' => 'int',
            'backend' => '',
            'frontend' => '',
            'label' => 'Enable Product Designer',
            'input' => 'boolean',
            'class' => '',
            'source' => 'Magento\Catalog\Model\Product\Attribute\Source\Boolean',
            'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_GLOBAL,
            'visible' => true,
            'required' => false,
                ]
        );



        //alter table ALTER TABLE quote_address MODIFY shipping_method VARCHAR(255);
    }

}
