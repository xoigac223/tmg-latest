<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBrand
 */


namespace Amasty\ShopbyBrand\Plugin\Catalog\Model\Layer;

use Magento\Catalog\Model\Layer\State as MagentoStateModel;
use Amasty\ShopbyBrand\Helper\Content;
use Magento\Catalog\Model\Layer\Filter\Item;

class State
{
    /**
     * @var  Content
     */
    protected $contentHelper;

    /**
     * State constructor.
     * @param Content $contentHelper
     */
    public function __construct(Content $contentHelper)
    {
        $this->contentHelper = $contentHelper;
    }

    /**
     * @param MagentoStateModel $subject
     * @param callable $proceed
     * @param Item $filter
     * @return MagentoStateModel
     */
    public function aroundAddFilter(MagentoStateModel $subject, callable $proceed, $filter)
    {
        if ($this->isCurrentBranding($filter->getFilter())) {
            return $subject;
        }
        return $proceed($filter);
    }

    /**
     * @param Item $filter
     * @return bool
     */
    protected function isCurrentBranding($filter)
    {
        $brand = $this->contentHelper->getCurrentBranding();
        return $brand &&
            (\Amasty\ShopbyBase\Helper\FilterSetting::ATTR_PREFIX . $filter->getRequestVar() == $brand->getFilterCode());
    }
}
