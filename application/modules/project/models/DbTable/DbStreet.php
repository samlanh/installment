<?php

class Project_Model_DbTable_DbStreet extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_street';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getAllStreet($search = null){
    	$db = $this->getAdapter();
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql = "SELECT DISTINCT street AS id,street, 
    	(SELECT s.code FROM ln_street AS s WHERE s.title=street ORDER BY s.id DESC LIMIT 1) as street_code
    	 FROM ln_properties 
				GROUP BY street ORDER BY street ASC";
    	return $db->fetchAll($sql);
    }
    
    function getStreetByTitle($title){
    	$db = $this->getAdapter();
    	$sql="SELECT s.* FROM ln_street AS s WHERE s.title='$title' ORDER BY s.id DESC LIMIT 1 ";
    	$row = $db->fetchRow($sql);
    	return $row;
    }
    
    function getStreetById($id){
    	$db = $this->getAdapter();
    	$sql="SELECT DISTINCT s.street AS id,s.street as title,
    	(SELECT ss.code FROM ln_street AS ss WHERE ss.title=street ORDER BY ss.id DESC LIMIT 1) AS code FROM ln_properties AS s WHERE s.street='$id' LIMIT 1 ";
    	$row = $db->fetchRow($sql);
    	return $row;
    }
    public function editStreet($_data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		
    		$streetInfo = $this->getStreetByTitle($_data['title']);
    		
	    	$_arr = array(
	    			'title'=>$_data['title'],
	    			'code'      =>      $_data['code'],
// 	    			'note'=>$_data['note'],
	    			'status'=>1,
	    			'modify_date'=>date("Y-m-d H:i:s"),
	    			'user_id'=>$this->getUserId(),
	    			 
	    	);
	    	$id= empty($_data['id'])?0:$_data['id'];
	    	
	    	if (!empty($streetInfo)){
		    	$where="title = '".$id."'";
		    	$this->_name="ln_street";
		    	$this->update($_arr, $where);
	    	}else{
	    		$this->_name="ln_street";
	    		$_arr['create_date']=date("Y-m-d H:i:s");
	    		$this->insert($_arr);
	    	}
	    	if (!empty($_data['id'])){
		    	$_arrA = array(
		    			'street_code'=>$_data['code'],
		    	);
		    	$this->_name="ln_properties";
		    	$where1="street = '".$id."'";
		    	$this->update($_arrA, $where1);
	    	}
	    	$db->commit();
	    	return 1;
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    	}
    }
    
    
    public function addStreet($_data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		$_arr = array(
    				'title'=>$_data['street'],
    				'code' => $_data['streetCode'],
    				'status'=>1,
    				'create_date'=>date("Y-m-d H:i:s"),
    				'modify_date'=>date("Y-m-d H:i:s"),
    				'user_id'=>$this->getUserId(),
    		);
    		$this->_name="ln_street";
    		$id = $this->insert($_arr);
    
    		$db->commit();
    		return $id;
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    	}
    }
    
}