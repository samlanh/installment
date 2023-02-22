<?php
class Po_Model_DbTable_DbCateIncome extends Zend_Db_Table_Abstract
{
	protected $_name = 'st_cate_income';

	public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
	
	public function getBranchId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->branch_id;
	}
	
	function getAllCateIncome($search=null,$parent = 0, $spacing = '', $cate_tree_array = ''){
		$db = $this->getAdapter();
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$sql="SELECT
					id,
					title AS name,
					accountCode,
					createDate,
					(SELECT first_name FROM rms_users WHERE id=userId LIMIT 1 ) AS userName,
					status
			";
		$sql.=$dbgb->caseStatusShowImage("status");
		$sql.="
			FROM
				st_cate_income
				where
					parent = $parent
		";
		$order = " ORDER BY id desc ";
		$where = '';
	/*
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = $search['adv_search'];
			$s_where[]=" category_name LIKE '%{$s_search}%'";
			$s_where[]=" account_code LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['status']>-1){
			$where.= " AND status = ".$search['status'];
		}
		*/
		
    	return $db->fetchAll($sql.$where.$order);
	}
	
	function addCateIncome($data){
		$_db= $this->getAdapter();
		try{
			$sql="SELECT id FROM `st_cate_income` WHERE title ='".$data['title']."'";
			$rs = $_db->fetchOne($sql);
			if(!empty($rs)){
				return -1;
			}
			$array = array(
				'title'			=>$data['title'],
				'parent'		=>$data['parent'],
				'accountCode'	=>$data['accountCode'],
				'userId'		=>$this->getUserId(),
				'createDate'	=>date('Y-m-d'),
			);
			$this->insert($array);
		}catch(Exception $e){
		}
 	 }
 	 
	 function updateCateIncome($data){
	 	try{
			$status = empty($data['status'])?0:1;
			$arr = array(
				'title'			=>$data['title'],
				'parent'		=>$data['parent'],
				'accountCode'	=>$data['accountCode'],
				'status'		=>$status,
				'userId'		=>$this->getUserId(),
				);
			$where=" id = ".$data['id'];
			$this->update($arr, $where);
		}catch(Exception $e){
		}
	}
	
	function getCateIncomeById($id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM st_cate_income where id=$id ";
		return $db->fetchRow($sql);
	}
	
	public function getParentCateIncome($cate_id='',$parent = 0, $spacing = '', $cate_tree_array = ''){
		$db=$this->getAdapter();
		if (!is_array($cate_tree_array))
			$cate_tree_array = array();
		$sql = " SELECT id , title AS name FROM `st_cate_income` WHERE STATUS=1 AND `parent` = $parent ";
		if (!empty($cate_id)){
			$sql.=" AND id != $cate_id";
		}
		$query = $db->fetchAll($sql);
		$rowCount = count($query);
	
		$id='';
		if ($rowCount > 0) {
			foreach ($query as $row){
				$cate_tree_array[] = array("id" => $row['id'], "name" => $spacing . $row['name']);
				$cate_tree_array = $this->getParentCateIncome($cate_id,$row['id'], $spacing . ' - ', $cate_tree_array);
			}
		}
		return $cate_tree_array;
	}
}