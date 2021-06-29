<?php


namespace Visiture\Orderexport\Plugin\Xtento\OrderExport\Model\Export\Data\Order;

class General
{

    public function afterGetExportData(\Xtento\OrderExport\Model\Export\Data\Order\General $subject, $result)
    {
    	/*echo "<pre>";print_r($result);;die;
    	$result["aaaaa"] = "aaaaa";*/
     	return $result;   
    }
}
