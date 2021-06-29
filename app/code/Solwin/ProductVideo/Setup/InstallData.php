<?php
/**
 * Solwin Infotech
 * Solwin Advanced Product Video Extension
 *
 * @category   Solwin
 * @package    Solwin_ProductVideo
 * @copyright  Copyright © 2006-2016 Solwin (https://www.solwininfotech.com)
 * @license    https://www.solwininfotech.com/magento-extension-license/
 */
namespace Solwin\ProductVideo\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    const BACKEND = 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend';
    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * Init
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory) {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function install(
            ModuleDataSetupInterface $setup,
            ModuleContextInterface $context
        ) {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

       $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY, 'productvideo', [
            'group' => 'Product Videos',
            'type' => 'varchar',
            'sort_order' => 5,
            'backend' => self::BACKEND,
            'frontend' => '',
            'label' => 'Product Videos',
            'input' => 'multiselect',
            'class' => '',
            'source' => 'Solwin\ProductVideo\Model\Source\Video',
            'global' => Attribute::SCOPE_GLOBAL,
            'visible' => true,
            'required' => false,
            'user_defined' => true,
            'default' => '',
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'used_in_product_listing' => true,
            'unique' => false,
            'apply_to' => 'simple,configurable,virtual,bundle,downloadable,grouped'
        ]);
    }
}