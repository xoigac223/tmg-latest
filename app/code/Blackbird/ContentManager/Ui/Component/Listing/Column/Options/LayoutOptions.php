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
use Blackbird\ContentManager\Model\Config\Source\ContentType\Layouts;

class LayoutOptions implements OptionSourceInterface
{
    /**
     * @var Layouts
     */
    protected $layouts;

    /**
     * @var array
     */
    protected $options;

    /**
     * @param Layouts $layouts
     */
    public function __construct(
        Layouts $layouts
    ) {
        $this->layouts = $layouts;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options === null) {
            $this->generateCurrentOptions();
        }

        return $this->options;
    }

    /**
     * Generate current options
     *
     * @return void
     */
    protected function generateCurrentOptions()
    {
        $options = [];

        foreach ($this->layouts->toOptionArray() as $parent) {
            if (is_array($parent['value'])) {
                foreach ($parent['value'] as $item) {
                    $options[] = [
                        'label' => $item['label'],
                        'value' => $item['value']
                    ];
                }
            } else {
                $options[] = [
                    'label' => $parent['label'],
                    'value' => $parent['value']
                ];
            }
        }

        $this->options = $options;
    }
}
