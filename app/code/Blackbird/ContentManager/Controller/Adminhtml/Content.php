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
namespace Blackbird\ContentManager\Controller\Adminhtml;

use Blackbird\ContentManager\Model\ContentType as ContentTypeModel;
use Blackbird\ContentManager\Model\Content as ContentModel;

/**
 * Content Controller
 */
abstract class Content extends \Magento\Backend\App\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
    
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_datetime;
    
    /**
     * @var \Blackbird\ContentManager\Model\ContentType
     */
    protected $_contentTypeModel;
    
    /**
     * @var \Blackbird\ContentManager\Model\ResourceModel\ContentType\CollectionFactory
     */
    protected $_contentTypeCollectionFactory;
    
    /**
     * @var \Blackbird\ContentManager\Model\Content
     */
    protected $_contentModel;
    
    /**
     * @var \Blackbird\ContentManager\Model\ResourceModel\Content\CollectionFactory
     */
    protected $_contentCollectionFactory;
    
    /**
     * @var \Blackbird\ContentManager\Model\Factory
     */
    protected $_modelFactory;
    
    /**
     * @var \Magento\Framework\Controller\Result\Redirect
     */
    protected $resultRedirect;
    
    /**
     * @var \Magento\Framework\App\Cache\Manager
     */
    protected $_cacheManager;
    
    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $datetime
     * @param \Blackbird\ContentManager\Model\ResourceModel\ContentType\CollectionFactory $contentTypeCollectionFactory
     * @param \Blackbird\ContentManager\Model\ResourceModel\Content\CollectionFactory $contentCollectionFactory
     * @param \Blackbird\ContentManager\Model\Factory $modelFactory
     * @param \Magento\Framework\App\Cache\Manager $cacheManager
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
        \Blackbird\ContentManager\Model\ResourceModel\ContentType\CollectionFactory $contentTypeCollectionFactory,
        \Blackbird\ContentManager\Model\ResourceModel\Content\CollectionFactory $contentCollectionFactory,
        \Blackbird\ContentManager\Model\Factory $modelFactory,
        \Magento\Framework\App\Cache\Manager $cacheManager
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_datetime = $datetime;
        $this->_contentTypeCollectionFactory = $contentTypeCollectionFactory;
        $this->_contentCollectionFactory = $contentCollectionFactory;
        $this->_modelFactory = $modelFactory;
        $this->resultRedirect = $this->resultRedirectFactory->create();
        $this->_cacheManager = $cacheManager;
    }
    
    /**
     * Load relative Content Type
     * 
     * @return \Blackbird\ContentManager\Controller\Adminhtml\Content
     */
    protected function _loadContentType()
    {
        $contentTypeId = $this->_getCtId();
        
        if (is_numeric($contentTypeId)) {
            $contentTypeCollection = $this->_contentTypeCollectionFactory->create()
                ->addFieldToFilter(ContentTypeModel::ID, $contentTypeId);
            
            if ($contentTypeCollection->count()) {
                $this->_contentTypeModel = $contentTypeCollection->getFirstItem();
            } else {
                $this->messageManager->addErrorMessage(__('This content type no longer exists.'));
                return $this->resultRedirect->setPath('*/contenttype/');
            }
        }
        
        if ($this->_contentTypeModel) {
            $this->_coreRegistry->register('current_contenttype', $this->_contentTypeModel);
        }
        
        return $this;
    }
    
    /**
     * Load Current Content
     * 
     * @return \Blackbird\ContentManager\Controller\Adminhtml\Content
     */
    protected function _loadContent()
    {
        $contentId = $this->getRequest()->getParam('id');
        
        if (is_numeric($contentId)) {
            $contentModel = $this->_modelFactory->create(ContentModel::class)
                                ->setStoreId($this->getRequest()->getParam('store', 0))
                                ->load($contentId);
            
            if (!empty($contentModel->getId())) {
                $this->_contentModel = $contentModel;
            } else {
                $this->messageManager->addErrorMessage(__('This content no longer exists.'));
                return $this->resultRedirect->setPath('*/*/', ['ct_id' => $this->_getCtId()]);
            }
        }
        
        if ($this->_contentModel) {
            $this->_coreRegistry->register('current_content', $this->_contentModel);
        }
        
        return $this;
    }

    /**
     * Initiate action
     *
     * @return this
     */
    protected function _initAction()
    {
        // Load current content
        $this->_loadContent();
        
        // Load current content type
        $this->_loadContentType();
        
        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_Backend::content');
        
        return $this;
    }
    
    /**
     * Returns result of current user permission check on resource and privilege
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Blackbird_ContentManager::contents');
    }
    
    /**
     * Retrieve the related content type id
     * 
     * @return int|null
     */
    protected function _getCtId()
    {
        $contentTypeId = null;
        
        if ($this->_contentModel) {
            // If we had a content, get his content type id
            $contentTypeId = $this->_contentModel->getCtId();
        } else {
            // Get contentTypeId param if provided
            $contentTypeId = (int) $this->getRequest()->getParam('ct_id');
        }
        
        return $contentTypeId;
    }
    
}
