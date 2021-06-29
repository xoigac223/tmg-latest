<?php

namespace Biztech\Productdesigner\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;

class UpgradeSchema implements UpgradeSchemaInterface {

    public function __construct(
        EavSetupFactory $eavSetupFactory
    )
    {
        $this->_eavSetupFactory = $eavSetupFactory;
    }

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.1') <= 0) {
            if (!$setup->getConnection()->isTableExists($setup->getTable('productdesigner_simpleprinting'))) {
                $table = $setup->getConnection()->newTable(
                                $setup->getTable('productdesigner_simpleprinting')
                        )->addColumn(
                                'simpleprinting_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 10, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'primary Key of auspost -eparcel table'
                        )->addColumn(
                                'simpleprinting_name', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['nullable' => false, 'default' => 0], 'store website id '
                        )->addColumn(
                                'simpleprinting_code', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['nullable' => false, 'default' => 0], 'Stores Destination Country id'
                        )->addColumn(
                                'simpleprinting_description', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['nullable' => false, 'default' => 0], 'Stores Destination region id'
                        )->addColumn(
                                'minimum_quantity', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 10, ['nullable' => false, 'default' => '0'], 'Stores Destination zip code'
                        )->addColumn(
                                'front_surcharge', \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL, '10,2', ['nullable' => false, 'default' => '0'], 'Stores condition name'
                        )->addColumn(
                                'store_id', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['nullable' => false, 'default' => '0'], 'condition from value (price)'
                        )->addColumn(
                                'status', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 10, ['nullable' => false, 'default' => '0'], 'To condition value (price)'
                        )->setComment(
                        'Shipping Auspost E Parcel Rates'
                );
                $setup->getConnection()->createTable($table);

                //View File Fix For Frontend JS Layout
                //Auspost php Update to Separate Extra Cover Shipping
            }

            if (!$setup->getConnection()->isTableExists($setup->getTable('productdesigner_printing_method'))) {
                $table = $setup->getConnection()->newTable(
                                $setup->getTable('productdesigner_printing_method')
                        )->addColumn(
                                'printing_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 10, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'primary Key of productdesigner_printing_method table'
                        )->addColumn(
                                'printing_name', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['nullable' => false, 'default' => 0], 'name'
                        )->addColumn(
                                'printing_code', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['nullable' => false, 'default' => 0], 'Stores Destination Country id'
                        )->addColumn(
                                'printing_code', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['nullable' => false, 'default' => 0], 'Code'
                        )->addColumn(
                                'printing_description', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['nullable' => false, 'default' => '0'], 'printing_description'
                        )->addColumn(
                                'store_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, '10,2', ['nullable' => false, 'default' => '0'], 'store_id'
                        )->addColumn(
                                'status', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 255, ['nullable' => false, 'default' => '0'], 'status'
                        )->addColumn(
                                'minimum_quantity', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 10, ['nullable' => false, 'default' => '0'], 'minimum_quantity'
                        )->addColumn(
                                'colortype', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 10, ['nullable' => false, 'default' => '0'], 'colortype'
                        )->addColumn(
                                'info', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 10, ['nullable' => false, 'default' => '0'], 'info'
                        )->addColumn(
                                'customer_groups', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 10, ['nullable' => false, 'default' => '0'], 'customer_groups'
                        )->setComment(
                        'Printing Method for Configurable Products'
                );
                $setup->getConnection()->createTable($table);

                //View File Fix For Frontend JS Layout
                //Auspost php Update to Separate Extra Cover Shipping
            }

            if (!$setup->getConnection()->isTableExists($setup->getTable('productdesigner_colors'))) {
                $table = $setup->getConnection()->newTable(
                                $setup->getTable('productdesigner_colors')
                        )->addColumn(
                                'colors_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 10, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'primary Key of productdesigner_colors table'
                        )->addColumn(
                                'colors_counter', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 10, ['nullable' => false, 'default' => 0], 'name'
                        )->addColumn(
                                'colors_price', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 10, ['nullable' => false, 'default' => '0'], 'price'
                        )->setComment(
                        'Color Counter for Configurable Products'
                );
                $setup->getConnection()->createTable($table);

                //View File Fix For Frontend JS Layout
                //Auspost php Update to Separate Extra Cover Shipping
            }

