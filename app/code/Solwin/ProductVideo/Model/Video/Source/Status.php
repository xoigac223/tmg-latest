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
namespace Solwin\ProductVideo\Model\Video\Source;

class Status implements \Magento\Framework\Option\ArrayInterface
{
    const ENABLE = 1;
    const DISABLE = 2;


    /**
     * to option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'value' => self::ENABLE,
                'label' => __('Enable')
            ],
            [
                'value' => self::DISABLE,
                'label' => __('Disable')
            ],
        ];
        return $options;

    }
}