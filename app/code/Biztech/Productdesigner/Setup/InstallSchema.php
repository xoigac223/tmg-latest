<?php

/**
 * Copyright © 2015 Biztech. All rights reserved.
 */

namespace Biztech\Productdesigner\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface {

    private $eavSetupFactory;

    /**
     * Init
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $installer = $setup;

        $installer->startSetup();
        if (!$installer->tableExists('productdesigner_designtemplates')) {
            $table = $installer->getConnection()
                    ->newTable($installer->getTable('productdesigner_designtemplates'))
                    ->addColumn(
                            'designtemplates_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'designtemplates_id'
                    )
                    ->addColumn(
                            'product_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 11, [ 'unsigned' => true, 'nullable' => false], 'product_id'
                    )
                    ->addColumn(
                            'color_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 11, [ 'unsigned' => true], 'color_id'
                    )
                    ->addColumn(
                            'prices', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['default' => null], 'prices'
                    )
                    ->addColumn(
                            'layers', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '2M', ['default' => null], 'layers'
                    )
                    ->addColumn(
                            'layer_images', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '2M', ['default' => null], 'layer_images'
                    )
                    ->addColumn(
                    'masking_images', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '2M', ['default' => null], 'masking_images'
            );
            $installer->getConnection()->createTable($table);
        }

        $installer->run("ALTER TABLE `{$installer->getTable('productdesigner_designtemplates')}` ADD INDEX ( `product_id` ) ;");
        $installer->run("ALTER TABLE `{$installer->getTable('productdesigner_designtemplates')}` ADD FOREIGN KEY ( `product_id` ) REFERENCES `{$installer->getTable('catalog_product_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE ;");

        if (!$installer->tableExists('productdesigner_designtemplates_images')) {
            $table = $installer->getConnection()
                    ->newTable($installer->getTable('productdesigner_designtemplates_images'))
                    ->addColumn(
                            'image_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'image_id'
                    )
                    ->addColumn(
                            'designtemplates_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 11, [ 'unsigned' => true, 'nullable' => false], 'designtemplates_id'
                    )
                    ->addColumn(
                            'product_image_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 11, [ 'unsigned' => true, 'nullable' => false], 'product_image_id'
                    )
                    ->addColumn(
                            'design_image_type', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['default' => null, 'nullable' => false], 'design_image_type'
                    )
                    ->addColumn(
                    'image_path', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['default' => null, 'nullable' => false], 'image_path'
            );
            $installer->getConnection()->createTable($table);
        }
        $installer->run("ALTER TABLE `{$installer->getTable('productdesigner_designtemplates_images')}` ADD INDEX ( `designtemplates_id` );");

        $installer->run("ALTER TABLE `{$installer->getTable('productdesigner_designtemplates_images')}` ADD FOREIGN KEY ( `designtemplates_id` ) REFERENCES `{$installer->getTable('productdesigner_designtemplates')}` (`designtemplates_id`) ON DELETE CASCADE ON UPDATE CASCADE ;");

        if (!$installer->tableExists('productdesigner_shapes_media')) {
            $table = $installer->getConnection()
                    ->newTable($installer->getTable('productdesigner_shapes_media'))
                    ->addColumn(
                            'image_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'image_id'
                    )->addColumn(
                            'shapes_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 11, [ 'unsigned' => true, 'nullable' => false], 'shapes_id'
                    )
                    ->addColumn(
                            'image_path', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['default' => null, 'nullable' => false], 'image_path'
                    )
                    ->addColumn(
                            'label', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['default' => null, 'nullable' => false], 'label'
                    )
                    ->addColumn(
                            'tags', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['default' => null, 'nullable' => false], 'tags'
                    )
                    ->addColumn(
                            'position', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 11, ['default' => null, 'unsigned' => true], 'position'
                    )
                    ->addColumn(
                    'disabled', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 6, ['default' => 0, 'nullable' => false], 'disabled'
            );
            $installer->getConnection()->createTable($table);
        }

        if (!$installer->tableExists('productdesigner_masking_media')) {
            $table = $installer->getConnection()
                    ->newTable($installer->getTable('productdesigner_masking_media'))
                    ->addColumn(
                            'image_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'image_id'
                    )->addColumn(
                            'masking_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 11, [ 'unsigned' => true, 'nullable' => false], 'masking_id'
                    )
                    ->addColumn(
                            'image_path', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['default' => null, 'nullable' => false], 'image_path'
                    )
                    ->addColumn(
                            'label', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['default' => null, 'nullable' => false], 'label'
                    )
                    ->addColumn(
                            'tags', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['default' => null, 'nullable' => false], 'tags'
                    )
                    ->addColumn(
                            'position', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 11, ['default' => null, 'unsigned' => true], 'position'
                    )
                    ->addColumn(
                    'disabled', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 6, ['default' => 0, 'nullable' => false], 'disabled'
            );
            $installer->getConnection()->createTable($table);
        }
        $installer->run("  ALTER TABLE `{$installer->getTable('productdesigner_masking_media')}` ADD INDEX ( `masking_id` ); ");
        $installer->run("  ALTER TABLE `{$installer->getTable('productdesigner_masking_media')}` ADD FOREIGN KEY ( `masking_id` ) REFERENCES `{$installer->getTable('productdesigner_masking')}` (`masking_id`) ON DELETE CASCADE ON UPDATE CASCADE ; ");

        if (!$installer->tableExists('productdesigner_product_designtemplates')) {
            $table = $installer->getConnection()
                    ->newTable($installer->getTable('productdesigner_product_designtemplates'))
                    ->addColumn(
                            'product_template_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'product_template_id'
                    )
                    ->addColumn(
                            'product_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 11, ['unsigned' => true, 'nullable' => false], 'product_id'
                    )
                    ->addColumn(
                    'templates', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['default' => null], 'templates'
            );
            $installer->getConnection()->createTable($table);
        }
        $installer->run("ALTER TABLE `{$installer->getTable('productdesigner_product_designtemplates')}` ADD INDEX ( `product_id` );");
        $installer->run("ALTER TABLE `{$installer->getTable('productdesigner_product_designtemplates')}` ADD FOREIGN KEY ( `product_id` ) REFERENCES `{$installer->getTable('catalog_product_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE ;");

        if (!$installer->tableExists('productdesigner_clipart')) {
            $table = $installer->getConnection()
                    ->newTable($installer->getTable('productdesigner_clipart'))
                    ->addColumn(
                            'clipart_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'clipart_Id'
                    )
                    ->addColumn(
                            'clipart_title', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['default' => null, 'nullable' => false], 'clipart_Title'
                    )
                    ->addColumn(
                            'is_root_category', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 6, ['nullable' => false, 'default' => 0], 'is_root_category'
                    )
                    ->addColumn(
                            'parent_categories', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['nullable' => false, 'default' => null], 'parent_categories'
                    )
                    ->addColumn(
                            'clipart_media_images', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '2M', ['nullable' => false, 'default' => null], 'clipart_media_images'
                    )
                    ->addColumn(
                    'status', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 6, ['nullable' => false, 'default' => 0], 'status'
                    )->addColumn(
                    'store_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    10,
                    ['nullable' => false, 'default' =>'0'],
                    'store id');

            $installer->getConnection()->createTable($table);
        }

        if (!$installer->tableExists('productdesigner_designtemplates_category')) {
            $table = $installer->getConnection()
                    ->newTable($installer->getTable('productdesigner_designtemplates_category'))
                    ->addColumn(
                            'designtemplatescategory_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'clipart_Id'
                    )
                    ->addColumn(
                            'category_title', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['default' => null, 'nullable' => false], 'clipart_Title'
                    )
                    ->addColumn(
                            'is_root_category', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 6, ['nullable' => false, 'default' => 0], 'is_root_category'
                    )
                    ->addColumn(
                            'parent_categories', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['nullable' => false, 'default' => null], 'parent_categories'
                    )
                    ->addColumn(
                            'level', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 6, [ 'nullable' => false], 'level'
                    )
                    ->addColumn(
                            'status', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 6, ['nullable' => false, 'default' => 0], 'status'
                    )
                    ->addColumn(
                    'designs', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '2M', ['default' => null, 'nullable' => false], 'designs'
            );

            $installer->getConnection()->createTable($table);
        }

        if (!$installer->tableExists('productdesigner_shapes')) {
            $table = $installer->getConnection()
                    ->newTable($installer->getTable('productdesigner_shapes'))
                    ->addColumn(
                            'shapes_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'shapes_id'
                    )
                    ->addColumn(
                            'shapes_title', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['default' => null, 'nullable' => false], 'shapes_title'
                    )
                    ->addColumn(
                            'is_root_category', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 6, ['nullable' => false, 'default' => 0], 'is_root_category'
                    )
                    ->addColumn(
                            'parent_categories', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['nullable' => false, 'default' => null], 'parent_categories'
                    )
                    ->addColumn(
                            'shapes_media_images', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '2M', ['nullable' => false, 'default' => null], 'shapes_media_images'
                    )
                    ->addColumn(
                            'status', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 6, ['nullable' => false, 'default' => 0], 'status'
                    )
                    ->addColumn(
                    'level', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 6, [ 'nullable' => false], 'level'
            );

            $installer->getConnection()->createTable($table);
        }

        if (!$installer->tableExists('productdesigner_masking')) {
            $table = $installer->getConnection()
                    ->newTable($installer->getTable('productdesigner_masking'))
                    ->addColumn(
                            'masking_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'masking_id'
                    )
                    ->addColumn(
                            'masking_title', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['default' => null, 'nullable' => false], 'masking_title'
                    )
                    ->addColumn(
                            'is_root_category', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 6, ['nullable' => false, 'default' => 0], 'is_root_category'
                    )
                    ->addColumn(
                            'level', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 6, [ 'nullable' => false], 'level'
                    )
                    ->addColumn(
                            'parent_categories', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['nullable' => false, 'default' => null], 'parent_categories'
                    )
                    ->addColumn(
                            'masking_media_images', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '2M', ['nullable' => false, 'default' => null], 'masking_media_images'
                    )
                    ->addColumn(
                    'status', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 6, ['nullable' => false, 'default' => 0], 'status'
                    )->addColumn(
                    'store_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    10,
                    ['nullable' => false, 'default' =>'0'],
                    'store id');

            $installer->getConnection()->createTable($table);
        }

        if (!$installer->tableExists('productdesigner_imageside')) {
            $table = $installer->getConnection()
                    ->newTable($installer->getTable('productdesigner_imageside'))
                    ->addColumn(
                            'imageside_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'masking_id'
                    )
                    ->addColumn(
                            'imageside_title', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['default' => null, 'nullable' => false], 'masking_title'
                    )
                    ->addColumn(
                    'status', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 6, ['nullable' => false, 'default' => 0], 'status'
            );

            $installer->getConnection()->createTable($table);
        }

        if (!$installer->tableExists('productdesigner_media')) {
            $table = $installer->getConnection()
                    ->newTable($installer->getTable('productdesigner_media'))
                    ->addColumn(
                            'image_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'image_id'
                    )->addColumn(
                            'clipart_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 11, [ 'unsigned' => true, 'nullable' => false], 'clipart_id'
                    )
                    ->addColumn(
                            'image_path', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['default' => null, 'nullable' => false], 'image_path'
                    )
                    ->addColumn(
                            'label', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['default' => null, 'nullable' => false], 'label'
                    )
                    ->addColumn(
                            'tags', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['default' => null, 'nullable' => false], 'tags'
                    )
                    ->addColumn(
                            'position', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 11, ['default' => null, 'unsigned' => true], 'position'
                    )
                    ->addColumn(
                    'disabled', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 6, ['default' => 0, 'nullable' => false], 'disabled'
            );



            $installer->getConnection()->createTable($table);
        }

        $installer->run("  ALTER TABLE `{$installer->getTable('productdesigner_media')}` ADD INDEX ( `clipart_id` ); ");
        $installer->run("  ALTER TABLE `{$installer->getTable('productdesigner_media')}` ADD FOREIGN KEY ( `clipart_id` ) REFERENCES `{$installer->getTable('productdesigner_clipart')}` (`clipart_id`) ON DELETE CASCADE ON UPDATE CASCADE ; ");

        if (!$installer->tableExists('productdesigner_fonts')) {
            $table = $installer->getConnection()
                    ->newTable($installer->getTable('productdesigner_fonts'))
                    ->addColumn(
                            'fonts_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'fonts_id'
                    )
                    ->addColumn(
                            'font_label', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['default' => null, 'nullable' => false], 'font_label'
                    )->addColumn(
                            'font_file', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['default' => null, 'nullable' => false], 'font_file'
                    )
                    ->addColumn(
                            'position', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 11, ['default' => null, 'unsigned' => true], 'position'
                    )
                    ->addColumn(
                    'disabled', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 6, ['default' => 0, 'nullable' => false], 'disabled'
            );
            $installer->getConnection()->createTable($table);
        }
        if (!$installer->tableExists('productdesigner_attribute_option_image')) {
            $table = $installer->getConnection()
                    ->newTable($installer->getTable('productdesigner_attribute_option_image'))
                    ->addColumn(
                            'image_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'image_id'
                    )
                    ->addColumn(
                            'option_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 11, [ 'unsigned' => true, 'nullable' => false], 'option_id'
                    )
                    ->addColumn(
                            'image_path', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['default' => null, 'nullable' => false], 'image_path'
                    )
                    ->addColumn(
                    'attribute_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 11, [ 'unsigned' => true, 'nullable' => false], 'attribute_id'
            );
            $installer->getConnection()->createTable($table);
        }

        $installer->run("ALTER TABLE `{$installer->getTable('productdesigner_attribute_option_image')}` ADD INDEX ( `option_id` );");

        $installer->run("ALTER TABLE `{$installer->getTable('productdesigner_attribute_option_image')}` ADD FOREIGN KEY ( `option_id` ) REFERENCES `{$installer->getTable('eav_attribute_option')}` (`option_id`) ON DELETE CASCADE ON UPDATE CASCADE ;");

        if (!$installer->tableExists('productdesigner_image_selection_area')) {
            $table = $installer->getConnection()
                    ->newTable($installer->getTable('productdesigner_image_selection_area'))
                    ->addColumn(
                            'design_area_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'design_area_id'
                    )
                    ->addColumn(
                            'image_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 11, ['unsigned' => true, 'nullable' => false], 'image_id'
                    )
                    ->addColumn(
                            'selection_area', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['default' => null, 'nullable' => false], 'selection_area'
                    )
                    ->addColumn(
                            'product_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 11, ['unsigned' => true, 'nullable' => false], 'product_id'
                    )
                    ->addColumn(
                    'imageside_id', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['default' => null, 'nullable' => false], 'imageside_id'
            );
            $installer->getConnection()->createTable($table);
        }

        $installer->run("ALTER TABLE `{$installer->getTable('productdesigner_image_selection_area')}` ADD INDEX ( `image_id` );");

        $installer->run("ALTER TABLE `{$installer->getTable('productdesigner_image_selection_area')}` ADD FOREIGN KEY ( `image_id` ) REFERENCES `{$installer->getTable('catalog_product_entity_media_gallery')}` (`value_id`) ON DELETE CASCADE ON UPDATE CASCADE ;");


        if (!$installer->tableExists('productdesigner_designs')) {
            $table = $installer->getConnection()
                    ->newTable($installer->getTable('productdesigner_designs'))
                    ->addColumn(
                            'design_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'design_id'
                    )
                    ->addColumn(
                            'product_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 11, ['unsigned' => true, 'nullable' => false], 'product_id'
                    )
                    ->addColumn(
                            'customer_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 11, ['unsigned' => true], 'customer_id'
                    )
                    ->addColumn(
                            'color_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 11, ['unsigned' => true], 'color_id'
                    )
                    ->addColumn(
                            'prices', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['default' => null], 'prices'
                    )
                    ->addColumn(
                            'layers', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '2M', ['default' => null], 'layers'
                    )
                    ->addColumn(
                            'layer_images', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '2M', ['default' => null], 'layer_images'
                    )
                    ->addColumn(
                            'masking_images', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '2M', ['default' => null], 'masking_images'
                    )
                    ->addColumn(
                            'title', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['default' => null], 'title'
                    )
                    ->addColumn(
                            'group_order_details', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '2M', ['default' => null], 'group_order_details'
                    )
                    ->addColumn(
                    'customer_comments', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['default' => null], 'customer_comments'
                    )->addColumn(
                    'created_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                    'Created At');
            $installer->getConnection()->createTable($table);
        }


        $installer->run("ALTER TABLE `{$installer->getTable('productdesigner_designs')}` ADD INDEX ( `product_id` )");

        $installer->run("ALTER TABLE `{$installer->getTable('productdesigner_designs')}` ADD FOREIGN KEY ( `product_id` ) REFERENCES `{$installer->getTable('catalog_product_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE ");

        if (!$installer->tableExists('productdesigner_design_images')) {
            $table = $installer->getConnection()
                    ->newTable($installer->getTable('productdesigner_design_images'))
                    ->addColumn(
                            'image_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'image_id'
                    )
                    ->addColumn(
                            'design_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 11, ['unsigned' => true, 'nullable' => false], 'design_id')
                    ->addColumn(
                            'product_image_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 11, ['unsigned' => true, 'nullable' => false], 'product_image_id')
                    ->addColumn(
                            'design_image_type', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['default' => null, 'nullable' => false], 'design_image_type'
                    )
                    ->addColumn(
                    'image_path', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['default' => null, 'nullable' => false], 'image_path'
            );
            $installer->getConnection()->createTable($table);
        }



        $installer->run("ALTER TABLE `{$installer->getTable('productdesigner_design_images')}` ADD INDEX ( `design_id` )");

        $installer->run("ALTER TABLE `{$installer->getTable('productdesigner_design_images')}` ADD FOREIGN KEY ( `design_id` ) REFERENCES `{$installer->getTable('productdesigner_designs')}` (`design_id`) ON DELETE CASCADE ON UPDATE CASCADE");

        if (!$installer->tableExists('productdesigner_quotes')) {
            $table = $installer->getConnection()
                    ->newTable($installer->getTable('productdesigner_quotes'))
                    ->addColumn(
                            'quotes_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'quotes_id'
                    )
                    ->addColumn(
                            'quotes_text', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '2M', ['default' => null, 'nullable' => false], 'quotes_text'
                    )
                    ->addColumn(
                            'status', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 6, ['default' => 0, 'nullable' => false], 'status'
                    )
                    ->addColumn(
                    'category_id', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 6, ['default' => 0, 'nullable' => false], 'category_id'
                    )->addColumn(
                    'store_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    10,
                    ['nullable' => false, 'default' =>'0'],
                    'store id');
            $installer->getConnection()->createTable($table);
        }



        if (!$installer->tableExists('productdesigner_quotes_category')) {
            $table = $installer->getConnection()
                    ->newTable($installer->getTable('productdesigner_quotes_category'))
                    ->addColumn(
                            'quotescategory_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 11, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'quotescategory_id'
                    )
                    ->addColumn(
                            'category_title', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['default' => null, 'nullable' => false], 'category_title'
                    )
                    ->addColumn(
                            'is_root_category', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 6, ['default' => 0, 'nullable' => false], 'is_root_category'
                    )
                    ->addColumn(
                            'parent_categories', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['default' => null, 'nullable' => false], 'category_title'
                    )
                    ->addColumn(
                            'level', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 6, [ 'nullable' => false], 'level'
                    )
                    ->addColumn(
                    'status', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 6, ['default' => 0, 'nullable' => false], 'status'
                    )->addColumn(
                    'store_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    10,
                    ['nullable' => false, 'default' =>'0'],
                    'store id');
            $installer->getConnection()->createTable($table);
        }

        $installer->getConnection()->addColumn(
                $installer->getTable('catalog_product_entity_media_gallery_value'), 'image_side', [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            'nullable' => true,
            'comment' => 'Image Side',
        ]);

        /**
         * Add attributes to the eav/attribute
         */
        $installer->endSetup();
    }

}
