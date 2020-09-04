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
    	
    	$from_date =(empty($search['start_date']))? '1': " create_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " create_date <= '".$search['end_date']." 23:59:59'";
    	$where = " WHERE ".$from_date." AND ".$to_date;
    	$sql = "SELECT id,
    	code,title,
    	modify_date,
    	(SELECT  first_name FROM rms_users WHERE id=user_id limit 1 ) AS user_name";
    	
    	$sql.=" FROM $this->_name ";
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['adv_search']));
    		$s_where[] = " code LIKE '%{$s_search}%'";
    		$s_where[] = " title LIKE '%{$s_search}%'";
    		$s_where[] = " note LIKE '%{$s_search}%'";
    		$where .=' AND ('.implode(' OR ',$s_where).')';
    	}
    	$order= " ORDER BY code ASC";
    	return $db->fetchAll($sql.$where.$order);
    }
    
    function getStreetByTitle($title){
    	$db = $this->getAdapter();
    	$sql="SELECT s.* FROM ln_street AS s WHERE s.title='$title' ORDER BY s.id DESC LIMIT 1 ";
    	$row = $db->fetchRow($sql);
    	if (!empty($row)){
    		$_arr = array(
    				'street_id'=>$row['id'],
    		);
    		$this->_name="ln_properties";
    		$where="street = '$title'";
    		$this->update($_arr, $where);
    	}
    	return $row;
    }
    
    function getStreetById($id){
    	$db = $this->getAdapter();
    	$sql="SELECT s.* FROM ln_street AS s WHERE s.id='$id' LIMIT 1 ";
    	$row = $db->fetchRow($sql);
    	return $row;
    }
    public function editStreet($_data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
	    	$_arr = array(
	    			'title'=>$_data['title'],
	    			'code'      =>      $_data['code'],
	    			'note'=>$_data['note'],
	    			'status'=>1,
	    			'modify_date'=>date("Y-m-d H:i:s"),
	    			'user_id'=>$this->getUserId(),
	    			 
	    	);
	    	
	    	$id= empty($_data['id'])?0:$_data['id'];
	    	$where="id = ".$id;
	    	$this->_name="ln_street";
	    	$this->update($_arr, $where);
	    	
	    	if (!empty($_data['id'])){
		    	$_arrA = array(
		    			'street'=>$_data['title'],
		    	);
		    	$this->_name="ln_properties";
		    	$where1="street_id = ".$id;
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
    
    		if (!empty($_data['street'])){
    			$title = $_data['street'];
    			$_arrs = array(
    					'street_id'=>$id,
    			);
    			$this->_name="ln_properties";
    			$where="street = '$title'";
    			$this->update($_arrs, $where);
    		}
    		
    		$db->commit();
    		return $id;
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    	}
    }
    
}