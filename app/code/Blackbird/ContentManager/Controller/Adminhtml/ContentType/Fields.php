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
namespace Blackbird\ContentManager\Controller\Adminhtml\ContentType;

class Fields extends \Blackbird\ContentManager\Controller\Adminhtml\ContentType
{
    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $resultLayoutFactory;
    
    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $datetime
     * @param \Blackbird\ContentManager\Model\ResourceModel\ContentType\CollectionFactory $contentTypeCollectionFactory
     * @param \Blackbird\ContentManager\Model\Factory $modelFactory
     * @param \Magento\Framework\App\Cache\Manager $cacheManager
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
        \Blackbird\ContentManager\Model\ResourceModel\ContentType\CollectionFactory $contentTypeCollectionFactory,
        \Blackbird\ContentManager\Model\Factory $modelFactory,
        \Magento\Framework\App\Cache\Manager $cacheManager,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
    ) {
        $this->resultLayoutFactory = $resultLayoutFactory;
        parent::__construct(
            $context,
            $coreRegistry,
            $datetime,
            $contentTypeCollectionFactory,
            $modelFactory,
            $cacheManager
        );
    }
    
    /**
     * Manage custom fields
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $this->_loadContentType();
        
        return $this->resultLayoutFactory->create();
    }
}
