<?php
class Report_Model_DbTable_DbRptPaymentSchedule extends Zend_Db_Table_Abstract
{
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    public function getPaymentSchedule($id,$payment_id=null){
    	$db=$this->getAdapter();
    	$sql = "SELECT *,
    			paid_date as date_paid,
				received_date AS paid_date,
		    	(SELECT name_kh FROM ln_view WHERE type =29 AND key_code = ln_saleschedule.ispay_bank LIMIT 1) AS payment_type,
				(SELECT  first_name FROM rms_users WHERE id = ln_saleschedule.received_userid LIMIT 1 ) AS received_by
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
    	 
    	if($payment_id==4 OR $payment_id==7){
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
  
  function checkCoutingScheduleCompetedRecord($_data){
	  $db=$this->getAdapter();
	  $saleId = empty($_data["saleId"]) ?  0 : $_data["saleId"];
	  $sql="
		SELECT 
			COUNT(sch.`id`) AS completedRecord
		FROM `ln_saleschedule` AS sch
		WHERE sch.`sale_id` = $saleId 
			AND sch.`status` = 1
			AND sch.`is_completed` =1
	  ";
	  return $db->fetchOne($sql);
  }
}

