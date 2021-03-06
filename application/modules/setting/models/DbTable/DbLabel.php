<?php

class Setting_Model_DbTable_DbLabel extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_setting';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    	 
    }
	public function getAllSystemSetting(){
		$db = $this->getAdapter();
		$sql = " SELECT keycode ,value FROM `ln_system_setting` ";
		$_result = $db->fetchAll($sql);
		$_k = array();
		foreach ($_result as $key => $k) {
			$_k[$k['keycode']] = $k['value'];
		}
		return $_k;
	}
	public function updateLabel($data){
		$db = $this->getAdapter();
		$arr = array(
				'keyValue'=>$data['label_name'],
				);
		$where = $db->quoteInto('code=?', $data['id']);
		$this->update($arr, $where);
		
	}
	function updateKeyCode($post, $data){
		$this->_name='ln_system_setting';
		$_key_code_data=array();
		foreach ($post as $key => $val) {
			if($val != $data[$key]){
				$_key_code_data['value'] = $val;
	
				$where=$this->getAdapter()->quoteInto('keycode=?', $key);
				$this->update( $_key_code_data, $where);
				if($key == 'servername'){
					$where=$this->getAdapter()->quoteInto('keycode=?', "servername");
					$this->update( $_key_code_data, $where);
				}else if($key == 'dbuser'){
					$where=$this->getAdapter()->quoteInto('keycode=?', "dbuser");
					$this->update( $_key_code_data, $where);
				}else if($key == 'dbpassword'){
					$where=$this->getAdapter()->quoteInto('keycode=?', "dbpassword");
					$this->update( $_key_code_data, $where);
				}else if($key == 'dbname'){
					$where=$this->getAdapter()->quoteInto('keycode=?', "dbname");
					$this->update( $_key_code_data, $where);
				}else if($key == 'work_saturday'){
					$where=$this->getAdapter()->quoteInto('keycode=?', "work_saturday");
					$this->update( $_key_code_data, $where);
				}else if($key == 'work_sunday'){
					$where=$this->getAdapter()->quoteInto('keycode=?', "work_sunday");
					$this->update( $_key_code_data, $where);
				}
			}
				
		}
	}
}