<?php
namespace Themagnet\Orderstatus\Model;
 
class Orderstatus extends \Magento\Framework\Model\AbstractModel
{
	CONST QUERY_TYPE = 2;

	const CACHE_TAG = 'themagnet_orderstatus';
    protected $_cacheTag = 'themagnet_orderstatus';
    protected $_eventPrefix = 'themagnet_orderstatus';

	protected $_soap;
	protected $_orderCollection;
    protected $_storeManager;
    protected $_product;
    protected $_formkey;
    protected $_quote;
    protected $_quoteManagement;
    protected $_customerFactory;
    protected $_customerRepository;
    protected $_orderService;
	
	protected function _construct()
    {
        $this->_init(\Themagnet\Orderstatus\Model\ResourceModel\Orderstatus::class);
    }
    
	public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Themagnet\Orderstatus\Model\Api\Soap $soap,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollection,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\Data\Form\FormKey $formkey,
        \Magento\Quote\Model\QuoteFactory $quote,
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Sales\Model\Service\OrderService $orderService,
        array $data = []
    ) {
        parent::__construct($context , $registry);
        $this->_orderCollection = $orderCollection;
        $this->_soap = $soap;
        $this->_storeManager = $storeManager;
        $this->_product = $product;
        $this->_formkey = $formkey;
        $this->_quote = $quote;
        $this->_quoteManagement = $quoteManagement;
        $this->_customerFactory = $customerFactory;
        $this->_customerRepository = $customerRepository;
        $this->_orderService = $orderService;
    }

	public function getChangeOrderStatus()
	{
		$param = array('Account'=>$this->getRequestAccount(), 'QueryType'=>$this->getRequestQuerytype(),'ReferenceNumber'=>$this->getRequestReferencenumber());
		$result = $this->_soap->getOrderStatus($param);
        //echo "<prE>";
        //print_r($result); exit;
		/*echo "<prE>";
        print_r($result); */
		/*echo "<prE>";
        print_r($param); 
		$result = $this->_soap->getOrderStatus($param);
        print_r($result); exit;*/
         /*$result = array("getOrderStatusResult" => array
                            (
                                "ErrorMessage" => '',
                                "ExtendedErrorDetails" => '',
                                "OrderStatusArray" => array
                                    (
                                        "OrderStatus" => array
                                            (
                                                "BalanceDueAmount" => "0",
                                                "OrderDate" => "2017-01-19T00:00:00",
                                                "OrderNumber" => "PF0110389",
                                                "OrderQuantity" => "500",
                                                "OrderTotal" => "270",
                                                "PurchaseOrderNumber" => "P111135",
                                                "ShippingAmount" => "0",
                                                "Status" => "Complete",
                                                "WorkOrderArray" => array
                                                    (
                                                        "WorkOrder" => array
                                                            (
                                                                "WorkOrderNumber" => "W170160593",
                                                                "itemArray" => array
                                                                    (
                                                                        "Item" => array
                                                                            (
                                                                                "Carrier" => "UPS",
                                                                                "ExtendedPrice" => "230",
                                                                                "ImprintMethod" => "1 Color 1 Location",
                                                                                "ItemDescription" => "Flip Open Post A Note & Flag Set - Natural",
                                                                                "ItemNumber" => "F200NAT",
                                                                                "OrderQuantity" => "500",
                                                                                "PerPiecePrice" => "0.46",
                                                                                "ProductSubtotal" => "230",
                                                                                "ShipQuantity" => "500",
                                                                                "ShipToAddress1" => "10650 Tobben Dr.",
                                                                                "ShipToAddress2" => "",
                                                                                "ShipToCity" => "Independence",
                                                                                "ShipToCountry" => "",
                                                                                "ShipToName" => "Cengage Learning",
                                                                                "ShipToState" => "KY",
                                                                                "TaxAmount" => "0",
                                                                                "TrackingNumber" => "1Z9101910368055763"
                                                                            )
                     
                                                                    )
                     
                                                            )
                     
                                                    )
                     
                                            )
                     
                                    ),
                     
                               "Success" => ""
                            )
                     
                    );*/
		
        if(isset($result['getOrderStatusResult']['Success'])){
            return $result;
        }
        return $result;
	}

   public function createOrder($orderData, $customer) 
   { 
        $websiteId = $this->_storeManager->getStore()->getWebsiteId();
        
        $data = $this->_getResource()->insertOrder($orderData, $customer, $websiteId);
    }
}