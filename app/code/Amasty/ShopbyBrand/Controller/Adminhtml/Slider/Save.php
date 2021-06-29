<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBrand
 */


namespace Amasty\ShopbyBrand\Controller\Adminhtml\Slider;

/**
 * Class Save
 * @package Amasty\ShopbyBrand\Controller\Adminhtml\Slider
 * @author Evgeni Obukhovsky
 */
class Save extends \Amasty\ShopbyBase\Controller\Adminhtml\Option\Save
{
    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_ShopbyBrand::slider');
    }

    protected function _redirectRefer()
    {
        $this->_forward('index');
    }
}
