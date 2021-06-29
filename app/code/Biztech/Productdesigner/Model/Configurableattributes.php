<?php
namespace Biztech\Productdesigner\Model;
class Configurableattributes extends \Magento\Framework\Model\AbstractModel
{
    protected $productCollection;
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $coreregistry,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $productCollectionFactory
    ) {
        $this->productCollection = $productCollectionFactory;
        parent::__construct($context,$coreregistry);
    }   
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Biztech\Productdesigner\Model\Mysql4\Configurableattributes');
    }
    public function getAttributesets($id) {
    	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $attribut_array = array();
        $attribut_collection =  $objectManager->create('Biztech\Productdesigner\Model\Mysql4\Configurableattributes\Collection');
        if(isset($id))
        {
            foreach ($attribut_collection->getData() as $attribut_c) {
                if($id != $attribut_c['attribute_id'])                                             
                    $attribut_array[] = $attribut_c['attribute_set_id'];            
            } 
        }          
        else
        { 
            foreach ($attribut_collection->getData() as $attribut_c) {                                             
                $attribut_array[] = $attribut_c['attribute_set_id'];            
            } 
        }                         
        $attributeSetCollection =  $objectManager->create('\Magento\Catalog\Model\Product\AttributeSet\Options')->toOptionArray();                 
        
        $option_array = array();
        $option_array[] = array(
            'value' => 0,
            'label' => 'Please Select Attribute Set'
        );
        
        foreach ($attributeSetCollection as $attributeSet) {
        	
            if(!in_array($attributeSet['value'], $attribut_array))
            {
                $option_array[] = array(
                    'value' => $attributeSet['value'],
                    'label' => $attributeSet['label']
                );
            }
        }

        return $option_array;
    }

    public function getAttributesData($id) {    	

        if(isset($id))
        {        
        	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        	//$attributes = $objectManager->create('Biztech\Productdesigner\Model\Configurableattributes')->load($id); 
            $collection = $this->productCollection->create();

            $collection->addFieldToFilter(
                'frontend_input',
                'select'
            )->addFieldToFilter(
                'is_user_defined',
                1
            )->addFieldToFilter(
                'is_global',
                \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL
            );                                                
            /*$collection = $objectManager->create('\Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory')
                             ->setAttributeSetFilter($attributes->getAttributeSetId())
                            ->addFieldToFilter('frontend_input', 'select') 
                            ->addFieldToFilter('is_user_defined', 1)
                            ->addFieldToFilter('is_global', \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL); */                                    
                            $option_array = array();
                            $attributeS = $objectManager->create('\Magento\ConfigurableProduct\Model\ConfigurableAttributeHandler');
                            foreach ($collection as $c) {                                
                                if ($attributeS->isAttributeApplicable($c)) {
                                    $option_array[] = array(
                                        'value' => $c->getAttributeCode(),
                                        'label' => $c->getFrontendLabel()
                                    );
                                }

                            }
                            return $option_array;
                        }
                    }

                    public function getDefaultAttributes($id) {    	
                       if(isset($id))
                       {
                        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                        $attributes = $objectManager->create('Biztech\Productdesigner\Model\Configurableattributes')->load($id);    		        
                        $attrbutecodes = explode(',',$attributes->getConfigurableAttributes());        
                        $option_array = array();
                        foreach($attrbutecodes as $attribute)
                        {    
                           $attributedata = $objectManager->create('\Magento\Eav\Model\Config')->getAttribute('catalog_product', $attribute);            
                           $option_array[] = array(
                            'value' => $attributedata->getAttributeCode(),
                            'label' => $attributedata->getFrontendLabel()
                        );    
                       }                 
                       return $option_array;
                   }
               }
               
           }
