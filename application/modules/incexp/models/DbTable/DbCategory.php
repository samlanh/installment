<?php class Incexp_Model_DbTable_DbCategory extends Zend_Db_Table_Abstract{

	protected $_name = 'rms_category';
    public function getUserId(){
    	$_dbgb = new Application_Model_DbTable_DbGlobal();
    	return $_dbgb->getUserId();
    }
	
	function getAllItems($search = '',$type=null){
		$db = $this->getAdapter();
		$sql = " SELECT d.id,d.title,
		(SELECT CONCAT(first_name) FROM rms_users WHERE d.user_id=id LIMIT 1 ) AS user_name,
		d.status FROM `rms_category` AS d WHERE 1 ";
		$orderby = " ORDER BY d.id DESC ";
		$where = ' ';
		
		if(!empty($search['advance_search'])){
			$s_where = array();
	    		$s_search = addslashes(trim($search['advance_search']));
		 		$s_where[] = " d.title LIKE '%{$s_search}%'";
	    		$s_where[] = " d.shortcut LIKE '%{$s_search}%'";
	    		$sql .=' AND ( '.implode(' OR ',$s_where).')';	
		}
		if($search['status_search']>-1){
			$where.= " AND status = ".$db->quote($search['status_search']);
		}
		return $db->fetchAll($sql.$where.$orderby);
	}
	function getAllItemsOption($search = ''){
		$db = $this->getAdapter();
		
		$base_url = Zend_Controller_Front::getInstance()->getBaseUrl();
		$imgnone='<img src="'.$base_url.'/images/icon/cross.png"/>';
		$imgtick='<img src="'.$base_url.'/images/icon/apply2.png"/>';
		
		$sql = " SELECT d.id,d.title,
		(SELECT CONCAT(first_name) FROM rms_users WHERE d.user_id=id LIMIT 1 ) AS user_name,
		 CASE    
				WHEN  `d`.`status` = 1 THEN '".$imgtick."'
				WHEN  `d`.`status` = 0 THEN '".$imgnone."'
		 END AS status

		FROM `rms_category` AS d WHERE 1 ";
		$orderby = " ORDER BY d.id DESC ";
		$where = ' ';
		if(!empty($search['advance_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['advance_search']));
			$s_where[] = " d.title LIKE '%{$s_search}%'";
			$s_where[] = " d.shortcut LIKE '%{$s_search}%'";
			$sql .=' AND ( '.implode(' OR ',$s_where).')';
		}

		if($search['status_search']>-1){
			$where.= " AND status = ".$db->quote($search['status_search']);
		}
		return $db->fetchAll($sql.$where.$orderby);
	}
	public function getDegreeById($degreeId,$type=null){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM $this->_name WHERE `id` = $degreeId ";
		return $db->fetchRow($sql);
	}
	public function AddCategory($_data){
		$_db= $this->getAdapter();
		try{
			
			$_arr=array(
					'title'	  => $_data['title'],
	//				'shortcut' => $_data['shortcut'],
					'create_date' => date("Y-m-d H:i:s"),
					'modify_date' => date("Y-m-d H:i:s"),
					'status'=> $_data['status'],
					'user_id'	  => $this->getUserId()
			);
			$this->_name = "rms_category";
			$id =  $this->insert($_arr);
			
			return $id;	
		}catch(exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("Application Error!");
			echo $e->getMessage();
		}
	}
	public function UpdateDegree($_data){
		
		$_db= $this->getAdapter();
		try{
				
			$_arr=array(
					'title'	  => $_data['title'],
		//			'shortcut' => $_data['shortcut'],
					'modify_date' => date("Y-m-d H:i:s"),
					'status'=> $_data['status'],
					'user_id'	  => $this->getUserId()
			);
			$this->_name = "rms_category";
			$id = $_data["id"];
			$where = $this->getAdapter()->quoteInto("id=?",$id);
			$this->update($_arr, $where);

			return $id;
		}catch(exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("Application Error!");
			echo $e->getMessage();
		}
		
	}
	
}
