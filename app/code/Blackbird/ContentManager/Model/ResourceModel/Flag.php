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
namespace Blackbird\ContentManager\Model\ResourceModel;

use Blackbird\ContentManager\Api\Data\FlagInterface;

class Flag extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('blackbird_contenttype_flag', FlagInterface::ID);
    }
    
    /**
     * Add a new entry in table flag
     * 
     * @param int $storeId
     * @param string $value
     */
    public function addFlag($storeId, $value)
    {
        $bind = [
            FlagInterface::VALUE => (string) $value,
            FlagInterface::ID => (int) $storeId,
        ];
        
        $this->getConnection()->insert($this->getMainTable(), $bind);
    }
}
