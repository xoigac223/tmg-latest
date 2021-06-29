<?php

namespace Visiture\Achtandcss\Model\Payment;

class Acht extends \Magento\Payment\Model\Method\AbstractMethod
{
    const CODE = 'acht';
    
    protected $_code = self::CODE;

    protected $_isGateway                   = true;
    protected $_canCapture                  = true;
    protected $_canCapturePartial           = true;
    
    /**
     * @var AchtApi
     */
    protected $_achtApi;

    protected $_infoBlockType = 'Visiture\Achtandcss\Block\Info\Acht';

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Visiture\Achtandcss\Model\Payment\AchtApi $Acht,
        array $data = array()
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            null,
            null,
            $data
        );

        $this->_localeDate = $localeDate;
        $this->_achtApi = $Acht;
    }

    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
    	$order = $payment->getOrder();
        $billing = $order->getBillingAddress();
        $additionalData = $payment->getAdditionalInformation();
        try {
            $requestData = [
                'svcUser'          => '',
                'svcPassword'      => '',
                'bankRoutingNumber'=> $additionalData['bank_routing_number'],
                'bankAccountNumber'=> $additionalData['bank_account_number'],
                'checkNumber'      => $additionalData['check_number'],
                'transactionAmount'=> $amount,
                'encryptedAccount' => $order->getcustomer_id(),
                'customerName'     => $order->getCustomerName(),
                'customerAddress'  => $billing->getStreetLine(1).", ".$billing->getStreetLine(2),
                'customerCity'     => $billing->getCity(),
                'customerState'    => $billing->getRegion(),
                'customerPostal'   => $billing->getPostcode(),
                'customerCountry'  => $billing->getCountryId(),
                'customerEmail'    => '',
                'poNumber'         => $order->getIncrementId(),
                'poDate'           => $this->_localeDate->date()->format('Y-m-d H:i:s'),
            ];
            
            $responseData = $this->_achtApi->doAchtCaptureRequest($requestData);

            $payment
                ->setTransactionId($responseData->ReturnedAuthorization->AuthOriginationID)
                ->setIsTransactionClosed(0);

        } catch (\Exception $e) {
            $this->debugData(['request' => $requestData, 'exception' => $e->getMessage()]);
            $this->_logger->error(__('Payment capturing error.'.$e->getMessage()));
            throw new \Magento\Framework\Validator\Exception(__('Payment capturing error.'.$e->getMessage()));
        }

        return $this;
    }
}