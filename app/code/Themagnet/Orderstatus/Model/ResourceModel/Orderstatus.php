<?php

namespace Themagnet\Orderstatus\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Orderstatus extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('themagnet_orderstatus', 'orderstatus_id');
    }

    public function deleteDynamicRows()
    {
        $connection = $this->getConnection();
        $connection->delete(
            $this->getMainTable(),
            ['orderstatus_id > ?' => 0]
        );
    }

    public function getShippingMethod($code)
    {
        $carrier = '';
        switch ($code) {
            case 'UPS':
                $carrier = "United Parcel Service - Ground";
                break;
            default:
                $carrier = $code;
        }
        return $carrier;
    }

    public function getPaymentMethod()
    {
        return array('method_code'=>'checkmo','method_title'=>'Check / Money order');
    }

    public function checkIsExists($orderData)
    {
        $adapter = $this->getConnection();
        $order_table = $adapter->getTableName('sales_order'); 
        $select = $adapter->select()
            ->from($order_table, 'entity_id')
            ->where('increment_id = :increment_id');
        $binds['increment_id'] = $orderData['OrderNumber'];
        $result = $adapter->fetchAll($select, $binds);
        return $result;
    }

    public function insertOrder($orderData, $customer,  $websiteId)
    {
        
       // $orderData['OrderNumber'] = 'PF0110390';
        $existsData =  $this->checkIsExists($orderData);
        if(count($existsData) > 0){
            throw new \Exception(__('#%1 order already exists', $orderData['OrderNumber']));
            return false;
        }
        if (empty($orderData) !== true) {
            $rows = array();
            $rows[] = array('state'=>'new',
                            'status'=>strtolower($orderData['Status']), 
                            'shipping_description'=>$this->getShippingMethod($orderData['Carrier']),
                            'is_virtual'=>0,
                            'store_id'=>1,
                            'customer_id'=>$customer->getId(),
                            'customer_group_id'=>$customer->getGroupId(),
                            'base_grand_total'=>$orderData['orderTotal'],
                            'base_shipping_amount'=>$orderData['ShippingAmount'],
                            'base_subtotal'=>$orderData['orderTotal'],
                            'grand_total'=>$orderData['orderTotal'],
                            'subtotal'=>$orderData['orderTotal'],
                            'total_qty_ordered'=>$orderData['OrderQuantity'],
                            'increment_id'=>$orderData['OrderNumber'],
                            'base_currency_code'=>'USD',
                            'customer_email'=>$orderData['email'],
                            'customer_firstname'=>$orderData['shipping_address']['firstname'],
                            'customer_lastname'=>$orderData['shipping_address']['lastname'],
                            'global_currency_code'=>'USD',
                            'order_currency_code'=>'USD',
                            'shipping_method'=>'UPS',
                            'store_currency_code'=>'USD',
                            'store_name'=>'Main Website
                                           Main Website Store',
                            'created_at'=>date('Y-m-d H:i:s',strtotime($orderData['order_date'])),
                            'updated_at'=>date('Y-m-d H:i:s',strtotime($orderData['order_date'])),
                            'total_item_count'=>$orderData['OrderQuantity']

                           );
            $adapter = $this->getConnection();
            $order_table = $adapter->getTableName('sales_order'); 
            
            $adapter->insertMultiple($order_table, $rows);
            $orderId =  $adapter->lastInsertId();
            $this->insertOrderGrid($rows,  $orderId, $orderData);
            $this->insertOrderItem($orderData, $orderId);
            $this->insertShipping($orderData, $orderId);
            $this->insertPayment($orderData, $orderId);
            $this->addOrderComment($orderData, $orderId);
           
        }
        
    }

    public function insertOrderGrid($rows,  $orderId, $orderData)
    {
        
        if (empty($rows) !== true) {
            $rows = $rows[0];
            $rowsGrid = array();
            $paymentMethod = $this->getPaymentMethod();
            $rowsGrid[] = array('entity_id'=>$orderId,
                            'status'=>$rows['status'], 
                            'store_id'=>$rows['store_id'],
                            'store_name'=>$rows['store_name'],
                            'customer_id'=>$rows['customer_id'],
                            'base_grand_total'=>$rows['base_grand_total'],
                            'grand_total'=>$rows['grand_total'],
                            'increment_id'=>$rows['increment_id'],
                            'base_currency_code'=>$rows['base_currency_code'],
                            'order_currency_code'=>$rows['order_currency_code'],
                            'shipping_name'=>$rows['customer_firstname'].' '.$rows['customer_lastname'],
                            'billing_name'=>$rows['customer_firstname'].' '.$rows['customer_lastname'],
                            'created_at'=>$rows['created_at'],
                            'updated_at'=>$rows['updated_at'],
                            'billing_address'=>$orderData['shipping_address']['street'].' '.$orderData['shipping_address']['city'].' '.$orderData['shipping_address']['region'].' '.$orderData['shipping_address']['postcode'].' '.$orderData['shipping_address']['country_id'],
                            'shipping_address'=>$orderData['shipping_address']['street'].' '.$orderData['shipping_address']['city'].' '.$orderData['shipping_address']['region'].' '.$orderData['shipping_address']['postcode'].' '.$orderData['shipping_address']['country_id'],
                            'shipping_information'=>$rows['shipping_description'],
                            'customer_email'=>$rows['customer_email'],
                            'customer_group'=>$rows['customer_group_id'],
                            'subtotal'=>$rows['subtotal'],
                            'shipping_and_handling'=>0.0,
                            'customer_name'=>$rows['customer_firstname'].' '.$rows['customer_lastname'],
                            'payment_method'=>$paymentMethod['method_code'],
                            'total_refunded'=>''

                           );
            $adapter = $this->getConnection();
            $order_table = $adapter->getTableName('sales_order_grid'); 
            $adapter->insertMultiple($order_table, $rowsGrid);
           
        }
        
    }

    public function insertOrderItem($orderData, $orderId)
    {
        
        if (empty($orderData) !== true) {
            $rows = array();
            if(isset($orderData['items'])){
               // echo "<pre>";
              // print_r($orderData['items']); exit;
                foreach($orderData['items'] as $item){
                    $rows[] = array('order_id'=>$orderId,
                            'store_id'=>1, 
                            'created_at'=>date('Y-m-d H:i:s',strtotime($orderData['order_date'])),
                            'updated_at'=>date('Y-m-d H:i:s',strtotime($orderData['order_date'])),
                            'product_id'=>$item['product_id'],
                            'product_type'=>$item['type'],
                            'weight'=>$item['weight'],
                            'sku'=>$item['sku'],
                            'name'=>$item['name'],
                            'qty_ordered'=>$item['OrderQuantity'],
                            'price'=>$item['price'],
                            'base_price'=>$item['price'],
                            'original_price'=>$item['price'],
                            'row_total'=>$item['row_total'],
                            'base_row_total'=>$item['row_total'],
                            'row_weight'=>$item['weight']*$item['OrderQuantity']

                           );
                }

            }
            
            
            $adapter = $this->getConnection();
            $order_table = $adapter->getTableName('sales_order_item'); 
            
           return $adapter->insertMultiple($order_table, $rows);
        }
        
    }

    private function getSerializer($value)
    {
        
        return serialize($value);
    }

    public function insertShipping($orderData, $orderId)
    {
        
        if (empty($orderData) !== true) {
            $rows = array();
           
                
                $rows[] = array('parent_id'=>$orderId,
                        'region'=>$orderData['shipping_address']['region'], 
                        'postcode'=>$orderData['shipping_address']['postcode'], 
                        'firstname'=>$orderData['shipping_address']['firstname'], 
                        'lastname'=>$orderData['shipping_address']['lastname'], 
                        'email'=>$orderData['email'], 
                        'street'=>$orderData['shipping_address']['street'],
                        'city'=>$orderData['shipping_address']['city'],
                        'telephone'=>$orderData['shipping_address']['telephone'],
                        'country_id'=>'US',
                        'address_type'=>'billing'
                       );
                $rows[] = array('parent_id'=>$orderId,
                        'region'=>$orderData['shipping_address']['region'], 
                        'postcode'=>$orderData['shipping_address']['postcode'], 
                        'firstname'=>$orderData['shipping_address']['firstname'], 
                        'lastname'=>$orderData['shipping_address']['lastname'],
                        'email'=>$orderData['email'],  
                        'street'=>$orderData['shipping_address']['street'],
                        'city'=>$orderData['shipping_address']['city'],
                        'telephone'=>$orderData['shipping_address']['telephone'],
                        'country_id'=>'US',
                        'address_type'=>'shipping'
                       );
            
            
            $adapter = $this->getConnection();
            $order_table = $adapter->getTableName('sales_order_address'); 
            
            return $adapter->insertMultiple($order_table, $rows);
        }
        
    }

    public function insertPayment($orderData, $orderId)
    {
        
        if (empty($orderData) !== true) {
            $rows = array(); 
            $paymentMethod = $this->getPaymentMethod();
                $rows[] = array('parent_id'=>$orderId,
                        'base_amount_ordered'=>$orderData['orderTotal'], 
                        'amount_ordered'=>$orderData['orderTotal'], 
                        'method'=>$paymentMethod['method_code'], 
                        'additional_information'=>$this->getSerializer(array('method_title'=>$paymentMethod['method_title'])), 
                       );

            
            $adapter = $this->getConnection();
            $order_table = $adapter->getTableName('sales_order_payment'); 
            return $adapter->insertMultiple($order_table, $rows);
        }
        
    }

    public function addOrderComment($orderData, $orderId)
    {
        
        if (empty($orderData) !== true) {
            if(isset($orderData['TrackingNumber']) && $orderData['TrackingNumber'] != ''){
                 $rows = array(); 
                $paymentMethod = $this->getPaymentMethod();
                    $rows[] = array('parent_id'=>$orderId,
                            'comment'=>'Your order tracking number is '.$orderData['TrackingNumber'], 
                            'status'=>strtolower($orderData['Status']), 
                            'created_at'=>date('Y-m-d H:i:s'), 
                            'entity_name'=>'order', 
                           );

                
                $adapter = $this->getConnection();
                $order_table = $adapter->getTableName('sales_order_status_history'); 
                return $adapter->insertMultiple($order_table, $rows);
            }
           
        }
        
    }
}