<?php
class Accounting_Model_DbTable_DbChartaccount extends Zend_Db_Table_Abstract
{
	protected $_name = 'ln_account_name';
	function addchartaccount($data){
		$db = $this->getAdapter();
// 		print_r($data);exit();
		$option_type = 1;
		if($data['none']==2){
			if($data['parents']==1){
				$option_type=3;
			}else{
				$option_type=2;
			}
		}
	
		$data = array(
				'account_code'=>$data['account_No'],
				'account_name_kh'=>$data['account_Name'],
				'account_name_en'=>$data['account_Nameen'],
				'account_type'=>$data['account_Type'],
				'option_acc'=>$data['none'],
				'category_id'=>$data['category'],
				'parent_id'=>$data['parent'],
				'option_type'=>$option_type,
				'balance'=>$data['Balance'],
				'date'=>$data['date'],
				'status'=>$data['status'],
		);
		$this->insert($data);
        
}
function updatchartaccount($data){
	$arr = array(
			    'account_code'=>$data['account_No'],
				'account_name_kh'=>$data['account_Name'],
			    'account_name_en'=>$data['account_Nameen'],
				'account_type'=>$data['account_Type'],
				'option_acc'=>$data['none'],
				'category_id'=>$data['category'],
				'parent_id'=>$data['parent'],
				'option_type'=>$data['parents'],
				'balance'=>$data['Balance'],
				'date'=>$data['date'],
				'status'=>$data['status'],
				);
	$where=" id = ".$data['id'];
	$this->update($arr, $where);
}
function getechartaccountbyid($id){
	$db = $this->getAdapter();
	$sql=" SELECT id,account_code,account_name_kh,account_name_en,account_type,option_acc,
	category_id,parent_id,option_type,balance,date,status FROM $this->_name where id=$id ";
	return $db->fetchRow($sql);
}

function getAllchartaccount($type=null,$option=null){
	$db = $this->getAdapter();
	$where = '';
	if($type!=null){
		$where.=" AND option_type = $type";
	}
	$sql=" SELECT id,category_id,
	       parent_id,account_name_kh,account_name_en,status FROM $this->_name WHERE account_name_en!='' AND status = 1 ";
	
	$rows =  $db->fetchAll($sql.$where);
	if($option!=null){
		$opt = array(0=>'---Non Category---');
		if(!empty($rows))foreach ($rows as $rs){ 
			$opt[$rs['id']]=$rs['account_name_en'];
		}
		return $opt;
	}else{
		return $rows;
	}															
}
function getAllchartaccounts($search=null){
	$db = $this->getAdapter();
	$sql=" SELECT ac.id,(SELECT name_en FROM ln_view WHERE TYPE=8 AND key_code=account_type LIMIT 1) AS account_type, 
			(SELECT v.account_name_en FROM ln_account_name AS v WHERE v.id=ac.parent_id LIMIT 1) AS parent ,
			(SELECT v.account_name_en FROM ln_account_name AS v WHERE v.id=ac.category_id LIMIT 1) AS cate_name ,
			ac.account_code,ac.account_name_en,ac.account_name_kh,ac.status FROM $this->_name AS ac
			WHERE ac.option_type =1 AND ac.account_name_en!='' ";

		$where = '';
// 		print_r($search); exit();
		if($search['status']>-1){
			$where.= " AND status = ".$search['status'];
		}
		if(!empty($search['account_Type'])){
			$where.= " AND account_type = ".$search['account_Type'];
		}
		if(!empty($search['parent'])){
			$where.= " AND parent_id = ".$search['parent'];
		}
		if(!empty($search['category'])){
			$where.= " AND category_id = ".$search['category'];
		}
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = $search['adv_search'];
			$s_where[]="account_name_en LIKE '%{$s_search}%'";//no query
			$where.=' AND ('.implode(' OR ',$s_where).')';
		}

		return $db->fetchAll($sql.$where);
}
function getAllParentAccountByid($account_id){
	$db=$this->getAdapter();
	$sql="SELECT id,CONCAT(account_name_en,'-',account_name_kh) AS name FROM `ln_account_name` WHERE status =1 
			     AND option_type=3 AND account_type=$account_id";
	return $db->fetchAll($sql);
}
}
