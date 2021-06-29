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
namespace Blackbird\ContentManager\Model\Config\Source\ContentType;

class Breadcrumb implements \Magento\Framework\Option\ArrayInterface
{    
    /**
     * @var \Blackbird\ContentManager\Model\Config\Source\ContentType\CustomFields
     */
    protected $customFieldSource;
    
    /**
     * @var int
     */
    protected $contentTypeId;
    
    /**
     * @param \Blackbird\ContentManager\Model\Config\Source\ContentType\CustomFields $customFieldsSource
     */
    public function __construct(
        \Blackbird\ContentManager\Model\Config\Source\ContentType\CustomFields $customFieldsSource
    ) {
        $this->customFieldSource = $customFieldsSource;
    }

    /**
     * @param int $contentTypeId
     */
    public function setContentTypeId($contentTypeId)
    {
        if (is_numeric($contentTypeId)) {
            $this->contentTypeId = $contentTypeId;
        }
    }

    /**
     * @return int
     */
    public function getContentTypeId()
    {
        return $this->contentTypeId;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $return = [
            ['value' => '', 'label' => __('No Breadcrumb')],
            ['value' => 'title', 'label' => __('Page Title')]
        ];
        
        if (!empty($this->getContentTypeId())) {
            $arrayFields = $this->customFieldSource->toOptionArrayByContentType($this->contentTypeId);
            $return = array_merge($return, $arrayFields);
        }
        
        return $return;
    }
}
