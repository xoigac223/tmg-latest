<?php
/**
 * UpgradeData
 *
 * @copyright Copyright © 2018 Firebear Studio. All rights reserved.
 * @author    Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExport\Setup;

use Firebear\ImportExport\Setup\Operations\CreateCmsEntityTypes;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

/**
 * Upgrade Data script
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var CreateCmsEntityTypes
     */
    protected $createCmsEntityTypes;

    /**
     * UpgradeData constructor.
     *
     * @param CreateCmsEntityTypes $createCmsEntityTypes
     */
    public function __construct(
        CreateCmsEntityTypes $createCmsEntityTypes
    ) {
        $this->createCmsEntityTypes = $createCmsEntityTypes;
    }

    /**
     * Upgrades data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @return void
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '2.1.2', '<')) {
            $this->createCmsEntityTypes->execute($setup);
        }
        if (version_compare($context->getVersion(), '2.1.4', '<')) {
            $this->createCmsEntityTypes->execute($setup);
        }

        $setup->endSetup();
    }
}
