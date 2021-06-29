<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */
namespace Firebear\ImportExport\Model\Source\Platform;

class Magmi extends AbstractPlatform
{
    /**
     * Prepare Rows
     *
     * @param $rowData
     *
     * @return mixed
     */
    public function prepareRow($rowData)
    {
        /*visibility phase*/
        if (isset($rowData['visibility'])) {
            $rowData['visibility'] = $this->getVisibilityText($rowData['visibility']);
        }
        
        if (isset($rowData['_store'])) {
            if ($rowData['_store'] == 'admin') {
                $rowData['_store'] = 0;
            }
            $rowData['store_view_code'] = $rowData['_store'];
        } else {
            $rowData['store_view_code'] = '';
        }
        
        if (isset($rowData['website'])) {
            $rowData['product_websites'] = $rowData['website'];
        } else {
            $rowData['product_websites'] = '';
        }
        
        if (isset($rowData['status'])) {
            // 1 - Enabled 2 - Disabled 3 - Out-of-stock
            $status = [2, 1, 3];
            if (isset($status[$rowData['status']])) {
                $rowData['status'] = $status[$rowData['status']];
            } else {
                $rowData['status'] = 0;
            }
        }
        
        /*bundle phase*/
        if (isset($rowData['bundle_options']) && isset($rowData['bundle_skus'])) {
            $values = [];
            $options = explode(';', $rowData['bundle_options']);
            $skus = explode(';', $rowData['bundle_skus']);
            foreach ($options as $option) {
                list($code, $name, $type, $required, $position) = explode(':', $option);
                foreach ($skus as $row) {
                    $data = explode(':', $row);
                    $row_code = $data[0] ?? '';
                    $sku = $data[1] ?? '';
                    $qty = $data[2] ?? '';
                    $change = $data[3] ?? '';
                    $pos = $data[4] ?? '';
                    $default = $data[5] ?? '';
                    $price = $data[6] ?? '';
                    $price_type = $data[7] ?? '';
                    if ($row_code != $code) {
                        continue;
                    }
            
                    $value = [
                        'name=' . $name,
                        'type=' . $type,
                        'required=' . $required,
                        'sku=' . $sku,
                        'price=' . $price,
                        'default=' . $default,
                        'default_qty=' . $qty ,
                        'price_type=' . ($price_type ? 'dynamic' : 'fixed')
                    ];
                    $values[] = implode(',', $value);
                }
            }

            $rowData['bundle_values'] = implode('|', $values);
            
            if (isset($rowData['price_type'])) {
                $rowData['bundle_price_type'] = ($rowData['price_type'] ? 'dynamic' : 'fixed');
            } else {
                $rowData['bundle_price_type'] = 'fixed';
            }
            $rowData['price_type'] = $rowData['bundle_price_type'];
        
            if (isset($rowData['sku_type'])) {
                $rowData['bundle_sku_type'] = $rowData['sku_type'] ? 'dynamic' : 'fixed';
            } else {
                $rowData['bundle_sku_type'] = 'fixed';
            }
            $rowData['sku_type'] = $rowData['bundle_sku_type'];
            
            if (isset($rowData['weight_type'])) {
                $rowData['bundle_weight_type'] = $rowData['weight_type'] ? 'dynamic' : 'fixed';
            } else {
                $rowData['bundle_weight_type'] = 'fixed';
            }
            $rowData['weight_type'] = $rowData['bundle_weight_type'];
            
            if (isset($rowData['price_view'])) {
                $rowData['bundle_price_view'] = $rowData['price_view'] ? 'Price Range' : 'As Low as';
            } else {
                $rowData['bundle_price_view'] = 'Price Range';
            }
            $rowData['price_view'] = $rowData['bundle_price_view'];

            if (isset($rowData['options_container'])) {
                unset($rowData['options_container']);
            }
            
            if (isset($rowData['shipment_type'])) {
                $rowData['shipment_type'] = $rowData['shipment_type'] ? 'Together' : 'Separately';
            } else {
                $rowData['shipment_type'] = 'Together';
            }
        }
        return $rowData;
    }

    /**
     * @param $rowData
     * @return mixed
     */
    public function prepareColumns($rowData)
    {
        return $rowData;
    }

    /**
     * @param $data
     * @param $maps
     * @return mixed
     */
    public function afterColumns($data, $maps)
    {
        $systems = [];
        foreach ($maps as $field) {
            $systems[] = $field['system'];
        }
        foreach ($data as $key => $item) {
            if (!in_array($item, $systems)) {
                unset($data[$key]);
            }
        }
        return $data;
    }

    public function deleteColumns($array)
    {
        return $array;
    }

