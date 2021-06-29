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
namespace Blackbird\ContentManager\Model\ResourceModel\ContentList\Layout\Block;

use Blackbird\ContentManager\Model\ContentList\Layout\Block;
use Blackbird\ContentManager\Model\ResourceModel\ContentList\Layout\Block as ResourceBlock;

/**
 * Layout Block Resource Model Collection
 */
class Collection extends \Blackbird\ContentManager\Model\ResourceModel\ContentType\Layout\Block\Collection
{
    /**
     * Initialize resource collection
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(Block::class, ResourceBlock::class);
    }
}
