<?php class Stock_Model_DbTable_DbProductCate extends Zend_Db_Table_Abstract{
	protected $_name = 'rms_product_cate';
	
    public function getUserId(){
    	$_dbgb = new Application_Model_DbTable_DbGlobal();
    	return $_dbgb->getUserId();
    }
	
	function getAllItems($search = '',$type=null){
		$db = $this->getAdapter();
		$sql = " SELECT 
					d.id,
					d.title,
					d.title_en,
					d.shortcut,
					d.ordering,
					note,
					(SELECT CONCAT(first_name) FROM rms_users WHERE d.user_id=id LIMIT 1 ) AS user_name,
					d.create_date,
					d.modify_date
			";
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbgb->caseStatusShowImage("d.status");
		$sql.=" FROM `rms_product_cate` AS d  WHERE 1 ";
		
		$orderby = " ORDER BY d.type ASC, d.ordering DESC,d.id DESC ";
		$where = ' ';
		if(!empty($type)){
			$where.= " AND d.type = ".$db->quote($type);
		}
		if(!empty($search['advance_search'])){
			$s_where = array();
	    		$s_search = addslashes(trim($search['advance_search']));
		 		$s_where[] = " d.title LIKE '%{$s_search}%'";
		 		$s_where[] = " d.title_en LIKE '%{$s_search}%'";
	    		$s_where[] = " d.shortcut LIKE '%{$s_search}%'";
	    		$sql .=' AND ( '.implode(' OR ',$s_where).')';	
		}
		if($search['status_search']>-1){
			$where.= " AND status = ".$db->quote($search['status_search']);
		}
		return $db->fetchAll($sql.$where.$orderby);
	}
	function getAllItemsOption($search = '',$type=null){
		$db = $this->getAdapter();
		$sql = "SELECT d.id,d.title,d.title_en,
			(SELECT CONCAT(first_name) FROM rms_users WHERE d.user_id=id LIMIT 1 ) AS user_name
				  ";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->caseStatusShowImage("d.status");
		$sql.=" FROM `rms_product_cate` AS d WHERE 1 ";
		$where = ' ';
		if(!empty($type)){
			$where.= " AND d.type = ".$db->quote($type);
		}
		if(!empty($search['advance_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['advance_search']));
			$s_where[] = " d.title LIKE '%{$s_search}%'";
			$s_where[] = " d.title_en LIKE '%{$s_search}%'";
			$s_where[] = " d.shortcut LIKE '%{$s_search}%'";
			$sql .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if($search['status_search']>-1){
			$where.= " AND status = ".$db->quote($search['status_search']);
		}
		
		$orderby = " ORDER BY d.type ASC, d.id DESC ";
		return $db->fetchAll($sql.$where.$orderby);
	}
	public function getDegreeById($degreeId,$type=null){
		$db = $this->getAdapter();
		$sql=" SELECT d.* FROM $this->_name AS d WHERE d.`id` = $degreeId ";
		if (!empty($type)){
			$sql.=" AND d.type=$type";
		}
		return $db->fetchRow($sql);
	}
	public function AddDegree($_data){
		$_db= $this->getAdapter();
		try{
			$_arr=array(
					'title'	  		=> $_data['title'],
					'title_en'	  	=> $_data['title_en'],
					'shortcut' 		=> $_data['shortcut'],
					'ordering'		=> $_data['ordering'],
					'note'			=> $_data['note'],
					'type'			=> 3,//product type
					'create_date' 	=> date("Y-m-d H:i:s"),
					'modify_date' 	=> date("Y-m-d H:i:s"),
					'status'		=> 1,
					'user_id'	 	=> $this->getUserId()
			);
			return $this->insert($_arr);
		}catch(exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("Application Error!");
		}
	}
	public function UpdateDegree($_data,$id){
		$_db= $this->getAdapter();
		try{
			$_arr=array(
					'title'	  		=> $_data['title'],
					'title_en'	  	=> $_data['title_en'],
					'shortcut' 		=> $_data['shortcut'],
					'note'			=> $_data['note'],
					'type'			=> 3,//product type
					'ordering'		=> $_data['ordering'],
					'modify_date' 	=> date("Y-m-d H:i:s"),
					'status'		=> $_data['status'],
					'user_id'	  	=> $this->getUserId()
			);
			$where = " id = $id ";
			$this->update($_arr, $where);
		}catch(exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("Application Error!");
		}
	}
	
	function checkuDuplicate($data){
		$db = $this->getAdapter();
		$sql="
			SELECT 
				* FROM rms_product_cate AS i
			WHERE i.title='".$data['title']."'
				AND i.title_en='".$data['title_en']."'
				AND i.schoolOption='".$data['schoolOption']."' 
				AND i.type='".$data['type']."' ";
		
		if (!empty($data['id'])){
			$sql.=" AND i.id != ".$data['id'];
		}
		
		$sql.=" LIMIT 1 ";
		$row = $db->fetchRow($sql);
		if (!empty($row)){
			return 1;
		}
		return 0;
	}
}