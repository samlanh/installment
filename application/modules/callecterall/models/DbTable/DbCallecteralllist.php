<?php

class Callecterall_Model_DbTable_DbCallecteralllist extends Zend_Db_Table_Abstract
{
    protected $_name = 'ln_callecteralllist';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    }
    function addcallecteralllist($data){
    	try {
    	$arr = array(
    			'branch'=>$data['branch'],
    			'receipt'=>$data['receipt'],
    			'code_call'=>$data['code_call'],
    			'customer_id'=>$data['customer_name'],
    			'type_call'=>$data['callecterall_type'],
    			'owner_call'=>$data['nameouner'],
    			'callnumber'=>$data['callnumber'],
    			'create_date'=>date('Y-m-d'),
    			'date_debt'=>$data['date_call'],
    			'term'=>$data['time_think'],
    			'amount_term'=>$data['time_boro'],
    			'date_line'=>$data['dayless'],
    			'curr_type'=>$data['cash_type'],
    			'amount_debt'=>$data['much_boro'],
    			'note'=>$data['note'],
    			'term_fun'=>$data['term_fun'],
    			'charge_term'=>$data['charge_term'],
    			'amount_money'=>$data['amount_money'],
    			'user_id'  => $this->getUserId(),
    		);    	
         $id=$this->insert($arr);
    	}catch (Exception $e){
    		echo $e->getMessage();
    	}
         
     
    }
    function updatcallecteralllist($data){
    	try {
    	$arr = array(
    			'branch'=>$data['branch'],
    			'receipt'=>$data['receipt'],
    			'code_call'=>$data['code_call'],
    			'customer_id'=>$data['customer_name'],
    			'type_call'=>$data['callecterall_type'],
    			'owner_call'=>$data['nameouner'],
    			'callnumber'=>$data['callnumber'],
    			'create_date'=>date('Y-m-d'),
    			'date_debt'=>$data['date_call'],
    			'term'=>$data['time_think'],
    			'amount_term'=>$data['time_boro'],
    			'date_line'=>$data['dayless'],
    			'curr_type'=>$data['cash_type'],
    			'amount_debt'=>$data['much_boro'],
    			'note'=>$data['note'],
    			'term_fun'=>$data['term_fun'],
    			'charge_term'=>$data['charge_term'],
    			'amount_money'=>$data['amount_money'],
    		);
    	$where=" id = ".$data['id'];
    	$this->update($arr, $where);
    	}catch (Exception $e){
    		echo $e->getMessage();
    	}
    }
    function getcallecteralllistbyid($id){
    	$db = $this->getAdapter();
    	$sql=" SELECT id,branch,receipt,code_call,customer_id,type_call,owner_call,
    	       callnumber,create_date,date_debt,term,amount_term,date_line,curr_type,amount_debt,note,term_fun,charge_term,amount_money FROM $this->_name where id=$id ";
    	return $db->fetchRow($sql);
    }
    
    function getcallecteralllistAllid($search=null){
    	$db = $this->getAdapter();
    	$sql=" SELECT id,(SELECT branch_namekh FROM ln_branch WHERE br_id = branch limit 1)as branch
    	       ,receipt,code_call,(SELECT name_kh FROM ln_client WHERE client_id=customer_id limit 1) AS customer_name,
    	       date_debt,(SELECT name_en FROM ln_view WHERE type=14 AND key_code=term limit 1) AS term,amount_term,date_line,
    	       (SELECT name_en FROM ln_view WHERE type=15 AND key_code=curr_type Limit 1) AS curr_type,amount_debt,note FROM $this->_name" ;
    	return $db->fetchAll($sql);
    	
    }
    function checkisFun($id){
    	$db = $this->getAdapter();
    	$sql=" SELECT id,`is_verify`,`verify_by`,`is_fund` FROM $this->_name WHERE id = $id";
    	return $db->fetchRow($sql);
    }
}

