<?php
class Setting_BackupdatabaseController extends Zend_Controller_Action {
	
	
public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
	}
	public function indexAction()
	{
		$db = new Setting_Model_DbTable_DbLabel();
		$data = $db->getAllSystemSetting();
		$this->view->data = $data;		 
		if($this->getRequest()->isPost()){
			ini_set('display_errors', 1);
			ini_set('display_startup_errors', 1);
			error_reporting(E_ALL);
			$database = $data['dbname'];//'db_loannewversion';
			$user = $data['dbuser'];//'root';
			$pass = $data['dbpassword'];//'';
			$host = $data['servername'];//'localhost'
			
			$namebackup = $database.date("d")."_".date("m")."_".date("Y")."-".time();
			$part=PUBLIC_PATH. '/sytembackup/';
			if (!file_exists($part)){
				mkdir($part, 0777, true);
			}
			$dir = $part.$namebackup.'.sql';
			$mysqldumpart = $data['mysqldump'];//$mysqldumpart = "D:/wamp/bin/mysql/mysql5.6.17/bin/mysqldump";
			if (exec("$mysqldumpart  --user={$user} --password={$pass} --host={$host} --routines {$database} --result-file={$dir} 2>&1 ", $output)){
				Application_Form_FrmMessage::Sucessfull("BACKUP_SUCCESS","/setting/backupdatabase");
			}else{
				
				Application_Form_FrmMessage::Sucessfull("BACKUP_FAIL","/setting/backupdatabase");
			}
			
			
		}
	}
	public function addAction(){
		$this->_redirect('/setting/backupdatabase');
	}
	
	
}

