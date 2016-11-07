<?php

class Application_Model_DbTable_DbDept extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_major';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    	 
    }
	
	private function _buildQuery($search = ''){
		$sql = "SELECT 
					CONCAT(u.first_name,' ',u.last_name) As user_name,dept_id,en_name,shortcut
					,modify_date,user_id,is_active
					FROM `rms_dept` AS d,rms_users AS u				
					WHERE d.user_id=u.id ";
		$orderby = " ORDER BY en_name ";
		if(empty($search)){
			return $sql.$orderby;
		}
		$where = '';
			
		return $sql.$where.$orderby;
	}
	
	function getUserList($start, $limit){
		$db = $this->getAdapter();
		$sql = $this->_buildQuery()." LIMIT ".$start.", ".$limit;
		if ($limit == 'All') {
			$sql = $this->_buildQuery();
		}
		return $db->fetchAll($sql);		
	}
	
	function getUserListBy($search, $start, $limit){        
		$db = $this->getAdapter();		
		$sql = $this->_buildQuery($search)." LIMIT ".$start.", ".$limit;
		if ($limit == 'All') {
			$sql = $this->_buildQuery($search);
		}		
		return $db->fetchAll($sql);
	}
	
	function getUserListTotal($search=''){        
		$db = $this->getAdapter();
		$sql = $this->_buildQuery();
		if(!empty($search)){
			$sql = $this->_buildQuery($search);
		}
		$_result = $db->fetchAll($sql); 
		return count($_result);
	}
	
	public function AddNewMajor($_data){
			$_arr=array(
					'dept_id'	  => $_data['dept_id'],
					'major_enname'  => $_data['major_enname'],
					'major_khname'  => $_data['major_khname'],
					'shortcut'	  => $_data['shortcut'],
					'modify_date' => Zend_Date::now(),
					'is_active'	  => $_data['status'],
					'user_id'	  => $this->getUserId()
			);
			return  $this->insert($_arr);
	}
	public function AddNewDepartment($_data){
		$this->_name='rms_dept';
		if(!empty($_data["dept_id"])){
			$this->UpdateDepartment($_data);
		}else{
			$_arr=array(
					'en_name'	  => $_data['en_name'],
					'kh_name'	  => $_data['kh_name'],
					'shortcut'    => $_data['shortcut'],
					'modify_date' => new Zend_Date(),
					'is_active'   => $_data['status'],
					'user_id'	  => $this->getUserId()
			);
			return  $this->insert($_arr);
		}
	}
	public function UpdateDepartment($_data){
		$this->_name='rms_dept';
		$_arr=array(
				'en_name'	  => $_data['kh_name'],
				'en_name'	  => $_data['en_name'],
				'kh_name'	  => $_data['kh_name'],
				'shortcut'    => $_data['shortcut'],
				'modify_date' => Zend_Date::now(),
				'is_active'   => $_data['status'],
				'user_id'	  => $this->getUserId()
		);
		$where = $this->getAdapter()->quoteInto("dept_id=?",$_data["dept_id"]);
		$this->update($_arr, $where);
	    //return  $this->insert($_arr);
	}
}

