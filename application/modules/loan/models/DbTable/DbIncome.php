<?php
class Loan_Model_DbTable_DbIncome extends Zend_Db_Table_Abstract
{
	protected $_name = 'ln_income';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace('auth');
		return $session_user->user_id;
	
	}
	function addIncome($data){
		
		$invoice = $this->getInvoiceNo($data['branch_id']);
		
		$array = array(
			'branch_id'		=>$data['branch_id'],
			'client_id'		=>$data['customer'],
			'title'			=>$data['title'],
			'total_amount'	=>$data['total_amount'],
			'invoice'		=>$invoice,
			'category_id'	=>$data['income_category'],
			'cheque'		=>$data['cheque'],
			'description'	=>$data['Description'],
			'date'			=>$data['Date'],
			'status'		=>$data['Stutas'],
			'user_id'		=>$this->getUserId(),
			'create_date'	=>date('Y-m-d'),
		);
		$this->insert($array);
 }
	 function updateIncome($data,$id){
	 	
		$arr = array(
					'branch_id'=>$data['branch_id'],
					'client_id'=>$data['customer'],
					'title'=>$data['title'],
					'total_amount'=>$data['total_amount'],
					'invoice'=>$data['invoice'],
					'category_id'=>$data['income_category'],
					'cheque'=>$data['cheque'],
					'description'=>$data['Description'],
					'date'=>$data['Date'],
					'status'=>$data['Stutas'],
					'user_id'=>$this->getUserId(),
				);
		$where=" id =  $id " ;
		$this->update($arr, $where);
		
	}
	function getexpensebyid($id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM ln_income where id=$id ";
		return $db->fetchRow($sql);
	}
	
	function getAllIncome($search=null){
		$db = $this->getAdapter();
		$session_user=new Zend_Session_Namespace('auth');
		$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
		$where = " WHERE ".$from_date." AND ".$to_date;
		
		$sql=" SELECT id,
		(SELECT project_name FROM `ln_project` WHERE ln_project.br_id =branch_id LIMIT 1) AS branch_name,
		(SELECT name_en FROM `ln_client` WHERE ln_client.client_id =ln_income.client_id LIMIT 1) AS client_name,
		title, invoice,
		(SELECT name_en FROM `ln_view` WHERE type=12 and key_code=category_id limit 1) AS category_name,
		total_amount,description,date,status FROM ln_income ";
		
		if (!empty($search['adv_search'])){
				$s_where = array();
				$s_search = trim(addslashes($search['adv_search']));
				$s_where[] = " description LIKE '%{$s_search}%'";
				$s_where[] = " title LIKE '%{$s_search}%'";
				$s_where[] = " total_amount LIKE '%{$s_search}%'";
				$s_where[] = " invoice LIKE '%{$s_search}%'";
				$where .=' AND ('.implode(' OR ',$s_where).')';
			}
// 			if($search['status']>-1){
// 				$where.= " AND status = ".$search['status'];
// 			}
			if($search['branch_id']>-1){
				$where.= " AND branch_id = ".$search['branch_id'];
			}
	       $order=" order by id desc ";
			return $db->fetchAll($sql.$where.$order);
	}
	function getAllExpenseReport($search=null){
		$db = $this->getAdapter();
		$session_user=new Zend_Session_Namespace('auth');
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

	function getExchangeRate(){
		$db = $this->getAdapter();
		$sql="select * from rms_exchange_rate where active = 1 ";
		return $db->fetchRow($sql);
	}

	function getPrefixCodeByBranch($branch_id){
		
		$db = $this->getAdapter();
		$sql="select prefix from ln_project where status = 1 and br_id = $branch_id limit 1";
		return $db->fetchOne($sql);
		
	}
	
	function getInvoiceNo($branch_id){
		$db = $this->getAdapter();
		
		$prefix = $this->getPrefixCodeByBranch($branch_id);
		
		$sql = " select count(id) from ln_income where branch_id = $branch_id";
		$amount = $db->fetchOne($sql);
		$pre = '-Inc.';
		$result = $amount + 1;
		$length = strlen((int)$result);
		for($i = $length;$i < 3 ; $i++){
			$pre.='0';
		}
		return $prefix.$pre.$result;
	}

	function getAllIncomeCategory(){
		
		$db = $this->getAdapter();
		$sql = " select key_code as id,name_kh as name from ln_view where type=12 AND name_kh!='' ";
		return $db->fetchAll($sql);
		
	}
	
	function getNewKeyCode($type){
		$db = $this->getAdapter();
		$sql="SELECT key_code FROM ln_view WHERE TYPE = $type ORDER BY key_code DESC LIMIT 1";
		$result = $db->fetchOne($sql);
		$key_code = $result + 1;
		return $key_code;
	}
	
	function AddNewCategory($data,$type){ // type=1 => income , type=2 => expense
		$db = $this->getAdapter();
		if($type==1){
			$type=12;
		}else{
			$type=13;
		}
		$key_code = $this->getNewKeyCode($type);
		
		$this->_name = "ln_view" ;
		$array = array(
				'name_kh'	=>$data['cate_name'],
				'type'		=>$type,
				'key_code'	=>$key_code,
				'status'	=>$data['status_j'],
				);
		$this->insert($array);
		return $key_code; // to set key_code value to added new field
	}
	
	function getAllCustomer($branch_id){
		$db = $this->getAdapter();
		$sql="SELECT client_id as id,name_kh as name FROM ln_client WHERE status = 1 and branch_id = $branch_id";
		return $db->fetchAll($sql);
	}
	
	
}







