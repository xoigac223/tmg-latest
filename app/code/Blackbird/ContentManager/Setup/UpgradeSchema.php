<?php
/**
 * Blackbird ContentManager Module
 *
 * NOTICE OF LICENSE
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@bird.eu so we can send you a copy immediately.
 *
 * @category        Blackbird
 * @package         Blackbird_ContentManager
 * @copyright       Copyright (c) 2017 Blackbird (https://black.bird.eu)
 * @author          Blackbird Team
 * @license         https://www.advancedcontentmanager.com/license/
 */
namespace Blackbird\ContentManager\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Upgrade the ContentManager module DB scheme
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.1.0', '<')) {
            $this->renameSearchAttributeWeight($setup);
        }

        //todo add fields for meta canonical (content & content list)

        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return void
     */
    protected function renameSearchAttributeWeight(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->changeColumn(
            $setup->getTable('blackbird_contenttype_eav_attribute'),
            'search_attribute_weight',
            'search_weight',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'length' => 11,
                'comment' => 'Attribute search weight'
            ]
        );
    }
}
