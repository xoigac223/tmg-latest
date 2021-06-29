<?php
namespace Themagnet\Productimport\Model;
 
class SftpConnection extends \Magento\Framework\Model\AbstractModel
{
	CONST FILE_NAME = 'xml-updates';
	protected $_helper;
	protected $_importlogger;
	protected $sftp;
	public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Themagnet\Productimport\Helper\Data $helper,
        \Themagnet\Productimport\Model\Logger $logger,
        \Magento\Framework\Filesystem\Io\Sftp $sftp,
        array $data = []
    ) {
        $this->_helper = $helper;
        $this->_importlogger = $logger;
        $this->sftp = $sftp;
        parent::__construct($context , $registry);
    }

    public function getFtpConnection()
	{
		$ftp_server = $this->_helper->getConfig('themagnet/general/ftp_host');
        $ftp_username = $this->_helper->getConfig('themagnet/general/ftp_username');
        $ftp_userpass = $this->_helper->getConfig('themagnet/general/ftp_password');
        $port = 22;
        $sftp = '';
        try {
        	$args['host'] = $ftp_server;
        	$args['username'] = $ftp_username;
        	$args['password'] = $ftp_userpass;

        	$ftp_conn =  $this->sftp->open($args);
		}catch(\Exception $e) {
			$this->_importlogger->errorLog((string)$e->getMessage());
			$this->_importlogger->errorLog((string)__('Could not connect to server login incorrect'));
			return array('error'=>__($e->getMessage()));
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
		        			$this->sftp->read($file ,$local_file);
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
								//ftp_pasv($ftp_conn, true);
								$this->sftp->mv($file, $moveFile);
	        				}
	        			}else{
	        				//ftp_pasv($ftp_conn, true);
							$this->sftp->mv($file, $moveFile);
	        			}
					}catch(\Exception $e) {
						$this->_importlogger->errorLog((string)$e->getMessage());
					}
				
			}
        }
        
	}

	public function ftpIsDir($ftpcon, $dir ) {
	    $original_directory = $this->sftp->pwd();
	    if ( $this->sftp->cd($dir ) ) {
	        $this->sftp->cd($original_directory );
	        return true;
	    } 
	    else {
	        return false;
	    }        
	} 

	public function createFtpFolder($ftp_conn, $dir)
	{
		
		try {
			 return $this->sftp->rmdir($dir);
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
			$this->sftp->cd(self::FILE_NAME);
			$file = $this->sftp->ls(self::FILE_NAME);
			$files = array();
			if(empty($file) !== true){
				foreach ($file as  $item) {
					if(isset($item['text']) && $item['text'] != '.' && $item['text'] != '..'){
						$files[] = $item['text'];
					}
				}
			}
	        return $files;
		}
       
	}

	
}