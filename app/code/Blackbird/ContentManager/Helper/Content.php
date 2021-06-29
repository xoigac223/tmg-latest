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
namespace Blackbird\ContentManager\Helper;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Content retriever helper
 */
class Content extends \Magento\Framework\Url\Helper\Data
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Blackbird\ContentManager\Model\Content
     */
    protected $_content;

    /** 
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Blackbird\ContentManager\Api\Data\ContentInterface $content
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $coreRegistry,
        \Blackbird\ContentManager\Api\Data\ContentInterface $content
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_content = $content;
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * Initialize the Content to be used for the Content Controller actions and layouts
     *
     * @param int $contentId
     * @param \Magento\Framework\App\Action\Action $controller
     * @return \Blackbird\ContentManager\Model\Content|bool
     */
    public function initContent($contentId, $controller)
    {
        // Init and load content
        $this->_eventManager->dispatch(
            'contentmanager_controller_content_init_before',
            ['controller_action' => $controller]
        );

        if (!$contentId) {
            return false;
        }

        try {
            $content = $this->_content;
            $content->setData('store_id', $this->_storeManager->getStore()->getId());
            $content->load($contentId);
        } catch (NoSuchEntityException $e) {
            return false;
        }

        // Register current data and dispatch final events
        $this->_coreRegistry->register('current_content', $content);

        try {
            $this->_eventManager->dispatch(
                'contentmanager_controller_content_init_after',
                ['content' => $content, 'controller_action' => $controller]
            );
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->_logger->critical($e);
            return false;
        }

        return $content;
    }
}
