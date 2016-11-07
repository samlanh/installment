<?php
class Accounting_Model_DbTable_DbAccountcate extends Zend_Db_Table_Abstract
{
	protected $_name = 'ln_account_category';
	function addaccountcate($data){
		$data = array(
				'cate_namekh'=>$data['categoryname_kh'],
				'cate_nameen'=>$data['categoryname_eng'],
				'parent_id'=>$data['parent'],
				'account_type'=>$data['type'],
				'date'=>$data['date'],
				'deplay'=>$data['display'],
				'status'=>$data['status'],
		);
		$this->insert($data);

}
function updataccountcate($data){
	$arr = array(
			'cate_namekh'=>$data['categoryname_kh'],
			'cate_nameen'=>$data['categoryname_eng'],
			'parent_id'=>$data['parent'],
			'account_type'=>$data['type'],
			'date'=>$data['date'],
			'deplay'=>$data['display'],
			'status'=>$data['status'],
				
				
		);
	$where=" id = ".$data['id'];
	$this->update($arr, $where);
}
function geteaccountcatebyid($id){
	$db = $this->getAdapter();
	$sql=" SELECT id,cate_namekh,cate_nameen,parent_id,account_type,
	date,deplay,status FROM $this->_name where id=$id ";
	return $db->fetchRow($sql);
}

function getAllaccountcate($search=null){
	$db = $this->getAdapter();
	$sql=" SELECT id,cate_namekh,cate_nameen,parent_id,(SELECT name_en FROM ln_view WHERE TYPE=8 AND key_code=account_type LIMIT 1)As type,
	date,(SELECT name_en FROM ln_view WHERE TYPE=18 AND key_code=deplay LIMIT 1)AS deplay,status FROM $this->_name ";

	return $db->fetchAll($sql);
}



}