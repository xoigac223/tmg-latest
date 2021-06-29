<?php
namespace Themagnet\Zoomcatalog\Block\Index;

class Index extends \Magento\Framework\View\Element\Template {
	const ZOOM_URL = "https://www.zoomcatalog.com/";
	const ZOOM_CUSTMIZ_URL = "https://themagnetgroup.zoomcustom.com/";
	protected $_postFactory;
	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		\Themagnet\Zoomcatalog\Model\Zoomcatalog $zoomcatalog
	) {
		$this->_postFactory = $zoomcatalog;
		parent::__construct($context);
	}

	public function getCollection() {
		return $this->_postFactory->getCollection();
	}

	public function getIsPdfEnable() 
	{
		$allowCall = array(\Themagnet\Zoomcatalog\Model\Config\Source\Service::FLYERS);
		$data = $this->_postFactory->getApiCall();
		if(in_array($data, $allowCall) === true){
			return true;
		}
		return false;
	}

	public function getpdfUrl($id) 
	{
		$data = $this->_postFactory->getApiCall();
		return self::ZOOM_CUSTMIZ_URL.$data.'/'.$id.'/download/pdf?parent';
		
	}

	public function getZoomCatalogUrl() {
		return self::ZOOM_URL;
	}

	public function getZoomCustomizeUrl() {
		return self::ZOOM_CUSTMIZ_URL;
	}

	public function getZoomCustomizeItemUrl($url) {
		//echo  $url.'test'; exit;
		return str_replace('https://www.zoomcustom.com/', self::ZOOM_CUSTMIZ_URL, $url);
	}

	public function getCatalogPageTitle() {
		$CatalogPageTitle = $this->_postFactory->getCatalogPageTitle();
		return $CatalogPageTitle;
	}

	public function getCatalogPageSubTitle() {
		$CatalogPageSubTitle = $this->_postFactory->getCatalogPageSubTitle();
		return $CatalogPageSubTitle;
	}
}