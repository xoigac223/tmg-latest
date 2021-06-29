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
namespace Blackbird\ContentManager\Model\Adapter\Mysql\Aggregation;

use Blackbird\ContentManager\Model\Content;
use Magento\Eav\Model\Config;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\Aggregation\DataProviderInterface;
use Magento\Framework\Search\Request\BucketInterface;

class DataProvider implements DataProviderInterface
{
    /**
     * @var Config
     */
    protected $eavConfig;

    /**
     * @var Resource
     */
    protected $resource;

    /**
     * @var ScopeResolverInterface
     */
    protected $scopeResolver;

    /**
     * @var AdapterInterface
     */
    protected $connection;

    /**
     * @param Config $eavConfig
     * @param ResourceConnection $resource
     * @param ScopeResolverInterface $scopeResolver
     */
    public function __construct(
        Config $eavConfig,
        ResourceConnection $resource,
        ScopeResolverInterface $scopeResolver
    ) {
        $this->eavConfig = $eavConfig;
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->scopeResolver = $scopeResolver;
    }

    /**
     * {@inheritdoc}
     * @todo $currentScope, $attribute
     */
    public function getDataSet(BucketInterface $bucket, array $dimensions, Table $entityIdsTable)
    {
        $currentScope = $this->scopeResolver->getScope($dimensions['scope']->getValue());
        $attribute = $this->eavConfig->getAttribute(Content::ENTITY, $bucket->getField());
        $select = $this->getSelect();

        $select->joinInner(
            ['entities' => $entityIdsTable->getName()],
            'main_table.entity_id  = entities.entity_id',
            []
        );

        return $select;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Select $select)
    {
        return $this->connection->fetchAssoc($select);
    }

    /**
     * @return Select
     */
    protected function getSelect()
    {
        return $this->connection->select();
    }
}
