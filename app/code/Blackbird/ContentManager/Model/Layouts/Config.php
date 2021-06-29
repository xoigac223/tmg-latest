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
namespace Blackbird\ContentManager\Model\Layouts;

class Config extends \Magento\Framework\Config\Data implements \Blackbird\ContentManager\Model\Layouts\ConfigInterface
{
    /**
     * @param \Blackbird\ContentManager\Model\ContentType\Layouts\Config\Reader $reader
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param string $cacheId
     */
    public function __construct(
        \Blackbird\ContentManager\Model\Layouts\Config\Reader $reader,
        \Magento\Framework\Config\CacheInterface $cache,
        $cacheId = 'contenttype_layout_layouts'
    ) {
        parent::__construct($reader, $cache, $cacheId);
    }

    /**
     * Get configuration of contenttype layouts by name
     *
     * @param string $name
     * @return array
     */
    public function getField($name)
    {
        return $this->get($name, []);
    }

    /**
     * Get configuration of all registered contenttype layouts
     *
     * @return array
     */
    public function getAll()
    {
        return $this->get();
    }
}
