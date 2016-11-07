<?php
class Accounting_Model_DbTable_DbGeneraljurnal extends Zend_Db_Table_Abstract
{
	protected $_name = 'ln_accountname_detail';
	function getAllJurnalEntry($search=null){
		$this->_name=`ln_journal`;
		$db = $this->getAdapter();
		$sql="SELECT j.id,
		(SELECT branch_namekh FROM `ln_branch` WHERE br_id =j.branch_id LIMIT 1) AS branch_name
		,j.journal_code,j.receipt_number,
		CONCAT((SELECT symbol FROM `ln_currency` WHERE id =j.currency_id),j.debit) AS debit,
		CONCAT((SELECT symbol FROM `ln_currency` WHERE id =j.currency_id),j.credit) AS credit,
		(SELECT name_en FROM `ln_view` WHERE TYPE=3 AND key_code=j.status LIMIT 1) status,
        (SELECT  CONCAT(first_name,' ', last_name) FROM rms_users WHERE id=j.user_id )AS user_name
		FROM ln_journal AS j WHERE j.journal_code!=''";
		
	
		$where = '';
		if($search['status']>-1){
		$where.= " AND j.status = ".$search['status'];
		}
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[]="debit LIKE '%{$s_search}%'";//no query
			$s_where[]="receipt_number LIKE '%{$s_search}%'";//no query
			$s_where[]="credit LIKE '%{$s_search}%'";//no query
			$s_where[]="journal_code LIKE '%{$s_search}%'";//no query
			$where.=' AND ('.implode(' OR ',$s_where).')';
		}	
		$order=" ORDER BY j.id DESC ";
		return $db->fetchAll($sql.$where.$order);
	}
	function addjurnal($data){
		$data = array(
				'branch_id'=>$data['account_id'],
				'date'=>$data['account_namekh'],
				'account_id'=>$data['account_nameeg'],
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
   function getjurnalEntryById($id){
  	    $db = $this->getAdapter();
   		$sql="SELECT * FROM ln_journal WHERE id=$id LIMIT 1";
   		return $db->fetchRow($sql);
   }
   function getjurnalEntryDetail($id){
   	$db = $this->getAdapter();
   	$sql="SELECT * FROM ln_journal_detail WHERE jur_id=$id ";
   	return $db->fetchAll($sql);
   }
function updateaccountname($data){
	$arr = array(
			
				
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
	return $db->fetchAll($sql);
}



}