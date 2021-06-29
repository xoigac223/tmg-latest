<?php
namespace Themagnet\Orderstatus\Controller\Index;

use Magento\Customer\Model\Session;
use Magento\Framework\Controller\ResultFactory;

class Ordercheck extends \Magento\Customer\Controller\AbstractAccount
{
    protected $_orderstatus;

    protected $_customers;

    protected $_helper;
    protected $resultPageFactory;
    
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Themagnet\Orderstatus\Model\Orderstatus $inventory,
        \Magento\Customer\Model\Customer $customers,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Themagnet\Orderstatus\Helper\Data $helper,
        Session $customerSession
    ) {
        parent::__construct($context);
        $this->_customers = $customers;
        $this->_orderstatus = $inventory;
        $this->resultPageFactory = $resultPageFactory;
        $this->_helper = $helper;
    }
   
    public function execute()
    {

        if ($this->_helper->getEnableModule()) {
            
            $dynamicRowData = $this->getRequest()->getParams();
            $result = array();
            if(empty($dynamicRowData) !== true && isset($dynamicRowData['request_account']) && isset($dynamicRowData['request_querytype']) && isset($dynamicRowData['request_referencenumber'])){

                try {
                    $model = $this->_orderstatus;
                    $model->setRequestAccount($dynamicRowData['request_account']);
                    $model->setRequestQuerytype($dynamicRowData['request_querytype']);
                    $model->setRequestReferencenumber($dynamicRowData['request_referencenumber']);
                    $model->setCreatedTime(date('Y-m-d H:i:s'));
                    $data = $model->getChangeOrderStatus();
                    if (isset($data['getOrderStatusResult']['OrderStatusArray']) && empty($data['getOrderStatusResult']['OrderStatusArray']) !== true) {
                        $result['success'] = 1;
                        $result['html'] = $this->createHtml($data);
                    }else{
                        $result['error'] = 1;
                        $result['message'] = __('Data not found');
                    }
                    $orderData = array();                
                    } catch (\Exception $e) {
                       $result['error'] = 1;
                       $result['message'] = __($e->getMessage());
                    }
                }else{
                    $result['error'] = 1;
                    $result['message'] = __('Data invalid');
                }
            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $resultJson->setData($result);
            return $resultJson;
        } else {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('home');
            return $resultRedirect;
        }
    }

    public function createHtml($data)
    {
        $html = '';
        $html .= '
        <h1 class="page-title">
        <span class="base" data-ui-id="page-title-wrapper">'.__('Order #%1',$data['getOrderStatusResult']['OrderStatusArray']['OrderStatus']['OrderNumber']).'</span>    </h1>
    
    <div class="order-date" style="margin: 7px;">
        '.__('<strong> Order Status </strong>: %1',$data['getOrderStatusResult']['OrderStatusArray']['OrderStatus']['Status']).'
    </div>
    <div class="order-date" style="margin: 7px;">
        '.__('<strong> Order Date </strong>: %1',date('d F Y H:i:s',strtotime($data['getOrderStatusResult']['OrderStatusArray']['OrderStatus']['OrderDate']))).'
    </div>

    <div class="order-date" style="margin: 7px;">
        '.__('<strong> Purchase Order Number </strong>: %1',$data['getOrderStatusResult']['OrderStatusArray']['OrderStatus']['PurchaseOrderNumber']).'
    </div>

    <div class="order-date" style="margin: 7px;">
        '.__('<strong> Shipping Amount </strong>: %1',$data['getOrderStatusResult']['OrderStatusArray']['OrderStatus']['ShippingAmount']).'
    </div>
    <div class="order-date" style="margin: 7px;">
        '.__('<strong> Order Total </strong>: %1',$data['getOrderStatusResult']['OrderStatusArray']['OrderStatus']['OrderTotal']).'
    </div>
            <div class="table-wrapper order-items-shipment">
        <table class="data table table-order-items shipment" id="my-shipment-table-7" style="margin-top: 15px">
            <caption class="table-caption">Items</caption>
            <thead>
                <tr>
                    <th class="col name">Product Name</th>
                    <th class="col sku">SKU</th>
                    <th class="col qty">Qty</th>
                    <th class="col price">Price</th>
                    <th class="col subtotal">Subtotal</th>
                    <th class="col tracking">Tracking Number</th>
                </tr>
            </thead>
            <tbody>'.$this->getItems($data).'          
                    </tbody>
            </table>
    </div>
</div>';
    return $html;
    }

    public function getItems($data)
    {
        $html = '';
        if(isset($data['getOrderStatusResult']['OrderStatusArray']['OrderStatus']['WorkOrderArray']['WorkOrder']['itemArray'])){
                $orderedItem = array();
                $shippingAddress = array();
                $orderTotal = 0;
                foreach ($data['getOrderStatusResult']['OrderStatusArray']['OrderStatus']['WorkOrderArray']['WorkOrder']['itemArray'] as $key => $item) {

                   $html .='<tr id="order-item-row-7">
                        <td class="col name" data-th="Product Name">
                            <strong class="product name product-item-name">'.$item['ItemDescription'].'</strong>
                                                                            </td>
                        <td class="col sku" data-th="SKU">'.$item['ItemNumber'].'</td>
                        <td class="col qty" data-th="Qty Shipped">'.$item['OrderQuantity'].'</td>
                        <td class="col price" data-th="Price Shipped">'.$item['PerPiecePrice'].'</td>
                        <td class="col price" data-th="Subtotal Shipped">'.$item['ProductSubtotal'].'</td>
                        <td class="col price" data-th="Tracking Shipped">'.$item['TrackingNumber'].'</td>
                    </tr>';
            }
        }
        return $html;
    }
}
