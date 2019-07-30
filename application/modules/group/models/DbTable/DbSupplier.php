<?php

class Group_Model_DbTable_DbSupplier extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_supplier';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    	 
    }
	public function addSupplier($_data){
		
		
		$_arr=array(
				'branch_id'	  => $_data['branch_id'],
				'supplier_code'	  => $_data['supplier_code'],
				'name'	  => $_data['name'],
				'phone'	  	  => $_data['tel'],
				'email'	      => $_data['email'],
				'address'	  => $_data['address'],
				'user_id'	  => $this->getUserId(),
				'note'		  => $_data['note'],
				'create_date' => date("Y-m-d H:i:s"),

		);
		$this->_name="ln_supplier";
		if(!empty($_data['id'])){
			$_arr['status'] = $_data['status'];
			$where = 'id = '.$_data['id'];
			  $this->update($_arr, $where);
			  $id = $_data['id'];
		}else{
			$_arr['status'] = 1;
			$id =  $this->insert($_arr);
		}
		return $id;
		
	}
	function addUserSystem($sale_id){
		
	}
	public function addCoByAjax($data){
		$arr = array(
		        //'co_code'	  => $_data['co_id'],
				'co_khname'	  => $data['last_name'],
				'co_firstname'=> $data['first_name'],
				'co_lastname' => $data['last_name'],
				'displayby'	  => 1,
				'position_id' =>1,
				'sex'		  => $data['co_sex'],
				'tel'	  	  => $data['tel'],
				'email'	      => $data['email'],
				'create_date' => Zend_Date::now(),
				'status'      => 1,
				'user_id'	  => $this->getUserId(),
				'basic_salary'=> 0,
// 				'note'		  => $data['note']
		);
		return $this->insert($arr);
		
	}
	public function getCOById($id){
		$db = $this->getAdapter();
		$this->_name="ln_supplier";
		$sql = "SELECT s.*
		FROM $this->_name AS s WHERE s.id = ".$db->quote($id);
		$sql.=" LIMIT 1 ";
		$row=$db->fetchRow($sql);
		return $row;
	}
// 	public function getUserByStaffID($staff_id){
// 		$db = $this->getAdapter();
// 		$sql="SELECT u.* FROM `rms_users` AS u WHERE u.`staff_id` = $staff_id LIMIT 1";
// 		return $db->fetchRow($sql);
		
// 	}
	function getAllSupplier($search=null){
		$db = $this->getAdapter();
		$sql = "SELECT id,
		(SELECT p.project_name FROM `ln_project` AS p WHERE p.br_id = branch_id limit 1) AS branch_name,
		supplier_code,name,address,
					phone,email ";
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->caseStatusShowImage("status");
		$sql.=" FROM $this->_name WHERE 1 ";
		
		$order=" ORDER BY id DESC";
		$where = '';
		
		if($search['status_search']>-1){
			$where.= " AND status = ".$search['status_search'];
		}
		if(!empty($search['branch_id'])){
			$where.=" AND branch_id = ".$search['branch_id'];
		}
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = ($search['adv_search']);
			$s_where[] = " supplier_code LIKE '%{$s_search}%'";
			$s_where[] = " name LIKE '%{$s_search}%'";
			$s_where[] =" phone LIKE '%{$s_search}%'";
			$s_where[] =" email LIKE '%{$s_search}%'";
			$s_where[] =" address LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		return $db->fetchAll($sql.$where.$order);	
	}	
	
	function checkusername($user_name){
		$db = $this->getAdapter();
		$sql="SELECT * FROM `rms_users` AS u WHERE u.`user_name`='$user_name' LIMIT 1";
		$row = $db->fetchRow($sql);
		if (!empty($row)) {
			return 1;
		}
		return 2;
	}
}

