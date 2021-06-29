<?php
namespace Biztech\Productdesigner\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Eav\Setup\EavSetupFactory;

class UpgradeData implements UpgradeDataInterface
{
    private $eavSetupFactory;

    public function __construct(
    EavSetupFactory $eavSetupFactory) {
        $this->eavSetupFactory = $eavSetupFactory;       
    }

    public function upgrade( ModuleDataSetupInterface $setup, ModuleContextInterface $context ) {
        if (version_compare($context->getVersion(), '1.0.1') <= 0) {

            
            $installer = $setup;
            $installer->startSetup();


        /*    $installer->run("
                INSERT INTO `productdesigner_clipart` (`clipart_id`, `clipart_title`, `is_root_category`, `parent_categories`, `clipart_media_images`, `status`) VALUES (NULL, 'Funny', 1, '', '', 1),
                    (2, 'Nature', 1, '', '', 1)
            ");
            $installer->run("
                INSERT INTO `productdesigner_media` (`image_id`, `clipart_id`, `image_path`, `label`, `tags`, `position`, `disabled`) VALUES
                    (1, 1, '/d/e/demo.jpg', '', '', 1, 0),
                    (2, 1, '/e/l/elephant.png', '', '', 1, 0),
                    (3, 2, '/g/u/guitor.png', '', '', 1, 0),
                    (4, 2, '/t/r/tree.png', '', '', 1, 0)
            ");


            $installer->run("
                INSERT INTO `productdesigner_shapes` (`shapes_id`, `shapes_title`, `is_root_category`, `parent_categories`, `shapes_media_images`, `status`,`level`) VALUES (1, 'Shapes 1', 1, '', '', 1,''),
                    (2, 'Shapes 2', 1, '', '', 1,'')
            ");
            $installer->run("
                INSERT INTO `productdesigner_shapes_media` (`image_id`, `shapes_id`, `image_path`, `label`, `tags`, `position`, `disabled`) VALUES
                    (1, 1, '/b/i/bird3.png', '', '', 1, 0),
                    (2, 1, '/c/i/circle_shape.png', '', '', 1, 0),
                    (3, 2, '/j/o/joans-red-star-hi.png', '', '', 1, 0),
                    (4, 2, '/s/q/square.png', '', '', 1, 0)
            ");


            $installer->run("
                INSERT INTO `productdesigner_masking` (`masking_id`, `masking_title`, `is_root_category`, `level`, `parent_categories`, `masking_media_images`,`status`) VALUES (1, 'Masking 1', 1, 0, '', '',1)
            ");
            $installer->run("
                INSERT INTO `productdesigner_masking_media` (`image_id`, `masking_id`, `image_path`, `label`, `tags`, `position`, `disabled`) VALUES
                    (1, 1, '/b/o/bootstrap-solid.svg', '', '', 1, 0),
                    (2, 1, '/c/o/country_gb.svg', '', '', 1, 0)
                    
            ");


            $installer->run("
                INSERT INTO `productdesigner_fonts` (`fonts_id`, `font_label`, `font_file`,`position`,`disabled`) VALUES 
                (1, 'Font 1', '/a/_/a_arialblack.ttf', 1, 0),
                (2, 'Font 1', '/c/o/coastershadow.ttf', 1, 0)
            ");
*/
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            $eavSetup->addAttribute(
                    \Magento\Catalog\Model\Product::ENTITY, 'design_templates', [
                'type' => 'varchar',
                'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'frontend' => '',
                'label' => 'Design Templates Category',
                'input' => 'multiselect',
                'visible' => true,
                'required' => false,
                'source' => 'Biztech\Productdesigner\Model\Entity\Attribute\Source\Designtemplates',
                'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_GLOBAL,                
                    ]
            );

            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            $eavSetup->addAttribute(
                    \Magento\Catalog\Model\Product::ENTITY, 'pre_loaded_template', [
                'type' => 'varchar',
                'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'frontend' => '',
                'label' => 'Pre Loaded Template',
                'input' => 'select',
                'visible' => true,
                'required' => false,
                'source' => 'Biztech\Productdesigner\Model\Entity\Attribute\Source\PreLoadedTemplate',
                'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_GLOBAL,                
                    ]
            );

            $installer->endSetup();
        }
        if (version_compare($context->getVersion(), '1.0.4') <= 0) {
            $installer = $setup;
            $installer->startSetup();
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            $eavSetup->addAttribute(
                    \Magento\Catalog\Model\Product::ENTITY, 'designer_imprint_option', [
                'type' => 'varchar',
                'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'frontend' => '',
                'label' => 'Product Designer Default Imprint Option',
                'input' => 'select',
                'visible' => true,
                'required' => false,
                'source' => 'Biztech\Productdesigner\Model\Entity\Attribute\Source\ImprintOption',
                'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_GLOBAL,                
                    ]
            );


        }
        if (version_compare($context->getVersion(), '1.0.5') <= 0) {
            $installer = $setup;
            $installer->startSetup();
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            $eavSetup->addAttribute(
                    \Magento\Catalog\Model\Product::ENTITY, 'allow_pms_color', [
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Enable Product Printable Colors',
                'input' => 'boolean',
                'visible' => true,
                'required' => false,
                'source' => 'Magento\Catalog\Model\Product\Attribute\Source\Boolean',
                'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_GLOBAL,                
                    ]
            );


        }


    }
}
