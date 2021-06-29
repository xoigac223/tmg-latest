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
namespace Blackbird\ContentManager\Block\Content\Widget;

use Blackbird\ContentManager\Model\Content;

class Link extends \Magento\Framework\View\Element\Html\Link implements \Magento\Widget\Block\BlockInterface
{
    /**
     * Prepared href attribute
     *
     * @var string
     */
    protected $_href;

    /**
     * Prepared anchor text
     *
     * @var string
     */
    protected $_anchorText;
    
    /**
     * @var \Blackbird\ContentManager\Model\ResourceModel\Content\CollectionFactory
     */
    protected $_contentCollectionFactory;
    
    /**
     * @var string
     */
    protected $_template = 'Blackbird_ContentManager::content/widget/link_block.phtml';
    
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Blackbird\ContentManager\Model\ResourceModel\Content\CollectionFactory $contentCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Blackbird\ContentManager\Model\ResourceModel\Content\CollectionFactory $contentCollectionFactory,
        array $data = []
    ) {
        $this->_contentCollectionFactory = $contentCollectionFactory;
        parent::__construct($context, $data);
    }
    
    /**
     * Retrieve the content
     * 
     * @return Content
     */
    public function getContent()
    {
        if (!$this->hasData('content')) {
            $content = null;
            
            if ($this->hasData('content_id')) {
                $collection = $this->_contentCollectionFactory->create()
                    ->addAttributeToSelect(['url_key', 'title'])
                    ->addAttributeToFilter(Content::ID, $this->getData('content_id'));
                
                if ($collection->count()) {
                    $content = $collection->getFirstItem();
                }
            }
            
            $this->setData('content', $content);
        }
        
        return $this->getData('content');
    }
    
    /**
     * Prepare url using passed id path and return it
     * or return false if path was not found in url rewrites
     *
     * @return string
     */
    public function getHref()
    {
        if (is_null($this->_href) && $this->getContent()) {
            $this->_href = $this->getContent()->getLinkUrl();
        }
        
        return $this->_href;
    }

    /**
     * Prepare label using passed text as parameter
     *
     * @return string
     */
    public function getLabel()
    {
        if (!$this->_anchorText) {
            if ($this->hasData('anchor_text')) {
                $this->_anchorText = $this->getData('anchor_text');
            } else {
                $this->_anchorText = $this->getContent()->getTitle();
            }
        }

        return $this->_anchorText;
    }

    /**
     * Render block HTML or return empty string if url can't be prepared
     *
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->getHref()) {
            return parent::_toHtml();
        }
        return '';
    }
}
