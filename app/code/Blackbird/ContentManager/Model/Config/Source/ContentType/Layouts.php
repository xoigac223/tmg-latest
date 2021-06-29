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

class Layouts implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * ContentType Layouts Config
     * 
     * @var \Blackbird\ContentManager\Model\Layouts\ConfigInterface
     */
    protected $_layoutsConfig;

    /**
     * @var array
     */
    protected $options;

    /**
     * @param \Blackbird\ContentManager\Model\Layouts\ConfigInterface $config
     */
    public function __construct(\Blackbird\ContentManager\Model\Layouts\ConfigInterface $config)
    {
        $this->_layoutsConfig = $config;
    }
        
    /**
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options === null) {
            $groups = [];

            foreach ($this->_layoutsConfig->getAll() as $layout) {
                $group = [];
                foreach ($layout['layout'] as $lay) {
                    if ($lay['disabled']) {
                        continue;
                    }
                    $group[] = [
                        'label' => __($lay['label']),
                        'value' => $lay['id']
                    ];
                }
                if (count($group)) {
                    $groups[] = [
                        'label' => __($layout['label']),
                        'value' => $group,
                        'optgroup-name' => $lay['label']
                    ];
                }
            }

            $this->options = $groups;
        }

        return $this->options;
    }
    
    /**
     * @return array
     */
    public function toArray()
    {        
        return $this->_layoutsConfig->getAll();
    }
    
    /**
     * Retrieve the data of the given layout id
     * 
     * @param string $layoutId
     * @return array
     */
    public function retrieveLayout($layoutId)
    {
        $layoutData = [];
        
        // If the layout does not exists
        if (!$this->layoutExists($layoutId)) {
            return $layoutData;
        }
        
        foreach ($this->_layoutsConfig->getAll() as $layout) {
            foreach ($layout['layout'] as $lay) {
                if ($lay['disabled']) {
                    continue;
                }

                if ($lay['id'] == $layoutId) {
                    $layoutData = $lay;
                }
            }
        }
        
        return $layoutData;
    }
    
    /**
     * Check if a given layout id exists
     * 
     * @param string $layoutId
     * @return boolean
     */
    public function layoutExists($layoutId)
    {
        $exists = false;
        
        foreach ($this->_layoutsConfig->getAll() as $layout) {
            foreach ($layout['layout'] as $lay) {
                if ($lay['disabled']) {
                    continue;
                }
                
                if ($lay['id'] == $layoutId) {
                    $exists = true;
                }
            }
        }
        
        return $exists;
    }
}
