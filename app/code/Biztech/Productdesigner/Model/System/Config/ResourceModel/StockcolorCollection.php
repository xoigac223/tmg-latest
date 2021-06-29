<?php

namespace Biztech\Productdesigner\Model\System\Config\ResourceModel\Stockcolors;

class StockcolorCollection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection{
	public function _construct(){
		$this->_init("Biztech\Productdesigner\Model\System\Config\Stockcolors","Biztech\Productdesigner\Model\System\Config\ResourceModel\Stockcolors");
	}
}