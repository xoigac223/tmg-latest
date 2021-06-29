<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Biztech\Productdesigner\Controller\Index;

class emboidery extends \Magento\Framework\App\Action\Action
{   
    protected $directory_list;
     public function __construct(\Magento\Framework\App\Action\Context $context,\Magento\Framework\App\Filesystem\DirectoryList $directory_list) {
        $this->directory_list = $directory_list;  
       
        parent::__construct($context);
    }
    public function execute()
    {
        $embroidery_images = array();
        $params = $this->getRequest()->getParams();
        $urls = json_decode($params['data']);        
        $path = $this->directory_list->getRoot();

        foreach($urls as $key=>$url)
        {             
            $urlarray = explode('.',$url);            
            for($i=0;$i<count($urlarray);$i++)
            {
                if($i == 0)
                {
                    $url1 = '';
                    $url1 = $url1.$urlarray[$i];
                } else if($i == count($urlarray)-1) {                    
                    $url1 = $url1.'-emb.'.$urlarray[$i];
                } else {
                    $url1 = $url1.'.'.$urlarray[$i];
                }
            } 
            $urlpath = explode('pub',$url);
            $urlnewpath = explode('.',$urlpath[1]);         
            $orignalPath = $path . "/pub" . $urlnewpath[0] . "." . $urlnewpath[1];
            if (!file_exists($path . "/pub" . $urlnewpath[0] . "-emb." . $urlnewpath[1])) {                
                //exec("bash " . $path . "/lib/Emboidery/embroidery -n 8 -p crosshatch -t 2 -P n " . $orignalPath . " " . $path . "/pub" . $urlnewpath[0] . "-emb." . $urlnewpath[1]);
                exec("bash " . $path . "/lib/Emboidery/embroidery -n 8 -p crosshatch -t 2 -P preserve " . $orignalPath . " " . $path . "/pub" . $urlnewpath[0] . "-emb." . $urlnewpath[1]);
            }
            $embroidery_images[$key] = $url1;
        }
        $this->getResponse()->setBody(json_encode($embroidery_images));
    }
}