    public function saveValidatedBunches(
        $source,
        $maxDataSize,
        $bunchSize,
        $dataSourceModel,
        $parameters,
        $entityTypeCode,
        $behavior,
        $processedRowsCount,
        $separator,
        $model
    ) {
        $currentDataSize = 0;
        $bunchRows = [];
        $prevData = [];
        $startNewBunch = false;
        $nextRowBackup = [];
        $repeatStore = 0;
        $source->rewind();
        $dataSourceModel->cleanBunches();
        $configurables = [];
        $simplesCount = 0;
        $file = null;
        $jobId = null;

        if (isset($parameters['file'])) {
            $file = $parameters['file'];
        }
        if (isset($parameters['job_id'])) {
            $jobId = $parameters['job_id'];
        }
        $repeats = 0;
        $end = 0;
        while ($source->valid() || $bunchRows) {
            if ($startNewBunch || !$source->valid()) {
                $dataSourceModel->saveBunches(
                    $entityTypeCode,
                    $behavior,
                    $jobId,
                    $file,
                    $bunchRows
                );
                $bunchRows = $nextRowBackup;
                $currentDataSize = strlen(serialize($bunchRows));
                $startNewBunch = false;
                $nextRowBackup = [];
            }
            if ($source->valid()) {
                try {
                    $rowData = $source->current();
                } catch (\InvalidArgumentException $e) {
                    $model->addRowError($e->getMessage(), $processedRowsCount);
                    $processedRowsCount++;
                    $source->next();
                    continue;
                }
                if (empty($rowData['sku']) && !$end) {
                    $prevData = $this->mergeData($rowData, $prevData, $separator);
                    $source->next();
                    $repeats++;
                    if ($source->valid()) {
                        continue;
                    } else {
                        $end = 1;
                    }
                }

                $rowData = $model->customFieldsMapping($rowData);
                if (empty($rowData['name'])) {
                    $repeatStore = 1;
                }
                if (!empty($prevData) && $repeats > 0) {
                    $this->separator = $separator;
                    $prevData = $this->prepareRow($prevData);
                    if ($simplesCount > 0 && isset($prevData['config'])) {
                        $configurables['items'][] = 'sku=' . $prevData['sku'] . $prevData['config'];
                    }
                    $processedRowsCount++;
                    $rowSize = strlen($model->getJsonHelper()->jsonEncode($prevData));
                    $isBunchSizeExceeded = $bunchSize > 0 && count($bunchRows) >= $bunchSize;
                    if ($currentDataSize + $rowSize >= $maxDataSize || $isBunchSizeExceeded) {
                        $startNewBunch = true;
                        $nextRowBackup = [$processedRowsCount => $prevData];
                    } else {
                        $bunchRows[$processedRowsCount] = $prevData;
                        $currentDataSize += $rowSize;
                    }
                }
                if ($repeatStore) {
                    $simplesCount++;
                    $prevData = array_merge($prevData, $this->deleteEmpty($rowData));
                    if ($simplesCount == 1) {
                        $configurables = ['data' => $prevData, 'items' => []];
                    }
                    $repeatStore = 0;
                } else {
                    if (!empty($configurables)) {
                        $confData = $configurables['data'];
                        $confData['sku'] .= '-Conf';
                        $confData['product_type'] = 'configurable';
                        $confData['configurable_variations'] = implode("|", $configurables['items']);
                        $processedRowsCount++;
                        $rowSize = strlen($model->getJsonHelper()->jsonEncode($confData));
                        $isBunchSizeExceeded = $bunchSize > 0 && count($bunchRows) >= $bunchSize;
                        if ($currentDataSize + $rowSize >= $maxDataSize || $isBunchSizeExceeded) {
                            $startNewBunch = true;
                            $nextRowBackup = [$processedRowsCount => $prevData];
                        } else {
                            $bunchRows[$processedRowsCount] = $confData;
                            $currentDataSize += $rowSize;
                        }
                    }
                    $simplesCount = 0;
                    $prevData = $rowData;
                }
                if (!$end) {
                    $repeats = 1;
                    $key = $source->key();
                }

                $source->next();
                if (!$source->valid() && $end == 0) {
                    $source->rewind();
                    $source->seek($key);
                    $end = 1;
                }
            }
        }

        return $this;
    }

    protected function deleteEmpty($array)
    {
        $newElement = [];
        foreach ($array as $key => $element) {
            if (strlen($element)) {
                $newElement[$key] = $element;
            }
        }

        return $newElement;
    }

    protected function mergeData($rowData, $prevData, $separator)
    {

        $data = $this->deleteEmpty($rowData);
        foreach ($data as $key => $value) {
            if (isset($prevData[$key])) {
                if ($prevData[$key] != $rowData[$key]) {
                    $prevData[$key] .= $separator . $value;
                }
            }
        }

        return $prevData;
    }
}
