<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */

// @codingStandardsIgnoreFile

namespace Amasty\Shopby\Test\Integration;

use Magento\TestFramework\TestCase\AbstractController;

class RatingFilterTest extends AbstractController
{
    /**
     * @magentoConfigFixture current_store amshopby/rating_filter/enabled 1
     * @magentoConfigFixture current_store amshopby_root/general/enabled 1
     */
    public function testRatingFilter()
    {
        $this->dispatch('amshopby/index/index');
        $body = $this->getResponse()->getBody();
        $message = 'rating filter not found on all-products page';
        $this->assertContains('am-filter-items-rating', $body, $message);
        $this->assertContains('data-amshopby-filter="rating"', $body, $message);
    }
}

