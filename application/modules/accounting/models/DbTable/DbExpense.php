<?php
class Accounting_Model_DbTable_DbExpense extends Zend_Db_Table_Abstract
{
	protected $_name = 'ln_income_expense';
	function addexpense($data){
		$data = array(
				'invoice'=>$data['invoice'],
				'account_id'=>$data['account_id'],
				'total_amount'=>$data['total_amount'],
				'fordate'=>$data['Date'],
				'tran_type'=>1,
				'disc'=>$data['Description'],
				'date'=>$data['Date'],
				'status'=>$data['Stutas'],
		);
		$this->insert($data);

}
function updatexpense($data){
	$arr = array(
				'invoice'=>$data['invoice'],
				'account_id'=>$data['account_id'],
				'total_amount'=>$data['total_amount'],
				'fordate'=>$data['Date'],
				'disc'=>$data['Description'],
				'status'=>$data['Stutas'],
			   
				
		);
	$where=" id = ".$data['id'];
	$this->update($arr, $where);
}
function getexpensebyid($id){
	$db = $this->getAdapter();
	$sql=" SELECT id,branch_id,account_id,total_amount,fordate,disc,date,status FROM $this->_name where id=$id ";
	return $db->fetchRow($sql);
}

function getAllExpense($search=null){
	$db = $this->getAdapter();
	$sql=" SELECT id,account_id,total_amount,fordate,disc,date,status FROM $this->_name ";
	return $db->fetchAll($sql);
}
function getAllExpenseReport($search=null){
	$db = $this->getAdapter();
	$session_user=new Zend_Session_Namespace('auth');
	$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
	$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
	$where = " WHERE ".$from_date." AND ".$to_date;

	$sql=" SELECT id,
	account_id,invoice,
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
	$order=" order by id desc ";
	return $db->fetchAll($sql.$where.$order);
}



}