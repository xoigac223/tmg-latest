<?php
namespace Themagnet\Productimport\Model;
 
class Ftpfiles extends \Magento\Framework\Model\AbstractModel
{
	CONST FILE_NAME = 'xml-updates';
	protected $_helper;
	protected $_importlogger;
	public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Themagnet\Productimport\Helper\Data $helper,
        \Themagnet\Productimport\Model\Logger $logger,
        array $data = []
    ) {
        $this->_helper = $helper;
        $this->_importlogger = $logger;
        parent::__construct($context , $registry);
    }

    public function getFtpConnection()
	{
		$ftp_server = $this->_helper->getConfig('themagnet/general/ftp_host');
        $ftp_username = $this->_helper->getConfig('themagnet/general/ftp_username');
        $ftp_userpass = $this->_helper->getConfig('themagnet/general/ftp_password');
        try {
        	$ftp_conn = ftp_ssl_connect($ftp_server);
	        $login = ftp_login($ftp_conn, $ftp_username, $ftp_userpass);
		}catch(\Exception $e) {
			$this->_importlogger->errorLog((string)$e->getMessage());
			$this->_importlogger->errorLog((string)__('Could not connect to server login incorrect'));
			return array('error'=>$e->getMessage());
		}
		return $ftp_conn;
	}

	public function ftpSync($dir, $conn_id) {
		$contents = ftp_nlist($conn_id, self::FILE_NAME);
		foreach ($contents as $file) {
			ftp_get($conn_id, $file, $file, FTP_BINARY);
		}
	}

	public function downloadFiles($csvFiles)
	{
		$ftp_conn = $this->getFtpConnection();
		$filePath = $csvFiles->downloadFilesPath();
		if(file_exists($filePath)){
			$files = $this->getFiles();
			foreach($files as $file){
				$mainFile = str_replace('xml-updates/', '', $file);
				$local_file = $filePath.$mainFile;
				$path_info = pathinfo($local_file);
			  	if(isset($path_info['extension']) && $path_info['extension'] == 'xml'){
					try {
						 if(!file_exists($local_file)){
							ftp_pasv($ftp_conn, true);
		        			ftp_get($ftp_conn, $local_file, $file, FTP_BINARY );
	        			}
					}catch(\Exception $e) {
						$this->_importlogger->errorLog((string)$e->getMessage());
					}
				}
			}
        }
        
	}

	public function moveFtpFiles($csvFiles)
	{
		$ftp_conn = $this->getFtpConnection();
		$filePath = $csvFiles->downloadFilesPath();
		if(file_exists($filePath)){
			$files = $this->getFiles();
			foreach($files as $file){
				$folderPath = 'xml-processed/'.date('Y-m-d').'/';
		        //$folderPath = 'xml-processed/2018-10-27/';
				if(!$this->ftpIsDir($ftp_conn,$folderPath )) {
				       $create = $this->createFtpFolder($ftp_conn,$folderPath);
				       if($create === false){
				       	 $this->_importlogger->errorLog((string)__('%1 not created on FTP server',$folderPath));
				       	 return false;
				       }
				} 
				$moveFile = str_replace('xml-updates/', $folderPath, $file);
				$mainFile = str_replace('xml-updates/', '', $file);
				$local_file = $filePath.$mainFile;
				$path_info = pathinfo($local_file);
			  	
					try {
						if(isset($path_info['extension']) && $path_info['extension'] == 'xml'){
						 	if(file_exists($local_file)){
								ftp_pasv($ftp_conn, true);
								ftp_rename($ftp_conn, $file, $moveFile);
	        				}
	        			}else{
	        				ftp_pasv($ftp_conn, true);
							ftp_rename($ftp_conn, $file, $moveFile);
	        			}
					}catch(\Exception $e) {
						$this->_importlogger->errorLog((string)$e->getMessage());
					}
				
			}
			$uploadPathfile = 'xml-updates/';
			if(!$this->ftpIsDir($ftp_conn,$uploadPathfile )) {
			       $create = $this->createFtpFolder($ftp_conn,$uploadPathfile);
			       if($create === false){
			       	 $this->_importlogger->errorLog((string)__('%1 not created on FTP server',$uploadPathfile));
			       	// return false;
			       }
			} 
        }
        
	}

	public function ftpIsDir($ftpcon, $dir ) {
	    $original_directory = ftp_pwd( $ftpcon );
	    if ( @ftp_chdir( $ftpcon, $dir ) ) {
	        ftp_chdir( $ftpcon, $original_directory );
	        return true;
	    } 
	    else {
	        return false;
	    }        
	} 

	public function createFtpFolder($ftp_conn, $dir)
	{
		
		try {
			 return ftp_mkdir($ftp_conn, $dir);
		}catch(\Exception $e) {
			$this->_importlogger->errorLog((string)$e->getMessage());
			return false;
		}
	}

    public function getFiles()
	{
		$ftp_conn = $this->getFtpConnection();
		if(isset($ftp_conn['error'])){
			return $ftp_conn;
		}else{
			ftp_pasv($ftp_conn, true);
	        $file = ftp_nlist($ftp_conn, self::FILE_NAME);
	        return $file;
		}
        
	}
}