<?php

class Other_Model_DbTable_DbCreditOfficer extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_co';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    	 
    }
	public function addCreditOfficer($_data){
		$photoname = str_replace(" ", "_", $_data['first_name']) . '.jpg';
		$upload = new Zend_File_Transfer();
		$upload->addFilter('Rename',
				array('target' => PUBLIC_PATH . '/images/'. $photoname, 'overwrite' => true) ,'photo');
		$receive = $upload->receive();
		//echo $receive; exit();
		if($receive)
		{
			$_data['photo'] = $photoname;
		}
		else{
			$_data['photo']="";
		}
		unset($_data['MAX_FILE_SIZE']);
		$_arr=array(
				'co_code'	  => $_data['co_id'],
				'co_khname'	  => $_data['name_kh'],
				'co_firstname'=> $_data['first_name'],
				'co_lastname' => '',//$_data['last_name'],
				'displayby'	  => $_data['display'],
				'position_id' =>$_data['position'],
				'sex'		  => $_data['co_sex'],
				'national_id'	  => $_data['national_id'],
				'address'	  => $_data['address'],
				'pob'	      => $_data['pob'],
				'degree'	      => $_data['degree'],
				'tel'	  	  => $_data['tel'],
				'email'	      => $_data['email'],
				'create_date' => Zend_Date::now(),
				'status'      => $_data['status'],
				'user_id'	  => $this->getUserId(),
				'basic_salary'=> $_data['basic_salary'],
				'start_date'  => $_data['start_date'],
				'end_date'	  => $_data['end_date'],
				'contract_no' => $_data['contract_no'],
				'note'		  => $_data['note'],
				'shift'		  => $_data['shift'],
				'workingtime' => $_data['workingtime'],
				'annual_lives'=>$_data['annual_lives'],
				'photo'=>$_data['photo'],
				'department_id'=>$_data['department_id'],
				'figer_print_id'=>$_data['figer_print_id'],
		);
		if(!empty($_data['id'])){
			$where = 'co_id = '.$_data['id'];
			return  $this->update($_arr, $where);
		}else{
			return  $this->insert($_arr);
		}
		
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
		$sql = "SELECT * FROM $this->_name WHERE co_id = ".$db->quote($id);
		$sql.=" LIMIT 1 ";
		$row=$db->fetchRow($sql);
		return $row;
	}
	function getAllCreditOfficer($search=null){
		$db = $this->getAdapter();
		$sql = "SELECT co_id,co_code,co_khname,CONCAT(co_firstname,co_lastname) AS co_engname,national_id,address,
					tel,email,address,(SELECT name_kh FROM ln_view WHERE type=20 AND key_code=degree) AS degree,
					(SELECT department_kh FROM ln_department WHERE id=department_id) AS department_id,
					annual_lives,status FROM $this->_name WHERE 1";
// 		(SELECT first_name FROM rms_users WHERE id=user_id) As user_name
		$order=" ORDER BY co_id DESC";
		$where = '';
		
		if($search['status_search']>-1){
			$where.= " AND status = ".$search['status_search'];
		}
		if(!empty($search['degree'])){
			$where.=" AND degree = ".$search['degree'];
		}
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = ($search['adv_search']);
			$s_where[] = "co_khname LIKE '%{$s_search}%'";
			$s_where[] = "co_firstname LIKE '%{$s_search}%'";
			$s_where[] = "co_lastname LIKE '%{$s_search}%'";
			$s_where[]= "national_id LIKE '%{$s_search}%'";
			$s_where[] = " tel LIKE '%{$s_search}%'";
			$s_where[] = "email LIKE '%{$s_search}%'";
			$s_where[] = "address LIKE '%{$s_search}%'";
			$s_where[]="annual_lives LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		return $db->fetchAll($sql.$where.$order);	
	}	
}

