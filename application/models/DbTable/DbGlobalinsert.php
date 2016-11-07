<?php

class Application_Model_DbTable_DbGlobalinsert extends Zend_Db_Table_Abstract
{
    // set name value
	public function setName($name){
		$this->_name=$name;
	}
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function getUserId(){
		$session_user=new Zend_Session_Namespace('auth');
		return $session_user->user_id;
	
	}
	public function insertSerViceProgramType($_data){
		$this->_name='rms_program_type';
		$_db = new Application_Model_DbTable_DbGlobal();
		$_rs = $_db->getServicTypeByName($_data['servicetype_title'],$_data['ser_type']);
		if(!empty($_rs)){
			return -1;	
		}else{
			$_arr = array(
					'title'=>$_data['servicetype_title'],
					'item_desc'=>$_data['item_desc'],
					'status'=>$_data['sertype_status'],
					'type'=>$_data['ser_type'],
					'create_date'=> new Zend_Date(),
					'user_id' => $this->getUserId()
			);
			return $this->insert($_arr);
		}
	}


	
}
?>