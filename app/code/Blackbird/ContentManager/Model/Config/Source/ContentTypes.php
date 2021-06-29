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
namespace Blackbird\ContentManager\Model\Config\Source;

class ContentTypes implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Blackbird\ContentManager\Model\ResourceModel\ContentType\CollectionFactory
     */
    protected $_ctCollectionFactory;
    
    /**
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;
    
    /**
     * @param \Blackbird\ContentManager\Model\ResourceModel\ContentType\CollectionFactory $ctCollectionFactory
     * @param \Magento\Framework\Escaper $escaper
     */
    public function __construct(
        \Blackbird\ContentManager\Model\ResourceModel\ContentType\CollectionFactory $ctCollectionFactory,
        \Magento\Framework\Escaper $escaper
    ) {
        $this->_ctCollectionFactory = $ctCollectionFactory;
        $this->_escaper = $escaper;
    }
    
    /**
     * @return array
     */
    public function toOptionArray($default = false)
    {
        $collection = $this->_ctCollectionFactory->create();
        $array = [];
        
        if ($default) {
            $array[] = [
                'label' => __('All Content Types'),
                'value' => 0
            ];
        }
        
        foreach ($collection as $contenttype) {
            $array[] = [
                'label' => $this->_escaper->escapeQuote($contenttype->getTitle(), true),
                'value' => $contenttype->getIdentifier()
            ];
        }
        
        return $array;
    }
    
    /**
     * @return array
     */
    public function getOptions()
    {
        $collection = $this->_ctCollectionFactory->create();
        $array = [];
        
        foreach ($collection as $contenttype) {
            $array[$contenttype->getCtId()] = $contenttype->getTitle();
        }
        
        return $array;
    }
}
