<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Firebear\ConfigurableProducts\Plugin\Block\Swatches\Product\Renderer\Listing;

/**
 * Swatch renderer block
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Configurable
{
    /**
     * Custom Swatch renderer template.
     */
    const SWATCH_RENDERER_LISTING_TEMPLATE = 'Firebear_ConfigurableProducts::product/view/listing/renderer.phtml';

    /**
     * @param \Magento\Swatches\Block\Product\Renderer\Configurable $subject
     * @param                                                      $template
     *
     * @return string
     */
    public function afterGetTemplate(
        \Magento\Swatches\Block\Product\Renderer\Configurable $subject,
        $template
    ) {
        return self::SWATCH_RENDERER_LISTING_TEMPLATE;
    }
}
