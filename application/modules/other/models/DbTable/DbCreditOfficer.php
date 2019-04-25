<?php

class Other_Model_DbTable_DbCreditOfficer extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_staff';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authinstall');
    	return $session_user->user_id;
    	 
    }
	public function addCreditOfficer($_data){
		
		$photoname = str_replace(" ", "_", $_data['co_id']) . '.jpg';
		$upload = new Zend_File_Transfer();
		$upload->addFilter('Rename',
				array('target' => PUBLIC_PATH . '/images/'. $photoname, 'overwrite' => true) ,'photo');
		$receive = $upload->receive();
		if($receive)
		{
			$_data['photo'] = $photoname;
		}
		else{
			$_data['photo']="";
		}
		unset($_data['MAX_FILE_SIZE']);
// 		if(!empty($_data['id'])){
// 			$oldCode = $this->getCOById($_data['id']);
// 			$staff_id = $oldCode['co_code'];
// 		}else{
// 			$db = new Application_Model_DbTable_DbGlobal();
// 			$staff_id = $db->getStaffNumberByBranch($_data['branch_id']);
// 		}
		$_arr=array(
				'branch_id'	  => $_data['branch_id'],
				'co_code'	  => $_data['co_id'],
				'co_khname'	  => $_data['name_kh'],
				//'co_lastname' => '',//$_data['last_name'],
				'sex'		  => $_data['co_sex'],
				'national_id'	  => $_data['national_id'],
				'address'	  => $_data['address'],
				'pob'	      => $_data['pob'],
				'tel'	  	  => $_data['tel'],
				'email'	      => $_data['email'],
				'create_date' => date("Y-m-d"),
				'user_id'	  => $this->getUserId(),
				'note'		  => $_data['note'],
				//'contract_no' => $_data['contract_no'],
				
// 				'shift'		  => $_data['shift'],
// 				'workingtime' => $_data['workingtime'],
// 				'annual_lives'=>$_data['annual_lives'],
//				'department_id'=>$_data['department_id'],
//				'figer_print_id'=>$_data['figer_print_id'],
// 				'basic_salary'=> $_data['basic_salary'],
// 				'start_date'  => $_data['start_date'],
// 				'end_date'	  => $_data['end_date'],
// 				'co_firstname'=> $_data['name_en'],
// 				'displayby'	  => $_data['display'],
				'position_id' =>1,
				'photo'=>$_data['photo'],

		);
		$this->_name="ln_staff";
		if(!empty($_data['id'])){
			$_arr['status'] = $_data['status'];
			$where = 'co_id = '.$_data['id'];
			  $this->update($_arr, $where);
			  $id = $_data['id'];
		}else{
			$_arr['status'] = 1;
			$id =  $this->insert($_arr);
		}
		if (!empty($_data['check_create'])){
			$userdata = array(
					'branch_id'=>$_data['branch_id'],
					'first_name'=>$_data['name_kh'],
					'user_name'=>$_data['user_name'],
					'password'=> MD5($_data['password']),
					'user_type'=> $_data['user_type'],
					'active'=> 1,
					'staff_id'=>$id,
					'branch_list'=>$_data['branch_id'],
			);
			$this->_name="rms_users";
			$this->insert($userdata);
		}else{
			if (!empty($_data['id']) && !empty($_data['check_create'])){
				$userdata = array(
						'branch_id'=>$_data['branch_id'],
						'first_name'=>$_data['name_kh'],
						'user_name'=>$_data['user_name'],
						'user_type'=> $_data['user_type'],
						'active'=> 1,
						'staff_id'=>$id,
						'branch_list'=>$_data['branch_id'],
				);
				if (!empty($_data['check_create'])){
					$userdata = MD5($_data['password']);
				}
				$this->_name="rms_users";
				$where="staff_id = ".$id;
				$this->update($userdata, $where);
			}
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
		$this->_name="ln_staff";
		$sql = "SELECT s.*,
		(SELECT u.user_name FROM `rms_users` AS u WHERE u.staff_id = s.co_id LIMIT 1) AS user_name,
		(SELECT u.user_type FROM `rms_users` AS u WHERE u.staff_id = s.co_id LIMIT 1) AS user_type
		FROM $this->_name AS s WHERE s.co_id = ".$db->quote($id);
		$sql.=" LIMIT 1 ";
		$row=$db->fetchRow($sql);
		return $row;
	}
	public function getUserByStaffID($staff_id){
		$db = $this->getAdapter();
		$sql="SELECT u.* FROM `rms_users` AS u WHERE u.`staff_id` = $staff_id LIMIT 1";
		return $db->fetchRow($sql);
		
	}
	function getAllCreditOfficer($search=null){
		$db = $this->getAdapter();
		$sql = "SELECT co_id,
			(SELECT p.project_name FROM `ln_project` AS p WHERE p.br_id = branch_id limit 1) AS branch_name,
			co_code,co_khname,(select name_kh FROM `ln_view` WHERE type=11 and key_code =sex LIMIT 1) as gender,
			national_id,address,
			tel,email,
			(SELECT  CONCAT(first_name) FROM rms_users WHERE id=user_id ) AS user_name,
		status FROM $this->_name WHERE 1";
// 		(SELECT first_name FROM rms_users WHERE id=user_id) As user_name
		$order=" ORDER BY co_id DESC";
		$where = '';
		
		if($search['status_search']>-1){
			$where.= " AND status = ".$search['status_search'];
		}
// 		if(!empty($search['degree'])){
// 			$where.=" AND degree = ".$search['degree'];
// 		}
		if(!empty($search['branch_id'])){
			$where.=" AND branch_id = ".$search['branch_id'];
		}
// 		if(!empty($search['position'])){
// 			$where.=" AND position_id = ".$search['position'];
// 		}
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = ($search['adv_search']);
			$s_where[] = " co_khname LIKE '%{$s_search}%'";
			$s_where[] = " co_firstname LIKE '%{$s_search}%'";
			$s_where[] = " co_lastname LIKE '%{$s_search}%'";
			$s_where[] = " co_code LIKE '%{$s_search}%'";
			
			$s_where[]= " national_id LIKE '%{$s_search}%'";
			$s_where[] =" tel LIKE '%{$s_search}%'";
			$s_where[] =" email LIKE '%{$s_search}%'";
			$s_where[] =" address LIKE '%{$s_search}%'";
// 			$s_where[]="annual_lives LIKE '%{$s_search}%'";
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

