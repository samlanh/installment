<?php
class Incexp_Model_DbTable_DbExpense extends Zend_Db_Table_Abstract
{
	protected $_name = 'ln_expense';
	
	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}
	
	public function getBranchId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->branch_id;
	}
	
	function addExpense($data){
		$_db= $this->getAdapter();
		$_db->beginTransaction();
		try{
			$invoice = $this->getInvoiceNo($data['branch_id']);
			$data = array(
				'branch_id'		=> $data['branch_id'],
				'title'			=> $data['title'],
				'total_amount'	=> $data['total_amount'],
				'invoice'		=> $invoice,
				'cheque'		=> $data['cheque'],
				'cheque_issuer'	=> $data['cheque_issuer'],
				'other_invoice'	=> $data['other_invoice'],
	            'payment_id'	=> $data['payment_type'],
				'category_id'	=> $data['income_category'],
				'description'	=> $data['Description'],
				'date'			=> $data['Date'],
				'status'		=> 1,
				'supplier_id'	=> $data['supplier_id'],
				'user_id'		=> $this->getUserId(),
				'create_date'   => date('Y-m-d'),
			);
			$this->insert($data);
			$_db->commit();
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
 	}
 	function updatExpense($data){
 		$_db= $this->getAdapter();
 		$_db->beginTransaction();
 		try{
			$arr = array(
				'branch_id'		=> $data['branch_id'],
				'title'			=> $data['title'],
				'total_amount'	=> $data['total_amount'],
				'payment_id'	=> $data['payment_type'],
				'cheque'		=> $data['cheque'],
				'cheque_issuer'	=> $data['cheque_issuer'],
				'other_invoice'	=> $data['other_invoice'],
				'category_id'	=> $data['income_category'],
				'description'	=> $data['Description'],
				'date'			=> $data['Date'],
				'status'		=> $data['Stutas'],
				'supplier_id'	=> $data['supplier_id'],
				'user_id'		=> $this->getUserId(),	
			);
			$where=" id = ".$data['id'];
			$this->update($arr, $where);
			$_db->commit();
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	
	function getexpensebyid($id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM ln_expense where id=$id ";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission("branch_id");
		return $db->fetchRow($sql);
	}

	function getAllExpense($search=null){
		$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
		
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
		$where = " WHERE ".$from_date." AND ".$to_date;
		
		$sql=" SELECT id,
		(SELECT project_name FROM `ln_project` WHERE ln_project.br_id =branch_id LIMIT 1) AS branch_name,
		(SELECT sup.name FROM `ln_supplier` AS sup WHERE sup.id = supplier_id LIMIT 1) AS supplier,
		title,invoice,
		(SELECT name_kh FROM `ln_view` WHERE type=26 and key_code=payment_id limit 1) AS payment_type,
		(SELECT name_kh FROM `ln_view` WHERE type=13 and key_code=category_id limit 1) AS category_name,
		total_amount,description,date,cheque_issuer,
		(SELECT  first_name FROM rms_users WHERE id=user_id limit 1 ) AS user_name  ";
		
		$sql.=$dbp->caseStatusShowImage("status");
		$sql.=" FROM ln_expense ";
		
		if (!empty($search['adv_search'])){
				$s_where = array();
				$s_search = trim(addslashes($search['adv_search']));
				$s_where[] = " description LIKE '%{$s_search}%'";
				$s_where[] = " title LIKE '%{$s_search}%'";
				$s_where[] = " total_amount LIKE '%{$s_search}%'";
				$s_where[] = " invoice LIKE '%{$s_search}%'";
				$s_where[] = " other_invoice LIKE '%{$s_search}%'";
				$where .=' AND ('.implode(' OR ',$s_where).')';
			}
	
		if($search['branch_id']>0){
			$where.= " AND branch_id = ".$search['branch_id'];
		}
// 		if($search['category_id_expense']>0){
// 			$where.= " AND category_id = ".$search['category_id_expense'];
// 		}
		if(!empty($search['category_id_expense'])){
			$condiction = $dbp->getChildType($search['category_id_expense']);
			if (!empty($condiction)){
				$where.=" AND category_id IN ($condiction)";
			}else{
				$where.=" AND category_id=".$search['category_id_expense'];
			}
		}
		if(!empty($search['supplier_id'])){
			$where.= " AND supplier_id = ".$search['supplier_id'];
		}
		if($search['payment_type']>0){
			$where.= " AND payment_id = ".$search['payment_type'];
		}
		if (!empty($search['cheque_issuer_search'])){
			$where.= " AND cheque_issuer = '".$search['cheque_issuer_search']."'";
		}
		$where.=$dbp->getAccessPermission("branch_id");
       $order=" order by id desc ";
	       
			return $db->fetchAll($sql.$where.$order);
	}
	
	function getAllExpenseReport($search=null){
		
		$db = $this->getAdapter();
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
		$where = " WHERE ".$from_date." AND ".$to_date;
	
		$sql=" SELECT id,
		(SELECT branch_namekh FROM `rms_branch` WHERE rms_branch.br_id =branch_id LIMIT 1) AS branch_name,
		account_id,
		(SELECT symbol FROM `ln_currency` WHERE ln_currency.id =curr_type) AS currency_type,invoice,
		curr_type,
		total_amount,disc,date,status FROM $this->_name ";
	
		if (!empty($search['adv_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['adv_search']));
			$s_where[] = " account_id LIKE '%{$s_search}%'";
			$s_where[] = " title LIKE '%{$s_search}%'";
			$s_where[] = " total_amount LIKE '%{$s_search}%'";
			$s_where[] = " invoice LIKE '%{$s_search}%'";
			
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['status']>-1){
			$where.= " AND status = ".$search['status'];
		}
		if($search['currency_type']>-1){
			$where.= " AND curr_type = ".$search['currency_type'];
		}
		$order=" order by id desc ";
		return $db->fetchAll($sql.$where.$order);
	}
	
	function getAllExpenseCategory($parent = 0, $spacing = '', $cate_tree_array = ''){
		if (!is_array($cate_tree_array))
			$cate_tree_array = array();
		$db = $this->getAdapter();
		$sql = " select key_code as id,name_kh as name from ln_view where type=13 AND name_kh!='' AND `parent_id` = $parent ";
		$query =  $db->fetchAll($sql);
		
		$rowCount = count($query);
		if ($rowCount > 0) {
			foreach ($query as $row){
				$cate_tree_array[] = array("id" => $row['id'], "name" => $spacing . $row['name']);
				$cate_tree_array = $this->getAllExpenseCategory($row['id'], $spacing . ' - ', $cate_tree_array);
			}
		}
		return $cate_tree_array;
	}
	
	function getPrefixCodeByBranch($branch_id){
	
		$db = $this->getAdapter();
		$sql="select prefix from ln_project where status = 1 and br_id = $branch_id limit 1";
		return $db->fetchOne($sql);
	
	}
	
	function getInvoiceNo($branch_id){
		$db = $this->getAdapter();
		
		$prefix = $this->getPrefixCodeByBranch($branch_id);
		
		$sql = " select count(id) from ln_expense where branch_id = $branch_id";
		$amount = $db->fetchOne($sql);
		
		$sql1 = " select count(id) from ln_comission where branch_id = $branch_id";
		$amount1 = $db->fetchOne($sql1);
		
		$sql3 = " select count(id) from ln_otherincomepayment where branch_id = $branch_id AND cate_type=13 ";
		$amount2 = $db->fetchOne($sql3);
		
		$amount = $amount+$amount1+$amount2;
		$pre = 'inc1:';
		$result = $amount + 1;
		$length = strlen((int)$result);
		for($i = $length;$i < 3 ; $i++){
			$pre.='0';
		}
		return $pre.$result;
	}
	function getAllChequeIssue(){
		$db = $this->getAdapter();
		$sql = " SELECT DISTINCT cheque_issuer as name,cheque_issuer as id FROM `ln_expense` WHERE cheque_issuer!='' ORDER BY cheque_issuer ASC ";
		return $db->fetchAll($sql);
	}
}