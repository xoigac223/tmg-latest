<?php

namespace TMG\Shipping\Controller\Test;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Context;

use TMG\Base\Controller\Test;
use TMG\Shipping\Model\NoRatesException;
use TMG\Shipping\Model\Api\FreightEstimates;


class ApiCall extends Test
{
    /**
     * @var FreightEstimates
     */
    protected $freightEstimates;
    
    protected $checkoutSession;
    
    public function __construct(
        Context $context,
        FreightEstimates $freightEstimates,
        CheckoutSession $checkoutSession
    )
    {
        parent::__construct($context);
        $this->freightEstimates = $freightEstimates;
        $this->checkoutSession = $checkoutSession;
    }
    
    public function execute()
    {
        // Mapping Table
//        echo '<pre>';
//        echo "[\n";
//        $methods = $this->freightEstimates->getConfigHelper()->getPriceKeyMapping(); ksort($methods);
//        foreach ($methods as $key => $value) {
//            echo "    '" . str_replace(["\r", "\n",'        ','       ','      ','     ','    ','   ','  '],'',$key) . "' => '{$value}',\n";
//        }
//        echo "\n]";
//        die();
        
        $quote = $this->checkoutSession->getQuote();
        
        if(null === $quote) {
            echo '<h3>No Quote Available</h3>';
            die("\n" . __METHOD__);
        }

        if(!count($quote->getAllItems())) {
            echo '<h3>No Quote Items</h3>';
            die("\n" . __METHOD__);
        }
        
        
//        $params = [
//            'DestinationAddress' => [
//                'Address1' => '296 14TH St NW',
//                'Zip' => '30318',
//                'City' =>'Atlanta',
//                'Country' => 'US',
//                'State' => 'GA',
//            ],
//            'Item' => [
//                'ItemCode' => 'BG227',
//                'Thickness' => 'DM',
//                'Quantity' => '1',
//            ],
////            'Provider' => [
////                'string' => 'FedEx' ,
////            ],
//        ];
        
        try {
            
            print_r("\nRESULT:\n");
            echo '<pre>';
            
//            $result = $this->freightEstimates->doGetFreightDataRequest($params);
            $methods = $this->freightEstimates->getAvailableRates();

            print_r($methods);
            echo '</pre>';

        } catch (NoRatesException $e) {
            
            echo '<pre>';
            print_r("\nNO RATES:\n");
            print_r($e->getMessage());
            echo '</pre>';
            
        } catch (\Exception $e) {

            print_r("\nERROR:\n");
            echo "<h3>{$e->getMessage()}</h3>";

            echo '<pre>';
            print_r("\nDEBUG:\n");
            print_r("\nREQUEST:\n");
            print_r($this->freightEstimates->getRequestString(true,false));
            print_r("\nRESPONSE:\n");
            print_r($this->freightEstimates->getResponseString(true,false));
            echo '</pre>';

        }
        
        
        die("\n" . __METHOD__ . "\n");
    }
}