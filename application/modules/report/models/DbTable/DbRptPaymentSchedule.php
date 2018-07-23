<?php
class Report_Model_DbTable_DbRptPaymentSchedule extends Zend_Db_Table_Abstract
{

  
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authinstall');
    	return $session_user->user_id;
    }
    public function getPaymentSchedule($id,$payment_id=null){
    	$db=$this->getAdapter();
    	$sql = "SELECT *,
		(SELECT (paid_date) FROM `ln_client_receipt_money_detail` WHERE lfd_id=ln_saleschedule.id limit 1) as paid_date,
		(SELECT SUM(total_recieve) FROM `ln_client_receipt_money_detail` WHERE lfd_id=ln_saleschedule.id limit 1) as total_recieve,
		(SELECT SUM(total_interest) FROM `ln_client_receipt_money_detail` WHERE lfd_id=ln_saleschedule.id limit 1) as total_interestpaid,
		(SELECT SUM(principal_permonth) FROM `ln_client_receipt_money_detail` WHERE lfd_id=ln_saleschedule.id limit 1) as principal_paid
    	FROM `ln_saleschedule` 
    		WHERE sale_id= $id AND (status=1 OR (status=0 AND collect_by=2)) ";
    	
    	if($payment_id==4){
    		$sql.=" AND is_installment=1 ";
    	};
    	$sql.=" ORDER BY no_installment ASC,date_payment ASC, collect_by ASC, status DESC ";
    	return $db->fetchAll($sql);
    }
    public function getPaymentupdateSchedule($id,$payment_id=null){//យកតែតារាងពិតមិនយក Record បង់បន្ថែមទេ
    	$db=$this->getAdapter();
    	$sql = "SELECT *,
    	(SELECT (paid_date) FROM `ln_client_receipt_money_detail` WHERE lfd_id=ln_saleschedule.id limit 1) as paid_date,
    	(SELECT SUM(total_recieve) FROM `ln_client_receipt_money_detail` WHERE lfd_id=ln_saleschedule.id limit 1) as total_recieve,
    	(SELECT SUM(total_interest) FROM `ln_client_receipt_money_detail` WHERE lfd_id=ln_saleschedule.id limit 1) as total_interestpaid,
    	(SELECT SUM(principal_permonth) FROM `ln_client_receipt_money_detail` WHERE lfd_id=ln_saleschedule.id limit 1) as principal_paid
    	FROM `ln_saleschedule`
    	WHERE sale_id= $id AND status=1 ";
    	 
    	if($payment_id==4){
    		$sql.=" AND is_installment=1 ";
    	};
    	$sql.=" ORDER BY date_payment ASC,no_installment ASC, collect_by ASC, status DESC ";
    	return $db->fetchAll($sql);
    }
    public function getPaymentScheduleById($id){
    	$db=$this->getAdapter();
    	$sql = "SELECT *,
    	(SELECT (paid_date) FROM `ln_client_receipt_money_detail` WHERE lfd_id=ln_saleschedule.id limit 1) as paid_date,
    	(SELECT SUM(total_recieve) FROM `ln_client_receipt_money_detail` WHERE lfd_id=ln_saleschedule.id limit 1) as total_recieve,
    	(SELECT SUM(total_interest) FROM `ln_client_receipt_money_detail` WHERE lfd_id=ln_saleschedule.id limit 1) as total_interestpaid,
    	(SELECT SUM(principal_permonth) FROM `ln_client_receipt_money_detail` WHERE lfd_id=ln_saleschedule.id limit 1) as principal_paid
    	FROM `ln_saleschedule` WHERE sale_id= $id AND status=1 ORDER BY date_payment ASC ";
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
    	,(SELECT co_khname FROM `ln_staff` WHERE co_id = lg.co_id ) AS co_khname, m.status 
    	    FROM `ln_loan_member` AS m,`ln_loan_group` AS lg,`ln_client` AS c 
    		WHERE lg.g_id = m.group_id AND c.client_id = m.client_id ";
    	$Other =" ORDER BY member_id DESC ";
    	
    	return $db->fetchAll($sql.$Other); 
    }
	
}

