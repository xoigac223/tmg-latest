<?php

namespace TMG\Customer\Controller\Test;

use TMG\Customer\Controller\CustomerTest;
use TMG\Customer\Exception\CustomerSecurityServiceException;

class createLogin extends CustomerTest
{
    public function execute()
    {
        
        // Param
        if(!$email = $this->getRequest()->getParam('email',false)) {
            echo 'NO EMAIL'; die();
        }
        if(!$accountId = $this->getRequest()->getParam('id',false)) {
            echo 'NO ACCOUNT ID'; die();
        }
        if(!$accountType = $this->getRequest()->getParam('type',false)) {
            echo 'NO ACCOUNT TYPE'; die();
        }
        if(!$accountTypeId = $this->customerHelper->getAccountTypeId($accountType)) {
            echo 'INVALID ACCOUNT TYPE'; die();
        }
        if(!$pass = $this->getRequest()->getParam('pass',false)) {
            echo 'NO PASS'; die();
        }
        
        try {
            
            echo '<pre>';
            
            // Prepare Data
            $lookupData = $this->customerHelper->validateApiAccountId($email,$accountTypeId,$accountId)['data'];
            
//            [ASIAccount] => ASITEST
//            [AccountFound] => 1
//            [AccountID] => A6UJ9A003ON2
//            [Address1] => 1234 Some Street
//            [Address2] =>
//            [BillingAddress] => 1
//            [City] => New York
//            [CompanyName] => OB Test Account Special Pricing 1
//            [Country] =>
//            [DefaultBillingAddress] => 1
//            [DefaultShippingAddress] => 1
//            [EncryptAccount] => 15o7rr+szFbLqvqCoCE0Ew==
//            [ErrorMessage] =>
//            [ExtendedErrorDetails] =>
//            [Fax] =>
//            [FirstName] => Mauro
//            [LastName] => Nigrele
//            [MagnetAccount] => OBTESTSP1
//            [PPAIAccount] => PPAITEST
//            [SAGEAccount] => SAGETEST
//            [ShippingAddress] => 1
//            [State] => New York
//            [Success] => 1
//            [WorkPhone] =>
//            [ZipCode] => 000101
            
            $createLoginData = [
                'Account' => '',
                'AccountID' => $lookupData['AccountID'],
                'Address1' => $lookupData['Address1'],
                'Address2' => $lookupData['Address2'],
                'BillingAddress' => $lookupData['BillingAddress'],
                'City' => $lookupData['City'],
                'CompanyName' => $lookupData['CompanyName'],
                'Country' => $lookupData['Country'],
                'DefaultBillingAddress' => $lookupData['DefaultBillingAddress'],
                'DefaultShippingAddress' => $lookupData['DefaultShippingAddress'],
                'Fax' => $lookupData['Fax'],
                'FirstName' => $lookupData['FirstName'],
                'IDType' => '',
                'LastName' => $lookupData['LastName'],
                'Password' => $pass, // Pass
                'ShippingAddress' => $lookupData['ShippingAddress'],
                'State' => $lookupData['State'],
                'User' => $email, // Email
                'WorkPhone' => $lookupData['WorkPhone'],
                'ZipCode' => $lookupData['ZipCode'],
            ];
            
            print_r([
                'lookupData' => $lookupData,
                'createLoginData' => $createLoginData,
                'result' => $this->customerHelper->createApiLogin($createLoginData),
            ]);
            
//            print_r($lookupData);
            
        
//            $result = $this->customerHelper->createApiLogin($email,$accountTypeId,$accountId);
//            print_r($result);
            
            echo '</pre>';
        
        } catch (\Exception $e) {

            print_r("\nERROR:\n");
            echo "<h3>{$e->getMessage()}</h3>";

            echo '<pre>';
            print_r("\nDEBUG:\n");
            print_r("\nREQUEST:\n");
            print_r($this->inventoryService->getRequestString(true,false));
            print_r("\nRESPONSE:\n");
            print_r($this->inventoryService->getResponseString(true,false));
            echo '</pre>';

        }
        
        die("\n" . __METHOD__ . "\n");
        
    }
}