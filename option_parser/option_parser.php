<?php
$media_base_url = 'http://uat1.themagnetgroup.com.vhost.zerolag.com/pub/media/colors/';
$input_file = 'bg-140-options-template-r3.json';  //DPO Template
$csv_file = '636604504836724435_all-active-items-for-2017201703220432339bg139.xml - final-import (1).csv';
$stock_colors_csv_file = 'StockColors_qry_BG139_v2.csv.csv';
$addl_charges_csv_file = 'additional_charges_all_import_qry_bg139.csv';
$output_file = 'newimport.json';
$imprint_location_file = 'imprint_locations_BG139_clean.csv';

//converting CSV file into array
$csv = array_map('str_getcsv', file($csv_file));
$headers = array_flip($csv[0]);
array_splice($csv, 0, 1); //removing first row with headers

//grouping options by SKU, Printing method and Tier quantiry
$skus = [];
foreach($csv as $line) {
	$sku = $line[$headers['ItemNumber']];
	$printMethod = $line[$headers['PriceMethod']];
	$qty = $line[$headers['QuantityBreak']];
	if (!isset($skus[$sku])) $skus[$sku] = [];
	if (!isset($skus[$sku][$printMethod])) $skus[$sku][$printMethod] = [];
	$skus[$sku][$printMethod][$qty] = $line;
}

//sorting by tier quantity
foreach($skus as & $printMethods) {
	foreach($printMethods as & $qtys) {
		ksort($qtys);
	}
}

//converting Stock Color CSV file into array
$stock_colors_csv = array_map('str_getcsv', file($stock_colors_csv_file));
$headers_stock_colors = array_flip($stock_colors_csv[0]);
array_splice($stock_colors_csv, 0, 1); //removing first row with headers

//grouping colors by SKU
$colors = [];
foreach($stock_colors_csv as $line) {
	$sku = $line[$headers_stock_colors['ItemNumber']];
    preg_match( '/src+.="([^"]*)"/i', $line[$headers_stock_colors['stock_pms_swatch']], $matches ) ;
    $img = $media_base_url.trim(@$matches[1]);
    
	$label = $line[$headers_stock_colors['stock_pms']];
	if (!isset($colors[$sku])) $colors[$sku] = [];
	$colors[$sku][] = [
        'image' => $img,
        'label' => $label
    ];
}

//converting Additional CSV file into array
$additional_csv = array_map('str_getcsv', file($addl_charges_csv_file));
$headers_additional = array_flip($additional_csv[0]);
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
$imprint_location = array_map('str_getcsv', file($imprint_location_file));
$headers_imprint_locations = array_flip($imprint_location[0]);
array_splice($imprint_location, 0, 1); //removing first row with headers

//grouping Imprint Locations by SKU
$imprint_locations = [];
foreach($imprint_location as $line) {
    $sku = $line[$headers_imprint_locations['ItemNumber']];
    //$sku = substr($sku, 0, -2);
    $imprints = $line[$headers_imprint_locations['ImprintLocationName']];
    if (!isset($imprint_locations[$sku])) $imprint_locations[$sku] = [];
    if (!isset($imprint_locations[$imprints])) $imprint_locations[$imprints] = [];
    $imprint_locations[$sku][$imprints] = $line;
}


//decoding from json into PHP array
$dataTemplate = json_decode(file_get_contents($input_file), 1);
$sectionsTemplate = json_decode($dataTemplate[0]['configuration'], 1);

