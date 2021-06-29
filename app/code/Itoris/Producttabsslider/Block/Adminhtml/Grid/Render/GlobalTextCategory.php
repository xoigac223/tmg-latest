<?php
/**
 * ITORIS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the ITORIS's Magento Extensions License Agreement
 * which is available through the world-wide-web at this URL:
 * http://www.itoris.com/magento-extensions-license.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to sales@itoris.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extensions to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to the license agreement or contact sales@itoris.com for more information.
 *
 * @category   ITORIS
 * @package    ITORIS_M2_PRODUCT_TABS
 * @copyright  Copyright (c) 2016 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */

namespace Itoris\Producttabsslider\Block\Adminhtml\Grid\Render;


class GlobalTextCategory extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text
{
    public function _getValue(\Magento\Framework\DataObject $row)
    {
        $allCategories = $row->getData('all_categories');
        $categories = $row->getData('categories');
        if (!$categories) $categories = [-1]; else $categories = explode(',',$categories);
        $cats = [];
        foreach($allCategories as $category) if (in_array($category['value'], $categories)) $cats[] = $category['label'];
        return htmlspecialchars(implode(', ', $cats));
    }
}