            if (!$setup->getConnection()->isTableExists($setup->getTable('productdesigner_areasize'))) {
                $table = $setup->getConnection()->newTable(
                                $setup->getTable('productdesigner_areasize')
                        )->addColumn(
                                'areasize_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 10, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'primary Key of productdesigner_areasize table'
                        )->addColumn(
                                'area_price', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 10, ['nullable' => false, 'default' => '0'], 'price'
                        )->addColumn(
                                'area_size', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['nullable' => false, 'default' => 0], 'name'
                        )->setComment(
                        'Ara size'
                );
                $setup->getConnection()->createTable($table);

                //View File Fix For Frontend JS Layout
                //Auspost php Update to Separate Extra Cover Shipping
            }

            if (!$setup->getConnection()->isTableExists($setup->getTable('productdesigner_pcolor'))) {
                $table = $setup->getConnection()->newTable(
                                $setup->getTable('productdesigner_pcolor')
                        )->addColumn(
                                'pcolor_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 10, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'primary Key of productdesigner_pcolor table'
                        )->addColumn(
                                'color_name', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['nullable' => false, 'default' => 0], 'name')
                        ->addColumn(
                                'color_code', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['nullable' => false, 'default' => 0], 'name')
                        ->addColumn(
                                'status', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 10, ['nullable' => false, 'default' => 0], 'name')
                        ->addColumn(
                                'store_id', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['nullable' => true, 'default' => 0], 'name')
                        ->setComment(
                        'Ara size'
                );
                $setup->getConnection()->createTable($table);

                //View File Fix For Frontend JS Layout
                //Auspost php Update to Separate Extra Cover Shipping
            }

            if ($setup->getConnection()->isTableExists($setup->getTable('productdesigner_image_selection_area'))) {
                $table = $setup->getConnection()->addColumn($setup->getTable('productdesigner_image_selection_area'), 'masking_image_id', ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 'length' => 10, 'nullable' => false, 'default' => '0', 'comment' => 'Selection Area']);
            }
            
            if ($setup->getConnection()->isTableExists($setup->getTable('productdesigner_printing_method'))) {
                $table = $setup->getConnection()->addColumn($setup->getTable('productdesigner_printing_method'), 'colorqty', ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'length' => 255, 'nullable' => true, 'default' => '0', 'comment' => 'Color Qty']);
                $setup->getConnection()->addColumn($setup->getTable('productdesigner_printing_method'), 'areasize', ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'length' => 255, 'nullable' => true, 'default' => '0', 'comment' => 'Area Size']);
            }
        }
        if (version_compare($context->getVersion(), '1.0.2') <= 0) {
            $setup->getConnection()->addColumn(
                    $setup->getTable('catalog_product_entity_media_gallery_value'), 'is_imprintdefaultlocation', [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'nullable' => true,
                'comment' => 'Imprint Default Location',
            ]);
        }


        if (version_compare($context->getVersion(), '1.0.3') <= 0) {
        if (!$setup->getConnection()->isTableExists($setup->getTable('productdesigner_configurableattributes'))) {
                $table = $setup->getConnection()->newTable(
                                $setup->getTable('productdesigner_configurableattributes')
                        )->addColumn(
                                'attribute_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'primary Key of productdesigner_configurableattributes table'
                        )->addColumn(
                                'attribute_set_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 11, ['nullable' => false, 'default' => 0], 'attribute_set_id')
                        ->addColumn(
                                'configurable_attributes', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['nullable' => false, 'default' => 0], 'configurable_attributes')
                        ->addColumn(
                        'default_attributes', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['nullable' => false, 'default' => 0], 'default_attributes');
                $setup->getConnection()->createTable($table);
            }
        }
        
         if (version_compare($context->getVersion(), '1.0.6') <= 0) {
        if (!$setup->getConnection()->isTableExists($setup->getTable('productdesigner_product_stockcolors'))) {
                $table = $setup->getConnection()->newTable(
                                $setup->getTable('productdesigner_product_stockcolors')
                        )->addColumn(
                                'product_stockcolor_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'primary Key of productdesigner_product_stockcolors table'
                        )->addColumn(
                                'stockcolor_product_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 11, ['nullable' => false, 'default' => 0], 'stockcolor_product_id')
                        ->addColumn(
                                'stockcolor_product_colorsinfo', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,'2M',['nullable' => false, 'default' => null], 'stockcolor_product_colorsinfo');
                $setup->getConnection()->createTable($table);
            }
        }
        
        
        
        

        $setup->endSetup();
    }

}
