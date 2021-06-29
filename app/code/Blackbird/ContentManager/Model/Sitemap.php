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
namespace Blackbird\ContentManager\Model;

class Sitemap extends \Magento\Sitemap\Model\Sitemap
{
    /**
     * @var \Blackbird\ContentManager\Model\ResourceModel\ContentType\CollectionFactory
     */
    protected $_contentTypeCollectionFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Sitemap\Helper\Data $sitemapData
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Sitemap\Model\ResourceModel\Catalog\CategoryFactory $categoryFactory
     * @param \Magento\Sitemap\Model\ResourceModel\Catalog\ProductFactory $productFactory
     * @param \Magento\Sitemap\Model\ResourceModel\Cms\PageFactory $cmsFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $modelDate
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Blackbird\ContentManager\Model\ResourceModel\ContentType\CollectionFactory $contentTypeCollectionFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Escaper $escaper,
        \Magento\Sitemap\Helper\Data $sitemapData,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Sitemap\Model\ResourceModel\Catalog\CategoryFactory $categoryFactory,
        \Magento\Sitemap\Model\ResourceModel\Catalog\ProductFactory $productFactory,
        \Magento\Sitemap\Model\ResourceModel\Cms\PageFactory $cmsFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $modelDate,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Blackbird\ContentManager\Model\ResourceModel\ContentType\CollectionFactory $contentTypeCollectionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_contentTypeCollectionFactory = $contentTypeCollectionFactory;
        parent::__construct(
            $context,
            $registry,
            $escaper,
            $sitemapData,
            $filesystem,
            $categoryFactory,
            $productFactory,
            $cmsFactory,
            $modelDate,
            $storeManager,
            $request,
            $dateTime,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Add Content Type Contents Pages
     * 
     * @return void
     */
    protected function _initContentTypeSitemapItems()
    {
        $contentTypeCollection = $this->_contentTypeCollectionFactory->create()
            ->addFieldToFilter(ContentType::SITEMAP_ENABLE, 1);
        $storeId = $this->getStoreId();
        
        // Retrieves all available contents for each contenttype
        /** @var \Blackbird\ContentManager\Model\ContentType $contentType */
        foreach ($contentTypeCollection as $contentType) {
            $contentCollection = $contentType->getContentCollection()
                ->addStoreFilter($storeId)
                ->addAttributeToFilter(Content::STATUS, 1)
                ->addAttributeToSelect(Content::URL_KEY)
                ->addAttributeToSelect(Content::UPDATED_AT);
            $contentListCollection = $contentType->getContentListCollection()
                ->addFieldToFilter(ContentList::STATUS, 1);

            $this->_sitemapItems[] = new \Magento\Framework\DataObject(
                [
                    'changefreq' => $contentType->getSitemapFrequency(),
                    'priority' => $contentType->getSitemapPriority(),
                    'collection' => array_merge(
                        $contentCollection->getItems(),
                        $contentListCollection->getItems()
                    ),
                ]
            );
        }
    }
    
    /**
     * Initialize sitemap items
     *
     * @return void
     */
    protected function _initSitemapItems()
    {
        // Add Content Type pages
        $this->_initContentTypeSitemapItems();
        
        parent::_initSitemapItems();
    }
}
