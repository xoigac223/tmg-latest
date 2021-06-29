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
namespace Blackbird\ContentManager\Plugin\Store\Model;

use Blackbird\ContentManager\Model\Content;
use Blackbird\ContentManager\Model\ContentList;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\StoreManagerInterface;

class Group
{
    /** 
     * @var \Blackbird\ContentManager\Helper\UrlRewriteGenerator
     */
    protected $_urlRewriteHelper;

    /**
     * @var \Blackbird\ContentManager\Model\ResourceModel\Content\CollectionFactory
     */
    protected $_contentCollectionFactory;
    
    /**
     * @var \Blackbird\ContentManager\Model\ResourceModel\ContentList\CollectionFactory
     */
    protected $_contentListCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Blackbird\ContentManager\Helper\UrlRewriteGenerator $urlRewriteHelper
     * @param \Blackbird\ContentManager\Model\ResourceModel\Content\CollectionFactory $contentCollectionFactory
     * @param \Blackbird\ContentManager\Model\ResourceModel\ContentList\CollectionFactory $contentListCollectionFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        \Blackbird\ContentManager\Helper\UrlRewriteGenerator $urlRewriteHelper,
        \Blackbird\ContentManager\Model\ResourceModel\Content\CollectionFactory $contentCollectionFactory,
        \Blackbird\ContentManager\Model\ResourceModel\ContentList\CollectionFactory $contentListCollectionFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->_urlRewriteHelper = $urlRewriteHelper;
        $this->_contentCollectionFactory = $contentCollectionFactory;
        $this->_contentListCollectionFactory = $contentListCollectionFactory;
        $this->_storeManager = $storeManager;
    }

    /**
     * @param \Magento\Store\Model\ResourceModel\Group $object
     * @param \Closure $proceed
     * @param AbstractModel $group
     * @return mixed
     */
    public function aroundSave(
        \Magento\Store\Model\ResourceModel\Group $object,
        \Closure $proceed,
        AbstractModel $group
    ) {
        $originGroup = $group;
        $result = $proceed($originGroup);
        if (!$group->isObjectNew()
            && ($group->dataHasChangedFor('website_id')
                || $group->dataHasChangedFor('root_category_id'))
        ) {
            $this->_storeManager->reinitStores();
            $this->_urlRewriteHelper->deleteUrlRewrite(
                [Content::ENTITY, ContentList::ENTITY_TYPE],
                null,
                $group->getStoreIds()
            );

            foreach ($group->getStoreIds() as $storeId) {
                $this->generateContentManagerUrlsRewrite($storeId);
            }
        }

        return $result;
    }

    /**
     * Generate all the url rewrites of the ContentManager module
     * 
     * @param int $storeId
     * @return void
     */
    protected function generateContentManagerUrlsRewrite($storeId)
    {
        $urls = array_merge(
            $this->generateContentListUrlsRewrite($storeId),
            $this->generateContentUrlsRewrite($storeId)
        );
        
        $this->_urlRewriteHelper->addUrlRewrites($urls);
    }
    
    /**
     * Generate url rewrites for content list assigned to store view
     *
     * @param int $storeId
     * @return array
     */
    protected function generateContentListUrlsRewrite($storeId)
    {
        $urls = [];
        $contentListCollection = $this->_contentListCollectionFactory->create()
            ->addStoreFilter($storeId)
            ->addFieldToFilter(ContentList::STATUS, 1)
            ->addFieldToSelect(ContentList::URL_KEY);
        
        foreach ($contentListCollection as $contentList) {
            $urls[] = [
                'entity_type' => ContentList::ENTITY_TYPE,
                'entity_id' => $contentList->getId(),
                'request_path' => $contentList->getUrlKey(),
                'target_path' => 'contentmanager/index/contentlist/contentlist_id/' . $contentList->getId(),
                'store_id' => $storeId,
            ];
        }
        
        return $urls;
    }

    /**
     * Generate url rewrites for content assigned to store view
     * 
     * @param int $storeId
     * @return array
     */
    protected function generateContentUrlsRewrite($storeId)
    {
        $urls = [];
        $contentCollection = $this->_contentCollectionFactory->create()
            ->addStoreFilter($storeId)
            ->addAttributeToFilter(Content::STATUS, 1)
            ->addAttributeToSelect(Content::URL_KEY);
        
        foreach ($contentCollection as $content) {
            $urls[] = [
                'entity_type' => Content::ENTITY,
                'entity_id' => $content->getId(),
                'request_path' => $content->getUrlKey(),
                'target_path' => 'contentmanager/index/content/content_id/' . $content->getId(),
                'store_id' => $storeId,
            ];
        }
        
        return $urls;
    }
}
