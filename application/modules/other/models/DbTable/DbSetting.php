<?php

class Global_Model_DbTable_DbSetting extends Zend_Db_Table_Abstract
{
    protected $_name = 'rms_setting';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    
    }
    public function AddNewSetting($_data){
    	$_array = array(
    			'keyName'=>$_data['key_name'],
    			'keyValue'=>$_data['key_value'],
    			'user_id'=>$this->getUserId(),
    			//'access_type'=>$_data['']
    			);
    	if(!empty($_data['id'])){
    		$where = $this->getAdapter()->quoteInto("code=?", $_data['id']);
    		$this->update($_array, $where);
    	}else{
    		$this->insert($_array);
    	}
    	
    	
    }
    public function getSettingById($_id){
    	$_db = $this->getAdapter();
    	$sql="SELECT code,keyName,keyValue FROM rms_setting WHERE code=$_id LIMIT 1";
    	return $_db->fetchRow($sql);
    }
	function getAllSetting($search=null){
		$sql = " SELECT code as id, keyvalue, 
					(SELECT first_name FROM rms_users WHERE id=user_id) As user_name
					,access_type
					FROM rms_setting
		 	 WHERE access_type!=4 ";
		$order=" order by keyvalue";
		$where = '';
 		if(!empty($search['title'])){
 			$where.=" AND keyvalue LIKE '%".$search['title']."%'";
 		}
 		$db = $this->getAdapter();
		return $db->fetchAll($sql.$where.$order);
	}
}

