<?php
class Report_Model_DbTable_DbStaffSalary extends Zend_Db_Table_Abstract
{
      
       protected  $db_name='ln_client';
//     public function getUserId(){
//     	$session_user=new Zend_Session_Namespace('auth');
//     	return $session_user->user_id;
//     }
    public function getAllLnClient(){
    	 $db = $this->getAdapter();
          $sql="SELECT client_id,client_number,name_kh,name_en,sex,village_id,street,house,phone,remark FROM ln_client ORDER BY client_id";
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

