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
namespace Blackbird\ContentManager\Ui\Component\Listing\Column\Options;

use Magento\Framework\Data\OptionSourceInterface;

class StatusOptions implements OptionSourceInterface
{
    const DISABLED = 0;
    const ENABLED = 1;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => __('Disabled'),
                'value' => self::DISABLED,
            ],
            [
                'label' => __('Enabled'),
                'value' => self::ENABLED,
            ],
        ];
    }
}
