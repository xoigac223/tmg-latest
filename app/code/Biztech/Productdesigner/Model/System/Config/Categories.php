<?php

namespace Biztech\Productdesigner\Model\System\Config;

class Categories extends \Magento\Framework\Model\AbstractModel {

    public function toOptionArray() {

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $productCollection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Category\CollectionFactory');

        $categories = $productCollection->create()
                ->addAttributeToSelect('*')
                ->addFieldToFilter('is_active', array('eq' => '1'))
                ->addFieldToFilter('level', array('gteq' => '2'));

        $disabledCategories = $productCollection->create()
                ->addAttributeToSelect('*')
                ->addFieldToFilter('is_active', array('eq' => '0'))
                ->addFieldToFilter('level', array('eq' => 2));
        $discats = array();
        foreach ($disabledCategories as $product_discategory):
            $discats[] = $product_discategory->getId();
        endforeach;

        $allCategories = array();
        foreach ($categories as $category) {

            $enabled = true;
            $path = $category->getPath();

            $ids = explode('/', $path);
            foreach ($ids as $value) {
                if (in_array($value, $discats)) {
                    $enabled = false;
                    break;
                }
            }
            if ($enabled) {
                $label = $category->getName();


                // Trying to create a visiual heiracrchy so you can see what level you're on
                $padLength = ($category->getLevel() - 2) * 2 + strlen($label);
                $label = str_pad($label, $padLength, '- ', STR_PAD_LEFT);
                $allCategories[] = array(
                    'label' => $label,
                    'value' => $category->getId()
                );
            }
        }

        return $allCategories;
    }

}
