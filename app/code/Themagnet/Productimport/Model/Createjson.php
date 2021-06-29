<?php
namespace Themagnet\Productimport\Model;
use Magento\Framework\App\Filesystem\DirectoryList;

class Createjson extends \Magento\Framework\Model\AbstractModel
{
	protected $input_file;
	protected $input_file_blank;
	protected $input_file_blank_variation;
    protected $csv_config_file;
    protected $csv_simple_file;
    protected $stock_colors_csv_file;
    protected $addl_charges_csv_file;
    protected $imprint_location_file;
    protected $out_put_file_simple;
    protected $out_put_file_config;
    protected $base_media_url;
    protected $csv_run_file;
    protected $out_put_json;
    protected $_importlogger;
    protected $_output;
    protected $_helper;

	public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Themagnet\Productimport\Model\Csvfiles $csvfiles,
        \Themagnet\Productimport\Helper\Data $helper,
        array $data = []
    ) {
        $this->csvfiles = $csvfiles;
        $this->_helper = $helper;
        parent::__construct($context , $registry);
    }

    public function setExternalFiles($logger, $output = null)
    {
    	$folderPath = $this->csvfiles->setMediaDirectory().'/';
    	$this->input_file = $folderPath.\Themagnet\Productimport\Model\Csvfiles::OUT_PUT_FILE_JSON;
    	$this->input_file_blank = $folderPath.\Themagnet\Productimport\Model\Csvfiles::OUT_PUT_FILE_JSON_BLANK;
    	$this->input_file_blank_variation = $folderPath.\Themagnet\Productimport\Model\Csvfiles::OUT_PUT_FILE_JSON_BLANK_VARIATION;
    	$this->csv_config_file = $folderPath.\Themagnet\Productimport\Model\Csvfiles::OUT_PUT_FILE;
    	$this->csv_simple_file = $folderPath.\Themagnet\Productimport\Model\Csvfiles::OUT_PUT_FILE_SIMPLE;
    	$this->stock_colors_csv_file = $folderPath.\Themagnet\Productimport\Model\Csvfiles::OUT_PUT_FILE_COLOR;
    	$this->addl_charges_csv_file = $folderPath.\Themagnet\Productimport\Model\Csvfiles::OUT_PUT_FILE_ADDITIONAL;
    	$this->imprint_location_file = $folderPath.\Themagnet\Productimport\Model\Csvfiles::OUT_PUT_FILE_QTY;
    	$this->out_put_file_simple = $folderPath.\Themagnet\Productimport\Model\Csvfiles::OUT_PUT_FILE_JSON_SIMPLE;
    	$this->out_put_file_config = $folderPath.\Themagnet\Productimport\Model\Csvfiles::OUT_PUT_FILE_JSON_CONFIG;
    	$this->base_media_url = $this->csvfiles->getMediaUrl();
    	$this->_importlogger = $logger;
    	$this->_output = $output;
    }
    public function createJsonFileFromCsvSimple()
    {
    	$this->csv_run_file = $this->csv_simple_file;
    	$this->out_put_json = $this->out_put_file_simple;
    	return $this->createJsonFileFromCsv();
    }

    public function createJsonFileFromCsvConfig()
    {
    	$this->csv_run_file = $this->csv_config_file;
    	$this->out_put_json = $this->out_put_file_config;
    	return $this->createJsonFileFromCsv();
    }

    public function getOptionsBelow($options , $headers)
    {
    	$optionArray = array();
    	$optionsMain = ['Place Order With Logo', 'Order Without Logo', 'Order Sample'];
    	if(count($options) > 0){
    		foreach ($options as $key => $option) {
    			foreach ($option as $key_value => $value) {
    				$value = (isset($value[$headers['Select an Option Below']]) && $value[$headers['Select an Option Below']] == 'Place Order With Logo')?'Place Order With Logo':$value[$headers['Select an Option Below']];
    				$optionArray[] = isset($value[$headers['Select an Option Below']])?$value:'';
    			}
    		}
    	}
    	$dataArray = array_unique(array_filter($optionArray));
    	return array_values(array_intersect($optionsMain, $dataArray));
    }

    public function getDefaultTemplate($sku)
    {
    	$productSkus = $this->_helper->getBlankProductSkus();
    	$blankVariationProductSkus = $this->_helper->getBlankVariationProductSkus();
    	if(in_array($sku, $productSkus)){
    		$dataTemplate['file'] = json_decode(file_get_contents($this->input_file_blank), 1);
    		$dataTemplate['type'] = 'blank';
    	}elseif(in_array($sku, $blankVariationProductSkus)){
    		$dataTemplate['file'] = json_decode(file_get_contents($this->input_file_blank_variation), 1);
    		$dataTemplate['type'] = 'variation';
    	}else{
    		$dataTemplate['file'] = json_decode(file_get_contents($this->input_file), 1);
    		$dataTemplate['type'] = 'general';
    	}
    	return $dataTemplate;
    }

    public function getKeySortArray($arrayElement)
    {
    	//ksort($array);
    	foreach($arrayElement as & $qtys) {
				ksort($qtys);
		}
		return $arrayElement;
    }

    public function skipKeyValue($arrayValue , $skip = 'Blank')
    {
    	//ksort($array);
    	$dataValue = array();
    	if(count($arrayValue)>1){
    		foreach($arrayValue as $value) {
				if($value != $skip){
					$dataValue[] = $value;
				}
			}
    	}else{
    		$dataValue = $arrayValue;
    	}
    	
		return $dataValue;
    }


    public function getBlankTablateValue($printMethods)
    {
    	if(isset($printMethods) > 0){
    		foreach($printMethods as $key=>$value){
    			if(strtolower($key) != strtolower('Blank')){
    				unset($printMethods[$key]);
    			}
    		}
    	}
    	return $printMethods;
    }


    public function createJsonFileFromCsv()
    {
    	if(filesize($this->csv_run_file) == 0){
    		$this->_importlogger->debugLog((string)__($this->csv_run_file. " file is empty"));
        	if($this->_output){
        		$this->_output->writeln('<comment>'.$this->csv_run_file.' file is empty</comment>');
        	}
        	return false;
    	}
    	$csv = array_map('str_getcsv', file($this->csv_run_file));
		$headers = array_flip($csv[0]);
		array_splice($csv, 0, 1); //removing first row with headers

		//grouping options by SKU, Printing method and Tier quantiry
		$skus = [];
		foreach($csv as $line) {
			$sku = trim($line[$headers['ItemNumber']]);
			$printMethod = trim($line[$headers['PriceMethod']]);
			$qty = $line[$headers['QuantityBreak']];
			$selectBelow = $line[$headers['Select an Option Below']];
			if (!isset($skus[$sku])) $skus[$sku] = [];
			if (!isset($skus[$sku][$printMethod])) $skus[$sku][$printMethod] = [];
			if($selectBelow == 'Order Sample'){
				$skus[$sku][$printMethod][0] = $line;
			}else{
				$skus[$sku][$printMethod][$qty] = $line;
			}
		}

		//sorting by tier quantity
		/*foreach($skus as & $printMethodsArray) {
			foreach($printMethodsArray as & $qtys) {
				ksort($qtys);
			}
		}*/

		//converting Stock Color CSV file into array
		$stock_colors_csv = array_map('str_getcsv', file($this->stock_colors_csv_file));
		if(isset($stock_colors_csv[0])){
			$headers_stock_colors = array_flip($stock_colors_csv[0]);
		}
		array_splice($stock_colors_csv, 0, 1); //removing first row with headers

		//grouping colors by SKU
		$colors = [];
		foreach($stock_colors_csv as $line) {
			//echo "<prE>";
			//print_r($line); exit;
			$sku = $line[$headers_stock_colors['ItemNumber']];
			
		    preg_match( '/src+.="([^"]*)"/i', $line[$headers_stock_colors['stock_pms_swatch']], $matches ) ;
		    $img = $this->base_media_url.trim(@$matches[1]);
		    
			$label = $line[$headers_stock_colors['stock_pms']];
			if (!isset($colors[$sku])) $colors[$sku] = [];
			$colors[$sku][] = [
		        'image' => $img,
		        'label' => $label
		    ];
		}

		//converting Additional CSV file into array
		$additional_csv = array_map('str_getcsv', file($this->addl_charges_csv_file));
		if(isset($additional_csv[0])){
			$headers_additional = array_flip($additional_csv[0]);
		}
		array_splice($additional_csv, 0, 1); //removing first row with headers

		$additional = [];
		foreach($additional_csv as $line) {
			$sku = $line[$headers_additional['ItemNumber']];

		    $priceMethod = $line[$headers_additional['PriceMethod']];
		    
		    if ($priceMethod != 'Embroidery, up to 8000 Stitches') continue;

			if (!isset($additional[$sku])) $additional[$sku] = [
		        'Additional Stitches, per 1000' => [],
		        'Digitizing Fee (Embroidery)' => [],
		        'Personalization' => [],
		        'Swatch Proof' => []
		    ];
		    

		    if ($line[$headers_additional['additional_stitches_catalog_price_em']]) $additional[$sku]['Additional Stitches, per 1000'][] = [
		        'not-logged' => (float) $line[$headers_additional['additional_stitches_catalog_price_em']],
		        'general' => (float) $line[$headers_additional['additional_stitches_net_price_em']]
		    ];
		    if ($line[$headers_additional['digitizing_fee_catalog_price_em']]) $additional[$sku]['Digitizing Fee (Embroidery)'][] = [
		        'not-logged' => (float) $line[$headers_additional['digitizing_fee_catalog_price_em']],
		        'general' => (float) $line[$headers_additional['digitizing_fee_net_price_em']]
		    ];
		    if ($line[$headers_additional['personalization_catalog_price_em']]) $additional[$sku]['Personalization'][] = [
		        'not-logged' => (float) $line[$headers_additional['personalization_catalog_price_em']],
		        'general' => (float) $line[$headers_additional['personalization_net_price_em']]
		    ];
		    if ($line[$headers_additional['swatchproof_catalog_price_em']]) $additional[$sku]['Swatch Proof'][] = [
		        'not-logged' => (float) $line[$headers_additional['swatchproof_catalog_price_em']],
		        'general' => (float) $line[$headers_additional['swatchproof_net_price_em']]
		    ];
		}

		//converting Imprint Location file into array
		$imprint_location = array_map('str_getcsv', file($this->imprint_location_file));
		if(isset($imprint_location[0])){
			$headers_imprint_locations = array_flip($imprint_location[0]);
		}
		array_splice($imprint_location, 0, 1); //removing first row with headers

		//grouping Imprint Locations by SKU
		$imprint_locations = [];
		foreach($imprint_location as $line) {
		    $sku = $line[$headers_imprint_locations['ItemNumber']];
		    //$sku = substr($sku, 0, -2);
		    $imprints = $line[$headers_imprint_locations['ImprintLocationName']];
		    if (!isset($imprint_locations[$sku])) $imprint_locations[$sku] = [];
		    array_push($imprint_locations[$sku], $imprints);  //because there are multiple imprints per sku
		}

		//decoding from json into PHP array
		//$dataTemplate = json_decode(file_get_contents($this->input_file), 1);
		//$sectionsTemplate = json_decode($dataTemplate[0]['configuration'], 1);

		$data = []; $sampleOption = null; $sampleWholesaleOption = null;

		foreach($skus as $sku => $printMethods) {
			/*if($sku == 'BC10'){
				continue;
			}*/
			//echo $sku.'=>';
			/*echo "<prE>";
			print_r($printMethods); exit;*/
			$printMethods = $this->getKeySortArray($printMethods);
			
			$dataTemplateDetail = $this->getDefaultTemplate($sku);
			$dataTemplate = $dataTemplateDetail['file'];
			$dataTemplateType = $dataTemplateDetail['type'];
		    $sectionsTemplate = json_decode($dataTemplate[0]['configuration'], 1);

			$key = count($data);
			$data[$key] = $dataTemplate[0];
			$sections = $sectionsTemplate;
			
			if($dataTemplateType == 'blank'){
				$printMethods = $this->getBlankTablateValue($printMethods);
			}
			
			//walking through sections -> fields -> items
			foreach($sections as $sectionId => $section) {
				if (!is_array($section)) continue;
				foreach($section['fields'] as $fieldId => $field) {
					if (!is_array($field)) continue;

					if ($field['title'] == 'Select an Option Below') {
						$selectOptionField = & $sections[$sectionId]['fields'][$fieldId];
						$sections[$sectionId]['fields'][$fieldId]['price'] = 0;
						$sections[$sectionId]['fields'][$fieldId]['price_type'] = 'fixed';
						$sections[$sectionId]['fields'][$fieldId]['items'] = [null];
						
						
						if(isset($headers['Select an Option Below'])){
							$options = $this->getOptionsBelow($printMethods, $headers);
						}else{
							$options = ['Place Order With Logo', 'Order Without Logo', 'Order Sample'];
						}
						
						foreach($options as $option) {

							$order = count($sections[$sectionId]['fields'][$fieldId]['items']);
							
							$is_selected = $option == $options[0] ? 1 : 0;

							$sections[$sectionId]['fields'][$fieldId]['items'][] = [ //item for not logged in
								'title' => $option,
								'price' => 0,
								'price_type' => 'fixed',
								'sku' => '',
								'order' => $order,
								'sort_order' => $order,
								'internal_id' => $order,
								'visibility' => 'visible',
								'visibility_action' => 'hidden',
								'customer_group' => '0', //not logged in
								'image_src' => '',
								'use_qty' => 0,
								'is_selected' => $is_selected,
								'css_class' => $option == 'Order Sample' ? 'order-sample' : '',
								//'tier_price' => json_encode([])
								'tier_price' => json_encode(array())
							];
							if ($option == 'Order Without Logo') $sampleOption = & $sections[$sectionId]['fields'][$fieldId]['items'][$order];
							if ($option == 'Place Order With Logo') {
								$maxPrice = 0;

								$sections[$sectionId]['fields'][$fieldId]['items'][$order]['price'] = $maxPrice;
							}

							$order = count($sections[$sectionId]['fields'][$fieldId]['items']);

							$sections[$sectionId]['fields'][$fieldId]['items'][] = [ //item for not logged in
								'title' => $option,
								'price' => 0,
								'price_type' => 'fixed',
								'sku' => '',
								'order' => $order,
								'sort_order' => $order,
								'internal_id' => $order,
								'visibility' => 'visible',
								'visibility_action' => 'hidden',
								'customer_group' => '1', //general
								'image_src' => '',
								'use_qty' => 0,
								'is_selected' => $is_selected,
								'css_class' => $option == 'Order Sample' ? 'order-sample' : '',
								//'tier_price' => json_encode([])
								'tier_price' => json_encode(array())
							];

		                    $order = count($sections[$sectionId]['fields'][$fieldId]['items']);

		                    $sections[$sectionId]['fields'][$fieldId]['items'][] = [ //item for not logged in
		                        'title' => $option,
		                        'price' => 0,
		                        'price_type' => 'fixed',
		                        'sku' => '',
		                        'order' => $order,
		                        'sort_order' => $order,
		                        'internal_id' => $order,
		                        'visibility' => 'visible',
		                        'visibility_action' => 'hidden',
		                        'customer_group' => '2', //wholesale
		                        'image_src' => '',
		                        'use_qty' => 0,
		                        'is_selected' => $is_selected,
		                        'css_class' => $option == 'Order Sample' ? 'order-sample' : '',
		                        //'tier_price' => json_encode([])
		                        'tier_price' => json_encode(array())
		                    ];


							if ($option == 'Order Without Logo') $sampleWholesaleOption = & $sections[$sectionId]['fields'][$fieldId]['items'][$order];
							if ($option == 'Place Order With Logo') {
								$maxPrice = 0;

								$sections[$sectionId]['fields'][$fieldId]['items'][$order]['price'] = $maxPrice;
							}
						}
						/*print_r($sections); exit;*/
					}
					if($dataTemplateType == 'blank' || $dataTemplateType == 'variation'){

						//$sections[$sectionId]['fields'][$fieldId]['items'] = [null];

						foreach($printMethods as $printMethod => $qtys) {

							$tierPrices = [];
							foreach($qtys as $qty => $line) {
							    if ($qty != 0) {
								$price = $line[$headers['CatalogPrice']];
								$tierPrices[] = [
									'qty' => $qty,
									'price_type' => 'fixed',
									'price' => $price
								];
		                        }
							}

							$tierPricesWholesale = [];
							foreach($qtys as $qty => $line) {
		                        if ($qty != 0) {
		                            $price = $line[$headers['StandardNetPrice']];
		                            $tierPricesWholesale[] = [
		                                'qty' => $qty,
		                                'price_type' => 'fixed',
		                                'price' => $price
		                            ];
		                        }
							}


							if ($printMethod == 'Blank') {
								//updating price and tier price in 'Select an Option Below' instead
								$sampleOption['tier_price'] = json_encode($tierPrices);
								$sampleOption['price'] = (count($qtys) && isset(array_values($qtys)[1])) ? array_values($qtys)[1][$headers['CatalogPrice']] : 0;
								
								$sampleWholesaleOption['tier_price'] = json_encode($tierPricesWholesale);
								$sampleWholesaleOption['price'] = (count($qtys) && isset(array_values($qtys)[1])) ? array_values($qtys)[1][$headers['StandardNetPrice']] : 0;

								continue;
							}

						}

					}

					if ($field['title'] == 'Choose an Imprint Method/Option') {
						
		                $sections[$sectionId]['fields'][$fieldId]['items'] = [null];


						foreach($printMethods as $printMethod => $qtys) {

							$tierPrices = [];
							foreach($qtys as $qty => $line) {
							    if ($qty != 0) {
								$price = $line[$headers['CatalogPrice']];
								$tierPrices[] = [
									'qty' => $qty,
									'price_type' => 'fixed',
									'price' => $price
								];
		                        }
							}

							$tierPricesWholesale = [];
							foreach($qtys as $qty => $line) {
		                        if ($qty != 0) {
		                            $price = $line[$headers['StandardNetPrice']];
		                            $tierPricesWholesale[] = [
		                                'qty' => $qty,
		                                'price_type' => 'fixed',
		                                'price' => $price
		                            ];
		                        }
							}


							if ($printMethod == 'Blank') {
								//updating price and tier price in 'Select an Option Below' instead
								$sampleOption['tier_price'] = json_encode($tierPrices);
								$sampleOption['price'] = (count($qtys) && isset(array_values($qtys)[1])) ? array_values($qtys)[1][$headers['CatalogPrice']] : 0;
								$sampleWholesaleOption['tier_price'] = json_encode($tierPricesWholesale);
								$sampleWholesaleOption['price'] = (count($qtys) && isset(array_values($qtys)[1])) ? array_values($qtys)[1][$headers['StandardNetPrice']] : 0;
								continue;
							}

							if ($field['title'] == 'Choose an Imprint Method/Option - Sample') {
								//tier price is not needed for 'Choose an Imprint Method/Option - Sample'
								$tierPrices = [];
								$tierPricesWholesale = [];
							}

							$order = count($sections[$sectionId]['fields'][$fieldId]['items']);
							
							$methodKeyValue = $this->skipKeyValue(array_keys($printMethods));

							/*if($sku == 'CKB12'){
								echo "<pre>";
								echo 'SKU=>'.$sku;
								echo "<br>";
								$priceSingle = (count($qtys) && isset($qtys[1]))?$qtys[1][$headers['CatalogPrice']]:0;
								print_r($priceSingle); exit;
							}*/
							$priceNotLogging = (count($qtys) && isset($qtys[1][$headers['CatalogPrice']]))?$qtys[1][$headers['CatalogPrice']]:0;
							$sections[$sectionId]['fields'][$fieldId]['items'][] = [ //item for not logged in
								'title' => $printMethod,
								'price' => $priceNotLogging,//isset($qtys[1]) ? $qtys[1][$headers['CatalogPrice']] : 0,
								'price_type' => 'fixed',
								'sku' => '',
								'order' => $order,
								'sort_order' => $order,
								'internal_id' => $order,
								'visibility' => 'visible',
								'visibility_action' => 'hidden',
								'customer_group' => '0', //not logged in
								'image_src' => '',
								'use_qty' => 0,
		                        'is_selected' => $field['title'] == 'Choose an Imprint Method/Option' && $printMethod == $methodKeyValue[0]  ? 1 : 0,
								'tier_price' => json_encode($tierPrices)
							];

							$order = count($sections[$sectionId]['fields'][$fieldId]['items']);
							$priceGeneral = (count($qtys) && isset($qtys[1][$headers['StandardNetPrice']]))?$qtys[1][$headers['StandardNetPrice']]:0;
							$sections[$sectionId]['fields'][$fieldId]['items'][] = [ //item for general and wholesalers
								'title' => $printMethod,
								'price' => $priceGeneral, //isset($qtys[1]) ? $qtys[1][$headers['StandardNetPrice']] : 0,
								'price_type' => 'fixed',
								'sku' => '',
								'order' => $order,
								'sort_order' => $order,
								'internal_id' => $order,
								'visibility' => 'visible',
								'visibility_action' => 'hidden',
								'customer_group' => '1', //general
								'image_src' => '',
								'use_qty' => 0,
		                        'is_selected' => $field['title'] == 'Choose an Imprint Method/Option' && $printMethod == $methodKeyValue[0]  ? 1 : 0,
								'tier_price' => json_encode($tierPricesWholesale)
							];

		                    $order = count($sections[$sectionId]['fields'][$fieldId]['items']);
		                    $priceWholesale = (count($qtys) && isset($qtys[1][$headers['StandardNetPrice']]))?$qtys[1][$headers['StandardNetPrice']]:0;
		                    $sections[$sectionId]['fields'][$fieldId]['items'][] = [ //item for general and wholesalers
		                        'title' => $printMethod,
		                        'price' => $priceWholesale, //isset($qtys[1]) ? $qtys[1][$headers['StandardNetPrice']] : 0,
		                        'price_type' => 'fixed',
		                        'sku' => '',
		                        'order' => $order,
		                        'sort_order' => $order,
		                        'internal_id' => $order,
		                        'visibility' => 'visible',
		                        'visibility_action' => 'hidden',
		                        'customer_group' => '2', //wholesale
		                        'image_src' => '',
		                        'use_qty' => 0,
		                        'is_selected' => $field['title'] == 'Choose an Imprint Method/Option' && $printMethod == $methodKeyValue[0]  ? 1 : 0,
		                        'tier_price' => json_encode($tierPricesWholesale)
		                    ];
						}

						//echo $sku; print_r($sections[$sectionId]['fields'][$fieldId]); exit;

					}

					if ($field['title'] == 'Choose an Imprint Method/Option - Sample') {
						
		                $sections[$sectionId]['fields'][$fieldId]['items'] = [null];

		                

						foreach($printMethods as $printMethod => $qtys) {

							$tierPrices = [];
							foreach($qtys as $qty => $line) {
							    //if ($qty != 1) {
								$price = $line[$headers['CatalogPrice']];
								$tierPrices[] = [
									'qty' => $qty,
									'price_type' => 'fixed',
									'price' => $price
								];
		                        //}
							}

							$tierPricesWholesale = [];
							foreach($qtys as $qty => $line) {
		                        //if ($qty != 1) {
		                            $price = $line[$headers['StandardNetPrice']];
		                            $tierPricesWholesale[] = [
		                                'qty' => $qty,
		                                'price_type' => 'fixed',
		                                'price' => $price
		                            ];
		                        //}
							}


							

							if ($printMethod == 'Blank') {
								//updating price and tier price in 'Select an Option Below' instead
								$sampleOption['tier_price'] = json_encode($tierPrices);
								$sampleOption['price'] = (count($qtys) && isset(array_values($qtys)[1])) ? array_values($qtys)[1][$headers['CatalogPrice']] : 0;
								$sampleWholesaleOption['tier_price'] = json_encode($tierPricesWholesale);
								$sampleWholesaleOption['price'] = (count($qtys) && isset(array_values($qtys)[1])) ? array_values($qtys)[1][$headers['StandardNetPrice']] : 0;
								continue;
							}

							//if ($field['title'] == 'Choose an Imprint Method/Option - Sample') {
								//tier price is not needed for 'Choose an Imprint Method/Option - Sample'
								$tierPrices = [];
								$tierPricesWholesale = [];
							//}

							$order = count($sections[$sectionId]['fields'][$fieldId]['items']);
							
							
							$methodSimpleKeyValue = $this->skipKeyValue(array_keys($printMethods));
							$priceSimpleNotLogging = (count($qtys) && isset($qtys[0][$headers['CatalogPrice']]))?$qtys[0][$headers['CatalogPrice']]:0;
							$sections[$sectionId]['fields'][$fieldId]['items'][] = [ //item for not logged in
								'title' => $printMethod,
								'price' => $priceSimpleNotLogging,//isset($qtys[1]) ? $qtys[1][$headers['CatalogPrice']] : 0,
								'price_type' => 'fixed',
								'sku' => '',
								'order' => $order,
								'sort_order' => $order,
								'internal_id' => $order,
								'visibility' => 'visible',
								'visibility_action' => 'hidden',
								'customer_group' => '0', //not logged in
								'image_src' => '',
								'use_qty' => 0,
		                        'is_selected' => $field['title'] == 'Choose an Imprint Method/Option - Sample' && $printMethod == $methodSimpleKeyValue[0]  ? 1 : 0,
								'tier_price' => json_encode($tierPrices)
							];

							$order = count($sections[$sectionId]['fields'][$fieldId]['items']);
							$priceSimpleGeneral = (count($qtys) && isset($qtys[0][$headers['StandardNetPrice']]))?$qtys[0][$headers['StandardNetPrice']]:0;
							$sections[$sectionId]['fields'][$fieldId]['items'][] = [ //item for general and wholesalers
								'title' => $printMethod,
								'price' => $priceSimpleGeneral, //isset($qtys[1]) ? $qtys[1][$headers['StandardNetPrice']] : 0,
								'price_type' => 'fixed',
								'sku' => '',
								'order' => $order,
								'sort_order' => $order,
								'internal_id' => $order,
								'visibility' => 'visible',
								'visibility_action' => 'hidden',
								'customer_group' => '1', //general
								'image_src' => '',
								'use_qty' => 0,
		                        'is_selected' => $field['title'] == 'Choose an Imprint Method/Option - Sample' && $printMethod == $methodSimpleKeyValue[0]  ? 1 : 0,
								'tier_price' => json_encode($tierPricesWholesale)
							];

		                    $order = count($sections[$sectionId]['fields'][$fieldId]['items']);
		                    $priceSimpleWholesale = (count($qtys) && isset($qtys[0][$headers['StandardNetPrice']]))?$qtys[0][$headers['StandardNetPrice']]:0;
		                    $sections[$sectionId]['fields'][$fieldId]['items'][] = [ //item for general and wholesalers
		                        'title' => $printMethod,
		                        'price' => $priceSimpleWholesale, //isset($qtys[1]) ? $qtys[1][$headers['StandardNetPrice']] : 0,
		                        'price_type' => 'fixed',
		                        'sku' => '',
		                        'order' => $order,
		                        'sort_order' => $order,
		                        'internal_id' => $order,
		                        'visibility' => 'visible',
		                        'visibility_action' => 'hidden',
		                        'customer_group' => '2', //wholesale
		                        'image_src' => '',
		                        'use_qty' => 0,
		                        'is_selected' => $field['title'] == 'Choose an Imprint Method/Option - Sample' && $printMethod == $methodSimpleKeyValue[0]  ? 1 : 0,
		                        'tier_price' => json_encode($tierPricesWholesale)
		                    ];
						}

						//echo $sku; print_r($sections[$sectionId]['fields'][$fieldId]); exit;

					}

		            if ($field['title'] == 'Add Color/Run Charge') {
		                //print_r($sections[$sectionId]['fields'][$fieldId]); exit;
		                $sections[$sectionId]['fields'][$fieldId]['items'] = [null];
		                $notLoggedInPrice = 0;
		                $generalPrice = 0;
		                foreach($printMethods as $printMethod => $qtys) {
		                    foreach($qtys as $qty2=>$line) {
		                    	if ($qty2 != 0) {
			                        if (floatval($line[$headers['AddColorPrice']]) > $notLoggedInPrice) $notLoggedInPrice = $line[$headers['AddColorPrice']];
			                        if (floatval($line[$headers['AddColorNetPrice']]) > $generalPrice) $generalPrice = $line[$headers['AddColorNetPrice']];
		                    	}
		                    }
		                }
		                $order = count($sections[$sectionId]['fields'][$fieldId]['items']);

		                $sections[$sectionId]['fields'][$fieldId]['items'][] = [ //item for not logged in
		                    'title' => 'Charge Per Color',
		                    'price' => $notLoggedInPrice,
		                    'price_type' => 'fixed',
		                    'sku' => '',
		                    'order' => $order,
		                    'sort_order' => $order,
		                    'internal_id' => $order,
		                    'visibility' => 'visible',
		                    'visibility_action' => 'hidden',
		                    'customer_group' => '0', //not logged in
		                    'is_selected' => 0,
		                    'image_src' => '',
		                    'use_qty' => 0,
		                    //'tier_price' => json_encode([])
		                    'tier_price' => json_encode(array())
		                ];

		                $order = count($sections[$sectionId]['fields'][$fieldId]['items']);

		                $sections[$sectionId]['fields'][$fieldId]['items'][] = [ //item for general and wholesalers
		                    'title' => 'Charge Per Color',
		                    'price' => $generalPrice,
		                    'price_type' => 'fixed',
		                    'sku' => '',
		                    'order' => $order,
		                    'sort_order' => $order,
		                    'internal_id' => $order,
		                    'visibility' => 'visible',
		                    'visibility_action' => 'hidden',
		                    'customer_group' => '1,2', //general and wholesale
		                    'is_selected' => 0,
		                    'image_src' => '',
		                    'use_qty' => 0,
		                    //'tier_price' => json_encode([])
		                    'tier_price' => json_encode(array())
		                ];

		            }

		            if ($field['title'] == 'Production Charges') {
		                //print_r($sections[$sectionId]['fields'][$fieldId]); exit;
		                $sections[$sectionId]['fields'][$fieldId]['items'] = [null];

		                foreach($printMethods as $printMethod => $qtys) {
		                    if ($printMethod == 'Blank') continue;

		                    $notLoggedInPrice = 0;
		                    $generalPrice = 0;
		                    foreach($qtys as $qty1=>$line) {
		                    	if ($qty1 != 0) {
			                        if (floatval($line[$headers['SetupChargeCatalogPrice']]) > $notLoggedInPrice) $notLoggedInPrice = $line[$headers['SetupChargeCatalogPrice']];
			                        if (floatval($line[$headers['NetSetupCharge']]) > $generalPrice) $generalPrice = $line[$headers['NetSetupCharge']];
		                    	}
		                    }

		                    $order = count($sections[$sectionId]['fields'][$fieldId]['items']);

		                    $sections[$sectionId]['fields'][$fieldId]['items'][] = [ //item for not logged in
		                        'title' => $printMethod,
		                        'price' => $notLoggedInPrice,
		                        'price_type' => 'fixed',
		                        'sku' => '',
		                        'order' => $order,
		                        'sort_order' => $order,
		                        'internal_id' => $order,
		                        'visibility' => 'hidden',
		                        'visibility_action' => 'visible',
		                        'visibility_condition' => '{"type":"all","value":1,"conditions":[{"type":"field","field":"2","value":"'.$printMethod.'","condition":"is"}]}',
		                        'customer_group' => '0', //not logged in
		                        'image_src' => '',
		                        'use_qty' => 0,
		                        'is_selected' => 0,
		                        //'tier_price' => json_encode([])
		                        'tier_price' => json_encode(array())
		                    ];

		                    $order = count($sections[$sectionId]['fields'][$fieldId]['items']);

		                    $sections[$sectionId]['fields'][$fieldId]['items'][] = [ //item for general and wholesalers
		                        'title' => $printMethod,
		                        'price' => $generalPrice,
		                        'price_type' => 'fixed',
		                        'sku' => '',
		                        'order' => $order,
		                        'sort_order' => $order,
		                        'internal_id' => $order,
		                        'visibility' => 'hidden',
		                        'visibility_action' => 'visible',
		                        'visibility_condition' => '{"type":"all","value":1,"conditions":[{"type":"field","field":"2","value":"'.$printMethod.'","condition":"is"}]}',
		                        'customer_group' => '1,2', //general and wholesale
		                        'image_src' => '',
		                        'use_qty' => 0,
		                        'is_selected' => 1,
		                        //'tier_price' => json_encode([])
		                        'tier_price' => json_encode(array())
		                    ];

		                }
		            }

		            if ($field['title'] == 'Stock/PMS Colors') {
		                //print_r($sections[$sectionId]['fields'][$fieldId]); exit;
		                $sections[$sectionId]['fields'][$fieldId]['items'] = [null];

		                if (isset($colors[$sku])) {
		                    foreach($colors[$sku] as $color) {
		                        $order = count($sections[$sectionId]['fields'][$fieldId]['items']);
		                        $sections[$sectionId]['fields'][$fieldId]['items'][] = [
		                            'title' => $color['label'],
		                            'price' => 0,
		                            'price_type' => 'fixed',
		                            'sku' => '',
		                            'order' => $order,
		                            'sort_order' => $order,
		                            'internal_id' => $order,
		                            'visibility' => 'visible',
		                            'visibility_action' => 'hidden',
		                            'customer_group' => '',
		                            'is_selected' => 0,
		                            'image_src' => $color['image'],
		                            'swatch' => 1,
		                            'carriage_return' => 0,
		                            'use_qty' => 0,
		                            //'tier_price' => json_encode([])
		                            'tier_price' => json_encode(array())
		                        ];
		                    }
		                } else { 
		                	$this->_importlogger->debugLog((string)__("Warning! Colors not found for SKU: ".$sku));
		                	if($this->_output){
		                		$this->_output->writeln('<comment>Colors not found for SKU: '.$sku.'</comment>');
		                	}
		            	}
		            }

		            if ($field['title'] == 'Select Imprint Location') {
		                $sections[$sectionId]['fields'][$fieldId]['items'] = [null];

		                if (isset($imprint_locations[$sku])) {
		                    foreach($imprint_locations[$sku] as $imprint_location) {
		                        $order = count($sections[$sectionId]['fields'][$fieldId]['items']);
		                        $sections[$sectionId]['fields'][$fieldId]['items'][] = [
		                            'title' => $imprint_location,
		                            'price' => 0,
		                            'price_type' => 'fixed',
		                            'sku' => '',
		                            'order' => $order,
		                            'sort_order' => $order,
		                            'internal_id' => $order,
		                            'visibility' => 'visible',
		                            'visibility_action' => 'hidden',
		                            'customer_group' => '',
		                            'is_selected' => 0,
		                            'image_src' => '',
		                            'swatch' => '',
		                            'carriage_return' => 0,
		                            'use_qty' => 0,
		                            //'tier_price' => json_encode([])
		                            'tier_price' => json_encode(array())
		                        ];
		                    }
		                } else{ 
		                	$this->_importlogger->debugLog((string)__("Warning! Imprints Not Found for SKU: ".$sku));
		                	if($this->_output){
		                		$this->_output->writeln('<comment>Imprints Not Found for SKU: '.$sku.'</comment>');
		                	}
		            	}
		            }


		            if ($field['title'] == 'Additional Stitches, per 1000' || $field['title'] == 'Digitizing Fee (Embroidery)' || $field['title'] == 'Personalization' || $field['title'] == 'Swatch Proof') {
		                
		                if (isset($additional[$sku])) {
		                    foreach($additional[$sku] as $code => $fees) {
		                        if ($field['title'] != $code) continue;
		                        
		                        $sections[$sectionId]['fields'][$fieldId]['items'] = [null];
		                        
		                        foreach($fees as $fee) {
		                            $order = count($sections[$sectionId]['fields'][$fieldId]['items']);                        
		                            $sections[$sectionId]['fields'][$fieldId]['items'][] = [ //item for not logged in
		                                'title' => $code,
		                                'price' => $fee['not-logged'],
		                                'price_type' => 'fixed',
		                                'sku' => '',
		                                'order' => $order,
		                                'sort_order' => $order,
		                                'internal_id' => $order,
		                                'visibility' => 'visible',
		                                'visibility_action' => 'hidden',
		                                'customer_group' => '0', //not logged in
		                                'is_selected' => 0,
		                                'image_src' => '',
		                                'use_qty' => 0,
		                                //'tier_price' => json_encode([])
		                                'tier_price' => json_encode(array())						
		                            ];
		                            
		                            $order = count($sections[$sectionId]['fields'][$fieldId]['items']);                        
		                            $sections[$sectionId]['fields'][$fieldId]['items'][] = [ //item for general and wholesalers
		                                'title' => $code,
		                                'price' => $fee['general'],
		                                'price_type' => 'fixed',
		                                'sku' => '',
		                                'order' => $order,
		                                'sort_order' => $order,
		                                'internal_id' => $order,
		                                'visibility' => 'visible',
		                                'visibility_action' => 'hidden',
		                                'customer_group' => '1,2', //general and wholesale
		                                'is_selected' => 0,
		                                'image_src' => '',
		                                'use_qty' => 0,
		                                //'tier_price' => json_encode([])
		                                'tier_price' => json_encode(array())						
		                            ];
		                        }                        
		                    }
		                } else {

		                	$this->_importlogger->debugLog((string)__("Warning! Additional fees not found for SKU: ".$sku));
		                	if($this->_output){
		                		$this->_output->writeln('<comment>Additional fees not found for SKU: '.$sku.'</comment>');
		                	}
		                } 
		            }
				}
			}
			/*if($sku == 'JB4260'){
				echo "<pre>";
				echo 'SKU=>'.$sku;
				echo "<br>";
				
				print_r($sections); exit;
			}*/
			//echo $sku.'=><br>';
			$data[$key]['configuration'] = json_encode($sections);

			//setting new product SKU
			$data[$key]['product_sku'] = $sku;

		}
		//exit;
		file_put_contents($this->out_put_json, json_encode($data));
		return true;
	}
}