$data = []; $sampleOption = null; $sampleWholesaleOption = null;
foreach($skus as $sku => $printMethods) {
	
	$key = count($data);
	$data[$key] = $dataTemplate[0];
	$sections = $sectionsTemplate;
	
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

				$options = ['Place Order With Logo', 'Order Without Logo', 'Order Sample'];
				foreach($options as $option) {

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
						'customer_group' => '0', //not logged in
						'image_src' => '',
						'use_qty' => 0,
						'is_selected' => $option == 'Place Order With Logo' ? 1 : 0,
						'css_class' => $option == 'Order Sample' ? 'order-sample' : '',
						'tier_price' => json_encode([])
					];
					if ($option == 'Order Without Logo') $sampleOption = & $sections[$sectionId]['fields'][$fieldId]['items'][$order];
					if ($option == 'Place Order With Logo') {
						$maxPrice = 0;
//						foreach($printMethods as $method) {
//							foreach($method as $ln) {
//								if (floatval($ln[$headers['CatalogPrice']]) > $maxPrice) $maxPrice = 0;
//							}
//						}
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
						'is_selected' => $option == 'Place Order With Logo' ? 1 : 0,
						'css_class' => $option == 'Order Sample' ? 'order-sample' : '',
						'tier_price' => json_encode([])
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
                        'is_selected' => $option == 'Place Order With Logo' ? 1 : 0,
                        'css_class' => $option == 'Order Sample' ? 'order-sample' : '',
                        'tier_price' => json_encode([])
                    ];


					if ($option == 'Order Without Logo') $sampleWholesaleOption = & $sections[$sectionId]['fields'][$fieldId]['items'][$order];
					if ($option == 'Place Order With Logo') {
						$maxPrice = 0;
//						foreach($printMethods as $method) {
//							foreach($method as $ln) {
//								if (floatval($ln[$headers['StandardNetPrice']]) > $maxPrice) $maxPrice = floatval($ln[$headers['StandardNetPrice']]);
//							}
//						}
						$sections[$sectionId]['fields'][$fieldId]['items'][$order]['price'] = $maxPrice;
					}
				}
			}

			if ($field['title'] == 'Choose an Imprint Method/Option' || $field['title'] == 'Choose an Imprint Method/Option - Sample') {
				//echo $sku; print_r($sections[$sectionId]['fields'][$fieldId]); exit;

				//$sections[$sectionId]['fields'][$fieldId]['visibility'] = 'hidden';
				//$sections[$sectionId]['fields'][$fieldId]['visibility_action'] = 'visible';
				//$sections[$sectionId]['fields'][$fieldId]['visibility_condition'] = '{"type":"all","value":1,"conditions":[{"type":"field","field":"6","value":"Order Without Logo","condition":"is_not"}]}';

				$sections[$sectionId]['fields'][$fieldId]['items'] = [null];

				foreach($printMethods as $printMethod => $qtys) {

					$tierPrices = [];
					foreach($qtys as $qty => $line) {
						$price = $line[$headers['CatalogPrice']];
						$tierPrices[] = [
							'qty' => $qty,
							'price_type' => 'fixed',
							'price' => $price
						];
					}

					$tierPricesWholesale = [];
					foreach($qtys as $qty => $line) {
						$price = $line[$headers['StandardNetPrice']];
						$tierPricesWholesale[] = [
							'qty' => $qty,
							'price_type' => 'fixed',
							'price' => $price
						];
					}

					if ($printMethod == 'Blank') {
						//updating price and tier price in 'Select an Option Below' instead

						$sampleOption['tier_price'] = json_encode($tierPrices);
						$sampleOption['price'] = count($qtys) ? array_values($qtys)[1][$headers['CatalogPrice']] : 0;
						$sampleWholesaleOption['tier_price'] = json_encode($tierPricesWholesale);
						$sampleWholesaleOption['price'] = count($qtys) ? array_values($qtys)[1][$headers['StandardNetPrice']] : 0;
						continue;
					}

					if ($field['title'] == 'Choose an Imprint Method/Option - Sample') {
						//tier price is not needed for 'Choose an Imprint Method/Option - Sample'
						$tierPrices = [];
						$tierPricesWholesale = [];
					}

					$order = count($sections[$sectionId]['fields'][$fieldId]['items']);

					$sections[$sectionId]['fields'][$fieldId]['items'][] = [ //item for not logged in
						'title' => $printMethod,
						'price' => isset($qtys[1]) ? $qtys[1][$headers['CatalogPrice']] : 0,
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
						'is_selected' => $field['title'] == 'Choose an Imprint Method/Option' && $printMethod == '1 Color 1 Location' ? 1 : 0,
						'tier_price' => json_encode($tierPrices)
					];

					$order = count($sections[$sectionId]['fields'][$fieldId]['items']);

					$sections[$sectionId]['fields'][$fieldId]['items'][] = [ //item for general and wholesalers
						'title' => $printMethod,
						'price' => isset($qtys[1]) ? $qtys[1][$headers['StandardNetPrice']] : 0,
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
						'is_selected' => $field['title'] == 'Choose an Imprint Method/Option' && $printMethod == '1 Color 1 Location' ? 1 : 0,
						'tier_price' => json_encode($tierPricesWholesale)
					];

                    $order = count($sections[$sectionId]['fields'][$fieldId]['items']);

                    $sections[$sectionId]['fields'][$fieldId]['items'][] = [ //item for general and wholesalers
                        'title' => $printMethod,
                        'price' => isset($qtys[1]) ? $qtys[1][$headers['StandardNetPrice']] : 0,
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
                        'is_selected' => $field['title'] == 'Choose an Imprint Method/Option' && $printMethod == '1 Color 1 Location' ? 1 : 0,
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
                    foreach($qtys as $line) {
                        if (floatval($line[$headers['AddColorPrice']]) > $notLoggedInPrice) $notLoggedInPrice = $line[$headers['AddColorPrice']];
                        if (floatval($line[$headers['AddColorNetPrice']]) > $generalPrice) $generalPrice = $line[$headers['AddColorNetPrice']];
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
                    'tier_price' => json_encode([])
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
                    'tier_price' => json_encode([])
                ];

            }

            if ($field['title'] == 'Production Charges') {
                //print_r($sections[$sectionId]['fields'][$fieldId]); exit;
                $sections[$sectionId]['fields'][$fieldId]['items'] = [null];

                foreach($printMethods as $printMethod => $qtys) {
                    if ($printMethod == 'Blank') continue;

                    $notLoggedInPrice = 0;
                    $generalPrice = 0;
                    foreach($qtys as $line) {
                        if (floatval($line[$headers['SetupChargeCatalogPrice']]) > $notLoggedInPrice) $notLoggedInPrice = $line[$headers['SetupChargeCatalogPrice']];
                        if (floatval($line[$headers['NetSetupCharge']]) > $generalPrice) $generalPrice = $line[$headers['NetSetupCharge']];
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
                        'tier_price' => json_encode([])
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
                        'tier_price' => json_encode([])
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
                            'tier_price' => json_encode([])
                        ];
                    }
                } else echo "Warning! Colors not found for SKU: ".$sku."<br />\n";
            }

            if ($field['title'] == 'Select Imprint Location') {
                $sections[$sectionId]['fields'][$fieldId]['items'] = [null];

                if (isset($imprint_locations[$sku])) {
                    foreach($imprint_locations[$sku] as $imprint_location) {
                        $order = count($sections[$sectionId]['fields'][$fieldId]['items']);
                        $sections[$sectionId]['fields'][$fieldId]['items'][] = [
                            'title' => $imprints,
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
                            'tier_price' => json_encode([])
                        ];
                    }
                } else echo "Warning! Imprints Not Found for SKU: ".$sku."<br />\n";
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
                                'tier_price' => json_encode([])						
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
                                'tier_price' => json_encode([])						
                            ];
                        }                        
                    }
                } else echo "Warning! Additional fees not found for SKU: ".$sku."<br />\n";             
            }
		}
	}

	//print_r($sections); exit;

	//encoding back to json
	$data[$key]['configuration'] = json_encode($sections);

	//setting new product SKU
	$data[$key]['product_sku'] = $sku;

}

//creating new import file
file_put_contents($output_file, json_encode($data));

?>
