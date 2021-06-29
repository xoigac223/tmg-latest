<?php

namespace Biztech\Productdesigner\Model\System\Config;

use Magento\Framework\Option\ArrayInterface;

class Parentcategory implements ArrayInterface {

    public function toOptionArray() {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $parent_categories = array();
        $model = $objectManager->create('Biztech\Productdesigner\Model\Mysql4\Quotescategory\Collection');
        $categoriesCollection = $model->getData();
        foreach ($categoriesCollection as $category):
            $parent_categories[$category['quotescategory_id']] = $category['category_title'];
        endforeach;
        return $parent_categories;
    }

}
