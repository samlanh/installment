<?php
class Invpayment_Model_DbTable_DbCateExpense extends Zend_Db_Table_Abstract
{
	protected $_name = 'st_cate_expense';
	
	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}
	
	
	function getAllCateIncome($search=null,$parent = 0, $spacing = '', $cate_tree_array = ''){
		$db = $this->getAdapter();
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$sql="SELECT
					c.id,
					c.title ,
					(SELECT t.title FROM `st_cate_expense` as t WHERE t.id=c.parent LIMIT 1 ) AS parentName,
					c.accountCode,
					c.createDate ,
					(SELECT first_name FROM rms_users WHERE id=c.userId LIMIT 1 ) AS userName,
					c.status ";
		$sql.=$dbgb->caseStatusShowImage("c.status");
		$sql.="
			FROM
					st_cate_expense as c
				where 1
		";
		$order = " ORDER BY id desc ";
		$where = '';
	
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = $search['adv_search'];
			$s_where[]=" c.title LIKE '%{$s_search}%'";
			$s_where[]=" c.accountCode LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['status']>-1){
			$where.= " AND c.status = ".$search['status'];
		}
		return $db->fetchAll($sql.$where.$order);
	}
	
	function addCateExpense($data){
		$db= $this->getAdapter();
		try{
			$sql="SELECT id FROM st_cate_expense where title ='".$data['title']."'";
			$rs = $db->fetchOne($sql);
			if(!empty($rs)){
				return -1;
			}
			$array = array(
					'title'    		=>$data['title'],
					'parent'		=>$data['parent'],
					'accountCode'	=>$data['accountCode'],
					'createDate'	=>date('Y-m-d'), 
					'userId'		=>$this->getUserId(),
				);
			$this->insert($array);
		}catch (Exception $e){
		}
 	 }
 	 
	 function updateCateExpense($data){
		$status = empty($data['status'])?0:1;
		$arr = array(
				'title'    		=>$data['title'],
				'parent'		=>$data['parent'],
				'accountCode'	=>$data['accountCode'],
				'status'		=>$status,
				'userId'		=>$this->getUserId(),
			);
		$where=" id = ".$data['id'];
		$this->update($arr, $where);
	}
	
	function getCateExpenseById($id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM st_cate_expense where id=$id ";
		return $db->fetchRow($sql);
	}
	
	function getAllCateExpense($search=null){
		$db = $this->getAdapter();
		$sql=" SELECT 
					ac.id,
					ac.account_name,
					ac.account_code,
					(select first_name from rms_users where rms_users.id = ac.user_id) as user,
					date,
					ac.status
				FROM 
					rms_account_name as ac 
				where 
					account_type=5
					and account_name!=''";
		$where = " ";
		if (!empty($search['adv_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['adv_search']));
			$s_where[] = " category_name LIKE '%{$s_search}%'";
			$s_where[] = " account_code LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['status']>-1){
			$where.= " AND status = ".$search['status'];
		}
        $order=" order by id desc ";
		return $db->fetchAll($sql.$where.$order);
	}
	
	public function getParentCateExpense($cate_id='',$parent = 0, $spacing = '', $cate_tree_array = ''){
		$db=$this->getAdapter();
		if (!is_array($cate_tree_array)){$cate_tree_array = array();}
		$sql = " SELECT id , title as name from st_cate_expense where status=1 AND `parent` = $parent ";
		if (!empty($cate_id)){
			$sql.=" AND id != $cate_id";
		}
		$query = $db->fetchAll($sql);
		$rowCount = count($query);
	
		$id='';
		if ($rowCount > 0) {
			foreach ($query as $row){
				$cate_tree_array[] = array("id" => $row['id'], "name" => $spacing . $row['name']);
				$cate_tree_array = $this->getParentCateExpense($cate_id,$row['id'], $spacing . ' - ', $cate_tree_array);
			}
		}
		return $cate_tree_array;
	}
}