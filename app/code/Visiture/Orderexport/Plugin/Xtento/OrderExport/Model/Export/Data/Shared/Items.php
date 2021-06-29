<?php


namespace Visiture\Orderexport\Plugin\Xtento\OrderExport\Model\Export\Data\Shared;

class Items
{
    protected $designs;

    protected $directoryList;

    public function __construct(
        \Biztech\Productdesigner\Model\DesignsFactory $designs,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList
    ) {
        $this->designs = $designs;
        $this->directoryList = $directoryList;  
    }

    public function afterGetExportData(\Xtento\OrderExport\Model\Export\Data\Shared\Items $subject, $result)
    {
        $mediaPath = $this->directoryList->getPath('media');

        foreach ($result['items'] as $itemKey => $item)
        {
            if(isset($item['additional_options']) && count($item['additional_options']) > 0)
            {
                $additionalOptions = $item['additional_options'];
                foreach ($additionalOptions as $optionKey => $additionalOption) {
                    if(!isset($additionalOption['design_id']) && !isset($additionalOption['value']))
                        continue;
                    
                    if($additionalOption['code'] == "product_design"){
                        if(isset($additionalOption['design_id'])){
                            $designId = $additionalOption['design_id'];
                        }
                        elseif(isset($additionalOption['value']))
                        {
                            $designId = $additionalOption['value'];
                        }
                        $designModel = $this->designs->create()->load($designId);
                        
                        $additionalOption['customer_comments'] = $designModel->getCustomerComments();
                        $layers = json_decode($designModel->getLayerImages(),1);
                        
                        foreach ($layers as $key => $layer) {
                            $additionalOption["imgs"][] =  ['path'=>$mediaPath.$layer['url']];
                            if($layer['type'] == "text")
                            {
                                $additionalOption["lines"][] = ['text' => $layer['text']];
                            }
                        }
                    }
                    $additionalOptions[$optionKey] = $additionalOption;
                }
                $item['additional_options'] = $additionalOptions;
            }
            $result['items'][$itemKey] = $item;
        }

        //echo "<pre>";print_r($result);;die;
        return $result;   
    }
}
