<?php

class Stock_Model_DbTable_DbSupplier extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_supplier';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getAllSupplier($search=null){
    	$db = $this->getAdapter();
    	$sql="SELECT s.id,s.sup_name,s.tel,s.email,s.status,s.create_date FROM `rms_supplier` AS s WHERE 1";
    	$where="";
    	$from_date =(empty($search['start_date']))? '1': " s.create_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " s.create_date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	if(!empty($search['adv_search'])){
    		$s_where=array();
    		$s_search=addslashes(trim($search['adv_search']));
    		$s_where[]="  s.sup_name LIKE '%{$s_search}%'";
    		$s_where[]= " s.tel LIKE '%{$s_search}%'";
    		$s_where[]= " s.email LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
    	if($search['status_search']==1 OR $search['status_search']==0){
    		$where.=" AND s.status=".$search['status_search'];
    	}
    	$order=" ORDER BY s.id DESC";
    	return $db->fetchAll($sql.$where.$order);
    }
    
    public function addSupplier($_data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
	    	$_arr = array(
	    		'sup_name'		=> $_data['supplier_name'],
	    		'sex'			=> $_data['sex'],
	    		'tel'			=> $_data['tel'],
	    		'email'			=> $_data['email'],
	    		'address'		=> $_data['address'],
	    		'note'			=> $_data['note'],
	    		'status'		=> 1,
	    		'date'			=> date("Y-m-d"),
	    		'create_date'	=> date("Y-m-d"),
	    		'modify_date'	=> date("Y-m-d H:i:s"),
	    		'user_id'		=> $this->getUserId()
	    	);
	    	$this->_name='rms_supplier';
	    	$sup_id = $this->insert($_arr);
    		$db->commit();
		   	}catch (Exception $e){
		   		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		   		$db->rollBack();
		   	}
    }
    function getSupplierById($id){
    	$db = $this->getAdapter();
    	$sql=" SELECT * FROM $this->_name WHERE id = $id LIMIT 1";
    	return $db->fetchRow($sql);
    }
    public function updateSupplier($_data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		$_arr = array(
    			'sup_name'		=> $_data['supplier_name'],
    			'sex'			=> $_data['sex'],
    			'tel'			=> $_data['tel'],
    			'email'			=> $_data['email'],
    			'address'		=> $_data['address'],
    			'note'		    => $_data['note'],
    			'status'		=> $_data['status'],
    			'modify_date'	=> date("Y-m-d H:i:s"),
    			'user_id'		=> $this->getUserId()
    		);
    		$this->_name='rms_supplier';
    		$where = " id = ".$_data['id'];
    		$sup_id = $this->update($_arr, $where);
    		$db->commit();
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    	}
    }
    public function checkDeplicateRecord($_data){
    	$db = $this->getAdapter();
    	$sql="SELECT s.* FROM `rms_supplier` AS s WHERE s.sup_name='".$_data['supplier_name']."'";
    	if (!empty($_data['id'])){
    		$sql.=" AND id != ".$_data['id'];
    	}
    	$sql.=" LIMIT 1";
		$row = $db->fetchRow($sql);
		if (empty($row)) {
			return 1;
		}else{
			return 2;
		}
    }
}