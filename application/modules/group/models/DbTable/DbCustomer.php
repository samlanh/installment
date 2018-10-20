<?php

class Group_Model_DbTable_DbCustomer extends Zend_Db_Table_Abstract
{

    protected $_name = 'in_customer';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authinstall');
    	return $session_user->user_id;
    }
	public function add($_data){
		try{
		    $_arr=array(		    
		    	'name'	      => $_data['name'],
		    	'phone'	      => $_data['phone'],
		    	'know_by'	  => $_data['know_by'],
				'date'		  => $_data['date'],
				'from_price'  => $_data['from_price'],
				'to_price'    => $_data['to_price'],
				'requirement' => $_data['requirement'],
				'type'        => $_data['type'],
				'description' => $_data['description'],
		    	'statusreq'   => $_data['statusreq'],
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
		$sql = "SELECT *,(SELECT kn.title FROM rms_know_by as kn WHERE kn.id = know_by LIMIT 1) as know_bytitle FROM $this->_name WHERE id = ".$db->quote($id);
		$sql.=" LIMIT 1 ";
		$row=$db->fetchRow($sql);
		return $row;
	}


	function getAllInfo($search = null){		
		$db = $this->getAdapter();
		$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
		$where = " WHERE ".$from_date." AND ".$to_date;		
		$sql = "SELECT id,name, phone,
		(SELECT title FROM `rms_know_by` WHERE rms_know_by.id=know_by LIMIT 1) as know_by,
		 `date`,from_price,to_price,requirement,type,description,	
		statusreq,			
		    (SELECT  first_name FROM rms_users WHERE id = user_id limit 1 ) AS user_name,
			status FROM $this->_name ";
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[] = " name LIKE '%{$s_search}%'";
			$s_where[] = " phone LIKE '%{$s_search}%'";
			$s_where[] = " requirement LIKE '%{$s_search}%'";
			$s_where[] = " type LIKE '%{$s_search}%'";
			$s_where[] = " from_price LIKE '%{$s_search}%'";
			$s_where[] = " to_price LIKE '%{$s_search}%'";
			
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['status']>-1){
			$where.= " AND status = ".$search['status'];
		}
		if(!empty($search['statusreq'])){
			$where.= " AND statusreq = '".$search['statusreq']."'";
		}
		if($search['know_by']>0){
			$where.= " AND know_by = ".$search['know_by'];
		}	
		$userid = $this->getUserId();
		$db_user=new Application_Model_DbTable_DbUsers();
		$user_info = $db_user->getUserInfo($userid);
		if (!empty($user_info['staff_id'])){
			$where.= " AND user_id = ".$userid;
		}
		$order=" ORDER BY id DESC ";
		return $db->fetchAll($sql.$where.$order);	
	}
	function  getAllstatusreqForOpt(){
		$db = $this->getAdapter();
		$sql = 'SELECT DISTINCT statusreq as name,statusreq as id FROM `in_customer` WHERE statusreq!="" ORDER BY statusreq ASC ';
		$rows =  $db->fetchAll($sql);
		return $rows;
	}
	public function AllHistoryContact($crm_id){
		$db = $this->getAdapter();
		$sql="SELECT c.*,
		(SELECT CONCAT(last_name,' ',first_name) FROM rms_users WHERE c.user_contact=id LIMIT 1 ) AS user_contact_name
		FROM `ln_history_contact` AS c WHERE customer_id = $crm_id ORDER BY c.id DESC";
		return $db->fetchAll($sql);
	}
	public function addContactHistory($_data){
		$_db= $this->getAdapter();
		try{
	
			$_arr=array(
					'customer_id'	  => $_data['id'],
					'contact_date' => $_data['contact_date'],
					'feedback'=> $_data['feedback'],
					'proccess'=> $_data['proccess'],
					'next_contact'=> $_data['next_contact'],
					'user_contact'=> $_data['user_contact'],
					'create_date' => date("Y-m-d H:i:s"),
					'modify_date' => date("Y-m-d H:i:s"),
					'user_id'	  => $this->getUserId()
			);
			$this->_name = "ln_history_contact";
			$id = $this->insert($_arr);
				
			return $id;
		}catch(exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("Application Error!");
			echo $e->getMessage();
		}
	}
}