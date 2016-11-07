<?php
class Report_Model_DbTable_Dbpersonal extends Zend_Db_Table_Abstract
{
      
      
//     public function getUserId(){
//     	$session_user=new Zend_Session_Namespace('auth');
//     	return $session_user->user_id;
//     }
    public function getPaymentSchedule($id){
    	$db=$this->getAdapter();
    	$sql = "SELECT * FROM `ln_loanmember_funddetail` WHERE member_id= $id";
    	return $db->fetchAll($sql);
    }
//     public function getAllClientPaymentListRpt(){
//     	$sql="SELECT member_id,client_id,total_capital,interest_rate,total_capital,
//     	loan_purpose,payment_method,currency_type,
//     	admin_fee,branch_id,status FROM `ln_loan_member`";
//     	$db = $this->getAdapter();
//     	return $db->fetchAll($sql);
//     }
	
}

