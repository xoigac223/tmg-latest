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

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Store\Model\StoreManagerInterface;
use Blackbird\ContentManager\Model\Content;
use Blackbird\ContentManager\Model\ContentList;
use Blackbird\ContentManager\Model\ResourceModel\Content\CollectionFactory as ContentCollectionFactory;
use Blackbird\ContentManager\Model\ResourceModel\ContentList\CollectionFactory as ContentListCollectionFactory;

/**
 * Upgrade Data script
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var ContentCollectionFactory
     */
    protected $_contentCollectionFactory;

    /**
     * @var ContentListCollectionFactory
     */
    protected $_contentListCollectionFactory;

    /**
     * @param StoreManagerInterface $storeManager
     * @param ContentCollectionFactory $contentCollectionFactory
     * @param ContentListCollectionFactory $contentListCollectionFactory
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ContentCollectionFactory $contentCollectionFactory,
        ContentListCollectionFactory $contentListCollectionFactory
    ) {
        $this->_storeManager = $storeManager;
        $this->_contentCollectionFactory = $contentCollectionFactory;
        $this->_contentListCollectionFactory = $contentListCollectionFactory;
    }

    /**
     * Upgrade Data
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        // Update all content and content lists urls
        if (version_compare($context->getVersion(), '2.1.0', '<')) {
            try {
                $this->upgradeUrlRewrite();
            } catch (LocalizedException $e) {
                // Is a fresh installation with no existing contents
            }
        }

        //todo add attributes for meta canonical

        $setup->endSetup();
    }

    /**
     * Upgrade data for the url rewrite implementation
     *
     * @return void
     */
    protected function upgradeUrlRewrite()
    {
        foreach ($this->_storeManager->getStores() as $store) {
            $this->updateContentUrls($store->getId());
            $this->updateContentListUrls($store->getId());
        }
    }

    /**
     * Update all urls of the enabled contents
     *
     * @param $storeId
     * @return void
     * @throws AlreadyExistsException
     */
    protected function updateContentUrls($storeId)
    {
        $contentCollection = $this->_contentCollectionFactory->create()
            ->addStoreFilter($storeId)
            ->addAttributeToFilter(Content::STATUS, 1);

        foreach ($contentCollection as $content) {
            try {
                $content->generateUrls();
            } catch (AlreadyExistsException $e) {
                throw new AlreadyExistsException($this->getContentUrlAlreadyExistsMessage($content, $storeId));
            }
        }
    }

    /**
     * Update all urls of the enabled content list
     *
     * @param $storeId
     * @return void
     * @throws AlreadyExistsException
     */
    protected function updateContentListUrls($storeId)
    {
        $contentListCollection = $this->_contentListCollectionFactory->create()
            ->addStoreFilter($storeId)
            ->addFieldToFilter(ContentList::STATUS, 1);

        foreach ($contentListCollection as $contentList) {
            try {
                $contentList->generateUrls();
            } catch (AlreadyExistsException $e) {
                throw new AlreadyExistsException($this->getContentListUrlAlreadyExistsMessage($contentList, $storeId));
            }
        }
    }

    /**
     * @param Content $content
     * @param $storeId
     * @return \Magento\Framework\Phrase
     */
    protected function getContentUrlAlreadyExistsMessage(Content $content, $storeId)
    {
        return __('Content ID: \'%1\' for Store ID: \'%2\' has an already used URL: \'%3\'. Modify the url before continue upgrading.', $content->getId(), $storeId, $content->getUrlKey());
    }

    /**
     * @param ContentList $contentList
     * @param $storeId
     * @return \Magento\Framework\Phrase
     */
    protected function getContentListUrlAlreadyExistsMessage(ContentList $contentList, $storeId)
    {
        return __('ContentList ID: \'%1\' for Store ID: \'%2\' has an already used URL: \'%3\'. Modify the url before continue upgrading.', $contentList->getId(), $storeId, $contentList->getUrlKey());
    }
}
