<?php
/**
 * Solwin Infotech
 * Solwin Advanced Product Video Extension
 *
 * @category   Solwin
 * @package    Solwin_ProductVideo
 * @copyright  Copyright © 2006-2016 Solwin (https://www.solwininfotech.com)
 * @license    https://www.solwininfotech.com/magento-extension-license/
 */
namespace Solwin\ProductVideo\Model\System\Config\Source;

class VideoOption implements \Magento\Framework\Option\ArrayInterface
{

    const ON_PAGE = 'page';
    const IN_FANCYBOX = 'fancybox';

    public function toOptionArray() {
        return [
            self::ON_PAGE => __('On Page'),
            self::IN_FANCYBOX => __('In FancyBox')
                ];
    }

}