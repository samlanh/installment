<?php
class Loan_Model_DbTable_DbIncome extends Zend_Db_Table_Abstract
{
	protected $_name = 'ln_income';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace('auth');
		return $session_user->user_id;
	
	}
	function addIncome($data){
// 		if($data['currency_type']==1){
// 			$amount_in_reil = 0 ;
// 			$amount_in_dollar = $data['total_amount'];
// 		}else{
// 			$amount_in_reil = $data['total_amount'] ;
// 			$amount_in_dollar = $data['convert_to_dollar'];
// 		}
		$array = array(
					'branch_id'=>$data['branch_id'],
					'title'=>$data['title'],
					'total_amount'=>$data['total_amount'],
					'invoice'=>$data['invoice'],
					'category_id'=>$data['category_id'],
					'description'=>$data['Description'],
					'date'=>$data['Date'],
					'status'=>$data['Stutas'],
					'user_id'=>$this->getUserId(),
					'create_date'=>date('Y-m-d'),
				);
		$this->insert($array);
 }
	 function updateIncome($data){
	 	
	//  	if($data['currency_type']==1){
	//  		$amount_in_reil = 0 ;
	//  		$amount_in_dollar = $data['total_amount'];
	//  	}else{
	//  		$amount_in_reil = $data['total_amount'] ;
	//  		$amount_in_dollar = $data['convert_to_dollar'];
	//  	}
	 	
		$arr = array(
					'branch_id'=>$data['branch_id'],
					'title'=>$data['title'],
					'total_amount'=>$data['total_amount'],
					'invoice'=>$data['invoice'],
					'category_id'=>$data['category_id'],
					'description'=>$data['Description'],
					'date'=>$data['Date'],
					'status'=>$data['Stutas'],
					'user_id'=>$this->getUserId()
				);
		$where=" id = ".$data['id'];
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

	function getInvoiceNo(){
		$db = $this->getAdapter();
		$sql = " select count(id) from ln_income ";
		$amount = $db->fetchOne($sql);
		
		
		//return 
	}

	
	
	
	
	
	
	
}







