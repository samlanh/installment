<?php

class Callteral_Model_DbTable_DbCallteral extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_client';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    }
	public function addClient($_data){
		try{
		$_arr=array(
				'is_group'	  => $_data['is_group'],
				'parent_id'	  => $_data['group_id'],
				'client_number'=> $_data['client_no'],
				'name_kh'	  => $_data['name_kh'],
				'name_en'	  => $_data['name_en'],
				'sex'	      => $_data['sex'],
				'sit_status'  => $_data['situ_status'],
				'pro_id'      => $_data['province'],
				'dis_id'      => $_data['district'],
				'com_id'      => $_data['commune'],
				'village_id'  => $_data['village'],
				'street'	  => $_data['street'],
				'house'	      => $_data['house'],
				'id_type'	  => $_data['id_type'],
				'id_number'	  => $_data['id_no'],
				'phone'	      => $_data['phone'],
				'spouse_name' => $_data['spouse'],
				'remark'	  => $_data['desc'],
				'create_date' => date("Y-m-d"),
				'status'      => $_data['status'],
				'user_id'	  => $this->getUserId()
		);
		if(!empty($_data['id'])){
			$where = 'client_id = '.$_data['id'];
			return  $this->update($_arr, $where);
		}else{
			return  $this->insert($_arr);
		}
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	public function getClientById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM $this->_name WHERE client_id = ".$db->quote($id);
		$sql.=" LIMIT 1 ";
		$row=$db->fetchRow($sql);
		return $row;
	}
	function getAllClients($search=null){
		$db = $this->getAdapter();
		$sql = " 
		SELECT client_id,client_number,name_kh,name_en,sex,phone,house,street,
			(SELECT village_name FROM `ln_village` WHERE vill_id= village_id) AS village_name
		    ,spouse_name,
			(SELECT  CONCAT(first_name,' ', last_name) FROM rms_users WHERE id=user_id )AS user_name,
			status FROM $this->_name WHERE 1 ";
		$order=" order by name_kh";
		$where = '';
		
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = $search['adv_search'];
			$s_where[] = "client_number LIKE '%{$s_search}%'";
			$s_where[] = " name_en LIKE '%{$s_search}%'";
			$s_where[] = " name_kh LIKE '%{$s_search}%'";
			$s_where[] = " phone LIKE '%{$s_search}%'";
			$s_where[] = " house LIKE '%{$s_search}%'";
			$s_where[] = " street LIKE '%{$s_search}%'";
			$s_where[] = " spouse_name LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['status']>-1){
			$where.= " AND status = ".$search['status'];
		}
		return $db->fetchAll($sql.$where.$order);	
	}
	public function getGroupCode($data){
		$db = $this->getAdapter();
		$sql = "SELECT COUNT(group_code) AS number FROM `ln_client` 
			WHERE is_group =1 AND branch_id=".$data['branch_id'];
		$rs = $db->fetchOne($sql);
		return $rs+1;
	}	
}

