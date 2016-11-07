<?php
class Accounting_Model_DbTable_DbIncome extends Zend_Db_Table_Abstract
{
	protected $_name = 'ln_income_expense';
	function addasset($data){
		$data = array(
				'branch_id'=>$data['branch_id'],
				'account_id'=>$data['account_id'],
				'total_amount'=>$data['total_amount'],
				'fordate'=>$data['for_date'],
				'disc'=>$data['Description'],
				'date'=>$data['Date'],
				'status'=>$data['Stutas'],
				'tran_type'=>2,
				
		);
		$this->insert($data);

}
function updatasset($data){
	$arr = array(
				'branch_id'=>$data['branch_id'],
				'account_id'=>$data['account_id'],
				'total_amount'=>$data['total_amount'],
				'fordate'=>$data['for_date'],
				'disc'=>$data['Description'],
				'status'=>$data['Stutas'],
			  
			   
				
		);
	$where=" id = ".$data['id'];
	//     	echo $where;exit();
	$this->update($arr, $where);
}
function getassetbyid($id){
	$db = $this->getAdapter();
	$sql=" SELECT id,branch_id,account_id,total_amount,fordate,disc,date,status FROM $this->_name where id=$id ";
	return $db->fetchRow($sql);
}
function getAllasset($search=null){
	$db = $this->getAdapter();
	$sql=" SELECT id,branch_id,account_id,total_amount,fordate,disc,date,status FROM $this->_name ";
	return $db->fetchAll($sql);
}




}