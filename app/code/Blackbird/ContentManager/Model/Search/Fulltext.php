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
namespace Blackbird\ContentManager\Model\Search;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb as DbCollection;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Search\Model\QueryFactory;
use Blackbird\ContentManager\Model\ResourceModel\Fulltext as ResourceFulltext;

/**
 * Content advanced search model
 *
 * @method \Blackbird\ContentManager\Model\ResourceModel\Fulltext _getResource()
 * @method \Blackbird\ContentManager\Model\ResourceModel\Fulltext getResource()
 * @method int getContentId()
 * @method \Blackbird\ContentManager\Model\Fulltext setContentId(int $value)
 * @method int getStoreId()
 * @method \Blackbird\ContentManager\Model\Fulltext setStoreId(int $value)
 * @method string getDataIndex()
 * @method \Blackbird\ContentManager\Model\Fulltext setDataIndex(string $value)
 */
class Fulltext extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Catalog search data
     *
     * @var QueryFactory
     */
    protected $queryFactory = null;

    /**
     * Core store config
     *
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param QueryFactory $queryFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param AbstractResource $resource
     * @param DbCollection $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        QueryFactory $queryFactory,
        ScopeConfigInterface $scopeConfig,
        AbstractResource $resource = null,
        DbCollection $resourceCollection = null,
        array $data = []
    ) {
        $this->queryFactory = $queryFactory;
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceFulltext::class);
    }

    /**
     * Reset search results cache
     *
     * @return $this
     */
    public function resetSearchResults()
    {
        $this->getResource()->resetSearchResults();
        return $this;
    }
}
