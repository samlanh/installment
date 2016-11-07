<?php

class Other_Model_DbTable_DbZone extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_zone';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    }
	public function addZone($_data){
		try {
		$_arr=array(
				'zone_name'	  => $_data['zone_name'],
				'zone_num'	  => $_data['zone_number'],
				'modify_date' => date('Y-m-d'),
				'status'	  => $_data['status'],
				'user_id'	  => $this->getUserId()
		);
		if(!empty($_data['id'])){
			$where = 'zone_id = '.$_data['id'];
			return  $this->update($_arr, $where);
		}else{
			return  $this->insert($_arr);
		}
		}catch (Exception $e){
			echo $e->getMessage();
		}
	}
	public function getZoneById($id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM $this->_name WHERE
			  zone_id = ".$db->quote($id);
		$sql.=" LIMIT 1 ";
		$row=$db->fetchRow($sql);
		return $row;
	}
	function getAllZoneArea($search=null){
		$db = $this->getAdapter();
		$sql = "SELECT
				zone_id,zone_name,zone_num,modify_date,
				(SELECT name_en FROM ln_view WHERE TYPE=3 AND key_code = status LIMIT 1) AS status,
				(SELECT first_name FROM rms_users WHERE id=user_id LIMIT 1) As user_name
				FROM $this->_name ";
		$where = ' WHERE zone_name!="" ';
		if($search['search_status']>-1){
			$where.= " AND status = ".$search['search_status'];
		}
		if(!empty($search['adv_search'])){
			$s_where = array();
			$search = addslashes(trim($search['adv_search']));
			$s_where[] = " zone_name LIKE '%{$search}%'";
			$s_where[] = "zone_num LIKE '%{$search}%'";
			$where.=' AND ('.implode(' OR ',$s_where).')';
		}
		$order = " ORDER BY zone_id DESC";
		return $db->fetchAll($sql.$where.$order);	
	}	
}

