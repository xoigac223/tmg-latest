<?php
namespace Themagnet\Productimport\Block\Adminhtml;

class Index extends \Magento\Backend\Block\Template
{
    protected $_ftp;
    protected $_csvfiles;

	public function __construct(
		\Magento\Backend\Block\Template\Context $context,
		\Magento\Backend\Model\UrlInterface $backendUrl,
        \Themagnet\Productimport\Helper\Data $helper,
        \Themagnet\Productimport\Model\Ftpfiles $ftp,
        \Themagnet\Productimport\Model\Csvfiles $csvfiles,
        array $data = []
    ) {
    	parent::__construct($context, $data);
        $this->_helper = $helper;
        $this->_ftp = $ftp;
        $this->_csvfiles = $csvfiles;
        $this->_backendUrl = $backendUrl;
        $this->formKey = $context->getFormKey();
    }
    public function checkConnection()
    {
        $connection = $this->_ftp->getFtpConnection();
        if(isset($connection['error'])){
            return $connection['error'];
        }else{
            return true;
        }
        
    }
	public function getFiles()
	{
		$connection = $this->_ftp->getFiles();
        return $connection;
	}

    public function isLokExixts($type)
    {
        return  $this->_csvfiles->isLokFileExists($type);
    }
    
	public function getPostUrl()
    {
       return $this->getUrl("themagnet_productimport/import/import",array('file'=>'simple'));
    }

    public function getPostUrlConfig()
    {
       return $this->getUrl("themagnet_productimport/import/import",array('file'=>'config'));
    }

    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

}