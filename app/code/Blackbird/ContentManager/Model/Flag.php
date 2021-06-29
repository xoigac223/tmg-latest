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
namespace Blackbird\ContentManager\Model;

use Blackbird\ContentManager\Model\ResourceModel\Flag as ResourceFlag;

class Flag extends \Blackbird\ContentManager\Model\AbstractModel implements \Blackbird\ContentManager\Api\Data\FlagInterface
{
    /**
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init(ResourceFlag::class);
        $this->setIdFieldName(self::ID);
    }
}
