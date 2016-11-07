<?php
class Setting_Model_DbTable_DbRestore extends Zend_Db_Table_Abstract
{
	public function getRestoreDatabase($file_name){
		$db = $this->getAdapter();
		$part =  PUBLIC_PATH;
		$str =  file_get_contents($part.'/'.$file_name);
		$strresult=$db->query($str);
		unlink($part.'/'.$file_name);
		// 			return $r;
	}
	
	public function UploadFileDatabase($data){
		$adapter = new Zend_File_Transfer_Adapter_Http();
		$adapter->setDestination(PUBLIC_PATH);
		$fileinfo=$adapter->getFileInfo();
		$rs = $adapter->receive();
		if($rs==1){
// 			return true;
			$file_name = $fileinfo['fileToUpload']['name'];
			$this->getRestoreDatabase($file_name);
		}else{
			return false;
		}
	}  
	function getAllTruncateTable(){//check after
		$db = $this->getAdapter();
		$sql = " CALL strtruncate ";
		$rows =  $db->fetchAll($sql);
// 		$sql='TRUNCATE TABLE `ln_account_branch`;';
		
// 		$db->fetchRow($sql);
		foreach ($rows as $key =>$value){
			//echo $value['tr_table'];
		}
	} 
}



