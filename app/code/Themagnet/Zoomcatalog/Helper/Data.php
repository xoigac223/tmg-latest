<?php
namespace Themagnet\Zoomcatalog\Helper;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;

class Data extends AbstractHelper {
	public function __construct(Context $context,
		StoreManagerInterface $storeManager,
		\Magento\Framework\Encryption\EncryptorInterface $encription
	) {
		$this->storeManager = $storeManager;
		$this->_encryption = $encription;
		parent::__construct($context);
	}

	function makeApiRequest($gatewayUrl, $requestArr, $method = 'GET', $header = array()) {
		$requestString = $requestArr;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $gatewayUrl);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $requestString);
		$data = curl_exec($ch);
		
		//print_r($data); 
		if (curl_error($ch)) {
			$this->_logger->info('Error while request zoomcatalog: ');
			$this->_logger->info(print_r(curl_error($ch), true));
			throw new \Magento\Framework\Validator\Exception(__("Error while request zoomcatalog:" . curl_error($ch)));
			return false;
		}
		curl_close($ch);
		unset($ch);
		if (!($data)) {
			$this->_logger->info('Error while request zoomcatalog.');
			throw new \Magento\Framework\Validator\Exception(__("Error while request zoomcatalog."));
			return false;
		}
		$result = json_decode($data, true);
		return $result;
	}
}