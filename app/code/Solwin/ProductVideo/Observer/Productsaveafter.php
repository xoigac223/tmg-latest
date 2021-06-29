<?php

namespace Solwin\ProductVideo\Observer;

use Magento\Framework\Event\ObserverInterface;

class Productsaveafter implements ObserverInterface
{

  /**
   * Video model
   *
   * @var \Solwin\ProductVideo\Model\VideoFactory
   */
  protected $_videoFactory;

  protected $_coreSession;

  public function __construct(
    \Solwin\ProductVideo\Model\VideoFactory $videoFactory,
    \Magento\Framework\App\RequestInterface $request,
    \Magento\Framework\Session\SessionManagerInterface $coreSession
  ) {
      $this->_videoFactory = $videoFactory;
      $this->_coreSession = $coreSession;
      $this->_request = $request;
  }

  public function getVideoById($video_id) {
    $collection = $this->_videoFactory->create();
    $collection->load($video_id);
    return $collection;
  }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $post = $this->_request->getPost();
        $_product = $observer->getProduct();  // you will get product object
        $_productvideo=$_product->getProductvideo();
            $_productid = $_product->getEntityId();
            $this->_coreSession->start();
            if(!isset($post["video"])) {
              $_dbproductvideo =$this->_coreSession->getProductvideo();
              $dbvideo_data = explode(',', $_dbproductvideo);
              $_productvideo=$_product->getProductvideo();
              $video_data = explode(',', $_productvideo);
              $delete_result=array_values(array_diff($dbvideo_data,$video_data));
              $add_result=array_values(array_diff($video_data,$dbvideo_data));
              $delete_result = array_filter($delete_result);
              $delete_result = array_values($delete_result);
              $add_result = array_filter($add_result);
              $add_result = array_values($add_result);
              for($i = 0 ;$i<count($delete_result); $i++) {
                $video_id = $delete_result[$i];
                $_video = $this->getVideoById($video_id);
                $array=$_video->getData();
                $old_product = $array["products"];
                $old_array = explode("&",$old_product);
                $old_array = array_values(array_filter($old_array));
                if(in_array($_productid,$old_array)) {
                  $old_array = array_diff($old_array, array($_productid));
                }
                $new_array = implode("&" , $old_array);
                $_video->setProducts($new_array);
                $_video->save();
              }

              for($i = 0 ;$i<count($add_result); $i++) {
                $video_id = $add_result[$i];
                $_video = $this->getVideoById($video_id);
                $array=$_video->getData();
                $old_product = $array["products"];
                $old_array = explode("&",$old_product);
                $old_array = array_values(array_filter($old_array));
                if(!in_array($_productid,$old_array)) {
                  $s = count($old_array) + 1;
                  $old_array[$s] = $_productid;
                }
                $new_array = implode("&" , $old_array);
                $_video->setProducts($new_array);
                $_video->save();
              }
              $this->_coreSession->unsProductvideo();
            }

    }
}
?>
