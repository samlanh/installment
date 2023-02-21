<?php

class Po_Model_DbTable_DbImport extends Zend_Db_Table_Abstract
{

    protected $_name = 'st_supplier';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    
    }
    
    public function SupplierByImport($data){
    	$db = $this->getAdapter();
    	$count = count($data);
    	$dbg = new Application_Model_DbTable_DbGlobal();

    	for($i=1; $i<=$count; $i++){

			if(empty($data[$i]['B']) OR empty($data[$i]['C'])){
				continue;
			}
			$supplierType = ($data[$i]['B']=="ក្នុងស្រុក")?1:2;
			
			$this->_name = "st_supplier";
			$_arr=array(

    				'supplierType'			=>$supplierType,
    				'supplierName'			=>$data[$i]['C'],
					'supplierTel'			=>$data[$i]['D'],
					'email'					=>$data[$i]['E'],
    				'address'				=>$data[$i]['F'],
    				'contactName'			=>$data[$i]['G'],
    				'contactNumber'			=>$data[$i]['H'],
					'bankNumber'			=>$data[$i]['J'],
    				'receiverName'			=>$data[$i]['K'],
					'createDate'			=>date("Y-m-d H:i:s"),
    				'modifyDate'			=>date("Y-m-d H:i:s"),
    				'userId'				=>$this->getUserId(),
			);
			$this->insert($_arr);

    	}
    }
}   

