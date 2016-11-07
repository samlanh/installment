<?php

class Other_Model_DbTable_DbPosition extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_position';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    	 
    }
	public function getPostionById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM $this->_name WHERE co_id = ".$db->quote($id);
		$sql.=" LIMIT 1 ";
		$row=$db->fetchRow($sql);
		return $row;
	}
	function getAllStaffPosition($search=null){
		$db = $this->getAdapter();
		$sql=" SELECT id,position_en,position_kh,
		(SELECT displayby_en FROM `ln_displayby` AS ld WHERE ld.id = id LIMIT 1) AS displayby,
		status
		FROM `ln_position` WHERE 1 ";
		$order=" order by id DESC";
		$where = '';
		
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = $search['adv_search'];
			$s_where[] = "position_kh LIKE '%{$s_search}%'";
			$s_where[] = " position_en LIKE '%{$s_search}%'";
			$s_where[] = " status LIKE '%{$s_search}%'";
			$s_where[] = " displayby LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['status_search']>-1){
			$where.= " AND status = ".$search['status_search'];
		}
		return $db->fetchAll($sql.$where.$order);	
	}	
}

