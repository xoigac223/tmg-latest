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
 * @package    ITORIS_M2_DYNAMIC_PRODUCT_OPTIONS
 * @copyright  Copyright (c) 2016 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */

namespace Bss\ConfigurableProductWholesaleCustomize\Model;

class Options extends \Itoris\DynamicProductOptions\Model\Options
{
    public function getSections() {
        if (is_null($this->sections)) {
            $sections = [];
            $usedOptionIds = [];
            $maxOrder = 0;
            $maxSectionOrder = 0;
            if ($this->getProductId()) {
                $defaultOptions = [];
                if ($this->getConfiguration()) {
                    $sections = \Zend_Json::decode($this->getConfiguration());
                    foreach ($sections as $key => $value) {
                        if (is_array($value) && isset($value['fields'])) {
                            (array)$sections[$key]['fields'];
                        }
                    }
                    /** @var $allOptions \Itoris\DynamicProductOptions\Model\ResourceModel\Option\Collection */
                    $allOptions = $this->_objectManager->create('Itoris\DynamicProductOptions\Model\Option')->getCollection();
                    $allOptions->addFieldToFilter('product_id', $this->getProductId())
                        ->addFieldToFilter('store_id', $this->getStoreId());
                    if ($this->getDataHelper()->isFrontend() || $this->getRequest()->getControllerName() == 'order_create') {
                        //$allOptions->addCustomerGroupFilter();
                    }
                    $resultOptions = [];
                    foreach ($allOptions as $option) {
                        $optionConfig = [];
                        foreach ($defaultOptions as $_defOption) {
                            if ($_defOption['option_id'] == $option->getOrigOptionId()) {
                                $optionConfig = $_defOption;
                                break;
                            }
                        }
                        if ($option->getOrigOptionId()) {
                            $usedOptionIds[] = $option->getOrigOptionId();
                        }
                        if ($option->getConfiguration()) {
                            $optionConfig = array_merge($optionConfig, \Zend_Json::decode($option->getConfiguration()));
                            if (!in_array($optionConfig['type'], $this->customTypes)) {
                                //lost option
                                if ($option->getOrigOptionId()) {
                                    if (!isset($optionConfig['option_id'])
                                        || !isset($optionConfig['section_order'])
                                        || !array_key_exists($optionConfig['section_order'], $sections)
                                    ) {
                                        $_defOptionObj = $this->_objectManager->create('Magento\Catalog\Model\Product\Option')->load($option->getOrigOptionId());
                                        if ($_defOptionObj) {
                                            $_defOptionObj->delete();
                                        }
                                        continue;
                                    }
                                } else {
                                    $option->delete();
                                    continue;
                                }
                            }
                            $optionConfig['img_src'] = $this->correctBaseImageUrl(@$optionConfig['img_src']);
                            $optionConfig['itoris_option_id'] = $option->getId();
                            $optionConfig['order'] = intval($optionConfig['order']);
                            if ($optionConfig['order'] > $maxOrder) {
                                $maxOrder = $optionConfig['order'];
                            }
                            if ($optionConfig['section_order'] > $maxSectionOrder) {
                                $maxSectionOrder = $optionConfig['section_order'];
                            }
                            if (!empty($optionConfig['items'])) {
                                $valuesIds = [];
                                foreach ($optionConfig['items'] as $key => $item) {
                                    $valuesIds[] = $item['option_type_id'];
                                }
                                $optionItems = $this->_objectManager->create('Itoris\DynamicProductOptions\Model\Option\Value')->getCollection()
                                    ->addFieldToFilter('orig_value_id', ['in' => $valuesIds])
                                    ->addFieldToFilter('product_id', ['eq' => $this->getProductId()])
                                    ->addFieldToFilter('store_id', ['eq' => intval($this->getStoreId())]);
                                if ($this->getDataHelper()->isFrontend() || $this->getRequest()->getControllerName() == 'order_create') {
                                    //$optionItems->addCustomerGroupFilter();
                                }
                                $dynamicItems = [];
                                foreach ($optionItems as $item) {
                                    if ($item->getConfiguration()) {
                                        foreach ($optionConfig['items'] as $_origItem) {
                                            if ($_origItem['option_type_id'] == $item['orig_value_id']) {
                                                $dynamicItems[] = array_merge($_origItem, \Zend_Json::decode($item->getConfiguration()));
                                            }
                                        }
                                    }
                                }
                                if ($this->getDataHelper()->isFrontend() || $this->getRequest()->getControllerName() == 'order_create') {
                                    foreach ($dynamicItems as &$_dynamicItem) {
                                        if (array_key_exists('sku_is_product_id', $_dynamicItem) && (int)$_dynamicItem['sku_is_product_id']) {
                                            $_dynamicItem['sku_is_product_id'] = 1;
                                            /** @var $product \Magento\Catalog\Model\Product */
                                            $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($_dynamicItem['sku']);
                                            $_dynamicItem['is_salable'] = $product->getId() && $product->getStatus() == 1 && $product->isSalable();
                                        } else {
                                            $_dynamicItem['sku_is_product_id'] = 0;
                                        }
                                        if (!array_key_exists('use_qty', $_dynamicItem)) {
                                            $_dynamicItem['use_qty'] = 0;
                                        } else $_dynamicItem['use_qty'] = !!intval($_dynamicItem['use_qty']);
                                    }
                                }
                                $optionConfig['items'] = $this->_sortByOrder($dynamicItems);
                                if (is_array($optionConfig['items'])) {
                                    foreach($optionConfig['items'] as $key => $item) {
                                        $optionConfig['items'][$key]['image_src'] = $this->correctBaseImageUrl(@$item['image_src']);
                                    }
                                }
                            }
                        }
                        if (!isset($resultOptions[$optionConfig['section_order']])) {
                            $resultOptions[$optionConfig['section_order']] = [];
                        }
                        $resultOptions[$optionConfig['section_order']][] = $optionConfig;
                    }

                    $defaultOptionsAdded = false;
                    foreach ($defaultOptions as $defOption) {
                        if (!in_array($defOption['option_id'], $usedOptionIds)) {
                            $defOption['section_order'] = $maxSectionOrder;
                            $defOption['order'] = ++$maxOrder;
                            $resultOptions[$maxSectionOrder][] = $defOption;
                            $defaultOptionsAdded = true;
                        }
                    }
                    if ($defaultOptionsAdded) {
                        if (!isset($sections[$maxSectionOrder]['cols'])) {
                            $sections[$maxSectionOrder]['cols'] = 3;
                        }
                        $minSectionRows = $maxOrder / $sections[$maxSectionOrder]['cols'];
                        if (!isset($sections[$maxSectionOrder]['rows'])) {
                            $sections[$maxSectionOrder]['rows'] = 3;
                        }
                        if ($minSectionRows > $sections[$maxSectionOrder]['rows']) {
                            $sections[$maxSectionOrder]['rows'] = $minSectionRows;
                        }
                    }
                    foreach ($resultOptions as $sectionOrder => $sectionOptions) {
                        if (isset($sections[$sectionOrder])) {
                            $sections[$sectionOrder]['fields'] = $this->_sortByOrder($sectionOptions);
                        }
                    }
                } else {
                    $defaultOptions = $this->_getDefaultOptions();
                    if (count($defaultOptions)) {
                        $order = 1;
                        foreach ($defaultOptions as &$_defOption) {
                            $_defOption['order'] = $order++;
                        }
                        $sections = [
                            [
                                'order'     => 1,
                                'cols'      => 1,
                                'rows'      => count($defaultOptions),
                                'removable' => 1,
                                'fields'    => $defaultOptions,
                            ],
                        ];
                        $this->setFormStyle('list_div')->setAppearance('on_product_view');
                    }
                }
            }
            if (!$sections) {
                $sections = [];
            }
            $this->sections = $sections;
        }
        return $this->sections;
    }
}