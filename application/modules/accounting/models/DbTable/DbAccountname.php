<?php
class Accounting_Model_DbTable_DbAccountname extends Zend_Db_Table_Abstract
{
	protected $_name = 'ln_account_name';
	function addaccountname($data){
		$data = array(
				'account_code'=>$data['account_id'],
				'account_name_kh'=>$data['account_namekh'],
				'account_name_en'=>$data['account_nameeg'],
				'displayby'=>$data['dispay_by'],
				'disc'=>$data['description'],
				'option_acc'=>$data['optionacc'],
				'account_type'=>$data['account_type'],
				'parent_id'=>$data['parent_acc'],
				'category_id'=>$data['categories'],
				'date'=>$data['date'],
				'status'=>$data['status'],
		);
		$this->insert($data);

}
function updateaccountname($data){
	$arr = array(
			'account_code'=>$data['account_id'],
			'account_name_kh'=>$data['account_namekh'],
			'account_name_en'=>$data['account_nameeg'],
			'displayby'=>$data['dispay_by'],
			'disc'=>$data['description'],
			'option_acc'=>$data['optionacc'],
			'account_type'=>$data['account_type'],
			'parent_id'=>$data['parent_acc'],
			'category_id'=>$data['categories'],
			'date'=>$data['date'],
			'status'=>$data['status'],
				
		);
	$where=" id = ".$data['id'];
	$this->update($arr, $where);
}
function geteaccountnamebyid($id){
	$db = $this->getAdapter();
	$sql=" SELECT id,account_code,account_name_kh,account_name_en,displayby,disc,
	option_acc,account_type,parent_id,category_id,date,status FROM $this->_name where id=$id ";
	return $db->fetchRow($sql);
}

function getAllaccountname($search=null){
	$db = $this->getAdapter();
	$sql=" SELECT id,account_code,account_name_kh,account_name_en
	,(SELECT v.name_en FROM ln_view AS v WHERE v.type = 4 AND v.key_code = displayby limit 1) AS disby
	,disc
	,(SELECT v.name_en FROM ln_view AS v WHERE v.type = 10 AND v.key_code = option_acc limit 1) AS option_acc
	,(SELECT v.name_en FROM ln_view AS v WHERE v.type = 8 AND v.key_code = account_type) AS account_type
	,(SELECT cate_nameen FROM ln_account_category WHERE id=category_id) AS category_name
	,parent_id
	,date,status FROM $this->_name  ";
// 	echo $sql;exit();
	return $db->fetchAll($sql);
}



}