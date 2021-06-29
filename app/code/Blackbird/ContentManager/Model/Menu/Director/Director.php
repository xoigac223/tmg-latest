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
namespace Blackbird\ContentManager\Model\Menu\Director;

use \Magento\Backend\Model\Menu\Builder;
use \Psr\Log\LoggerInterface;

class Director extends \Magento\Backend\Model\Menu\Director\Director
{    
    /**
     * @var \Blackbird\ContentManager\Model\ResourceModel\ContentType\CollectionFactory
     */
    protected $_contentTypeCollectionFactory;
    
    /**
     * @param \Magento\Backend\Model\Menu\Builder\CommandFactory $factory
     * @param \Blackbird\ContentManager\Model\ResourceModel\ContentType\CollectionFactory $contentTypeCollectionFactory
     */
    public function __construct(
        Builder\CommandFactory $factory,
        \Blackbird\ContentManager\Model\ResourceModel\ContentType\CollectionFactory $contentTypeCollectionFactory
    ) {
        parent::__construct($factory);
        $this->_contentTypeCollectionFactory = $contentTypeCollectionFactory;
    }
    
    /**
     * Build menu instance
     *
     * @param array $config
     * @param \Magento\Backend\Model\Menu\Builder $builder
     * @param \Psr\Log\LoggerInterface $logger
     * @return void
     */
    public function direct(array $config, Builder $builder, LoggerInterface $logger)
    {
        parent::direct($config, $builder, $logger);
        
        /**
         * Add dynamic menu of contenttypes
         */
        $configContentType = [];
        $contentTypes = $this->_contentTypeCollectionFactory->create();
        
        if ($contentTypes->count()) {
            foreach ($contentTypes as $contentType) {
                $configContentType[] = [
                    'type' => 'add',
                    'id' => 'Blackbird_ContentManager::content_'.$contentType->getId(),
                    'title' => ucwords($contentType->getTitle()),
                    'module' => 'Blackbird_ContentManager',
                    'action' => 'contentmanager/content/index/ct_id/'.$contentType->getId(),
                    'parent' => 'Blackbird_ContentManager::contents',
                    //'resource' => 'Blackbird_ContentManager::content_'.$contentType->getId(),
                    'resource' => 'Blackbird_ContentManager::contents',
                ];
            }
        } else {
            $configContentType[] = [
                    'type' => 'add',
                    'id' => 'Blackbird_ContentManager::content_empty',
                    'title' => __('Add a Content Type first')->__toString(),
                    'module' => 'Blackbird_ContentManager',
                    'action' => 'contentmanager/contenttype/new/',
                    'parent' => 'Blackbird_ContentManager::contents',
                    'resource' => 'Blackbird_ContentManager::contenttype',
            ];
        }
            
        foreach ($configContentType as $data) {
            $builder->processCommand($this->_getCommand($data, $logger));
        }
        
    }
}
