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

class VideoType implements \Magento\Framework\Option\ArrayInterface
{
    const MEDIA_FILE = 1;
    const YOUTUBE_URL = 2;
    const VIMEO_URL = 3;


    /**
     * to option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'value' => self::MEDIA_FILE,
                'label' => __('Media File')
            ],
            [
                'value' => self::YOUTUBE_URL,
                'label' => __('Youtube URL')
            ],
            [
                'value' => self::VIMEO_URL,
                'label' => __('Vimeo URL')
            ],
        ];
        return $options;

    }
}