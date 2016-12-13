<?php

class Other_Model_DbTable_DbCommune extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_commune';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    	 
    }
	public function addCommune($_data,$id=null){
		$_arr=array(
				'code' => $_data['code'],
				'district_id' => $_data['district_name'],
				'commune_namekh'=> $_data['commune_namekh'],
				'commune_name'=> $_data['commune_name'],
				'displayby'=> $_data['display'],
				'status'	  => $_data['status'],
				'modify_date' => Zend_Date::now(),
				'user_id'	  => $this->getUserId()
		);
		if(!empty($id)){
			$where = 'com_id = '.$id;
			return  $this->update($_arr, $where);
		}else{
			return  $this->insert($_arr);
		}
	}
	public function addCommunebyAJAX($_data,$id=null){
		$_arr=array(
				'district_id' => $_data['district_nameen'],
				'commune_namekh'=> $_data['commune_namekh'],
				'commune_name'=> $_data['commune_nameen'],
				//'displayby'=> $_data['display'],
				'status'	  => 1,
				'modify_date' => Zend_Date::now(),
				'user_id'	  => $this->getUserId()
		);
			return  $this->insert($_arr);
		
	}
	
	public function getCommuneById($id){
		$db = $this->getAdapter();
		$sql=" SELECT c.com_id,c.code,c.district_id,c.commune_name,commune_namekh,displayby,c.modify_date,c.status,c.user_id,
				(SELECT pro_id FROM `ln_district` WHERE dis_id =c.district_id LIMIT 1 ) as pro_id
				FROM ln_commune AS c WHERE c.com_id = $id  LIMIT 1";
		$row=$db->fetchRow($sql);
		return $row;
	}
	function getAllCommune($search=null){
		$db = $this->getAdapter();
		$sql = " SELECT com.com_id,com.code,com.commune_namekh,com.commune_name,com.district_name, 
			com.modify_date,com.status_name,com.user_name
			FROM v_getallcommune AS com";
		//$sql = " SELECT * FROM v_getallcommune ";
		
		$where = ' WHERE 1 ';
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[] = " com.district_name LIKE '%{$s_search}%'";
			$s_where[] = " com.code LIKE '%{$s_search}%'";
			$s_where[] = " com.commune_name LIKE '%{$s_search}%'";
			$s_where[]=" com.commune_namekh LIKE '%{$s_search}%'";
			$where .=' AND '.implode(' OR ',$s_where);
		}
		if(!empty($search['province_name'])){
			$where.=" AND com.province_id=".$search['province_name'];
		}
		if(!empty($search['district_name'])){
			$where.=" AND com.district_id=".$search['district_name'];
		}
		if($search['search_status']>-1){
			$where.=" AND com.status=".$search['search_status'];
		}
		$order = " ORDER BY com.com_id DESC ";
// 		echo $sql.$where.$order;
		return $db->fetchAll($sql.$where.$order);	
	}
        public function getCommuneBydistrict($distict_id){
		$db = $this->getAdapter();
		$sql = "SELECT com_id AS id ,commune_namekh AS name FROM $this->_name WHERE status=1 AND commune_name!='' AND  $this->_name.district_id=".$db->quote($distict_id); 
		$rows=$db->fetchAll($sql);
		return $rows;
	}	
}

