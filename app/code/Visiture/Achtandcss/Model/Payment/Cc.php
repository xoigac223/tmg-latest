<?php

namespace Visiture\Achtandcss\Model\Payment;

class Cc extends \Magento\Payment\Model\Method\Cc
{
    const CODE = 'cc';

    protected $_code = self::CODE;

    protected $_isGateway                   = true;
    protected $_canCapture                  = true;
    protected $_canCapturePartial           = true;
    
    /**
     * @var bool|CcApi
     */
    protected $_ccApi;
    
    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $_countryFactory;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Visiture\Achtandcss\Model\Payment\CcApi $cc,
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
            $moduleList,
            $localeDate,
            null,
            null,
            $data
        );

        $this->_countryFactory = $countryFactory;
        $this->_ccApi = $cc;        
    }

    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $order = $payment->getOrder();
        $billing = $order->getBillingAddress();
        try {
            $requestData = [
                'svcUser'          => '',
                'svcPassword'      => '',
                'creditCardNumber' => $payment->getCcNumber(),
                'creditCardExpDate'=> sprintf('%02d',$payment->getCcExpMonth()).$payment->getCcExpYear(),
                'creditCardCVV2'   => $payment->getCcCid(),
                'creditCardType'   => $payment->getCcType(),
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
            
            $responseData = $this->_ccApi->doCcCaptureRequest($requestData);

            $payment->setTransactionId($responseData->ReturnedAuthorization->AuthOriginationID)
                ->setIsTransactionClosed(0);

        } catch (\Exception $e) {
            $this->debugData(['request' => $requestData, 'exception' => $e->getMessage()]);
            $this->_logger->error(__('Payment capturing error.'.$e->getMessage()));
            throw new \Magento\Framework\Validator\Exception(__('Payment capturing error.'.$e->getMessage()));
        }

        return $this;
    }
}