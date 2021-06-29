<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_InfiniteScroll
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\InfiniteScroll\Model\System;

/**
 * Class Goupspeed
 *
 * @package Bss\InfiniteScroll\Model\System
 */
class Goupspeed extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * Go Up speed Config value
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (null === $this->_options) {
            $this->_options = [
                ['label' => __('Slow'), 'value' => 'slow'],
                ['label' => __('Fast'), 'value' => 'fast'],
            ];
        }
        return $this->_options;
    }
}
