<?php
namespace Themagnet\Orderstatus\Controller\Adminhtml\Index;

use Magento\Directory\Model\ResourceModel\Region\Collection;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory;

class Save extends \Magento\Backend\App\Action
{
    protected $_inventory;
    protected $_customer;
    protected $_productRepository;
    protected $_collectionFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Themagnet\Orderstatus\Model\Orderstatus $inventory,
        \Magento\Customer\Model\Customer $customers,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        CollectionFactory $collectionFactory
    ) {
        $this->_inventory = $inventory;
        $this->_customer = $customers;
        $this->_productRepository = $productRepository;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $dynamicRowData = $this->getRequest()->getParams();
        if(empty($dynamicRowData) !== true && isset($dynamicRowData['request_account']) && isset($dynamicRowData['request_querytype']) && isset($dynamicRowData['request_referencenumber'])){
            $customer = $this->_customer->load($dynamicRowData['request_account']);
            $dynamicRowData['request_account'] = $customer->getTmgEncryptAccount();
            try {
                $model = $this->_inventory;
                $model->setRequestAccount($dynamicRowData['request_account']);
                $model->setRequestQuerytype($dynamicRowData['request_querytype']);
                $model->setRequestReferencenumber($dynamicRowData['request_referencenumber']);
                $model->setCreatedTime(date('Y-m-d H:i:s'));
                $data = $model->getChangeOrderStatus();
                $orderData = array();
                if (isset($data['getOrderStatusResult'])) {
                    
                    

                    $orderData = [
                        // 'currency_id'  => 'USD',
                        'email'        => $customer->getEmail(), //buyer email id
                        'order_date'        => $data['getOrderStatusResult']['OrderStatusArray']['OrderStatus']['OrderDate'],
                        'OrderQuantity'        => $data['getOrderStatusResult']['OrderStatusArray']['OrderStatus']['OrderQuantity'],
                        'ShippingAmount'        => $data['getOrderStatusResult']['OrderStatusArray']['OrderStatus']['ShippingAmount'],
                        'Status'        => $data['getOrderStatusResult']['OrderStatusArray']['OrderStatus']['Status'],
                        'PurchaseOrderNumber'        => $data['getOrderStatusResult']['OrderStatusArray']['OrderStatus']['PurchaseOrderNumber'],
                        'OrderNumber'        => $data['getOrderStatusResult']['OrderStatusArray']['OrderStatus']['OrderNumber'],
                        'Carrier'        => $data['getOrderStatusResult']['OrderStatusArray']['OrderStatus']['WorkOrderArray']['WorkOrder']['itemArray']['Item']['Carrier'],
                        'TrackingNumber'        => $data['getOrderStatusResult']['OrderStatusArray']['OrderStatus']['WorkOrderArray']['WorkOrder']['itemArray']['Item']['TrackingNumber'],
                        /*'shipping_address' => [
                            'firstname'    => $customer->getFirstname(), //address Details
                            'lastname'     => $customer->getLastname(),
                            'street' => $street ,
                            'city' => $data['getOrderStatusResult']['OrderStatusArray']['OrderStatus']['WorkOrderArray']['WorkOrder']['itemArray']['Item']['ShipToCity'],
                            'country_id' => $region['country_id'],
                            'region' => $data['getOrderStatusResult']['OrderStatusArray']['OrderStatus']['WorkOrderArray']['WorkOrder']['itemArray']['Item']['ShipToState'],
                            'postcode' => '',
                            'telephone' => '',
                            'fax' => '',
                            'save_in_address_book' => 0
                        ],
                        'items'=> [ //array of product which order you want to create
                            ['product_id' => $product->getId(), 
                             'qty' => $data['getOrderStatusResult']['OrderStatusArray']['OrderStatus']['WorkOrderArray']['WorkOrder']['itemArray']['Item']['OrderQuantity'],
                             'price' => $data['getOrderStatusResult']['OrderStatusArray']['OrderStatus']['WorkOrderArray']['WorkOrder']['itemArray']['Item']['PerPiecePrice']
                         ]
                        ]*/
                    ];
                     if(isset($data['getOrderStatusResult']['OrderStatusArray']['OrderStatus']['WorkOrderArray']['WorkOrder']['itemArray'])){
                        $orderedItem = array();
                        $shippingAddress = array();
                        $orderTotal = 0;
                        foreach ($data['getOrderStatusResult']['OrderStatusArray']['OrderStatus']['WorkOrderArray']['WorkOrder']['itemArray'] as $key => $item) {
                            $product = $this->_productRepository->get($item['ItemNumber']);
                            $region = $this->_collectionFactory->create()
                                ->addFieldToFilter('code', ['eq' => $item['ShipToState']])
                                ->getFirstItem()
                                ->toArray(); 
                            $orderedItem[] = array('product_id' => $product->getId(),
                                                   'qty' => $item['OrderQuantity'],
                                                   'price'=>$item['PerPiecePrice'],
                                                   'sku' => $product->getSku(),
                                                   'name' => $product->getName(), 
                                                   'type' => $product->getTypeId(),
                                                   'weight' => $product->getWeight(),
                                                   'OrderQuantity' => $item['OrderQuantity'],
                                                   'row_total' => $item['OrderQuantity']*$item['PerPiecePrice'],
                                                   'TrackingNumber' => $item['TrackingNumber'],
                                                   'TaxAmount' => $item['TaxAmount'],

                                               );
                            $street = $item['ShipToAddress1'] . (($item['ShipToAddress2']) ? ', ' . $item['ShipToAddress2'] : '');
                            $shippingAddress = [
                                    'firstname'    => $customer->getFirstname(), //address Details
                                    'lastname'     => $customer->getLastname(),
                                    'street' => $street ,
                                    'city' => $item['ShipToCity'],
                                    'country_id' => $region['country_id'],
                                    'region' => $item['ShipToState'],
                                    'postcode' => '60005',
                                    'telephone' => '123456789',
                                    'fax' => '',
                                    'save_in_address_book' => 0
                                ];
                            $orderTotal = $orderTotal + ($item['OrderQuantity']*$item['PerPiecePrice']);
                        
                        }
                     }

                     $orderData['shipping_address'] = $shippingAddress;
                     $orderData['items'] = $orderedItem;
                     $orderData['orderTotal'] = $orderTotal;
                 
                }

                 $model->createOrder($orderData, $customer);


                //$model->save();
                $this->messageManager->addSuccessMessage(__('Order status have been saved successfully'));
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__($e->getMessage()));
            }
        }else{
            $this->messageManager->addErrorMessage(__('Please enter valid data'));
        }
        $this->_redirect('*/*/index');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Themagnet_Orderstatus::orderstatus');
    }
}