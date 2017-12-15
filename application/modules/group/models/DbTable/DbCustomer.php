<?php

class Group_Model_DbTable_DbCustomer extends Zend_Db_Table_Abstract
{

    protected $_name = 'in_customer';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    }
	public function add($_data){
		try{
		
		    $_arr=array(		    
		    	'name'	      => $_data['name'],
		    	'phone'	      => $_data['phone'],
				'date'			=>$_data['date'],
				'from_price'      => $_data['from_price'],
				'to_price'      => $_data['to_price'],
				'requirement'      => $_data['requirement'],
				'type'       => $_data['type'],
				'description'       => $_data['description'],
				'status'	  => 1,//$_data['status'],
				'user_id'	  => $this->getUserId(),
		    	
		);
	
		$this->_name;   
		if(!empty($_data['id'])){
			$where = 'id = '.$_data['id'];
			$this->update($_arr, $where);
			return $_data['id'];
			 
		}else{
			$_arr['create_date']=date('Y-m-d');
			return  $this->insert($_arr);
		}

		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}

	public function getById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM $this->_name WHERE id = ".$db->quote($id);
		$sql.=" LIMIT 1 ";
		$row=$db->fetchRow($sql);
		return $row;
	}


	function getAllInfo($search = null){		
		$db = $this->getAdapter();
		$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
		$where = " WHERE ".$from_date." AND ".$to_date;		
		$sql = "SELECT id,name, phone, `date`,from_price,to_price,requirement,type,description,				
		    (SELECT  CONCAT(last_name,' ', first_name) FROM rms_users WHERE id = user_id limit 1 ) AS user_name,
			status FROM $this->_name ";
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[] = " name LIKE '%{$s_search}%'";
			$s_where[] = " phone LIKE '%{$s_search}%'";
			$s_where[] = " requirement LIKE '%{$s_search}%'";
			$s_where[] = " type LIKE '%{$s_search}%'";
		
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['status']>-1){
			$where.= " AND status = ".$search['status'];
		}	
		$order=" ORDER BY id DESC ";
		return $db->fetchAll($sql.$where.$order);	
	}

}