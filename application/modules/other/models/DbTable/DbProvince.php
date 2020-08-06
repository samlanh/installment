<?php

class Other_Model_DbTable_DbProvince extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_province';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    	 
    }
    public function addNewProvince($_data){
    	$_arr=array(
    			'code' 			   => $_data['code'],
    			'province_en_name' => $_data['en_province'],
    			'province_kh_name' => $_data['kh_province'],
    			'modify_date'      => Zend_Date::now(),
    			'status'           => 1,
    			'user_id'	       => $this->getUserId()
    	);
    	return  $this->insert($_arr);
    }
    
	public function getProvinceById($id){
		$db = $this->getAdapter();
		$sql = "SELECT * FROM ln_province WHERE province_id = ".$id;
		$sql.=" LIMIT 1";
		$row=$db->fetchRow($sql);
		return $row;
	}
    public function updateProvince($_data,$id){
    	$_arr=array(
    			'code' 			   => $_data['code'],
    			'province_en_name' => $_data['en_province'],
    			'province_kh_name' => $_data['kh_province'],
    			//'displayby'	       => $_data['display'],
    			'modify_date'      => Zend_Date::now(),
    			'status'           => $_data['status'],
    			'user_id'	       => $this->getUserId()
    	);
    	$where=$this->getAdapter()->quoteInto("province_id=?", $id);
    	$this->update($_arr, $where);
    }
    function getAllProvince($search=null){
    	$db = $this->getAdapter();
    	$sql = " SELECT province_id AS id,code,province_en_name,province_kh_name,    	
    	modify_date
    	 ";
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->caseStatusShowImage("status");
    	$sql.=",
    	(SELECT CONCAT(last_name,' ',first_name) FROM rms_users WHERE id=user_id )AS user_name
    	FROM $this->_name
    	WHERE 1
    	";
    	$order=" ORDER BY province_id DESC";
    	$where = '';
    	
    	if(!empty($search['title'])){
    		$s_where=array();
    		$s_search=addslashes(trim($search['title']));
    		$s_where[]=" code LIKE '%{$s_search}%'";
    		$s_where[]=" province_en_name LIKE '%{$s_search}%'";
    		$s_where[]=" province_kh_name LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
    	if($search['status']>-1){
    		$where.= " AND status = ".$db->quote($search['status']);
    	}
    	return $db->fetchAll($sql.$where.$order);
    }
   
}

