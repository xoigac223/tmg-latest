<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Extrafee
 */


namespace Amasty\Extrafee\Plugin;

class Subselect
{
    /**
     * @var \Magento\SalesRule\Model\Rule\Condition\Product
     */
    private $conditionProduct;

    public function __construct(
        \Magento\SalesRule\Model\Rule\Condition\Product $conditionProduct
    ) {
        $this->conditionProduct = $conditionProduct;
    }

    public function aroundValidate(
        \Magento\SalesRule\Model\Rule\Condition\Product\Subselect $subject,
        \Closure $proceed,
        \Magento\Framework\Model\AbstractModel $model
    ) {
        $validate = $proceed($model);

        if (!$validate) {
            $attr  = $subject->getAttribute();
            $total = 0;
            foreach ($model->getQuote()->getAllItems() as $item) {
                if ($this->conditionProduct->validate($item)) {
                    $total += $item->getData($attr);
                }
            }
            $validate = $subject->validateAttribute($total);
        }

        return $validate;
    }
}