<?php
namespace Biztech\Productdesigner\Block\Adminhtml\Fonts\Gallery;
use Magento\Backend\Block\Media\Uploader;
use Magento\Framework\View\Element\AbstractBlock;

class Content extends \Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element implements
       \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{
       
       protected $_template = 'Biztech_Productdesigner::productdesigner/fonts/gallery/content.phtml';
       protected $_mediaConfig;
       protected $_jsonEncoder;

      public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Catalog\Model\Product\Media\Config $mediaConfig,
        array $data = []
        ) {
            $this->_jsonEncoder = $jsonEncoder;
            $this->_mediaConfig = $mediaConfig;
            parent::__construct($context, $data);
      }

      public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
      {
               $this->_element = $element;
               $html = $this->toHtml();
              return $html;
      }
      public function getImagesJson()
      {

          $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
          $images = $objectManager->create('Biztech\Productdesigner\Model\Mysql4\Fonts\Collection');
          if (count($images)){
            foreach($images as $image){
              

              $demo = $objectManager->create('\Magento\Store\Model\StoreManagerInterface');
              $url = $demo->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'productdesigner/fonts' . $image['image_path'];

              $image['url'] = $url;
              $image['file'] = $image['font_file'];
              $image['font_id'] = $image['font_id'];
              $image['label'] = $image['font_label'];
              $image['tags'] = $image['tags'];
              $image['position'] = $image['position'];
              $image['disabled'] = $image['disabled'];

            }
            $val1 = $this->_jsonEncoder->encode($images);
            $val2 = strstr($val1, '[', false);
            $val3 = rtrim($val2, "}");
            return $val3;
          }
          return '[]';  
      }



}