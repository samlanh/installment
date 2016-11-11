<?php
class Report_Model_DbTable_DbRptPaymentSchedule extends Zend_Db_Table_Abstract
{

  
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('auth');
    	return $session_user->user_id;
    }
    public function getPaymentSchedule($id){
    	$db=$this->getAdapter();
    	$sql = "SELECT * FROM `ln_saleschedule` WHERE sale_id= $id AND status=1";
    	return $db->fetchAll($sql);
    }
    public function getPaymentScheduleGroupById($id){//for group member total pay per month
    	$db=$this->getAdapter();
    	$sql = "SELECT f.*,
    	SUM(total_principal) AS total_principal,
    	SUM(total_interest) AS total_interest_permonth,SUM(f.principal_permonth) AS total_principal_permonth 
    	             ,SUM(total_payment) AS total_payment_permonth FROM `ln_loan_member` AS m,`ln_loanmember_funddetail` AS f
				   WHERE m.member_id = f.member_id AND m.group_id=$id AND f.status=1 
    			AND m.status=1 GROUP BY m.group_id ,f.date_payment";
    	return $db->fetchAll($sql);
    }
    public function getAllClientPaymentListRpt($search = null ){
    	$db = $this->getAdapter();
    	//$sql="select * FROM v_loanpaymentschedulelist";
    	$sql="SELECT m.member_id
    	,(SELECT `b`.`branch_namekh` FROM `ln_branch` AS b WHERE `b`.`br_id` =lg.`branch_id` LIMIT 1) AS `branch_namekh`
        ,`m`.`loan_number` ,`c`.`client_number`
  		,c.name_en ,m.total_capital,m.admin_fee
  		,m.interest_rate,
    	CONCAT( lg.total_duration,' ',(SELECT name_en FROM `ln_view` WHERE type = 14 AND key_code =lg.pay_term )),
    	(SELECT payment_nameen FROM `ln_payment_method` WHERE id = m.payment_method) AS payment_nameen,
    	lg.time_collect
    	,(SELECT `zone_name` FROM `ln_zone` WHERE zone_id = lg.zone_id) AS zone_name
    	,(SELECT co_khname FROM `ln_co` WHERE co_id = lg.co_id ) AS co_khname, m.status 
    	    FROM `ln_loan_member` AS m,`ln_loan_group` AS lg,`ln_client` AS c 
    		WHERE lg.g_id = m.group_id AND c.client_id = m.client_id ";
    	$Other =" ORDER BY member_id DESC ";
    	
    	return $db->fetchAll($sql.$Other); 
    }
	
}

