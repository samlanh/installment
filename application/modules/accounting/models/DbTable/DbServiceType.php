<?php

class Accounting_Model_DbTable_DbServiceType extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_program_type';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    	 
    }
    public function AddServiceType($_data){
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$_rs = $_db->getServicTypeByName($_data['title'],$_data['type']);
    	if(!empty($_rs)){
    		return -1;
    	}
    	$_arr = array(
    			'title'=>$_data['title'],
    			'item_desc'=>$_data['item_desc'],
    			'status'=>$_data['status'],
    			'type'=>$_data['type'],
    			'create_date'=> new Zend_Date(),
    			'user_id' => $this->getUserId(),
    			);
    	
    	if(!empty($_data['id'])){
    		$db = $this->getAdapter();
    		$where = $db->quoteInto('id=?', $_data['id']);
    		$this->update($_arr, $where);
    	}else{
    		$this->insert($_arr);
    	}
    }
    
    public function sqlServiceType($search=''){
    	$sql = "SELECT id,title,item_desc,status
    		,create_date
    		,(SELECT CONCAT(last_name,' ',first_name) FROM rms_users WHERE user_id=id ) AS user_name
    		,type FROM rms_program_type
    	WHERE 1";
    	$order=" ORDER BY title";
    	$where = '';
    	if(empty($search)){
    		return $sql.$order;
    	}
    	if(!empty($search['txtsearch'])){
    		$where.=" AND title LIKE '%".$search['txtsearch']."%'";
    	}
    	if($search['type']>-1){
    		$where.= " AND type = ".$search['type'];
    	}
    	if($search['status']>-1){
    		$where.= " AND status = '".$search['status']."'";
    	}
    	//print_r($search);exit();
    	return $sql.$where.$order;
    }
    function getAllServiceType($search, $start, $limit){
    
    	$sql_rs = $this->sqlServiceType($search)." LIMIT ".$start.", ".$limit;
    	if ($limit == 'All') {
    		$sql_rs = $this->sqlServiceType($search);
    	}
    	$sql_count = $this->sqlServiceType();
    	if(!empty($search)){
    		$sql_count = $this->sqlServiceType($search);
    	}
    	$_db = new Application_Model_DbTable_DbGlobal();
    	return($_db ->getGlobalResultList($sql_rs,$sql_count));
    	// 		return array(0=>$rows,1=>$_count);//get all result by param 0 ,get count record by param 1
    }
    //////////////
    public function getAllServicesType($search=''){
		$db = $this->getAdapter();
    	$sql = "SELECT id,title,item_desc
    	,type,status
    	,create_date
    	,(SELECT CONCAT(last_name,' ',first_name) FROM rms_users WHERE user_id=id ) AS user_name
    	 FROM rms_program_type
    	WHERE 1";
    	$order=" ORDER BY title";
    	$where = '';
    	if(empty($search)){
    		return $db->fetchAll($sql.$order);
    	}
    	if(!empty($search['txtsearch'])){
    		$where.=" AND title LIKE '%".$search['txtsearch']."%'";
    	}
    	if($search['type']>-1){
    		$where.= " AND type = ".$search['type'];
    	}
    	if($search['status']>-1){
    		$where.= " AND status = '".$search['status']."'";
    	}
    	return $db->fetchAll($sql.$where.$order);
    }
    
    public function getServiceTypeById($id){
    	$db = $this->getAdapter();
    	$sql = "SELECT * FROM rms_program_type WHERE id = $id limit 1";
    	return $db->fetchRow($sql);
    	
    }
}



