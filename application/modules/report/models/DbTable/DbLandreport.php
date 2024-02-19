<?php
class Report_Model_DbTable_DbLandreport extends Zend_Db_Table_Abstract
{
	public function getAllLoanReport($search = null){//rpt-loan-released/
		$db = $this->getAdapter();
		$session_lang=new Zend_Session_Namespace('lang');
		$lang = $session_lang->lang_id;
		$str = 'name_en';
		if($lang==1){
			$str = 'name_kh';
		}
		$from_date_receipt =(empty($search['start_date']))? '1': " date_pay >= '".$search['start_date']." 00:00:00'";
		$to_date_receipt = (empty($search['end_date']))? '1': " date_pay <= '".$search['end_date']." 23:59:59'";
		
		$from_dateCredit =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
		$to_dateCredit = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$statement = $dbp->soldreportSqlStatement();
		$sql= $statement['sql'];
		$sql.="
			,(SELECT SUM(total_principal_permonthpaid+extra_payment) FROM `ln_client_receipt_money` WHERE sale_id=s.id AND s.status=1 AND $from_date_receipt AND $to_date_receipt LIMIT 1) AS paid_amount,
			(SELECT SUM(total_interest_permonthpaid) FROM `ln_client_receipt_money` WHERE status=1 AND $from_date_receipt AND $to_date_receipt  AND sale_id = s.id LIMIT 1) AS total_interest_permonthpaid,
			(SELECT SUM(penalize_amountpaid) FROM `ln_client_receipt_money` WHERE status=1 AND $from_date_receipt AND $to_date_receipt  AND sale_id = s.id LIMIT 1) AS penalize_amountpaid,
			(SELECT SUM(total_amount) FROM `ln_credit` WHERE status=1 AND $from_dateCredit AND $to_dateCredit  AND sale_id = s.id LIMIT 1) AS totalAmountCreadit,
			(SELECT COUNT(id) FROM `ln_saleschedule` WHERE sale_id=s.id AND status=1 ) AS times,
			(SELECT first_name FROM `rms_users` WHERE id=s.user_id LIMIT 1) AS user_name,
			(SELECT $str FROM `ln_view` WHERE key_code =s.payment_id AND type = 25 limit 1) AS paymenttype,
			(SELECT p.old_land_id FROM `ln_properties` AS p WHERE p.id = s.house_id LIMIT 1) AS old_land_id
		";
		$where = $statement['where'];
// 		$where.=" AND s.is_cancel=0 ";
		$where.=$dbp->getAccessPermission("s.`branch_id`");
		$str = '`s`.`buy_date`';
		if($search['buy_type']>0 AND $search['buy_type']!=2){
			$str = ' `s`.`agreement_date` ';
		}
		if($search['buy_type']==2){
			$where.=" AND s.payment_id = 1";
		}
			if($search['buy_type']==1){
			$where.=" AND s.payment_id != 1";
		}
		$from_date =(empty($search['start_date']))? '1': " $str >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " $str <= '".$search['end_date']." 23:59:59'";
		$where.= " AND ".$from_date." AND ".$to_date;
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[] = " `p`.`land_code`  LIKE '%{$s_search}%'";
			$s_where[] = " `p`.`land_address` LIKE '%{$s_search}%'";
			$s_where[] = " `c`.`client_number`  LIKE '%{$s_search}%'";
			$s_where[] = " `c`.`name_en`  LIKE '%{$s_search}%'";
			$s_where[] = " `c`.`name_kh`  LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT
			`ln_staff`.`co_khname`
			FROM `ln_staff`
			WHERE (`ln_staff`.`co_id` = `s`.`staff_id`)
			LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[] = " `s`.`price_sold` LIKE '%{$s_search}%'";
			$s_where[] = " `s`.`comission` LIKE '%{$s_search}%'";
			$s_where[] = " `s`.`total_duration` LIKE '%{$s_search}%'";
			$s_where[] = " `p`.`street` LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if($search['branch_id']>0){
			$where.=" AND s.branch_id = ".$search['branch_id'];
		}
// 		if(!empty($search['streetlist']) AND $search['streetlist']>-1){
// 			$where.=" AND `p`.`street` = '".$search['streetlist']."'";
// 		}
		if(!empty($search['co_id']) AND $search['co_id']>-1){
// 			$where.=" AND `s`.`staff_id` = ".$search['co_id'];
			$condiction = $dbp->getChildAgency($search['co_id']);
			if (!empty($condiction)){
				$where.=" AND s.staff_id IN ($condiction)";
			}else{
				$where.=" AND s.staff_id=".$search['co_id'];
			}
		}
		if($search['land_id']>0){
			$where.=" AND ( s.house_id = ".$search['land_id']." OR (SELECT p.old_land_id FROM `ln_properties` AS p WHERE p.id = s.house_id LIMIT 1) LIKE '%".$search['land_id']."%' )";
		}
		if($search['property_type']>0 AND $search['property_type']>0){
			$where.=" AND p.property_type = ".$search['property_type'];
		}
		if($search['client_name']!='' AND $search['client_name']>0){
			$where.=" AND `s`.`client_id` = ".$search['client_name'];
		}
		if($search['schedule_opt']>0){
			if ($search['schedule_opt']==2 OR $search['schedule_opt']==6){
				$where.=" AND s.payment_id IN (2,6) ";
			}else{
				$where.=" AND s.payment_id = ".$search['schedule_opt'];
			}
		}
		$search['sale_status'] = empty($search['sale_status'])?0:$search['sale_status'];
		if($search['sale_status']>0){
 			if($search['sale_status']==1){//full paid
				$where.=" AND s.price_sold <= ((SELECT COALESCE(SUM(total_principal_permonthpaid+extra_payment),0) FROM `ln_client_receipt_money` WHERE sale_id=s.id AND s.status=1 AND $from_date_receipt AND $to_date_receipt LIMIT 1) + (SELECT COALESCE(SUM(total_amount),0) FROM `ln_credit` WHERE status=1 AND $from_dateCredit AND $to_dateCredit  AND sale_id = s.id LIMIT 1) ) ";
 			}else if($search['sale_status']==2){
				$where.=" AND s.price_sold > ((SELECT COALESCE(SUM(total_principal_permonthpaid+extra_payment),0) FROM `ln_client_receipt_money` WHERE sale_id=s.id AND s.status=1 AND $from_date_receipt AND $to_date_receipt LIMIT 1) + (SELECT COALESCE(SUM(total_amount),0) FROM `ln_credit` WHERE status=1 AND $from_dateCredit AND $to_dateCredit  AND sale_id = s.id LIMIT 1) ) ";
 			}else if($search['sale_status']==3){
				$where.=" AND s.is_cancel = 0 ";
			}else if($search['sale_status']==4){
				$where.=" AND s.is_cancel = 1 ";
			}else{
			}
 		}
		$order = " ORDER BY s.is_cancel ASC,s.payment_id DESC ";
		if(!empty($search['queryOrdering'])){
			if($search['queryOrdering']==1){
				$order =" ORDER BY s.is_cancel ASC,s.payment_id DESC, `s`.buy_date ASC ";
			}else if($search['queryOrdering']==2){
				$order =" ORDER BY s.is_cancel ASC,s.payment_id DESC, `s`.buy_date DESC ";
			}else if($search['queryOrdering']==3){
				$order =" ORDER BY s.is_cancel ASC,s.payment_id DESC, `s`.id ASC ";
			}else if($search['queryOrdering']==4){
				$order =" ORDER BY s.is_cancel ASC,s.payment_id DESC, `s`.id DESC ";
			}
		}
		
		return $db->fetchAll($sql.$where.$order);
	
	}
		
	public function getValidationAgreement($search = null){//rpt-loan-released/
		$db = $this->getAdapter();
		$session_lang=new Zend_Session_Namespace('lang');
		$lang = $session_lang->lang_id;
		$str = 'name_en';
		if($lang==1){
			$str = 'name_kh';
		}
// 		$from_date =(empty($search['start_date']))? '1': " date_pay >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " date_pay <= '".$search['end_date']." 23:59:59'";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$statement = $dbp->soldreportSqlStatement();
		$sql= $statement['sql'];
		$sql.="
			,
			(SELECT COUNT(id) FROM `ln_saleschedule` WHERE sale_id=s.id LIMIT 1 ) AS times,
      	(SELECT first_name FROM `rms_users` WHERE id=s.user_id LIMIT 1) AS user_name,
      	(SELECT $str FROM `ln_view` WHERE key_code =s.payment_id AND type = 25 limit 1) AS paymenttype,
      	(SELECT p.old_land_id FROM `ln_properties` AS p WHERE p.id = s.house_id LIMIT 1) AS old_land_id
		";
		$where = $statement['where'];
		$where.=" AND s.payment_id=1 AND s.is_cancel=0 ";
		$where.=$dbp->getAccessPermission("s.`branch_id`");
		$to_date = (empty($search['end_date']))? '1': " s.end_line <= '".$search['end_date']." 23:59:59'";
		$where.= " AND ".$to_date;
		
		if(!empty($search['adv_search'])){
		$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[] = " s.receipt_no LIKE '%{$s_search}%'";
			$s_where[] = " `p`.`land_code`  LIKE '%{$s_search}%'";
			$s_where[] = " `p`.`land_address` LIKE '%{$s_search}%'";
			$s_where[] = " `c`.`client_number`  LIKE '%{$s_search}%'";
			$s_where[] = " `c`.`name_en`  LIKE '%{$s_search}%'";
			$s_where[] = " `c`.`name_kh`  LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT
			`ln_staff`.`co_khname`
			FROM `ln_staff`
			WHERE (`ln_staff`.`co_id` = `s`.`staff_id`)
			LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[] = " `s`.`price_sold` LIKE '%{$s_search}%'";
			$s_where[] = " `s`.`comission` LIKE '%{$s_search}%'";
			$s_where[] = " `s`.`total_duration` LIKE '%{$s_search}%'";
			$s_where[] = " `p`.`street` LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if($search['branch_id']>0){
			$where.=" AND s.branch_id = ".$search['branch_id'];
		}
		if(!empty($search['co_id']) AND $search['co_id']>-1){
			$where.=" AND `s`.`staff_id` = ".$search['co_id'];
		}
		if($search['land_id']>0){
			$where.=" AND ( s.house_id = ".$search['land_id']." OR (SELECT p.old_land_id FROM `ln_properties` AS p WHERE p.id = s.house_id LIMIT 1) LIKE '%".$search['land_id']."%' )";
		}
		if($search['property_type']>0 AND $search['property_type']>0){
				$where.=" AND p.property_type = ".$search['property_type'];
		}
		if($search['client_name']!='' AND $search['client_name']>0){
			$where.=" AND `s`.`client_id` = ".$search['client_name'];
		}
		$order = " ORDER BY s.id ASC,s.payment_id DESC ";
	
		return $db->fetchAll($sql.$where.$order);
	
	}

 function getAmountReceiveByLoanNumber($saleId,$condition){
 	$sql="CALL st_getSumPricipalPaid($saleId,$condition) ";
 	return $this->getAdapter()->fetchRow($sql);
 }     
public function getAllOutstadingLoan($search=null){
      	$db = $this->getAdapter();
      	$where="";
      	$to_date = (empty($search['end_date']))? '1': " date_release <= '".$search['end_date']." 23:59:59'";
      	$where.= "  AND ".$to_date;
      	$sql="SELECT *,
      		(SELECT (totalPrincipalPaid+totalCredit) FROM `v_getsaleprincipalpaid` vpaid WHERE vpaid.saleId=vs.id LIMIT 1) totalPricipalPaid,
			(SELECT vs.totalInterestBalance FROM  `v_getsuminterestbalance` vs WHERE vs.saleId =vs.id LIMIT 1) AS balance_interest,
			(SELECT p.old_land_id FROM `ln_properties` AS p WHERE p.id = vs.house_id LIMIT 1) AS old_land_id
      	FROM v_loanoutstanding vs WHERE 1 ";
      	
      	$dbp = new Application_Model_DbTable_DbGlobal();
      	$sql.=$dbp->getAccessPermission("branch_id");
      	
      	if($search['client_name']>0){
           		$where.=" AND client_id = ".$search['client_name'];
      	}
      	if($search['land_id']>0){
      		$where.=" AND house_id = ".$search['land_id'];
      	}
      	if($search['schedule_opt']>0){
      		$where.=" AND payment_id = ".$search['schedule_opt'];
      	}
      	if($search['branch_id']>0){
      		$where.=" AND branch_id = ".$search['branch_id'];
      	}
      	
      	if(!empty($search['adv_search'])){
      		$s_where = array();
      		$s_search = addslashes(trim($search['adv_search']));
      		$s_where[] = " land_address LIKE '%{$s_search}%'";
      		$s_where[] = " client_number LIKE '%{$s_search}%'";
      		$s_where[] = " client_kh LIKE '%{$s_search}%'";
      	   $where .=' AND ('.implode(' OR ',$s_where).')';
      	}
      	return $db->fetchAll($sql.$where);
}
      
      public function getALLLoanlate($search = null){
      	$db = $this->getAdapter();
		$sql=" SELECT 
				  c.`client_number`,
				  c.`name_kh`,
				  c.`phone`,
				  (SELECT project_name FROM `ln_project` WHERE br_id=s.branch_id LIMIT 1 ) As branch_name ,
				  `l`.`land_code`               AS `land_code`,
				  `l`.`land_address`            AS `land_address`,
				  `l`.`street`                  AS `street`,
				  `s`.`price_sold`              AS `price_sold`,
				  `s`.`end_line`                AS `end_line`,
				  `s`.`interest_rate`           AS `interest_rate`,
				  `s`.`payment_id`              AS `payment_id`,
				  `s`.`expect_income_note`      AS `expect_income_note`,
				  `s`.`id`                     AS `sale_id`,
				  `sd`.`id`                     AS `id`,
				  `sd`.`penelize`               AS `penelize`,
				  `sd`.`date_payment`           AS `date_payment`,
				  `sd`.`amount_day`             AS `amount_day`,
				  `sd`.`status`                 AS `status`,
				  `sd`.`is_completed`           AS `is_completed`,
				  SUM(`sd`.`principal_permonthafter`) AS `principal_permonthafter`,
				  SUM(`sd`.`total_interest_after`)   AS `total_interest_after`,
				  SUM(`sd`.`total_payment_after`)    AS `total_payment_after`,
				  `sd`.`service_charge`         AS `service_charge`,
				  sd.no_installment,
				  sd.last_optiontype,
				  sd.ispay_bank,
				  (SELECT ln_view.name_kh FROM ln_view WHERE ln_view.type =29 AND key_code = sd.ispay_bank LIMIT 1) AS payment_type,
					(SELECT cr.date_input FROM `ln_client_receipt_money` AS cr WHERE cr.sale_id = s.id AND cr.recieve_amount>0 ORDER BY cr.id DESC LIMIT 1) AS last_pay_date
				  FROM
				 `ln_sale` AS s,
				 `ln_saleschedule` AS sd,
				`ln_properties` AS l,
				 `ln_client` AS c 
				WHERE 
				  s.`id` = sd.`sale_id` 
				  AND l.id=s.house_id 
				  AND s.`status` = 1 
				  AND sd.`is_completed` = 0 
				  AND sd.`status` = 1 
				  AND sd.last_optiontype=1
				  AND `s`.`is_cancel` = 0
				  AND c.`client_id` = s.`client_id` 
				  AND sd.ispay_bank =0 ";
		
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission("s.branch_id");
		
      	$from_date =(empty($search['start_date']))? '1': " sd.date_payment >= '".$search['start_date']." 00:00:00'";
      	$to_date = (empty($search['end_date']))? '1': " sd.date_payment < '".$search['end_date']."'";
      	$where = " AND ".$from_date." AND ".$to_date;
      	
      	if(!empty($search['adv_search'])){
      		$s_where = array();
      		$s_search = trim(addslashes($search['adv_search']));
      		$s_where[] = " `l`.`land_code` LIKE '%{$s_search}%'";
      		$s_where[] = " `l`.`street` LIKE '%{$s_search}%'";
      		$s_where[] = " `l`.`land_code` LIKE '%{$s_search}%'";
      		$s_where[] = " s.sale_number LIKE '%{$s_search}%'";
      		$s_where[] = " c.`client_number` LIKE '%{$s_search}%'";
      		$s_where[] = " c.`name_kh`  LIKE '%{$s_search}%'";
      		$s_where[] = " c.`phone` LIKE '%{$s_search}%'";
      		$where .=' AND ('.implode(' OR ',$s_where).')';
      	}
      	if(($search['branch_id']>0)){
      		$where.=" AND s.branch_id =".$search['branch_id'];
      	}
      	if(!empty($search['co_id'])){
			$where.=" AND s.staff_id =".$search['co_id'];
		}
		if($search['client_name']>0){
			$where.=" AND s.`client_id` =".$search['client_name'];
		}
		
        $group_by = " Group by s.id order by sd.date_payment ASC ";
      	return $db->fetchAll($sql.$where.$group_by);
      }
      
      public function getALLLoanPayment($search=null,$order11=0){
      	//$search['is_closed']='';
      	$db = $this->getAdapter();
      	
      	
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql="SELECT
			  (SELECT
			     `ln_project`.`project_name`
			   FROM `ln_project`
			   WHERE (`ln_project`.`br_id` = `crm`.`branch_id`)
			   LIMIT 1) AS `branch_name`,
			   (SELECT
			     `ln_project`.`branch_code`
			   FROM `ln_project`
			   WHERE (`ln_project`.`br_id` = `crm`.`branch_id`)
			   LIMIT 1) AS `branchShortCode`,
				`c`.`client_id`                      AS `client_id`,
				`c`.`client_number`                  AS `client_number`,
				`c`.`name_kh`                        AS `name_kh`,
				`c`.`name_en`                        AS `client_name`,
				`crm`.`id`                           AS `id`,
				`crm`.`sale_id`                      AS `sale_id`,
				`crm`.`branch_id`                    AS `branch_id`,
				`crm`.`receipt_no`                   AS `receipt_no`,
				`crm`.`date_pay`                     AS `date_pay`,
				`crm`.`date_input`                   AS `date_input`,
				`crm`.`note`                         AS `note`,
				`crm`.`user_id`                      AS `user_id`,
				`crm`.`return_amount`                AS `return_amount`,
				`crm`.`status`                       AS `status`,
				`crm`.`payment_option`               AS `payment_option`,
				`crm`.`principal_amount`             AS `principal_amount`,
				`crm`.`is_payoff`                    AS `is_payoff`,
				`crm`.`total_principal_permonth`     AS `total_principal_permonth`,
				`crm`.`total_principal_permonthpaid` AS `total_principal_permonthpaid`,
				`crm`.`total_interest_permonth`      AS `total_interest_permonth`,
				`crm`.`total_interest_permonthpaid`  AS `total_interest_permonthpaid`,
				`crm`.`penalize_amount`              AS `penalize_amount`,
				`crm`.`penalize_amountpaid`          AS `penalize_amountpaid`,
				`crm`.`service_chargepaid`           AS `service_chargepaid`,
				`crm`.`service_charge`               AS `service_charge`,
				`crm`.`amount_payment`               AS `amount_payment`,
				`crm`.`total_payment`                AS `total_payment`,
				`crm`.`recieve_amount`               AS `amount_recieve`,
				`crm`.`penalize_amount`              AS `penelize`,
				`crm`.`service_charge`               AS `service`,
				`crm`.`extra_payment`                AS `extra_payment`,
				`crm`.`payment_times`                AS `payment_times`,
				`crm`.`field3`                       AS `field3`,
				`crm`.`is_closed`                    AS `is_closed`,
				`crm`.`closing_note`                 AS `closing_note`,
				`crm`.`bank_id`                   	 AS `bankId`,
				`sl`.`sale_number`                   AS `sale_number`,
				`sl`.`price_sold`                    AS `sold_price`,
				`sl`.`total_duration`                   AS `times`,
			  
			  
			  (SELECT `l`.`land_address` FROM `ln_properties` `l` WHERE `l`.`id` = `sl`.`house_id` LIMIT 1 ) AS land_address,
			  (SELECT `l`.`street` FROM `ln_properties` `l` WHERE `l`.`id` = `sl`.`house_id` LIMIT 1 ) AS street,
			  
			  (SELECT `l`.`land_code` FROM `ln_properties` `l` WHERE `l`.`id` = `sl`.`house_id` LIMIT 1 ) AS land_code,
			  (SELECT `l`.`land_size` FROM `ln_properties` `l` WHERE `l`.`id` = `sl`.`house_id` LIMIT 1 ) AS land_size,
			  (SELECT `l`.`id` FROM `ln_properties` `l` WHERE `l`.`id` = `sl`.`house_id` LIMIT 1 ) AS hous_id,
			 
			  `crm`.`payment_method`               AS `payment_methodid`,
			  `crm`.`payment_method`               AS `payment_id`,
			  (SELECT bank_name FROM `st_bank` WHERE id=`crm`.`bank_id` LIMIT 1) AS bank,
			  `crm`.`date_payment`                 AS `date_payment`,
			  
			  `crm`.`void_reason`           AS `void_reason`,
			  `crm`.`void_date`             AS `void_date`,
			  `crm`.`void_by`               AS `void_by`,
			  (SELECT u.first_name FROM `rms_users` AS u WHERE u.id = `crm`.`void_by` LIMIT 1) AS voidByUserName,
			  
			  (SELECT `v`.`name_kh` FROM `ln_view` AS v  WHERE `v`.`key_code` = `crm`.`payment_method` AND `v`.`type` = 2 LIMIT 1) AS `payment_method`,
			  (SELECT `v`.`name_en` FROM `ln_view` AS v  WHERE `v`.`key_code` = `crm`.`payment_option` AND `v`.`type` = 7 LIMIT 1) AS `paymentoption`,
			  (SELECT u.first_name FROM `rms_users` AS u WHERE u.id = crm.user_id LIMIT 1) AS user_name,
			  (SELECT `staf`.`co_khname`  FROM `ln_staff` AS staf WHERE `staf`.`co_id` = `sl`.`staff_id` LIMIT 1) AS `staff_name`
				
				,(SELECT doc.document_name FROM `ln_expense_document` AS doc WHERE doc.exspense_id = crm.id AND doc.documentforType = 3 LIMIT 1) AS documentFile
				,(SELECT doc.title FROM `ln_expense_document` AS doc WHERE doc.exspense_id = crm.id AND doc.documentforType = 3 LIMIT 1) AS documentTitle
				
			FROM ( 
					(`ln_client_receipt_money` `crm` JOIN `ln_sale` `sl`)
					JOIN `ln_client` `c` 
				) 
			WHERE ((`crm`.`client_id` = `c`.`client_id`)
			       AND (`sl`.`id` = `crm`.`sale_id`)
			      ) " ;
      	$sql.=" AND crm.status= 1 ";
			
      	$from_date =(empty($search['start_date']))? '1': " `crm`.`date_pay` >= '".$search['start_date']." 00:00:00'";
      	$to_date = (empty($search['end_date']))? '1': " `crm`.`date_pay` <= '".$search['end_date']." 23:59:59'";
      	$where = " AND ".$from_date." AND ".$to_date;
      	
      	$dbp = new Application_Model_DbTable_DbGlobal();
      	$where.=$dbp->getAccessPermission("`crm`.`branch_id`");
      	
      	if(!empty($search['user_id']) AND $search['user_id']>0){
      		$where.=" AND `crm`.`user_id` = ".$search['user_id'];
      	}
      	if($search['client_name']>0){
      		$where.=" AND `crm`.`client_id` = ".$search['client_name'];
      	} 
		if($search['branch_id']>0){
		        $where.=" AND `crm`.`branch_id` = ".$search['branch_id'];
		}
		if(!empty($search['land_id']) AND $search['land_id']>0){
			$where.=" AND `sl`.`house_id` = ".$search['land_id'];
		}
		if(@$search['payment_method']>0){
			$where.=" AND `crm`.`payment_method` = ".$search['payment_method'];
		}
		if (!empty($search['streetlist'])){
			$where.=" AND `l`.`street` = '".$search['streetlist']."'";
		}
		if (!empty($search['agency_id'])){
			$where.=" AND `sl`.`staff_id` = '".$search['agency_id']."'";
		}
		
		$search['is_closed'] = empty($search['is_closed'])?0:$search['is_closed'];
		if (!empty($search['is_closed'])){
			if($search['is_closed']!=1){
				$search['is_closed']=0;
			}
			//if ($search['is_closed']!=""){
			$where.=" AND `crm`.`is_closed` = '".$search['is_closed']."'";
		}
		if (!empty($search['option_pay'])){
			if($search['option_pay']>0){
				$where.=" AND `crm`.`payment_option` = ".$search['option_pay'];
			}
		}
		if (!empty($search['receipt_type'])){
			if($search['receipt_type']>0){
				if($search['receipt_type']==1){
					$where.=" AND `crm`.`field3` = ".$search['receipt_type'];
				}else{
					$where.=" AND `crm`.`field3` !=1 ";
				}
			}
		}else{
			$where.=" AND `crm`.`field3` !=1 ";
		}
		
      	if(!empty($search['adv_search'])){
      		$s_where = array();
      		$s_search = addslashes(trim($search['adv_search']));
      		$s_where[] = " `sl`.`sale_number`  LIKE '%{$s_search}%'";
      		$s_where[] = " (SELECT `l`.`land_code` FROM `ln_properties` `l` WHERE `l`.`id` = `sl`.`house_id` LIMIT 1 )  LIKE '%{$s_search}%'";
      		$s_where[] = " (SELECT `l`.`land_address` FROM `ln_properties` `l` WHERE `l`.`id` = `sl`.`house_id` LIMIT 1 )  LIKE '%{$s_search}%'";
      		
      		$s_where[] = " `c`.`client_number` LIKE '%{$s_search}%'";
      		$s_where[] = " `c`.`name_en`  LIKE '%{$s_search}%'";
      		$s_where[] = " `c`.`name_kh`  LIKE '%{$s_search}%'";
      		$s_where[] = " `crm`.`total_principal_permonthpaid` LIKE '%{$s_search}%'";
      		$s_where[] = " `crm`.`total_interest_permonth`  LIKE '%{$s_search}%'";
            $s_where[] = " (SELECT
			     `ln_view`.`name_kh`
			   FROM `ln_view`
			   WHERE ((`ln_view`.`key_code` = `crm`.`payment_method`)
			          AND (`ln_view`.`type` = 2))
			   LIMIT 1) LIKE '%{$s_search}%'";
      		$s_where[] = " `crm`.`penalize_amountpaid` LIKE '%{$s_search}%'";  
      		$s_where[] = " `crm`.`service_chargepaid` LIKE '%{$s_search}%'";
      		$s_where[] = " `crm`.`amount_payment` LIKE '%{$s_search}%'";
      		$s_where[] = " `crm`.`receipt_no` LIKE '%{$s_search}%'";
      		$s_where[] = " `crm`.`cheque` LIKE '%{$s_search}%'";
      		$where .=' AND ('.implode(' OR ',$s_where).')';
      	}
		if(!empty($search['receiptStatus'])){
			if($search['receiptStatus']==1){
				$where.=" AND `crm`.total_payment >0 ";
			}else if($search['receiptStatus']==2){
				$where.=" AND `crm`.total_payment <=0 ";
			}
		}
		
		$order =" ORDER BY `crm`.date_pay DESC,`crm`.id DESC ";
		if($order11==1){//for history
			$order =" ORDER BY `crm`.`client_id` DESC ,`crm`.`sale_id` DESC , crm.id ASC";
		}
		if(!empty($search['queryOrdering'])){
			if($search['queryOrdering']==1){
				$order =" ORDER BY `crm`.date_pay ASC ";
			}else if($search['queryOrdering']==2){
				$order =" ORDER BY `crm`.date_pay DESC ";
			}else if($search['queryOrdering']==3){
				$order =" ORDER BY `crm`.id ASC ";
			}else if($search['queryOrdering']==4){
				$order =" ORDER BY `crm`.id DESC ";
			}
		}
		
      	return $db->fetchAll($sql.$where.$order);
      }
      function submitClosingEngry($data){
      	$db = $this->getAdapter();
      	if(!empty($data['id_selected'])){
      		$ids = explode(',', $data['id_selected']);
      		$key = 1;
      		$arr = array(
      				"is_closed"=>1,
      		);
      		foreach ($ids as $i){
      			$this->_name="ln_client_receipt_money";
      			if (!empty($data['note_'.$i])){
      				$arr['closing_note']=$data['note_'.$i];
      			}
      			$where="id= ".$data['id_'.$i];
      			$this->update($arr, $where);
      		}
      	}
      }
      public function getALLLoanIcome($search=null){
		$start_date = $search['start_date'];
    	$end_date = $search['end_date'];
    	
    	$db = $this->getAdapter();
    	$sql = " SELECT * FROM v_getcollectmoney where status=1 ";
    	$where ='';
    	if(!empty($search['advance_search'])){
    		//print_r($search);
    		$s_where = array();
    		$s_search = addslashes(trim($search['advance_search']));
    		$s_where[] = " land_code LIKE '%{$s_search}%'";
    		$s_where[] = " land_address LIKE '%{$s_search}%'";
    		
    		$s_where[] = "client_name LIKE '%{$s_search}%'";
    		$s_where[] = " client_number LIKE '%{$s_search}%'";
    		$s_where[] = " receipt_no LIKE '%{$s_search}%'";
    		
    		$where .=' AND ('.implode(' OR ',$s_where).')';
    	}
    	if($search['status']!=""){
    		$where.= " AND status = ".$search['status'];
    	}
    	
    	if(!empty($search['start_date']) or !empty($search['end_date'])){
    		$where.=" AND date_input BETWEEN '$start_date' AND '$end_date'";
    	}
    	if($search['client_name']>0){
    		$where.=" AND client_id = ".$search['client_name'];
    	}
    	
    	if($search['co_id']>0){
    		$where.=" AND `co_id`= ".$search['co_id'];
    	}    	
    	$order="";
    	$order = " ORDER BY id DESC";
    	return $db->fetchAll($sql.$where.$order);
      }
      
      public function getALLLoanExpectIncome($search=null){
		  
		  $dbp = new Application_Model_DbTable_DbGlobal();
			$statement = $dbp->getSqlStExpectedIncome();
			$sql= $statement['sql'];
			$sql.="";
			$where = $statement['where'];
			
      	$from_date =(empty($search['start_date']))? '1': " sd.date_payment >= '".$search['start_date']." 00:00:00'";
      	$to_date = (empty($search['end_date']))? '1': " sd.date_payment <= '".$search['end_date']." 23:59:59'";
      	$where.="";
      	
      	$db = $this->getAdapter();
      
      	$dbp = new Application_Model_DbTable_DbGlobal();
      	$where.=$dbp->getAccessPermission("s.branch_id");
      	
      	if(!empty($search['adv_search'])){
			$s_search = addslashes(trim($search['adv_search']));
      	 	$s_where[] = " s.sale_number LIKE '%{$s_search}%'";
      	 	$s_where[] = " l.land_code LIKE '%{$s_search}%'";
      	 	$s_where[] = " l.land_address LIKE '%{$s_search}
			%'";
      	 	$s_where[] = " l.street LIKE '%{$s_search}%'";
      	 	$s_where[] = " c.client_number LIKE '%{$s_search}%'";
      	 	$s_where[] = " c.phone LIKE '%{$s_search}%'";
      	 	$s_where[] = " c.name_kh LIKE '%{$s_search}%'";
			
      	 	$s_where[] = " s.price_sold LIKE '%{$s_search}%'";
      	 	$s_where[] = " s.total_duration LIKE '%{$s_search}%'";
      	 	$where .=' AND ( '.implode(' OR ',$s_where).')';
      	}
      	if($search['schedule_opt']>0){
      		$where.= " AND s.payment_id = ".$search['schedule_opt'];
      	}
      	if($search['client_name']>0){
      		$where.= " AND s.client_id = ".$search['client_name'];
      	}
      	if($search['branch_id']>0){
      		$where.= " AND s.branch_id = ".$search['branch_id'];
      	}
      	if(!empty($search['co_id'])){
      		$where.= " AND s.staff_id = ".$search['co_id'];
      	}
      	if($search['is_completed']>-1){
      		$where.= " AND sd.is_completed = ".$search['is_completed'];
      	}
      	
      	if($search['stepoption']>0){
      		$where.=" AND sd.ispay_bank = ".$search['stepoption'];
      	}else{
      		$where.= " AND ".$from_date." AND ".$to_date;
      		if ($search['stepoption']==0){
      			$where.= " AND sd.ispay_bank=0";
      		}
      	}
      	$group_by = " GROUP BY sd.id ORDER BY sd.date_payment ASC ";
        $row = $db->fetchAll($sql.$where.$group_by);
        return $row;
      }
     
     
      function roundhundred($n,$cu_type){
      	if($cu_type==1){
      		$y = round($n);
      		$a = $y%100 > 0 ? ($y-($y%100)+100) : $y;
      		$x= $a;
      	}else{
      		$total = $n;
      		$x = number_format($total,2);
      	}
      	return $x;
      }
	  function getReceiptByID($id){//total_principal_permonth
		  $db = $this->getAdapter();
		  //(SELECT `d`.`date_payment` FROM `ln_client_receipt_money_detail` `d` WHERE (`crm`.`id` = `d`.`crm_id`) ORDER BY `d`.`date_payment` ASC LIMIT 1) AS `date_payment`,
		  $sql="SELECT crm.*,
		  		s.payment_id AS payment_option,
		  		(SELECT project_name FROM `ln_project` WHERE br_id=crm.branch_id LIMIT 1) AS project_name,
				(SELECT p.land_address  FROM `ln_properties` AS p WHERE p.id  = s.`house_id` LIMIT 1) AS land_address,
				(SELECT p.street  FROM `ln_properties` AS p WHERE p.id  = s.`house_id` LIMIT 1) AS street,
				(SELECT p.old_land_id  FROM `ln_properties` AS p WHERE p.id  = s.`house_id` LIMIT 1) AS landlot_amount,
				(SELECT pt.type_nameen FROM `ln_properties_type` AS pt WHERE pt.id = (SELECT p.property_type  FROM `ln_properties` AS p WHERE p.id  = s.`house_id` LIMIT 1) LIMIT 1)AS property_type,
				
				s.sale_number AS sale_number,
				s.land_price AS land_price,
				
				s.price_before AS price_before,
				s.discount_amount AS discount_amount,
				s.discount_percent AS discount_percent,
				s.other_discount AS other_discount,
				
				s.price_sold AS price_sold,
				s.total_duration AS total_duration,
				
				(SELECT date_payment FROM `ln_saleschedule` WHERE sale_id= crm.sale_id AND status=1 AND no_installment>payment_times ORDER BY date_payment ASC LIMIT 1) as nextdate_payment,
				(SELECT c.name_kh FROM `ln_client` AS c WHERE c.client_id = crm.client_id LIMIT 1) AS name_kh,
				(SELECT c.client_number FROM `ln_client` AS c WHERE c.client_id = crm.client_id LIMIT 1) AS client_number,
				(SELECT c.phone FROM `ln_client` AS c WHERE c.client_id = crm.client_id LIMIT 1) AS phone,
				crm.date_payment as date_payment,
				
			   		crm.payment_method as payment_methodid,
					(SELECT `ln_view`.`name_kh` FROM `ln_view` WHERE ((`ln_view`.`key_code` = `crm`.`payment_method`) AND (`ln_view`.`type` = 2))LIMIT 1) AS `payment_method`,
					(SELECT CONCAT(`ln_view`.`name_kh`,' / ',`ln_view`.`name_en`) FROM `ln_view` WHERE ((`ln_view`.`key_code` = `crm`.`payment_method`) AND (`ln_view`.`type` = 2))LIMIT 1) AS `payment_methodKhAndEng`,
         		 CASE
					WHEN  crm.field3 = 1 THEN 'កក់លើកទី'
					ELSE  ''
				END AS paymnetLabel,
				(SELECT c.hname_kh FROM `ln_client` AS c WHERE c.client_id = crm.client_id LIMIT 1) AS hname_kh,
				(SELECT CONCAT(last_name,' ',first_name) FROM `rms_users` WHERE rms_users.id=crm.`user_id` LIMIT 1) As by_user,
				
				s.agreement_date AS agreement_date,
				(SELECT name_kh FROM `ln_view` WHERE key_code = s.pre_schedule_opt AND type = 25 limit 1) AS pre_paymenttype,
				s.pre_schedule_opt AS pre_schedule_opt,
				s.pre_percent_payment  AS pre_percent_payment,
				
				s.pre_amount_month AS pre_amount_month,
				s.pre_percent_installment AS pre_percent_installment,
				s.pre_amount_year AS pre_amount_year,
				s.pre_fix_payment AS pre_fix_payment
				
		FROM `ln_client_receipt_money` AS crm 
			LEFT JOIN `ln_sale` AS s ON s.id = crm.sale_id
		WHERE crm.`id`=".$id;
		  $dbp = new Application_Model_DbTable_DbGlobal();
		  $sql.=$dbp->getAccessPermission("crm.branch_id");
		  
	 $rs = $db->fetchRow($sql);
	 $rs['property_type']=ltrim(strstr($rs['property_type'], '('), '.');
	 if(empty($rs)){return ''; }else{
		return $rs;
	}
}
public static function getUserId(){
  	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
  	return $session_user->user_id;
}
function round_up($value, $places)
{
	$mult = pow(10, abs($places));
	return $places < 0 ?
	ceil($value / $mult) * $mult :
	ceil($value * $mult) / $mult;
}
function round_up_currency($curr_id, $value,$places=-2){
	//     	return (($curr_id==1)? $this->round_up($value, $places):$value);
	if ($curr_id==1){
		return $this->round_up($value, $places);
	}
	else{
		$digit_value = DIGIT_VALUE_SCHEDULE;// default =2;
		return round($value,$digit_value);
// 		return round($value,2);
	}
}
function updateReceipt($data){
	$db= $this->getAdapter();
	$db->beginTransaction();
	try{
	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
	$user_id = $session_user->user_id;
	
	//For record history 
	$record = $this->recordhistory($data);
	$activityold = $record['activityold'];
	$after_edit_info = $record['after_edit_info'];
	
		$arr_client_pay = array(
			'receipt_no'					=>	$data['receipt_no'],
			'date_pay'					    =>	$data['date_input'],
			'date_input'					=>	$data['date_input'],
			'client_id'                     =>	$data['customer_id'],
			'outstanding'                   =>	$data['balance']+$data['total_principal_permonth'],//ប្រាក់ដើមមុនបង់
			'total_principal_permonth'		=>	$data["total_principal_permonth"],//ប្រាក់ដើមត្រូវបង់
			'total_interest_permonth'		=>	$data["total_interest_permonthpaid"],
			'penalize_amount'				=>	$data["penalize_amountpaid"],
			'principal_amount'				=>	$data['balance'],//ប្រាក់ដើមនៅសល់បន្ទប់ពីបង់
			'selling_price'					=>	$data['price_sold'],
			'allpaid_before'				=>	$data['all_paid'],
			'total_principal_permonthpaid'	=>	$data['total_principal_permonth'],//ok ប្រាក់ដើមបានបង
			'total_interest_permonthpaid'	=>	$data["total_interest_permonthpaid"],//ok ការប្រាក់បានបង
			'penalize_amountpaid'			=>	$data["penalize_amountpaid"],// ok បានបង
			'balance'						=>	0,
			'total_payment'					=>	$data["total_payment"],//ប្រាក់ត្រូវបង់ok
			'recieve_amount'				=>	$data["recieve_amount"],//ok
			'amount_payment'				=>	$data["recieve_amount"],//brak ban borng
			'return_amount'					=>	0,//ok
			'note'							=>	$data['note'],
			'cheque'						=>	$data['cheque'],
			'status'						=>	1,
			'payment_method'				=>	$data['payment_method'],
			'extra_payment' 				=> $data["extra_payment"],
			'payment_times'					=>$data['paid_times'],
			
			'date_payment'          => $data['date_pay'],
			);
			if($data['pay_type']==1){
				$arr_client_pay['field2']=1;
				$arr_client_pay['field3']=1;
			}elseif($data['pay_type']==3){
				$arr_client_pay['field3']=3;
				$arr_client_pay['field2']='';
			}
			$where = " id = ".$data['id'];
		    $this->_name="ln_client_receipt_money";
		    $this->update($arr_client_pay, $where);
    
		    $array = array(
		    		'client_id'				=> $data['customer_id'],
		    		'paid_date'             => $data['date_input'],
		    		'date_payment'          => $data['date_pay'],
		    		'capital'				=> $data['balance']+$data['total_principal_permonth'],
		    		'remain_capital'		=>$data['balance'],
		    		'principal_permonth'	=>$data['total_principal_permonth'],
		    		'total_interest'		=>$data["total_interest_permonthpaid"],
		    		'total_payment'			=>$data["total_payment"],
		    		'total_recieve'			=>$data["recieve_amount"],
		    		'penelize_amount'		=>$data["penalize_amountpaid"],
		    		'old_interest'			 =>$data["total_interest_permonthpaid"],
		    		'old_principal_permonth'=>$data["total_principal_permonth"],
		    		'last_pay_date'=>$data["date_input"],
		    );
			$where = " crm_id = ".$data['id'];
		    $this->_name="ln_client_receipt_money_detail";
		    $this->update($array, $where);
		    
			//update saleSchedule paidDate Info
			$rsInfo = $this->getClientReciptInfo($data['id']);
			if(!empty($rsInfo)){
				$schDetailId = empty($rsInfo["scheduleDetailId"]) ? 0 : $rsInfo["scheduleDetailId"];
				$saleId = empty($rsInfo["sale_id"]) ? 0 : $rsInfo["sale_id"];
				$arrSchedule = array(
					"received_date" => $data['date_input'],
				);
				$whereSchedule = " id = ".$schDetailId." AND sale_id=".$saleId;
				$this->_name="ln_saleschedule";
				$this->update($arrSchedule, $whereSchedule);
			}
			
		    $dbgb = new Application_Model_DbTable_DbGlobal();
		    $_datas = array('description'=>'Edit OFFICIAL RECEIPT','activityold'=>$activityold,'after_edit_info'=>$after_edit_info);
		    $dbgb->addActivityUser($_datas);
		    
		    $db->commit();
	}catch (Exception $e){
		$db->rollBack();
		Application_Form_FrmMessage::message("INSERT_FAIL");
		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	}
}

function getClientReciptInfo($recieptId){
	$db = $this->getAdapter();
	$recieptId = empty($recieptId) ? 0 : $recieptId;
	$sql="
		SELECT 
			crmd.lfd_id AS scheduleDetailId
			,crm.*
			FROM `ln_client_receipt_money` AS crm	
				JOIN `ln_client_receipt_money_detail` AS crmd ON crmd.crm_id=crm.id
		WHERE crm.id = $recieptId
		ORDER BY crmd.id ASC
	";
	$sql.=" LIMIT 1";
	return $db->fetchRow($sql);
}


function updatePaymentStatus($data){
	  	$db = $this->getAdapter();
	  	$db->beginTransaction();
	  	try{
	  		$dbtable = new Application_Model_DbTable_DbGlobal();
	  		$arr = array(
	  				'client_id'=>$data['customer_id'],
	  				'payment_id'=>$data["payment_id"],
	  				'price_before'=>$data['price_before'],
	  				'discount_amount'=>$data['discount_amount'],
	  				'discount_percent'=>$data['discount_percent'],
	  				'price_sold'=>$data['price_sold'],
	  				'lastpayment_amount'=>$data['last_payment'],
	  				'buy_date'=>$data['date_buy'],
	  				'end_line'=>$data['end_date'],
	  				'interest_rate'=>$data['interest_rate'],
	  				'total_duration'=>$data['total_duration'],
	  				'startcal_date'=>$data['first_payment'],
	  				'first_payment'=>$data['first_payment'],
	  				'validate_date'=>$data['end_date'],
	  				'payment_method'=>1,
	  				'total_installamount'=>$data['total_installamount'],
	  				'agreement_date'=>$data['dateagreement'],
	  				'create_date'=>date("Y-m-d"),
	  				'user_id'=>$this->getUserId(),
	  				'land_price'=>$data['land_price'],
	  		);
	  		
	  		if(!empty($data['interest_policy'])){
	  			$arr['interest_policy']=$data['interest_policy'];
	  		}
	  		
	  		$this->_name="ln_sale";
	  		$where = " id = ".$data['id'];
	  		$this->update($arr, $where);

	  		$this->_name="ln_saleschedule";
	  		$where = " collect_by!=2 AND  sale_id = ".$data['id'];
	  		if($data['payment_id']==1 OR $data['payment_id']==2){//កក់
	  		     $where = " collect_by!=2 AND sale_id = ".$data['id'];
	  		     $db->commit();
	  		     return 1;
	  		}else{
	  			$this->delete($where);
	  		}
	  		$total_day=0;
	  		$old_remain_principal = 0;
	  		$old_pri_permonth = 0;
	  		$old_interest_paymonth = 0;
	  		$old_amount_day = 0;
	  		$cum_interest=0;
	  		$amount_collect = 1;

	  		$data['sold_price']=$data['price_sold'];
	  		$remain_principal = $data['sold_price'];
	  		$next_payment = $data['first_payment'];
	  		$from_date =  $data['date_buy'];
	  		$curr_type = 2;//$data['currency_type'];
	  		
	  		$key = new Application_Model_DbTable_DbKeycode();
	  		$key=$key->getKeyCodeMiniInv(TRUE);
	  		$term_types = $key['install_by'];
	  		
	  		$data["schedule_opt"]=$data["payment_id"];
	  		if($data["schedule_opt"]==3 OR $data["schedule_opt"]==6){
	  			$term_types=1;
	  		}
	  		$data['period'] = $data['total_duration'];
	  		$loop_payment = $data['period']*$term_types;
	  		$borrow_term = $data['period']*$term_types;
	  		$payment_method = $data["schedule_opt"];
	  		$j=0;
	  		$pri_permonth=0;
	  		$paid_principal=0;
	  		$paid_interest=0;
	  		$old_interestrate=0;
	  		
	  		$str_next = '+1 month';
	  		$ids =explode(',', $data['identity']);
	  		for($i=1;$i<=$loop_payment;$i++){
	  			$paid_receivehouse=1;
	  			if($payment_method==1){
	  				break;
	  			}elseif($payment_method==2){
	  				break;
	  			}elseif($payment_method==3){//បង់ថេរ
	  				if($i!=1){
	  					$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
	  					$start_date = $next_payment;
	  				}else{
	  				}
	  				$paid_principal = $data['paid_principal'.$i];
	  				$paid_interest = $data['interest_paid'.$i];
	  				$next_payment = $data['date_payment'.$i];
	  				$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
	  				$total_day = $amount_day;
	  				$interest_paymonth = $data['total_interest_'.$i];;
	  				$pri_permonth =$data['principal_permonth_'.$i]; //round($data['price_sold']/$borrow_term,0);
	  				if($i==$loop_payment){//for end of record only
	  					$pri_permonth = $remain_principal;
	  					$paid_receivehouse = $data['paid_receivehouse'];
	  				}
	  			}elseif($payment_method==4){//បង់រំលស់
	  				if($i!=1){
	  					$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
	  					$start_date = $next_payment;
	  					$next_payment = $dbtable->getNextPayment($str_next, $next_payment, 1,3,$data['first_payment']);
	  				}else{
	  					//​​បញ្ចូលចំនូនត្រូវបង់ដំបូងសិន
	  					if(!empty($data['identity'])){
	  						$ids = explode(',', $data['identity']);
	  						$key = 1;
	  						$installment_paid = 0;
	  						foreach ($ids as $j){
	  							if($key==1){
	  								$old_remain_principal = $data['price_sold'];
	  								$old_pri_permonth = $data['principal_permonth_'.$j];
	  							}else{
	  								$old_remain_principal = $old_remain_principal-$old_pri_permonth;
	  								$old_pri_permonth = $data['principal_permonth_'.$j];
	  							}
	  							$old_interest_paymonth = 0;
	  							$cum_interest = $cum_interest+$data['total_interest_'.$j];
	  							$amount_day = $dbtable->CountDayByDate($from_date,$data['date_payment'.$j]);
	  							 
	  							$this->_name="ln_saleschedule";
	  							$datapayment = array(
	  									'branch_id'=>$data['branch_id'],
	  									'sale_id'=>$data['id'],//good
	  									'begining_balance'=> $old_remain_principal,//good
	  									'begining_balance_after'=> $old_remain_principal-$data['paid_principal'.$j],//good
	  									'principal_permonth'=> $data['principal_permonth_'.$j],//good
	  									'principal_permonthafter'=>$data['principal_permonth_'.$j]-$data['paid_principal'.$j],//good
	  									'total_interest'=>$data['total_interest_'.$j],//good
	  									'total_interest_after'=>$data['total_interest_'.$j]-$data['interest_paid'.$j],//good
	  									'total_payment'=>$data['total_payment'.$j],//good
	  									'total_payment_after'=>$data['total_payment'.$j]-$data['paid_amount_'.$j],//good
	  									'ending_balance'=>$old_remain_principal-$old_pri_permonth,
	  									'cum_interest'=>$cum_interest,
	  									'amount_day'=>$amount_day,
	  									'is_completed'=>0,
	  									'date_payment'=>$data['date_payment'.$j],
	  									'percent'=>$data['percent'.$j],
	  									'percent_agree'=>$data['percent_agreement'.$j],
	  									'is_installment'=>1,
	  									'no_installment'=>$key,
	  									'ispay_bank'=>$data['pay_with'.$j],
	  									'last_optiontype'=>$paid_receivehouse,
	  							);
	  							$datapayment['paid_date']=empty($data['paid_date_'.$j])?null:$data['paid_date_'.$j];
								$datapayment['received_date']=empty($data['paid_date_'.$j])?null:$data['paid_date_'.$j];
	  							$datapayment['received_userid']=empty($data['user_id'.$j])?null:$data['user_id'.$j];
								
	  							$key = $key+1;
	  							$installment_paid = $installment_paid+$data['principal_permonth_'.$j];
	  							if($data['payment_option'.$j]==1 OR !empty($data['paid_amount_'.$j])){//complete or paid
	  								$is_completed = 0;
	  								if($data['payment_option'.$j]==1){$is_completed=1;}
	  								if(empty($data['fundid_'.$j])){
	  									$data['fundid_'.$j]=0;
	  								}
	  								$sql = "SELECT id FROM ln_saleschedule WHERE id =".$data['fundid_'.$j]." AND sale_id=".$data['id']." LIMIT 1";
	  								$rsschedule = $db->fetchRow($sql);
	  								$datapayment['is_completed']=$data['payment_option'.$j];
	  								$datapayment['date_payment']=$data['date_payment'.$j];
	  								if(!empty($rsschedule)){
	  									$datapayment['branch_id']=$data['branch_id'];
	  									$where=" id = ".$data['fundid_'.$j];
	  									$this->update($datapayment, $where);
	  								}else{
	  									$this->insert($datapayment);
	  								}
	  							}else{
	  								if(!empty($data['fundid_'.$j])){
	  									$datapayment['is_installment']=1;
	  									$datapayment['branch_id']=$data['branch_id'];
	  									$datapayment['is_completed']=$data['payment_option'.$j];
	  									$where=" id = ".$data['fundid_'.$j];
	  									$sql = "SELECT id FROM ln_saleschedule WHERE id =".$data['fundid_'.$j]." AND sale_id=".$data['id']." LIMIT 1";
	  									$rsschedule = $db->fetchRow($sql);
	  									if(!empty($rsschedule)){
	  										$where=" id = ".$data['fundid_'.$j];
	  										$this->update($datapayment, $where);
	  									}else{
	  										$this->insert($datapayment);
	  									}
	  								}else{
	  									$idsaleid = $this->insert($datapayment);
	  								}
	  							}
	  							$from_date = $data['date_payment'.$j];
	  						}
	  						$j=$key-1;
	  					}
	  					$old_remain_principal=0;
	  					$old_pri_permonth = 0;
	  					$old_interest_paymonth = 0;
	  					if(!empty($data['identity'])){
	  						$remain_principal = $data['sold_price']-$installment_paid;//check here
	  					}
	  					$next_payment = $data['first_payment'];
	  					$next_payment = $dbtable->checkFirstHoliday($next_payment,3);//normal day
	  				}
	  				
	  				$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
	  				$total_day = $amount_day;
	  				if(!empty($data['interest_policy'])){//lorn city
	  					$interst_rate = $dbtable->getInterestRatebySetting($data['interest_policy'],$i);
	  					$newperiod=$data['period'];
	  					if($old_interestrate!=$interst_rate){
	  						if($i>1){
	  							$newperiod = $data['period']-$i+1;
	  						}
	  						$rsfixed = $dbtable->getFixePaymentbyInterest($interst_rate,$remain_principal,$newperiod);
	  						$data['fixed_payment']=$rsfixed;
	  					}
	  					$interest_paymonth = $remain_principal*$interst_rate/12/100;
	  					$interest_paymonth = $this->round_up_currency($curr_type, $interest_paymonth);
	  					$old_interestrate = $interst_rate;
	  				}else{
		  				$interest_paymonth = $remain_principal*(($data['interest_rate']/12)/100);//fixed 30day
		  				$interest_paymonth = $this->round_up_currency($curr_type, $interest_paymonth);
	  				}
	  				if($data['install_type']==2){
	  					$pri_permonth=$data['total_remain']/($data['period']*$term_types);
	  					$pri_permonth = round($pri_permonth,0);
	  				}else{
	  					$pri_permonth = $data['fixed_payment']-$interest_paymonth;
	  				}
	  				if($i==$loop_payment){//for end of record only
	  					$pri_permonth = $remain_principal;
	  					$paid_receivehouse = $data['paid_receivehouse'];
	  				}
	  				
	  			}elseif($payment_method==6 OR $payment_method==5){
	  				$ids = explode(',', $data['identity']);
	  				$key = 1;
	  				foreach ($ids as $i){
	  					if($key==1){
	  						$old_remain_principal = $data['price_sold'];
	  					}else{
	  						$old_remain_principal = $old_remain_principal-$old_pri_permonth;
	  					}
	  					$old_pri_permonth = $data['total_payment'.$i];
	  					if(end($ids)==$i){
	  						$paid_receivehouse = $data['paid_receivehouse'];
	  					}
	  					$old_interest_paymonth = ($data['interest_rate']==0)?0:$this->round_up_currency(1,($old_remain_principal*$data['interest_rate']/12/100));
	  		
	  					$cum_interest = $cum_interest+$old_interest_paymonth;
	  					$amount_day = $dbtable->CountDayByDate($from_date,$data['date_payment'.$i]);
	  					$this->_name="ln_saleschedule";
	  					$datapayment = array(
	  							'branch_id'=>$data['branch_id'],
	  							'sale_id'=>$data['id'],//good
	  							'begining_balance'=> $old_remain_principal,//good
	  							'begining_balance_after'=> $old_remain_principal-$data['paid_principal'.$i],//good
	  							'principal_permonth'=> $data['principal_permonth_'.$i],//good
	  							'principal_permonthafter'=>$data['principal_permonth_'.$i]-$data['paid_principal'.$i],//good2
	  							'total_interest'=>$data['total_interest_'.$i],//good
	  							'total_interest_after'=>$data['total_interest_'.$i]-$data['interest_paid'.$i],//good
	  							'total_payment'=>$data['total_payment'.$i],//good
	  							'total_payment_after'=>$data['total_payment'.$i]-$data['paid_amount_'.$i],//good
	  							'ending_balance'=>$old_remain_principal-$old_pri_permonth,
	  							'cum_interest'=>$cum_interest,
	  							'amount_day'=>$old_amount_day,
	  							'is_completed'=>0,
	  							'date_payment'=>$data['date_payment'.$i],
	  							'percent'=>$data['percent'.$i],
	  							'percent_agree'=>$data['percent_agreement'.$i],
	  							'is_installment'=>1,
	  							'no_installment'=>$key,
	  							'last_optiontype'=>$paid_receivehouse,
	  							'ispay_bank'=>$data['pay_with'.$i],
	  					);
	  					$datapayment['paid_date']=empty($data['paid_date_'.$i])?null:$data['paid_date_'.$i];
						$datapayment['received_date']=empty($data['paid_date_'.$i])?null:$data['paid_date_'.$i];
						$datapayment['received_userid']=empty($data['user_id'.$i])?null:$data['user_id'.$i];
	  					
	  					$from_date = $data['date_payment'.$i];
	  					$key = $key+1;
	  					if($data['payment_option'.$i]==1 OR !empty($data['paid_amount_'.$i])){
	  						$datapayment['is_installment']=1;
	  						$datapayment['branch_id']=$data['branch_id'];
	  						$datapayment['is_completed']=$data['payment_option'.$i];
	  						$datapayment['date_payment']=$data['date_payment'.$i];
	  						if(empty($data['fundid_'.$i])){
	  							$data['fundid_'.$i]=0;
	  						}
	  						$sql = "SELECT id FROM ln_saleschedule WHERE id =".$data['fundid_'.$i]." AND sale_id=".$data['id']." LIMIT 1";
	  						$rsschedule = $db->fetchRow($sql);
	  						if(!empty($rsschedule)){
	  							$where=" id = ".$data['fundid_'.$i];
	  							$this->update($datapayment, $where);
	  						}else{
	  							$this->insert($datapayment);
	  						}
	  					}else{
	  						if(!empty($data['fundid_'.$i])){
	  							$sql = "SELECT id FROM ln_saleschedule WHERE id =".$data['fundid_'.$i]." AND sale_id=".$data['id']." LIMIT 1";
	  							$rsschedule = $db->fetchRow($sql);
	  							if(!empty($rsschedule)){
	  								$datapayment['is_installment']=1;
	  								$datapayment['branch_id']=$data['branch_id'];
	  								$datapayment['is_completed']=$data['payment_option'.$i];
	  								$datapayment['date_payment']=$data['date_payment'.$i];
	  								$where=" id = ".$data['fundid_'.$i];
	  								$this->update($datapayment, $where);
	  							}else{
	  								$this->insert($datapayment);
	  							}
	  						}else{
	  							$this->insert($datapayment);
	  						}
	  					}
	  				}
	  				break;
	  			}elseif($payment_method==7){//បង់រំលស់
	  				$data['price_sold'] = $data['price_sold']-$data['last_payment'];
	  				if($i!=1){
	  					$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
	  					$start_date = $next_payment;
	  					$next_payment = $dbtable->getNextPayment($str_next, $next_payment, 1,3,$data['first_payment']);
	  				}else{
	  					//​​បញ្ចូលចំនូនត្រូវបង់ដំបូងសិន
	  					if(!empty($data['identity'])){
	  						$ids = explode(',', $data['identity']);
	  						$key = 1;
	  						$installment_paid = 0;
	  						foreach ($ids as $j){
	  							if($key==1){
	  								$old_remain_principal = $data['price_sold'];
	  								$old_pri_permonth = $data['total_payment'.$j];
	  							}else{
	  								$old_remain_principal = $old_remain_principal-$old_pri_permonth;
	  								$old_pri_permonth = $data['total_payment'.$j];
	  							}
	  							$old_interest_paymonth = 0;
	  							$cum_interest = $cum_interest+$data['total_interest_'.$j];
	  							$amount_day = $dbtable->CountDayByDate($from_date,$data['date_payment'.$j]);
	  								
	  							$this->_name="ln_saleschedule";
	  							$datapayment = array(
	  									'branch_id'=>$data['branch_id'],
	  									'sale_id'=>$data['id'],//good
	  									'begining_balance'=> $old_remain_principal,//good
	  									'begining_balance_after'=> $old_remain_principal-$data['paid_principal'.$j],//good
	  									'principal_permonth'=> $data['principal_permonth_'.$j],//good
	  									'principal_permonthafter'=>$data['principal_permonth_'.$j]-$data['paid_principal'.$j],//good
	  									'total_interest'=>$data['total_interest_'.$j],//good
	  									'total_interest_after'=>$data['total_interest_'.$j]-$data['interest_paid'.$j],//good
	  									'total_payment'=>$data['total_payment'.$j],//good
	  									'total_payment_after'=>$data['total_payment'.$j]-$data['paid_amount_'.$j],//good
	  									'ending_balance'=>$old_remain_principal-$old_pri_permonth,
	  									'cum_interest'=>$cum_interest,
	  									'amount_day'=>$amount_day,
	  									'is_completed'=>0,
	  									'date_payment'=>$data['date_payment'.$j],
	  									'percent'=>$data['percent'.$j],
	  									'percent_agree'=>$data['percent_agreement'.$j],
	  									'is_installment'=>1,
	  									'no_installment'=>$key,
	  									'ispay_bank'=>$data['pay_with'.$j],
	  									'last_optiontype'=>$paid_receivehouse,
	  							);
								$datapayment['paid_date']=empty($data['paid_date_'.$j])?null:$data['paid_date_'.$j];
								$datapayment['received_date']=empty($data['paid_date_'.$j])?null:$data['paid_date_'.$j];
	  							$datapayment['received_userid']=empty($data['user_id'.$j])?null:$data['user_id'.$j];
								
	  							$key = $key+1;
	  							$installment_paid = $installment_paid+$data['principal_permonth_'.$j];
	  							if($data['payment_option'.$j]==1 OR !empty($data['paid_amount_'.$j])){//complete or paid
	  								$is_completed = 0;
	  								if($data['payment_option'.$j]==1){
	  									$is_completed=1;
	  								}
	  								if(empty($data['fundid_'.$j])){
	  									$data['fundid_'.$j]=0;
	  								}
	  								$sql = "SELECT id FROM ln_saleschedule WHERE id =".$data['fundid_'.$j]." AND sale_id=".$data['id']." LIMIT 1";
	  								$rsschedule = $db->fetchRow($sql);
	  								$datapayment['is_completed']=$data['payment_option'.$j];
	  								$datapayment['date_payment']=$data['date_payment'.$j];
	  								if(!empty($rsschedule)){
	  									$datapayment['branch_id']=$data['branch_id'];
	  									$where=" id = ".$data['fundid_'.$j];
	  									$this->update($datapayment, $where);
	  								}else{
	  									$this->insert($datapayment);
	  								}
	  							}else{
	  								if(!empty($data['fundid_'.$j])){
	  									$datapayment['is_installment']=1;
	  									$datapayment['branch_id']=$data['branch_id'];
	  									$datapayment['is_completed']=$data['payment_option'.$j];
	  									$where=" id = ".$data['fundid_'.$j];
	  									$sql = "SELECT id FROM ln_saleschedule WHERE id =".$data['fundid_'.$j]." AND sale_id=".$data['id']." LIMIT 1";
	  									$rsschedule = $db->fetchRow($sql);
	  									if(!empty($rsschedule)){
	  										$where=" id = ".$data['fundid_'.$j];
	  										$this->update($datapayment, $where);
	  									}else{
	  										$this->insert($datapayment);
	  									}
	  								}else{
	  									$idsaleid = $this->insert($datapayment);
	  								}
	  							}
	  							$from_date = $data['date_payment'.$j];
	  						}
	  						$j=$key-1;
	  					}
	  					$old_remain_principal=0;
	  					$old_pri_permonth = 0;
	  					$old_interest_paymonth = 0;
	  					if(!empty($data['identity'])){
	  						$remain_principal = $data['sold_price']-$installment_paid-$data['last_payment'];;//check here
	  					}
	  					$next_payment = $data['first_payment'];
	  					$next_payment = $dbtable->checkFirstHoliday($next_payment,3);//normal day
	  				}
	  					
	  				$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
	  				$total_day = $amount_day;
	  				$interest_paymonth = $remain_principal*(($data['interest_rate']/12)/100);//fixed 30day
	  				$interest_paymonth = $this->round_up_currency($curr_type, $interest_paymonth);
	  				if($data['install_type']==2){
	  					$pri_permonth=$data['total_remain']/($data['period']*$term_types);
	  					$pri_permonth = round($pri_permonth,0);
	  				}else{
	  					$pri_permonth = $data['fixed_payment']-$interest_paymonth;
	  				}
	  				if($i==$loop_payment){//for end of record only
	  					$pri_permonth = $remain_principal;
// 	  					$paid_receivehouse = $data['paid_receivehouse'];
	  				}
	  			}
	  			if($payment_method==3 OR $payment_method==4 OR $payment_method==7){//បង់ថេរនឹងរំលស់
	  				$old_remain_principal =$old_remain_principal+$remain_principal;
	  				$old_pri_permonth = $old_pri_permonth+$pri_permonth;
	  				$old_interest_paymonth = $this->round_up_currency($curr_type,($old_interest_paymonth+$interest_paymonth));
	  				if($payment_method==4 AND $data['install_type']==2){//រំលស់ថយ
	  					$old_interest_paymonth = round($old_interest_paymonth,0);
	  				}
	  				$cum_interest = $cum_interest+$old_interest_paymonth;
	  				$old_amount_day =$old_amount_day+ $amount_day;
	  				$this->_name="ln_saleschedule";
	  				$datapayment = array(
	  						'sale_id'=>$data['id'],//good
	  						'begining_balance'=> $old_remain_principal,//good
	  						'begining_balance_after'=> $old_remain_principal-$paid_principal,//good
	  						'principal_permonth'=> $old_pri_permonth,//good
	  						'principal_permonthafter'=>$old_pri_permonth-$paid_principal,//good
	  						'total_interest'=>$old_interest_paymonth,//good
	  						'total_interest_after'=>$old_interest_paymonth-$paid_interest,//good
	  						'total_payment'=>($old_pri_permonth+$old_interest_paymonth),//good
	  						'total_payment_after'=>($old_pri_permonth+$old_interest_paymonth)-($paid_principal+$paid_interest),//good
	  						'ending_balance'=>$old_remain_principal-$old_pri_permonth,
	  						'cum_interest'=>$cum_interest,
	  						'amount_day'=>$old_amount_day,
	  						'is_completed'=>0,
	  						'date_payment'=>$next_payment,
	  						'no_installment'=>$i+$j,
	  						'last_optiontype'=>$paid_receivehouse,
	  						'interest_rate'=>$old_interestrate
	  				);
	  				if($payment_method==3){//បង់ថេរ
	  					if($old_remain_principal-$old_pri_permonth<0){
	  						break;
	  					}
	  					if($data['payment_option'.$i]==1 OR !empty($data['paid_amount_'.$i])){//ផ្តាច់
	  						$datapayment['is_installment']=1;
	  						$datapayment['branch_id']=$data['branch_id'];
	  						$datapayment['is_completed']=$data['payment_option'.$i];
	  						$datapayment['date_payment']=$data['date_payment'.$i];
	  						
	  						if(empty($data['fundid_'.$i])){
	  							$data['fundid_'.$i]=0;
	  						}
							$sql = "SELECT id FROM ln_saleschedule WHERE id =".$data['fundid_'.$i]." AND sale_id=".$data['id']." LIMIT 1";
	  						$rsschedule = $db->fetchRow($sql);
	  						
	  						if(!empty($rsschedule)){
	  							$where=" id = ".$data['fundid_'.$i];
	  							$this->update($datapayment, $where);
	  						}else{
	  							$this->insert($datapayment);
	  						}
	  					}else{
	  						if(!empty($data['fundid_'.$i])){
	  							$datapayment['is_installment']=1;
	  							$datapayment['branch_id']=$data['branch_id'];
	  							$datapayment['is_completed']=$data['payment_option'.$i];
	  							$where=" id = ".$data['fundid_'.$i];
	  							
	  							$sql = "SELECT id FROM ln_saleschedule WHERE id =".$data['fundid_'.$i]." AND sale_id=".$data['id']." LIMIT 1";
	  							$rsschedule = $db->fetchRow($sql);
	  							if(!empty($rsschedule)){
	  								$where=" id = ".$data['fundid_'.$i];
	  								$this->update($datapayment, $where);
	  							}else{
	  								$this->insert($datapayment);
	  							}
	  						}else{
	  							$idsaleid = $this->insert($datapayment);
	  						}
	  					}
	  				}else{
	  						$idsaleid = $this->insert($datapayment);
	  				}
	  				$old_remain_principal = 0;
	  				$old_pri_permonth = 0;
	  				$old_interest_paymonth = 0;
	  				$old_amount_day = 0;
	  				$from_date=$next_payment;
	  			}
	  		}
	  		if($payment_method==7 AND $data['last_payment']>0){
	  			$this->_name="ln_saleschedule";
	  			$old_remain_principal =$old_remain_principal+$remain_principal;
	  			$old_pri_permonth = $old_pri_permonth+$pri_permonth;
	  			$cum_interest = $cum_interest+$old_interest_paymonth;
	  			$old_amount_day =$old_amount_day+ $amount_day;
	  			$datapayment = array(
  					'sale_id'=>$data['id'],//good
  					'begining_balance'=> $old_remain_principal,//good
  					'begining_balance_after'=> $old_remain_principal,//good
  					'principal_permonth'=> $old_pri_permonth,//good
  					'principal_permonthafter'=>$old_pri_permonth,//good
  					'total_interest'=>0,//good
  					'total_interest_after'=>0,//good
  					'total_payment'=>$old_pri_permonth+$old_interest_paymonth,//good
  					'total_payment_after'=>$old_pri_permonth+$old_interest_paymonth,
  					'ending_balance'=>0,
  					'cum_interest'=>$cum_interest,
  					'amount_day'=>$old_amount_day,
  					'is_completed'=>0,
  					'date_payment'=>$next_payment,
  					'no_installment'=>$i+$j+1,
  					'last_optiontype'=>0
	  			);
	  			$this->insert($datapayment);
	  		}
	  		$dbtable = new Application_Model_DbTable_DbGlobal();
	  		$dbtable->updateLateRecordSaleschedule($data['id']);
	  		$db->commit();
	  		return 1;
	  	}catch (Exception $e){
	  		$db->rollBack();
	  		Application_Form_FrmMessage::message("INSERT_FAIL");
	  		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	  	}
	  }
	  function getPreviewSchedule($data){
	  	$db = $this->getAdapter();
	  	$db = $this->getAdapter();
	  	$db->beginTransaction();
	  	try{
	  		$dbtable = new Application_Model_DbTable_DbGlobal();
	  		$this->_name="ln_saleschedule_test";
	  		$where = " sale_id = ".$data['id'];
	  		if($data['payment_id']==1 OR $data['payment_id']==2){//កក់
	  			$where = " sale_id = ".$data['id'];
	  			$db->commit();
	  			return 1;
	  		}else{
	  			$this->delete($where);
	  		}
	  		$total_day=0;
	  		$old_remain_principal = 0;
	  		$old_pri_permonth = 0;
	  		$old_interest_paymonth = 0;
	  		$old_amount_day = 0;
	  		$cum_interest=0;
	  		$amount_collect = 1;
	  	
	  		$data['sold_price']=$data['price_sold'];
	  		$remain_principal = $data['sold_price'];
	  		$next_payment = $data['first_payment'];
	  		$from_date =  $data['date_buy'];
	  		$curr_type = 2;
	  		
	  		$key = new Application_Model_DbTable_DbKeycode();
	  		$key=$key->getKeyCodeMiniInv(TRUE);
	  		$term_types = $key['install_by'];
	  		
	  		$data["schedule_opt"]=$data["payment_id"];
	  		if($data["schedule_opt"]==3 OR $data["schedule_opt"]==6){
	  			$term_types=1;
	  		}
	  		$data['period'] = $data['total_duration'];
	  		$loop_payment = $data['period']*$term_types;
	  		$borrow_term = $data['period']*$term_types;
	  		$payment_method = $data["schedule_opt"];
	  		$j=0;
	  		$pri_permonth=0;
	  		$paid_principal=0;
	  		$paid_interest=0;
	  		$old_interestrate=0;
	  		$str_next = '+1 month';
	  		$ids =explode(',', $data['identity']);
	  		for($i=1;$i<=$loop_payment;$i++){
	  			$paid_receivehouse=1;
	  			if($payment_method==3){//បង់ថេរ
	  				if($i!=1){
	  					$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
	  					$start_date = $next_payment;
	  				}else{
	  				}
	  				$paid_principal = $data['paid_principal'.$i];
	  				$paid_interest = $data['interest_paid'.$i];
	  				$next_payment = $data['date_payment'.$i];
	  				$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
	  				$total_day = $amount_day;
	  				$interest_paymonth = $data['total_interest_'.$i];;
	  				$pri_permonth =$data['principal_permonth_'.$i]; 
	  				if($i==$loop_payment){//for end of record only
	  					$pri_permonth = $remain_principal;
	  					$paid_receivehouse = $data['paid_receivehouse'];
	  				}
	  			}elseif($payment_method==4){
	  				if($i!=1){
	  					$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
	  					$start_date = $next_payment;
	  					$next_payment = $dbtable->getNextPayment($str_next, $next_payment, 1,3,$data['first_payment']);
	  				}else{
	  					//​​បញ្ចូលចំនូនត្រូវបង់ដំបូងសិន
	  					if(!empty($data['identity'])){
	  						$ids = explode(',', $data['identity']);
	  						$key = 1;
	  						$installment_paid = 0;
	  						foreach ($ids as $j){
	  							if($key==1){
	  								$old_remain_principal = $data['price_sold'];
	  								$old_pri_permonth = $data['principal_permonth_'.$j];
	  							}else{
	  								$old_remain_principal = $old_remain_principal-$old_pri_permonth;
	  								$old_pri_permonth = $data['principal_permonth_'.$j];
	  							}
	  							$old_interest_paymonth = 0;
	  							$cum_interest = $cum_interest+$data['total_interest_'.$j];
	  							$amount_day = $dbtable->CountDayByDate($from_date,$data['date_payment'.$j]);
	  							 
	  							$this->_name="ln_saleschedule_test";
	  							$datapayment = array(
	  									'branch_id'=>$data['branch_id'],
	  									'sale_id'=>$data['id'],//good
	  									'begining_balance'=> $old_remain_principal,//good
	  									'principal_permonth'=> $data['principal_permonth_'.$j],//good
	  									'principal_permonthafter'=>$data['principal_permonth_'.$j]-$data['paid_principal'.$j],//good
	  									'total_interest'=>$data['total_interest_'.$j],//good
	  									'total_interest_after'=>$data['total_interest_'.$j]-$data['interest_paid'.$j],//good
	  									'total_payment'=>$data['total_payment'.$j],//good
	  									'total_payment_after'=>$data['total_payment'.$j]-$data['paid_amount_'.$j],//good
	  									'ending_balance'=>$old_remain_principal-$old_pri_permonth,
	  									'cum_interest'=>$cum_interest,
	  									'amount_day'=>$amount_day,
	  									'is_completed'=>0,
	  									'date_payment'=>$data['date_payment'.$j],
	  							);
	  							$key = $key+1;
	  							$installment_paid = $installment_paid+$data['principal_permonth_'.$j];
	  							if($data['payment_option'.$j]==1 OR !empty($data['paid_amount_'.$j])){//complete or paid
	  								$is_completed = 0;
	  								if($data['payment_option'.$j]==1){$is_completed=1;}
	  								if(empty($data['fundid_'.$j])){
	  									$data['fundid_'.$j]=0;
	  								}
	  								$sql = "SELECT id FROM ln_saleschedule_test WHERE id =".$data['fundid_'.$j]." AND sale_id=".$data['id']." LIMIT 1";
	  								$rsschedule = $db->fetchRow($sql);
	  								$datapayment['is_completed']=$data['payment_option'.$j];
	  								$datapayment['date_payment']=$data['date_payment'.$j];
	  								if(!empty($rsschedule)){
	  									$datapayment['branch_id']=$data['branch_id'];
	  									$where=" id = ".$data['fundid_'.$j];
	  									$this->update($datapayment, $where);
	  								}else{
	  									$this->insert($datapayment);
	  								}
	  							}else{
	  								if(!empty($data['fundid_'.$j])){
	  									$datapayment['branch_id']=$data['branch_id'];
	  									$datapayment['is_completed']=$data['payment_option'.$j];
	  									$where=" id = ".$data['fundid_'.$j];
	  									$sql = "SELECT id FROM ln_saleschedule_test WHERE id =".$data['fundid_'.$j]." AND sale_id=".$data['id']." LIMIT 1";
	  									$rsschedule = $db->fetchRow($sql);
	  									if(!empty($rsschedule)){
	  										$where=" id = ".$data['fundid_'.$j];
	  										$this->update($datapayment, $where);
	  									}else{
	  										$this->insert($datapayment);
	  									}
	  								}else{
	  									$idsaleid = $this->insert($datapayment);
	  								}
	  							}
	  							$from_date = $data['date_payment'.$j];
	  						}
	  						$j=$key-1;
	  					}
	  					$old_remain_principal=0;
	  					$old_pri_permonth = 0;
	  					$old_interest_paymonth = 0;
	  					if(!empty($data['identity'])){
	  						$remain_principal = $data['sold_price']-$installment_paid;//check here
	  					}
	  					$next_payment = $data['first_payment'];
	  					$next_payment = $dbtable->checkFirstHoliday($next_payment,3);//normal day
	  				}
	  				
	  				$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
	  				$total_day = $amount_day;
	  				
	  				if(!empty($data['interest_policy'])){//lorn city
	  					$interst_rate = $dbtable->getInterestRatebySetting($data['interest_policy'],$i);
	  					$newperiod=$data['period'];
	  					if($old_interestrate!=$interst_rate){
	  						if($i>1){
	  							$newperiod = $data['period']-$i+1;
	  						}
	  						$rsfixed = $dbtable->getFixePaymentbyInterest($interst_rate,$remain_principal,$newperiod);
	  						$data['fixed_payment']=$rsfixed;
	  					}
	  					$interest_paymonth = $remain_principal*$interst_rate/12/100;
	  					$interest_paymonth = $this->round_up_currency($curr_type, $interest_paymonth);
	  					$old_interestrate = $interst_rate;
	  				}else{
	  					$interest_paymonth = $remain_principal*(($data['interest_rate']/12)/100);//fixed 30day
	  					$interest_paymonth = $this->round_up_currency($curr_type, $interest_paymonth);
	  				}
	  				
	  				if($data['install_type']==2){
	  					$pri_permonth=$data['total_remain']/($data['period']*$term_types);
	  					$pri_permonth = round($pri_permonth,0);
	  				}else{
	  					$pri_permonth = $data['fixed_payment']-$interest_paymonth;
	  				}
	  				if($i==$loop_payment){//for end of record only
	  					$pri_permonth = $remain_principal;
	  					$paid_receivehouse = $data['paid_receivehouse'];
	  				}	  				
	  			}elseif($payment_method==6 OR $payment_method==5){
	  				$ids = explode(',', $data['identity']);
	  				$key = 1;
	  				foreach ($ids as $i){
	  					if($key==1){
	  						$old_remain_principal = $data['price_sold'];
	  					}else{
	  						$old_remain_principal = $old_remain_principal-$old_pri_permonth;
	  					}
	  					$old_pri_permonth = $data['total_payment'.$i];
	  					if(end($ids)==$i){
	  						$paid_receivehouse = $data['paid_receivehouse'];
	  					}
	  					$old_interest_paymonth = ($data['interest_rate']==0)?0:$this->round_up_currency(1,($old_remain_principal*$data['interest_rate']/12/100));
	  					$cum_interest = $cum_interest+$old_interest_paymonth;
	  					$amount_day = $dbtable->CountDayByDate($from_date,$data['date_payment'.$i]);
	  					$this->_name="ln_saleschedule_test";
	  					$datapayment = array(
	  							'branch_id'=>$data['branch_id'],
	  							'sale_id'=>$data['id'],//good
	  							'begining_balance'=> $old_remain_principal,//good
	  							'principal_permonth'=> $data['total_payment'.$i],//good
	  							'principal_permonthafter'=>$data['total_payment'.$i]-$data['paid_principal'.$i],//good2
	  							'total_interest'=>$data['total_interest_'.$i],//good
	  							'total_interest_after'=>$data['total_interest_'.$i]-$data['interest_paid'.$i],//good
	  							'total_payment'=>$data['total_payment'.$i],//good
	  							'total_payment_after'=>$data['total_payment'.$i]-$data['paid_amount_'.$i],//good
	  							'ending_balance'=>$old_remain_principal-$old_pri_permonth,
	  							'cum_interest'=>$cum_interest,
	  							'amount_day'=>$old_amount_day,
	  							'is_completed'=>0,
	  							'date_payment'=>$data['date_payment'.$i],
	  					);
	  					$from_date = $data['date_payment'.$i];
	  					$key = $key+1;
	  					if($data['payment_option'.$i]==1 OR !empty($data['paid_amount_'.$i])){
	  						$datapayment['branch_id']=$data['branch_id'];
	  						$datapayment['is_completed']=$data['payment_option'.$i];
	  						$datapayment['date_payment']=$data['date_payment'.$i];
	  						if(empty($data['fundid_'.$i])){
	  							$data['fundid_'.$i]=0;
	  						}
	  						$sql = "SELECT id FROM ln_saleschedule_test WHERE id =".$data['fundid_'.$i]." AND sale_id=".$data['id']." LIMIT 1";
	  						$rsschedule = $db->fetchRow($sql);
	  						if(!empty($rsschedule)){
	  							$where=" id = ".$data['fundid_'.$i];
	  							$this->update($datapayment, $where);
	  						}else{
	  							$this->insert($datapayment);
	  						}
	  					}else{
	  						if(!empty($data['fundid_'.$i])){
	  							$sql = "SELECT id FROM ln_saleschedule_test WHERE id =".$data['fundid_'.$i]." AND sale_id=".$data['id']." LIMIT 1";
	  							$rsschedule = $db->fetchRow($sql);
	  							if(!empty($rsschedule)){
	  								//$datapayment['is_installment']=1;
	  								$datapayment['branch_id']=$data['branch_id'];
	  								$datapayment['is_completed']=$data['payment_option'.$i];
	  								$datapayment['date_payment']=$data['date_payment'.$i];
	  								$where=" id = ".$data['fundid_'.$i];
	  								$this->update($datapayment, $where);
	  							}else{
	  								$this->insert($datapayment);
	  							}
	  						}else{
	  							$this->insert($datapayment);
	  						}
	  					}
	  				}
	  				break;
	  			}elseif($payment_method==7){
	  				$data['price_sold'] = $data['price_sold']-$data['last_payment'];
	  				if($i!=1){
	  					$remain_principal = $remain_principal-$pri_permonth;//OSប្រាក់ដើមគ្រា
	  					$start_date = $next_payment;
	  					$next_payment = $dbtable->getNextPayment($str_next, $next_payment, 1,3,$data['first_payment']);
	  				}else{
	  					if(!empty($data['identity'])){
	  						$ids = explode(',', $data['identity']);
	  						$key = 1;
	  						$installment_paid = 0;
	  						foreach ($ids as $j){
	  							if($key==1){
	  								$old_remain_principal = $data['price_sold'];
	  								$old_pri_permonth = $data['total_payment'.$j];
	  							}else{
	  								$old_remain_principal = $old_remain_principal-$old_pri_permonth;
	  								$old_pri_permonth = $data['total_payment'.$j];
	  							}
	  							$old_interest_paymonth = 0;
	  							$cum_interest = $cum_interest+$data['total_interest_'.$j];
	  							$amount_day = $dbtable->CountDayByDate($from_date,$data['date_payment'.$j]);
	  							 
	  							$this->_name="ln_saleschedule_test";
	  							$datapayment = array(
	  									'branch_id'=>$data['branch_id'],
	  									'sale_id'=>$data['id'],//good
	  									'begining_balance'=> $old_remain_principal,//good
	  									'principal_permonth'=> $data['principal_permonth_'.$j],//good
	  									'principal_permonthafter'=>$data['principal_permonth_'.$j]-$data['paid_principal'.$j],//good
	  									'total_interest'=>$data['total_interest_'.$j],//good
	  									'total_interest_after'=>$data['total_interest_'.$j]-$data['interest_paid'.$j],//good
	  									'total_payment'=>$data['total_payment'.$j],//good
	  									'total_payment_after'=>$data['total_payment'.$j]-$data['paid_amount_'.$j],//good
	  									'ending_balance'=>$old_remain_principal-$old_pri_permonth,
	  									'cum_interest'=>$cum_interest,
	  									'amount_day'=>$amount_day,
	  									'is_completed'=>0,
	  									'date_payment'=>$data['date_payment'.$j],
	  							);
	  							$key = $key+1;
	  							$installment_paid = $installment_paid+$data['principal_permonth_'.$j];
	  							if($data['payment_option'.$j]==1 OR !empty($data['paid_amount_'.$j])){//complete or paid
	  								$is_completed = 0;
	  								if($data['payment_option'.$j]==1){
	  									$is_completed=1;
	  								}
	  								if(empty($data['fundid_'.$j])){
	  									$data['fundid_'.$j]=0;
	  								}
	  								$sql = "SELECT id FROM ln_saleschedule_test WHERE id =".$data['fundid_'.$j]." AND sale_id=".$data['id']." LIMIT 1";
	  								$rsschedule = $db->fetchRow($sql);
	  								$datapayment['is_completed']=$data['payment_option'.$j];
	  								$datapayment['date_payment']=$data['date_payment'.$j];
	  								if(!empty($rsschedule)){
	  									$datapayment['branch_id']=$data['branch_id'];
	  									$where=" id = ".$data['fundid_'.$j];
	  									$this->update($datapayment, $where);
	  								}else{
	  									$this->insert($datapayment);
	  								}
	  							}else{
	  								if(!empty($data['fundid_'.$j])){
	  									$datapayment['branch_id']=$data['branch_id'];
	  									$datapayment['is_completed']=$data['payment_option'.$j];
	  									$where=" id = ".$data['fundid_'.$j];
	  									$sql = "SELECT id FROM ln_saleschedule_test WHERE id =".$data['fundid_'.$j]." AND sale_id=".$data['id']." LIMIT 1";
	  									$rsschedule = $db->fetchRow($sql);
	  									if(!empty($rsschedule)){
	  										$where=" id = ".$data['fundid_'.$j];
	  										$this->update($datapayment, $where);
	  									}else{
	  										$this->insert($datapayment);
	  									}
	  								}else{
	  									$idsaleid = $this->insert($datapayment);
	  								}
	  							}
	  							$from_date = $data['date_payment'.$j];
	  						}
	  						$j=$key-1;
	  					}
	  					
	  					$old_remain_principal=0;
	  					$old_pri_permonth = 0;
	  					$old_interest_paymonth = 0;
	  					if(!empty($data['identity'])){
	  						$remain_principal = $data['sold_price']-$installment_paid-$data['last_payment'];//check here
	  					}
	  					$next_payment = $data['first_payment'];
	  					$next_payment = $dbtable->checkFirstHoliday($next_payment,3);//normal day
	  				}
	  				
	  				$amount_day = $dbtable->CountDayByDate($from_date,$next_payment);
	  				$total_day = $amount_day;
	  				$interest_paymonth = $remain_principal*(($data['interest_rate']/12)/100);//fixed 30day
	  				$interest_paymonth = $this->round_up_currency($curr_type, $interest_paymonth);
	  				if($data['install_type']==2){
	  					$pri_permonth=$data['total_remain']/($data['period']*$term_types);
	  					$pri_permonth = round($pri_permonth,0);
	  				}else{
	  					$pri_permonth = $data['fixed_payment']-$interest_paymonth;
	  				}
	  				if($i==$loop_payment){//for end of record only
	  					$pri_permonth = $remain_principal;
	  					$paid_receivehouse = $data['paid_receivehouse'];
	  				}	  				
	  			}
	  			if($payment_method==3 OR $payment_method==4 OR $payment_method==7){//បង់ថេរនឹងរំលស់
	  				$old_remain_principal =$old_remain_principal+$remain_principal;
	  				$old_pri_permonth = $old_pri_permonth+$pri_permonth;
	  				$old_interest_paymonth = $this->round_up_currency($curr_type,($old_interest_paymonth+$interest_paymonth));
	  				if($payment_method==4 AND $data['install_type']==2){//រំលស់ថយ
	  					$old_interest_paymonth = round($old_interest_paymonth,0);
	  				}
	  				$cum_interest = $cum_interest+$old_interest_paymonth;
	  				$old_amount_day =$old_amount_day+ $amount_day;
	  				
	  				$this->_name="ln_saleschedule_test";
	  				$datapayment = array(
	  						'sale_id'=>$data['id'],//good
	  						'begining_balance'=> $old_remain_principal,//good
	  						'principal_permonth'=> $old_pri_permonth,//good
	  						'principal_permonthafter'=>$old_pri_permonth-$paid_principal,//good
	  						'total_interest'=>$old_interest_paymonth,//good
	  						'total_interest_after'=>$old_interest_paymonth-$paid_interest,//good
	  						'total_payment'=>($old_pri_permonth+$old_interest_paymonth),//good
	  						'total_payment_after'=>($old_pri_permonth+$old_interest_paymonth)-($paid_principal+$paid_interest),//good
	  						'ending_balance'=>$old_remain_principal-$old_pri_permonth,
	  						'cum_interest'=>$cum_interest,
	  						'amount_day'=>$old_amount_day,
	  						'is_completed'=>0,
	  						'date_payment'=>$next_payment,
	  				);
	  				if($payment_method==3){//បង់ថេរ
	  					if($old_remain_principal-$old_pri_permonth<0){
	  						break;
	  					}
	  					if($data['payment_option'.$i]==1 OR !empty($data['paid_amount_'.$i])){//ផ្តាច់
	  						$datapayment['branch_id']=$data['branch_id'];
	  						$datapayment['is_completed']=$data['payment_option'.$i];
	  						$datapayment['date_payment']=$data['date_payment'.$i];
	  							
	  						if(empty($data['fundid_'.$i])){
	  							$data['fundid_'.$i]=0;
	  						}
	  						$sql = "SELECT id FROM ln_saleschedule_test WHERE id =".$data['fundid_'.$i]." AND sale_id=".$data['id']." LIMIT 1";
	  						$rsschedule = $db->fetchRow($sql);
	  							
	  						if(!empty($rsschedule)){
	  							$where=" id = ".$data['fundid_'.$i];
	  							$this->update($datapayment, $where);
	  						}else{
	  							$this->insert($datapayment);
	  						}
	  							
	  					}else{
	  						if(!empty($data['fundid_'.$i])){
	  							$datapayment['branch_id']=$data['branch_id'];
	  							$datapayment['is_completed']=$data['payment_option'.$i];
	  							$where=" id = ".$data['fundid_'.$i];
	  	
	  							$sql = "SELECT id FROM ln_saleschedule_test WHERE id =".$data['fundid_'.$i]." AND sale_id=".$data['id']." LIMIT 1";
	  							$rsschedule = $db->fetchRow($sql);
	  							if(!empty($rsschedule)){
	  								$where=" id = ".$data['fundid_'.$i];
	  								$this->update($datapayment, $where);
	  							}else{
	  								$this->insert($datapayment);
	  							}
	  						}else{
	  							$idsaleid = $this->insert($datapayment);
	  						}
	  					}
	  	
	  				}else{
	  					$idsaleid = $this->insert($datapayment);
	  				}
	  				$old_remain_principal = 0;
	  				$old_pri_permonth = 0;
	  				$old_interest_paymonth = 0;
	  				$old_amount_day = 0;
	  				$from_date=$next_payment;
	  			}
	  		}
	  		if($payment_method==7 AND $data['last_payment']>0){
		  			$old_remain_principal = $data['last_payment'];
		  			$old_pri_permonth = $old_remain_principal;
		  			$old_interest_paymonth=0;
		  			$old_amount_day=0;
		  			$cum_interest=0;
	  			
	  				$this->_name="ln_saleschedule_test";
	  				$datapayment = array(
	  					'sale_id'=>$data['id'],//good
	  					'begining_balance'=> $old_remain_principal,//good
	  					'principal_permonth'=> $old_pri_permonth,//good
	  					'principal_permonthafter'=>$old_pri_permonth-$paid_principal,//good
	  					'total_interest'=>$old_interest_paymonth,//good
	  					'total_interest_after'=>$old_interest_paymonth-$paid_interest,//good
	  					'total_payment'=>($old_pri_permonth+$old_interest_paymonth),//good
	  					'total_payment_after'=>($old_pri_permonth+$old_interest_paymonth)-($paid_principal+$paid_interest),//good
	  					'ending_balance'=>$old_remain_principal-$old_pri_permonth,
	  					'cum_interest'=>$cum_interest,
	  					'amount_day'=>$old_amount_day,
	  					'is_completed'=>0,
	  					'date_payment'=>$next_payment,
	  				);
	  				$this->insert($datapayment);
	  		}
	  		
	  		if($payment_method==3 OR $payment_method==4 OR $payment_method==6 OR $payment_method==5 OR $payment_method==7){
		  		$sql = " SELECT t.* , DATE_FORMAT(t.date_payment, '%d-%m-%Y') AS date_payments,
		  		DATE_FORMAT(t.date_payment, '%Y-%m-%d') AS date_name FROM
		  		ln_saleschedule_test AS t WHERE t.sale_id = ".$data['id']." ORDER BY date_payment ASC,id ASC ";
		  		$rows = $db->fetchAll($sql);
	  		}else{
	  			$sql = " SELECT *,'row_id' FROM ln_sale_test WHERE id = ".$data['id'];
	  			$rows = $db->fetchRow($sql);
	  		}
	  		return $rows;
	  	}catch (Exception $e){
	  		$db->rollBack();
	  		return $e->getMessage();
	  	}
	  }
	  function updateScheculeStatus($data){
	  	$db = $this->getAdapter();
	  	$db->beginTransaction();
	  	try{
	  		$ids =explode(',', $data['identity']);
	  		$this->_name="ln_saleschedule";
	  		
	  		foreach ($ids as $i){
		  			$date= new DateTime($data['payment_date'.$i]);
		  			$next_payment = $date->format("Y-m-d");
  					$datapayment = array(
  						'is_completed'=>$data['payment_option'.$i],
  						'date_payment'=>$next_payment,
  					);
  					if($data['pay_with'.$i]>0){
  						$datapayment['ispay_bank']= $data['pay_with'.$i];
  					}
  					$datapayment['paid_date']=empty($data['paid_date_'.$i])?null:$data['paid_date_'.$i];
					$datapayment['received_date']=empty($data['paid_date_'.$i])?null:$data['paid_date_'.$i];
					$datapayment['received_userid']=empty($data['user_id'.$i])?null:$data['user_id'.$i];
								
  					$where = "id = ".$data['fundid_'.$i];
  					$this->update($datapayment, $where);
	  		}	
			$db->commit();
	  	}catch (Exception $e){
	  		$db->rollBack();
	  	}
	  }
	 
	  public function getALLLoanPayoff($search=null){
	      	$db = $this->getAdapter();
	      	$sql = " SELECT *,
				(SELECT first_name FROM `rms_users` WHERE id=v_getloanpayoff.user_id) AS user_name
	      	FROM v_getloanpayoff WHERE 1 ";
	      	$where ='';
	      	$from_date =(empty($search['start_date']))? '1': " date_pay >= '".$search['start_date']." 00:00:00'";
	      	$to_date = (empty($search['end_date']))? '1': " date_pay <= '".$search['end_date']." 23:59:59'";
	      	$where= " AND ".$from_date." AND ".$to_date;
	      	
	      	$dbp = new Application_Model_DbTable_DbGlobal();
	      	$where.=$dbp->getAccessPermission("branch_id");
	      	
	      	if(!empty($search['advance_search'])){
	
	      		$s_where = array();
	      		$s_search = trim(addslashes($search['advance_search']));
	      		$s_where[] = " `receipt_no` LIKE '%{$s_search}%'";
	      		$s_where[] = " `total_payment` LIKE '%{$s_search}%'";
	      		$s_where[] = " `total_interest` LIKE '%{$s_search}%'";
	      		$where .=' AND ('.implode(' OR ',$s_where).')';
	      	}
	      	if($search['client_name']>0){
	      		$where.=" AND `group_id`= ".$search['client_name'];
	      	}
	      	if($search['branch_id']>0){
	      		$where.=" AND `branch_id`= ".$search['branch_id'];
	      	}
	      	if($search['land_id']>0){
	      		$where.=" AND `land_id`= ".$search['land_id'];
	      	}      	
	      	$order = " GROUP by id ORDER BY id DESC ";
	      	return $db->fetchAll($sql.$where.$order);
      }
      
      public function getPaymentSaleid($sale_id){
      	$db = $this->getAdapter();
      	$dbp = new Application_Model_DbTable_DbGlobal();
      	$sql = $dbp->getCollectPaymentSqlSt();
      	$sql.=" AND crm.status= 1
			AND crm.sale_id= $sale_id ";
      	$sql.=$dbp->getAccessPermission("crm.branch_id");
      	$order=" ORDER BY crm.id DESC ";
      	return $db->fetchAll($sql.$order);
      }
      
      function getAllTranferOwner($search){
      	 
      	$sql="SELECT w.*,
			      	(SELECT project_name FROM `ln_project` WHERE ln_project.br_id=w.branch_id LIMIT 1) AS from_branch,
			      	c.client_number,
			      	c.name_kh,
			      	(SELECT CONCAT(land_address,',',street) FROM `ln_properties` WHERE ln_properties.id=w.house_id LIMIT 1) from_property,
			      	(SELECT cc.name_kh FROM `ln_client` AS cc WHERE cc.client_id=w.to_customer LIMIT 1) AS to_branch,
			      	(SELECT first_name FROM `rms_users` WHERE id=w.user_id) AS user_name
		      	FROM
			      	`ln_change_owner` AS w,
			      	`ln_client` c
		      	WHERE c.client_id=w.from_customer AND w.status=1 ";
      	 
      	$dbp = new Application_Model_DbTable_DbGlobal();
      	$sql.=$dbp->getAccessPermission("w.branch_id");
      	
      	$from_date =(empty($search['start_date']))? '1': " w.change_date >= '".$search['start_date']." 00:00:00'";
      	$to_date = (empty($search['end_date']))? '1': " w.change_date <= '".$search['end_date']." 23:59:59'";
      	$where = " AND ".$from_date." AND ".$to_date;
      	if(!empty($search['adv_search'])){
      		$s_where = array();
      	}
      	if(($search['branch_id'])>0){
      		$where.= " AND w.branch_id=".$search['branch_id'];
      	}
      	if(($search['client_name'])>0){
      		$where.= " AND ".$search['client_name']." IN ( w.from_customer,w.to_customer )";
      	}
      	if ($search['land_id']>0){
      		$where.= " AND w.house_id=".$search['land_id'];
      	}
      	$order = " ORDER BY id DESC ";
      	$db = $this->getAdapter();
      	return $db->fetchAll($sql.$where.$order);
      }
	function getAllChangeHouse($search){
	   	$from_date =(empty($search['start_date']))? '1': " s.change_date >= '".$search['start_date']." 00:00:00'";
	   	$to_date = (empty($search['end_date']))? '1': " s.change_date <= '".$search['end_date']." 23:59:59'";
	   	$where = " AND ".$from_date." AND ".$to_date;
	   	$sql="SELECT cp.id,
	   		(SELECT project_name FROM `ln_project` WHERE ln_project.br_id=cp.from_branchid LIMIT 1) AS from_branch,
		c.name_kh,
		(SELECT CONCAT(land_address,',',street) FROM `ln_properties` WHERE ln_properties.id=cp.from_houseid LIMIT 1) from_property,
		cp.soldprice_before,cp.paid_before,cp.balance_before,
		(SELECT project_name FROM `ln_project` WHERE ln_project.br_id=cp.to_branchid LIMIT 1) AS to_branch,
		(SELECT CONCAT(land_address,',',street) FROM `ln_properties` WHERE ln_properties.id=cp.to_houseid LIMIT 1) to_propertype,
		cp.house_priceafter,cp.discount_percentafter,cp.discount_amountafter,cp.sold_priceafter,cp.balance_after,
		cp.change_date,
		(SELECT  first_name FROM rms_users WHERE id=cp.user_id limit 1 ) AS user_name,
		cp.status
		FROM `ln_change_house` AS cp,`ln_client` c WHERE c.client_id=cp.client_id ";
	   	
	   	$dbp = new Application_Model_DbTable_DbGlobal();
	   	$sql.=$dbp->getAccessPermission("cp.from_branchid");
	   	
	   	$from_date =(empty($search['start_date']))? '1': " cp.change_date >= '".$search['start_date']." 00:00:00'";
	   	$to_date = (empty($search['end_date']))? '1': " cp.change_date <= '".$search['end_date']." 23:59:59'";
	   	$where = " AND ".$from_date." AND ".$to_date;
	   	if(!empty($search['adv_search'])){
	   		$s_where = array();
	//    		$s_search = addslashes(trim($search['adv_search']));
	//    		$s_where[] = " cp.receipt_no LIKE '%{$s_search}%'";
	//    		$s_where[] = " p.land_code LIKE '%{$s_search}%'";
	//    		$s_where[] = " p.land_address LIKE '%{$s_search}%'";
	//    		$s_where[] = " c.client_number LIKE '%{$s_search}%'";
	//    		$s_where[] = " c.name_en LIKE '%{$s_search}%'";
	//    		$s_where[] = " c.name_kh LIKE '%{$s_search}%'";
	//    		$s_where[] = " s.price_sold LIKE '%{$s_search}%'";
	//    		$s_where[] = " s.comission LIKE '%{$s_search}%'";
	//    		$s_where[] = " s.total_duration LIKE '%{$s_search}%'";
	//    		$where .=' AND ( '.implode(' OR ',$s_where).')';
	   	}
	   	if($search['status']>-1){
	   		$where.= " AND cp.status = ".$search['status'];
	   	}
	   	if(($search['client_name'])>0){
	   		$where.= " AND `cp`.`client_id`=".$search['client_name'];
	   	}
	   	if(($search['branch_id'])>0){
	   		$where.= " AND ( cp.from_branchid = ".$search['branch_id']." OR cp.to_branchid = ".$search['branch_id']." )";
	   	}
	   	$order = " ORDER BY id DESC ";
	   	$db = $this->getAdapter();
	   	return $db->fetchAll($sql.$where.$order);
   }
   public function getSaleSummary($search = null){
   	$db = $this->getAdapter();
   	$session_lang=new Zend_Session_Namespace('lang');
   	$lang = $session_lang->lang_id;
   	$str = 'name_en';
   	if($lang==1){
   		$str = 'name_kh';
   	}
   	$from_datePayment =(empty($search['start_date']))? '1': " date_pay >= '".$search['start_date']." 00:00:00'";
   	$to_datePayment = (empty($search['end_date']))? '1': " date_pay <= '".$search['end_date']." 23:59:59'";
   	
   	$from_dateCredit =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
   	$to_dateCredit = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
   	
   	$dbp = new Application_Model_DbTable_DbGlobal();
   	$statement = $dbp->soldreportSqlStatement();//this script make slow because have sum 
//    	,(SELECT SUM(rm.total_principal_permonthpaid+rm.extra_payment) FROM `ln_client_receipt_money` as rm WHERE rm.status=1 AND sale_id=s.id  AND $from_datePayment AND $to_datePayment LIMIT 1) AS paid_amount,
//    	(SELECT SUM(rm.total_interest_permonthpaid) FROM `ln_client_receipt_money` AS rm WHERE rm.status=1  AND sale_id = s.id AND $from_datePayment AND $to_datePayment LIMIT 1) AS total_interest_permonthpaid,
//    	(SELECT SUM(rm.penalize_amountpaid) FROM `ln_client_receipt_money` AS rm WHERE rm.status=1 AND sale_id = s.id AND $from_datePayment AND $to_datePayment LIMIT 1) AS penalize_amountpaid,
//    	(SELECT SUM(total_amount) FROM `ln_credit` WHERE status=1 AND $from_dateCredit AND $to_dateCredit  AND sale_id = s.id LIMIT 1) AS totalAmountCreadit
   	$sql= "
   	SELECT
		  `s`.`id`               AS `id`,
		  (SELECT
		     `ln_project`.`project_name`
		   FROM `ln_project`
		   WHERE (`ln_project`.`br_id` = `s`.`branch_id`)
		   LIMIT 1) AS `branch_name`,
		  `s`.`branch_id`        AS `branch_id`,
		  `s`.`house_id`         AS `house_id`,
		  `s`.`price_before`     AS `price_before`,
		  `s`.`price_sold`       AS `price_sold`,
		  `s`.`discount_amount`  AS `discount_amount`,
		  `s`.`discount_percent` AS `discount_percent`,
		  `s`.`other_discount` AS `other_discount`,
		  s.verify_by,
		  `s`.`buy_date`         AS `buy_date`,
		  `s`.`end_line`         AS `end_line`,
		  `s`.`interest_rate`    AS `interest_rate`,
		  `s`.`total_duration`   AS `total_duration`,
		  `s`.`payment_id`       AS `payment_id`,
		  `s`.`is_cancel`        AS `is_cancel`,
		  `s`.`user_id`          AS `user_id`,
		  `p`.`land_code`        AS `land_code`,
		  `p`.`land_address`     AS `land_address`,
		  `p`.`land_size`        AS `land_size`,
		  `p`.`street`           AS `street`,
		  `c`.`name_kh`          AS `name_kh`,
		  `c`.`name_en`          AS `name_en`,
		  `c`.`phone`            AS `phone`, 
		   	'0' AS totalAmountCreadit,
		   	(SELECT $str FROM `ln_view` WHERE key_code =s.payment_id AND type = 25 limit 1) AS paymenttype,
		   	(SELECT p.old_land_id FROM `ln_properties` AS p WHERE p.id = s.house_id LIMIT 1) AS old_land_id,
		   	(SELECT sta.co_khname FROM ln_staff AS sta WHERE sta.co_id=`s`.`staff_id` LIMIT 1 ) AS agency_name
		   	
   	";
   	$where = $statement['where'];
	$where.=" AND s.is_cancel=0 ";
   	$where.=$dbp->getAccessPermission("s.`branch_id`");
   	$str = '`s`.`buy_date`';
   	if($search['buy_type']>0 AND $search['buy_type']!=2){
   		$str = ' `s`.`agreement_date` ';
   	}
   	if($search['buy_type']==2){
   		$where.=" AND s.payment_id = 1";
   	}
   	if($search['buy_type']==1){
   		$where.=" AND s.payment_id != 1";
   	}
   	$from_date =(empty($search['start_date']))? '1': " $str >= '".$search['start_date']." 00:00:00'";
   	$to_date = (empty($search['end_date']))? '1': " $str <= '".$search['end_date']." 23:59:59'";
   	$where.= " AND ".$from_date." AND ".$to_date;
   	
   	$s_search = addslashes(trim($search['adv_search']));
   	
   	$find = strpos($s_search,">");
   	if ($find === false){//
	   	if(!empty($search['adv_search'])){
	   		$s_where = array();
	   		$s_where[] = " s.receipt_no LIKE '%{$s_search}%'";
	   		$s_where[] = " `p`.`land_code`  LIKE '%{$s_search}%'";
	   		$s_where[] = " `p`.`land_address` LIKE '%{$s_search}%'";
	   		$s_where[] = " `c`.`client_number`  LIKE '%{$s_search}%'";
	   		$s_where[] = " `c`.`name_en`  LIKE '%{$s_search}%'";
	   		$s_where[] = " `c`.`name_kh`  LIKE '%{$s_search}%'";
	   		$s_where[] = " (SELECT
			     `ln_staff`.`co_khname`
			   FROM `ln_staff`
			   WHERE (`ln_staff`.`co_id` = `s`.`staff_id`)
			   LIMIT 1) LIKE '%{$s_search}%'";
	   		$s_where[] = " `s`.`price_sold` LIKE '%{$s_search}%'";
	   		$s_where[] = " `s`.`comission` LIKE '%{$s_search}%'";
	   		$s_where[] = " `s`.`total_duration` LIKE '%{$s_search}%'";
	   		$s_where[] = " `p`.`street` LIKE '%{$s_search}%'";
	   		$where .=' AND ( '.implode(' OR ',$s_where).')';
	   	}
	   	
   	}else{
   		$where.=" AND (SELECT  COUNT(s.id) FROM `ln_sale` AS s WHERE s.status=1  AND s.is_cancel=0 LIMIT 1)  $s_search";
   	}
   	
   	if($search['branch_id']>0){
   		$where.=" AND s.branch_id = ".$search['branch_id'];
   	}
   	if(!empty($search['streetlist']) AND $search['streetlist']>-1){
   		$where.=" AND `p`.`street` = '".$search['streetlist']."'";
   	}
   	if($search['land_id']>0){
   		$where.=" AND ( s.house_id = ".$search['land_id']." OR (SELECT p.old_land_id FROM `ln_properties` AS p WHERE p.id = s.house_id LIMIT 1) LIKE '%".$search['land_id']."%' )";
   	}
   	if($search['property_type']>0 AND $search['property_type']>0){
   		$where.=" AND p.property_type = ".$search['property_type'];
   	}
   	if($search['client_name']!='' AND $search['client_name']>0){
   		$where.=" AND `s`.`client_id` = ".$search['client_name'];
   	}
   	if($search['schedule_opt']>0){
   		if ($search['schedule_opt']==2 OR $search['schedule_opt']==6){
   			$where.=" AND s.payment_id IN (2,6) ";
   		}else{
   			$where.=" AND s.payment_id = ".$search['schedule_opt'];
   		}
   	}
	$search['sale_status'] = empty($search['sale_status'])?0:$search['sale_status'];
   	if($search['sale_status']>0){
   		if($search['sale_status']==1){//full paid
   			$where.=" AND s.price_sold <= ((SELECT COALESCE(SUM(rm.total_principal_permonthpaid+rm.extra_payment),0) FROM `ln_client_receipt_money` as rm WHERE rm.status=1 AND sale_id=s.id  AND $from_datePayment AND $to_datePayment LIMIT 1) + (SELECT COALESCE(SUM(total_amount),0) FROM `ln_credit` WHERE status=1 AND $from_dateCredit AND $to_dateCredit  AND sale_id = s.id LIMIT 1)) ";
   		}else if($search['sale_status']==2){
			$where.=" AND s.price_sold > ((SELECT COALESCE(SUM(rm.total_principal_permonthpaid+rm.extra_payment),0) FROM `ln_client_receipt_money` as rm WHERE rm.status=1 AND sale_id=s.id  AND $from_datePayment AND $to_datePayment LIMIT 1) + (SELECT COALESCE(SUM(total_amount),0) FROM `ln_credit` WHERE status=1 AND $from_dateCredit AND $to_dateCredit  AND sale_id = s.id LIMIT 1) ) ";
   		}else if($search['sale_status']==3){
			$where.=" AND s.is_cancel = 0 ";
		}else if($search['sale_status']==4){
			$where.=" AND s.is_cancel = 1 ";
		}else{
   		}
   	}
   	
   	if (!empty($search['agency_id'])){
//    		$where.=" AND `s`.`staff_id` = '".$search['agency_id']."'";
   		$condiction = $dbp->getChildAgency($search['agency_id']);
   		if (!empty($condiction)){
   			$where.=" AND s.staff_id IN ($condiction)";
   		}else{
   			$where.=" AND s.staff_id=".$search['agency_id'];
   		}
   	}
	   	$order = " ORDER BY s.buy_date DESC ";
		if(!empty($search['queryOrdering'])){
			if($search['queryOrdering']==1){
				$order =" ORDER BY `s`.buy_date ASC ";
			}else if($search['queryOrdering']==2){
				$order =" ORDER BY `s`.buy_date DESC ";
			}else if($search['queryOrdering']==3){
				$order =" ORDER BY `s`.id ASC ";
			}else if($search['queryOrdering']==4){
				$order =" ORDER BY `s`.id DESC ";
			}
			else if($search['queryOrdering']==5){
				$order =" ORDER BY `s`.client_id DESC ";
			}
		}
	   	return $db->fetchAll($sql.$where.$order);
   }
   
   function getAllIncomeOtherDetail($search=null){
   	$db = $this->getAdapter();
   	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
   	$from_date =(empty($search['start_date']))? '1': " oin.date >= '".$search['start_date']." 00:00:00'";
   	$to_date = (empty($search['end_date']))? '1': " oin.date <= '".$search['end_date']." 23:59:59'";
   	$where ="";
   	$where.= " AND ".$from_date." AND ".$to_date;
   
   	$sql=" SELECT 
			oin.id,
			oin.branch_id,
			(SELECT project_name FROM `ln_project` WHERE ln_project.br_id =oin.branch_id LIMIT 1) AS branch_name,
			oin.client_id,
			oin.date,
			oin.invoice,
			s.sale_number,
			p.land_address,
			p.street,
			(SELECT v.name_kh FROM `ln_view`  AS v WHERE v.type =13 AND v.key_code = oin.category_id LIMIT 1) AS category,
			c.name_kh,
			c.sex,
			c.tel,
			oind.description,
			oind.price,
			oind.qty,
			oind.total,
			oind.work_note
		FROM
				`ln_otherincome` AS oin,
				`ln_otherincome_detail` AS oind,
				`ln_sale` AS s,
				`ln_client` AS c,
				`ln_properties` AS p
		WHERE 
			oin.id = oind.income_id
			AND s.id = oin.sale_id
			AND p.id = oin.house_id
			AND c.client_id = oin.client_id
			AND oin.status=1 ";
   
	   	if (!empty($search['adv_search'])){
	   		$s_where = array();
	   		$s_search = trim(addslashes($search['adv_search']));
	   		$s_where[] = " oind.description LIKE '%{$s_search}%'";
	   		$s_where[] = " oind.price LIKE '%{$s_search}%'";
	   		$s_where[] = " s.sale_number LIKE '%{$s_search}%'";
	   		$s_where[] = " oin.invoice LIKE '%{$s_search}%'";
	   		$s_where[] = " p.land_address LIKE '%{$s_search}%'";
	   		$s_where[] = " c.name_kh LIKE '%{$s_search}%'";
	   		$where .=' AND ('.implode(' OR ',$s_where).')';
	   	}
	   	if($search['payment_process']==1){//paid
	   		$where.= " AND is_fullpaid =1 ";
	   	}
	   	if($search['payment_process']==0){//unpaid
	   		$where.= " AND is_fullpaid = 0 ";
	   	}
	   	if(!empty($search['category_id'])){
	   		$where.= " AND oin.category_id = ".$search['category_id'];
	   	}
	   	if($search['client_name']>0){
	   		$where.= " AND oin.client_id = ".$search['client_name'];
	   	}
	   	if($search['branch_id']>-0){
	   		$where.= " AND oin.branch_id = ".$search['branch_id'];
	   	}
	   	$order=" ORDER BY oin.client_id,oin.id DESC ";
	   	return $db->fetchAll($sql.$where.$order);
   }
  
   public function getAllRemainMonth($search = null){
	   	$db = $this->getAdapter();
	   	$session_lang=new Zend_Session_Namespace('lang');
	   	$lang = $session_lang->lang_id;
	   	
	   	$sql = " 
	   	 SELECT  
	   	 id ,
	   	 name_kh,
	   	 phone,
	   	 land_address,
	   	 street,
	   	 price_sold,
	   	 paid_amount,
	   	 total_duration,
	   	(SELECT COUNT(id) FROM `ln_saleschedule` WHERE sale_id=v_soldreport.id AND status=1 AND is_completed=0 ) AS times_remain
	   	FROM v_soldreport WHERE is_cancel=0 
	   	AND (SELECT COUNT(id) FROM `ln_saleschedule` WHERE sale_id=v_soldreport.id AND status=1 AND is_completed=0 LIMIT 1) <=6 
	   	AND (SELECT COUNT(id) FROM `ln_saleschedule` WHERE sale_id=v_soldreport.id AND status=1 AND is_completed=0 LIMIT 1) >0 ";
	   
	   	$where ='';
	   	$str = 'buy_date';
	   	if($search['buy_type']>0 AND $search['buy_type']!=2){
	   		$str = ' agreement_date ';
	   	}
	   	if($search['buy_type']==2){
	   		$where.=" AND v_soldreport.payment_id = 1";
	   	}
	   	if($search['buy_type']==1){
	   		$where.=" AND v_soldreport.payment_id != 1";
	   	}
	   		
	   	if(!empty($search['adv_search'])){
	   		$s_where = array();
	   		$s_search = addslashes(trim($search['adv_search']));
	   		$s_where[] = " receipt_no LIKE '%{$s_search}%'";
	   		$s_where[] = " land_code LIKE '%{$s_search}%'";
	   		$s_where[] = " land_address LIKE '%{$s_search}%'";
	   		$s_where[] = " client_number LIKE '%{$s_search}%'";
	   		$s_where[] = " name_en LIKE '%{$s_search}%'";
	   		$s_where[] = " name_kh LIKE '%{$s_search}%'";
	   		$s_where[] = " staff_name LIKE '%{$s_search}%'";
	   		$s_where[] = " price_sold LIKE '%{$s_search}%'";
	   		$s_where[] = " comission LIKE '%{$s_search}%'";
	   		$s_where[] = " total_duration LIKE '%{$s_search}%'";
	   		$s_where[] = " street LIKE '%{$s_search}%'";
	   		$where .=' AND ( '.implode(' OR ',$s_where).')';
	   		}
	   		if($search['branch_id']>0){
	   		$where.=" AND branch_id = ".$search['branch_id'];
	   		}
	   		if(!empty($search['co_id']) AND $search['co_id']>-1){
	   		$where.=" AND staff_id = ".$search['co_id'];
	   }
	   		if($search['land_id']>0){
	   		$where.=" AND house_id = ".$search['land_id'];
	   }
	   if($search['property_type']>0 AND $search['property_type']>0){
	   		$where.=" AND v_soldreport.property_type = ".$search['property_type'];
	   }
	   if($search['client_name']!='' AND $search['client_name']>0){
	   		$where.=" AND client_id = ".$search['client_name'];
	   }
	   if($search['schedule_opt']>0){
	   		$where.=" AND v_soldreport.payment_id = ".$search['schedule_opt'];
	   }
	   $order = " ORDER BY times_remain DESC,payment_id DESC ";
	   return $db->fetchAll($sql.$where.$order);
   } 
   function getAllIncomeOtherPayment($search=null,$typeRecord=null){
      	$db = $this->getAdapter();
      	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
      	$from_date =(empty($search['start_date']))? '1': " op.for_date >= '".$search['start_date']." 00:00:00'";
      	$to_date = (empty($search['end_date']))? '1': " op.for_date <= '".$search['end_date']." 23:59:59'";
      	$where ="";
      	$where.= " AND ".$from_date." AND ".$to_date;
    
      	$sql=" SELECT
		   	   	op.id,
		   	   	op.branch_id,
		   	   	(SELECT project_name FROM `ln_project` WHERE ln_project.br_id =op.branch_id LIMIT 1) AS branch_name,
		   	   	(SELECT `ln_view`.`name_kh` FROM `ln_view` WHERE `key_code` = `op`.`payment_method`
		         	AND `type` = 2 LIMIT 1) AS `payment_method`,
				(SELECT bank_name FROM `st_bank` WHERE id =op.bank_id LIMIT 1) AS bank,
				`op`.`bank_id` AS bankId,
				`op`.`payment_method` AS payment_id,
		   	   	oi.client_id,
		   	   	op.for_date AS date,
		   	   	op.receipt_no AS invoice,
		   	   	op.title_income,
		   	   	op.cheque,
		   	   	op.total_paid AS total_amount,
		   	   	op.note AS description,
		   	   	(SELECT p.land_address FROM `ln_properties` AS p WHERE p.id=oi.house_id LIMIT 1) AS land_address,
		   	   	(SELECT p.street FROM `ln_properties` AS p WHERE p.id=oi.house_id LIMIT 1) AS street,
		   	   	(SELECT vt.name FROM `ln_view_type` AS vt WHERE vt.id=op.cate_type LIMIT 1) AS typecate,
				(SELECT v.name_kh FROM ln_view AS v WHERE v.type=op.cate_type AND v.key_code=op.category LIMIT 1) AS category,
		   	   	(SELECT ln_client.name_kh FROM `ln_client` WHERE ln_client.client_id =oi.client_id LIMIT 1) AS name_kh,
		   	   	(SELECT ln_client.sex FROM `ln_client` WHERE ln_client.client_id =oi.client_id LIMIT 1) AS sex,
		   	   	(SELECT ln_client.tel FROM `ln_client` WHERE ln_client.client_id =oi.client_id LIMIT 1) AS tel,
		   	   	(SELECT  CONCAT(COALESCE(last_name,''),' ',COALESCE(first_name,'')) FROM rms_users WHERE id=op.user_id LIMIT 1 ) AS user_name,
		   	   	(SELECT ln_view.name_kh FROM `ln_view` WHERE ln_view.type=2 and ln_view.key_code=op.payment_method LIMIT 1) AS payment_type,
		   	   	op.status,
		   	   	op.is_close
	   	   	FROM `ln_otherincomepayment` AS op,
				`ln_otherincome` AS oi
			WHERE oi.id = op.otherincome_id
   	   	";
      	
      	$dbp = new Application_Model_DbTable_DbGlobal();
      	$sql.=$dbp->getAccessPermission("op.branch_id");
      	
    	if (!empty($typeRecord)){
    		$where.= " AND op.cate_type =$typeRecord ";
    	}
      	if (!empty($search['adv_search'])){
      		$s_where = array();
      		$s_search = trim(addslashes($search['adv_search']));
      		$s_where[] = " op.receipt_no LIKE '%{$s_search}%'";
      		$s_where[] = " op.total_paid LIKE '%{$s_search}%'";
      		$s_where[] = " (SELECT v.name_kh FROM ln_view AS v WHERE v.type=op.cate_type AND v.key_code=op.category LIMIT 1) LIKE '%{$s_search}%'";
      		$s_where[] = " (SELECT p.land_address FROM `ln_properties` AS p WHERE p.id=oi.house_id LIMIT 1) LIKE '%{$s_search}%'";
      		$s_where[] = " (SELECT p.street FROM `ln_properties` AS p WHERE p.id=oi.house_id LIMIT 1) LIKE '%{$s_search}%'";
      		$s_where[] = " (SELECT ln_client.name_kh FROM `ln_client` WHERE ln_client.client_id =oi.client_id LIMIT 1) LIKE '%{$s_search}%'";
      		$where .=' AND ('.implode(' OR ',$s_where).')';
      	}
      	if(!empty($search['payment_method'])){
      	   		$where.= " AND op.payment_method = ".$search['payment_method'];
      	}
      	if(!empty($search['category_id'])){
      		$where.= " AND op.category = ".$search['category_id'];
      	}
      	if($search['client_name']>0){
      		$where.= " AND oi.client_id = ".$search['client_name'];
      	}
      	if($search['land_id']>0){
      		$where.= " AND oi.house_id = ".$search['land_id'];
      	}
      	if($search['branch_id']>-0){
      		$where.= " AND op.branch_id = ".$search['branch_id'];
      	}
      	if(!empty($search['user_id']) AND $search['user_id']>-0){
      		$where.= " AND op.user_id = ".$search['user_id'];
      	}
      	if(!empty($search['type'])){
      		if ($search['type']>0){
	      		$where.= " AND op.cate_type = ".$search['type'];
	      		if(!empty($search['category_id'])){
	      			$where.= " AND op.category = ".$search['category_id'];
	      		}
      		}
      	}
		$order=" ORDER BY oi.branch_id DESC, op.for_date DESC ";
		if(!empty($search['queryOrdering'])){
			if($search['queryOrdering']==1){
				$order =" ORDER BY oi.branch_id DESC, `op`.for_date ASC ";
			}else if($search['queryOrdering']==2){
				$order =" ORDER BY oi.branch_id DESC, `op`.for_date DESC ";
			}else if($search['queryOrdering']==3){
				$order =" ORDER BY oi.branch_id DESC, `op`.id ASC ";
			}else if($search['queryOrdering']==4){
				$order =" ORDER BY oi.branch_id DESC, `op`.id DESC ";
			}
		}
		$search['is_closed'] = empty($search['is_closed'])?0:$search['is_closed'];
		if (!empty($search['is_closed'])){
			if($search['is_closed']!=1){
				$search['is_closed']=0;
			}
			$where.= " AND op.is_close = ".$search['is_closed']."";
		}
      	return $db->fetchAll($sql.$where.$order);
      }
      
      function recordhistory($_data){
      	$arr=array();
      	$stringold="";
      	$string="";
      	$dbclient = new Group_Model_DbTable_DbClient();
      	$dbpayment = new Loan_Model_DbTable_DbLoanILPayment();
      	
      	if (!empty($_data['id'])){
      		
      		$row = $dbpayment->getIlPaymentByID($_data['id']);
      		
      		$client = $dbclient->getClientById($row['client_id']);
      		$stringold="Receipt No : ".$row['receipt_no']."<br />";
      		$stringold.="ថ្ងៃត្រូវបង់/Date Payment : ".$row['date_pay']."<br />";
      		$stringold.="ថ្ងៃទទួល/Date Receive : ".$row['date_input']."<br />";
      		$stringold.="Customer : id=".$row['client_id']."-".$client['name_kh']."<br />";
      		$stringold.="Property Code : ".$row['land_id']."<br />";
      		$stringold.="បង់លើកទី : ".$row['payment_times']."<br />";
      
      		$stringold.="តម្លៃផ្ទះ : ".$row['selling_price']."<br />";
      		$stringold.="ប្រាក់បានបង់សរុប : ".$row['allpaid_before']."<br />";
      		$stringold.="ប្រាក់នៅសល់ : ".$row['balance']."<br />";
      		
      		$stringold.="ប្រាក់ដើមត្រូវបង់ : ".$row['total_principal_permonthpaid']."<br />";
      		$stringold.="ប្រាក់ការ : ".$row['total_interest_permonthpaid']."<br />";
      		$stringold.="ប្រាក់ពិន័យ : ".$row['penalize_amountpaid']."<br />";
      		$stringold.="ប្រាក់បង់បន្ថែម : ".$row['extra_payment']."<br />";
      		$stringold.="ប្រាក់ត្រូវបង់ : ".$row['total_payment']."<br />";
      		$stringold.="ប្រាក់បានបង់ : ".$row['recieve_amount']."<br />";
      		$payment="";
      		if ($row['payment_method']==1){
      			$payment="សាច់ប្រាក់";
      		}else if ($row['payment_method']==3){
      			$payment="សែក";
      		}else if ($row['payment_method']==2){
      			$payment="ធនាគារ";
      		}
      		$paymenttype="";
      		if ($row['payment_option']==1){
      			$payment="បង់ធម្មតា";
      		}else if ($row['payment_option']==3){
      			$payment="រំលស់ដើម";
      		}else if ($row['payment_option']==4){
      			$payment="បង់ផ្តាច់១០០%";
      		}
      		$stringold.="បង់ជា : ".$row['payment_method']."-".$payment."<br />";
      		$stringold.="ប្រភេទបង់ : ".$row['payment_option']."-".$paymenttype."<br />";
      
      		$client = $dbclient->getClientById($_data['customer_id']);
      		$string="Receipt No : ".$_data['receipt_no']."<br />";
      		$string.="ថ្ងៃត្រូវបង់/Date Payment : ".$_data['date_pay']."<br />";
      		$string.="ថ្ងៃទទួល/Date Receive : ".$_data['date_input']."<br />";
      		$string.="Customer : id=".$_data['customer_id']."-".$client['name_kh']."<br />";
      		$string.="Property Code : ".$_data['house_no']."<br />";
      		$string.="បង់លើកទី : ".$_data['paid_times']."<br />";
      
      		$string.="តម្លៃផ្ទះ : ".$_data['price_sold']."<br />";
      		$string.="ប្រាក់បានបង់សរុប : ".$_data['all_paid']."<br />";
      		$string.="ប្រាក់នៅសល់ : ".$_data['balance']."<br />";
      		
      		$string.="ប្រាក់ដើមត្រូវបង់ : ".$_data['total_principal_permonth']."<br />";
      		$string.="ប្រាក់ការ : ".$_data['total_interest_permonthpaid']."<br />";
      		$string.="ប្រាក់ពិន័យ : ".$_data['penalize_amountpaid']."<br />";
      		$string.="ប្រាក់បង់បន្ថែម : ".$_data['extra_payment']."<br />";
      		$string.="ប្រាក់ត្រូវបង់ : ".$_data['total_payment']."<br />";
      		$string.="ប្រាក់បានបង់ : ".$_data['recieve_amount']."<br />";
      		$payment="";
      		if ($_data['payment_method']==1){
      			$payment="សាច់ប្រាក់";
      		}else if ($_data['payment_method']==3){
      			$payment="សែក";
      		}else if ($_data['payment_method']==2){
      			$payment="ធនាគារ";
      		}
      		if ($_data['pay_type']==1){
      			$paymenttype="ដាក់ប្រាក់កក់";
      		}else if ($_data['pay_type']==3){
      			$paymenttype="បង់ធម្មតា";
      		}
      		$string.="បង់ជា : ".$_data['payment_method']."-".$payment."<br />";
      		$string.="ប្រភេទបង់ : ".$_data['pay_type']."-".$paymenttype."<br />";
      
      	}else{
      		$string="";
      		$stringold="";
      	}
      	$arr['activityold']=$stringold;
      	$arr['after_edit_info']=$string;
      	return $arr;
      }
      
      function getAllTransferCash($search){
      	$sql="SELECT cp.id,
		      	(SELECT project_name FROM `ln_project` WHERE ln_project.br_id=cp.branch_id LIMIT 1) AS from_branch,
		      	c.name_kh,
		      	(SELECT CONCAT(land_address,',',street) FROM `ln_properties` WHERE ln_properties.id=cp.from_property LIMIT 1) from_property,
		      	from_paid,
		      	(SELECT project_name FROM `ln_project` WHERE ln_project.br_id=cp.to_branch LIMIT 1) AS to_branch,
		      	(SELECT CONCAT(land_address,',',street) FROM `ln_properties` WHERE ln_properties.id=cp.to_property LIMIT 1) to_propertype,
		      	cp.trafer_date,cp.status
      	FROM `ln_transfercash` AS cp,
      		`ln_client` c
      	WHERE c.client_id=cp.from_clientid ";
      
      	$from_date =(empty($search['start_date']))? '1': " cp.trafer_date >= '".$search['start_date']." 00:00:00'";
      	$to_date = (empty($search['end_date']))? '1': " cp.trafer_date <= '".$search['end_date']." 23:59:59'";
      	$where = " AND ".$from_date." AND ".$to_date;
      	if(!empty($search['adv_search'])){
      		$s_where = array();
      		   	$s_search = addslashes(trim($search['adv_search']));
      		   	$s_where[] = " (SELECT CONCAT(land_address,',',street) FROM `ln_properties` WHERE ln_properties.id=cp.from_property LIMIT 1) LIKE '%{$s_search}%'";
      		   	$s_where[] = " c.name_kh LIKE '%{$s_search}%'";
      		   	$s_where[] = " (SELECT CONCAT(land_address,',',street) FROM `ln_properties` WHERE ln_properties.id=cp.to_property LIMIT 1) LIKE '%{$s_search}%'";
      		   	$s_where[] = " cp.from_paid LIKE '%{$s_search}%'";
      		$where .=' AND ( '.implode(' OR ',$s_where).')';
      	}
      	if(($search['client_name'])>0){
      		$where.= " AND cp.from_clientid=".$search['client_name'];
      	}
      	if(($search['branch_id'])>0){
      		$where.= " AND ( cp.branch_id = ".$search['branch_id']." OR cp.branch_id = ".$search['branch_id']." )";
      	}
      	$order = " ORDER BY id DESC ";
      	$dbp = new Application_Model_DbTable_DbGlobal();
      	$where.=$dbp->getAccessPermission("cp.branch_id");
      
      	$db = $this->getAdapter();
      	return $db->fetchAll($sql.$where.$order);
      }
      public function getSaleCondiction($search = null){//rpt-loan-released/
      	$db = $this->getAdapter();
      	$session_lang=new Zend_Session_Namespace('lang');
      	$lang = $session_lang->lang_id;
      	$str = 'name_en';
      	if($lang==1){
      		$str = 'name_kh';
      	}
      	$from_date =(empty($search['start_date']))? '1': " date_pay >= '".$search['start_date']." 00:00:00'";
      	$to_date = (empty($search['end_date']))? '1': " date_pay <= '".$search['end_date']." 23:59:59'";
      	$dbp = new Application_Model_DbTable_DbGlobal();
      	$statement = $dbp->soldreportSqlStatement();
      	$sql= $statement['sql'];
      	$sql.="
      	 
      	,(SELECT SUM(rm.total_principal_permonthpaid+rm.extra_payment) FROM `ln_client_receipt_money` as rm WHERE rm.status=1 AND sale_id=s.id  AND $from_date AND $to_date LIMIT 1) AS paid_amount,
      	(SELECT SUM(rm.total_interest_permonthpaid) FROM `ln_client_receipt_money` AS rm WHERE rm.status=1  AND sale_id = s.id AND $from_date AND $to_date LIMIT 1) AS total_interest_permonthpaid,
      	(SELECT SUM(rm.penalize_amountpaid) FROM `ln_client_receipt_money` AS rm WHERE rm.status=1 AND sale_id = s.id AND $from_date AND $to_date LIMIT 1) AS penalize_amountpaid,
      
      	(SELECT next_date_deposit FROM `ln_client_receipt_money` AS rm WHERE rm.status=1 AND sale_id = s.id AND field3=1 AND payment_times=1 ORDER BY payment_times ASC LIMIT 1) AS next_date_deposit_agreement,
      	
      	(SELECT COUNT(id) FROM `ln_saleschedule` WHERE sale_id=s.id AND status=1 ) AS times,
      	(SELECT first_name FROM `rms_users` WHERE id=s.user_id LIMIT 1) AS user_name,
      	(SELECT $str FROM `ln_view` WHERE key_code =s.payment_id AND type = 25 limit 1) AS paymenttype,
      	(SELECT p.old_land_id FROM `ln_properties` AS p WHERE p.id = s.house_id LIMIT 1) AS old_land_id,
      	(SELECT sta.co_khname FROM ln_staff AS sta WHERE sta.co_id=`s`.`staff_id` LIMIT 1 ) AS agency_name,
      	(SELECT $str FROM `ln_view` WHERE key_code =s.pre_schedule_opt AND type = 25 limit 1) AS pre_schedule_opt_title,
      	s.pre_fix_payment,
      	s.pre_percent_payment,
      	s.pre_amount_month,
      	s.pre_percent_installment,
      	s.pre_amount_year
      	";
      	$where = $statement['where'];
      	$where.=" AND s.is_cancel=0 ";
//       	$where.=" AND s.payment_id = 1 ";
      	$where.=" AND s.pre_schedule_opt > 0 ";
      	$where.=$dbp->getAccessPermission("s.`branch_id`");
      	
      $str = '`s`.`buy_date`';
      $from_date =(empty($search['start_date']))? '1': " $str >= '".$search['start_date']." 00:00:00'";
      $to_date = (empty($search['end_date']))? '1': " $str <= '".$search['end_date']." 23:59:59'";
      $where.= " AND ".$from_date." AND ".$to_date;
      if(!empty($search['adv_search'])){
      $s_where = array();
      $s_search = addslashes(trim($search['adv_search']));
      $s_where[] = " s.receipt_no LIKE '%{$s_search}%'";
      $s_where[] = " `p`.`land_code`  LIKE '%{$s_search}%'";
      $s_where[] = " `p`.`land_address` LIKE '%{$s_search}%'";
      $s_where[] = " `c`.`client_number`  LIKE '%{$s_search}%'";
      $s_where[] = " `c`.`name_en`  LIKE '%{$s_search}%'";
      $s_where[] = " `c`.`name_kh`  LIKE '%{$s_search}%'";
      $s_where[] = " (SELECT
      `ln_staff`.`co_khname`
      		FROM `ln_staff`
      		WHERE (`ln_staff`.`co_id` = `s`.`staff_id`)
      			LIMIT 1) LIKE '%{$s_search}%'";
      			$s_where[] = " `s`.`price_sold` LIKE '%{$s_search}%'";
      			$s_where[] = " `s`.`comission` LIKE '%{$s_search}%'";
      			$s_where[] = " `s`.`total_duration` LIKE '%{$s_search}%'";
      			$s_where[] = " `p`.`street` LIKE '%{$s_search}%'";
      			$where .=' AND ( '.implode(' OR ',$s_where).')';
      		}
      		if($search['branch_id']>0){
      		$where.=" AND s.branch_id = ".$search['branch_id'];
      		}
      		if(!empty($search['streetlist']) AND $search['streetlist']>-1){
      		$where.=" AND `p`.`street` = '".$search['streetlist']."'";
      		}
      		if($search['land_id']>0){
      		$where.=" AND ( s.house_id = ".$search['land_id']." OR (SELECT p.old_land_id FROM `ln_properties` AS p WHERE p.id = s.house_id LIMIT 1) LIKE '%".$search['land_id']."%' )";
      		}
      		if($search['property_type']>0 AND $search['property_type']>0){
      		$where.=" AND p.property_type = ".$search['property_type'];
      		}
      		if($search['client_name']!='' AND $search['client_name']>0){
      		$where.=" AND `s`.`client_id` = ".$search['client_name'];
      		}
      		
      		if (!empty($search['agency_id'])){
      			$condiction = $dbp->getChildAgency($search['agency_id']);
      			if (!empty($condiction)){
      				$where.=" AND s.staff_id IN ($condiction)";
      			}else{
      				$where.=" AND s.staff_id=".$search['agency_id'];
      			}
      		}
      		if($search['schedule_opt']>0){
      				$where.=" AND s.pre_schedule_opt = ".$search['schedule_opt'];
      		}
      			$order = " ORDER BY s.buy_date DESC ";
      			return $db->fetchAll($sql.$where.$order);
      		}
      		
      	public function updateNoteExpectIncome($data){
      			$db = $this->getAdapter();
      			$db->beginTransaction();
      			try{
      		
      				$arr = array(
      						'expect_income_note'=>$data['noted'],
      				);
      				$where=" id = ".$data['id'];
      				$this->_name="ln_sale";
      				$this->update($arr, $where);
      		
      				$db->commit();
      				return 1;
      			}catch (Exception $e){
      				$err =$e->getMessage();
      				Application_Model_DbTable_DbUserLog::writeMessageError($err);
      				$db->rollBack();
      			}
      	}
      	
      	public function getCreditBySaleid($sale_id){
      		$db = $this->getAdapter();
      		$dbp = new Application_Model_DbTable_DbGlobal();
      		$sql=" 
      			SELECT
					cd.*,
					(SELECT
			     `ln_project`.`project_name`
			   FROM `ln_project`
			   WHERE (`ln_project`.`br_id` = `cd`.`branch_id`)
			   LIMIT 1) AS `branch_name`,
					`c`.`client_id`                      AS `client_id`,
					`c`.`client_number`                  AS `client_number`,
					`c`.`name_kh`                        AS `name_kh`,
					`c`.`name_en`                        AS `client_name`,
					`l`.`land_code`                      AS `land_code`,
					`l`.`land_address`                   AS `land_address`,
					`l`.`land_size`                      AS `land_size`,
					`l`.`street`                         AS `street`,
					`l`.`id`                             AS `hous_id`,
					`cd`.`payment_id`,
					 (SELECT
			     `ln_view`.`name_kh`
			   FROM `ln_view`
			   WHERE ((`ln_view`.`key_code` = `cd`.`payment_id`)
			          AND (`ln_view`.`type` = 2))
			   LIMIT 1) AS `payment_method`,
					(SELECT first_name FROM `rms_users` WHERE id=cd.user_id LIMIT 1) AS user_name
					FROM ln_credit AS cd,
						ln_sale AS sl,
						ln_properties AS l,
						ln_client AS c
					WHERE sl.id = cd.sale_id 
					AND cd.client_id = c.client_id
					AND l.id = sl.house_id
      				AND cd.sale_id= $sale_id ";
      		$sql.=$dbp->getAccessPermission("cd.branch_id");
      		$order=" ORDER BY cd.id DESC ";
      		return $db->fetchAll($sql.$order);
      	}
		
	function getAllIncludeMaterial($search=null){
		$db = $this->getAdapter();
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$tr= Application_Form_FrmLanguages::getCurrentlanguage();
    	$givedLabel = $tr->translate("GIVED_TO_CUSTOMER");
		$notYerGiveLabel = $tr->translate("NOT_YET_GIVE");
		
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		$from_date =(empty($search['start_date']))? '1': " for_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " for_date <= '".$search['end_date']." 23:59:59'";
		$where = " WHERE ".$from_date." AND ".$to_date;
		
		$sql=" SELECT ic.*,
		(SELECT project_name FROM `ln_project` WHERE ln_project.br_id =ic.branch_id LIMIT 1) AS branch_name,
		(SELECT name_kh FROM `ln_client` WHERE ln_client.client_id =ic.client_id LIMIT 1) AS client_name,
		(SELECT phone FROM `ln_client` WHERE ln_client.client_id =ic.client_id LIMIT 1) AS tel,
		(SELECT CONCAT(land_address,',',street) FROM `ln_properties` WHERE id=ic.house_id LIMIT 1) AS house_no,
		(SELECT  first_name FROM rms_users WHERE id=ic.user_id LIMIT 1 ) AS user_name,
		(SELECT title FROM `ln_items_material` WHERE ln_items_material.id =icd.items_id LIMIT 1) AS itmesTitle,
		icd.description as descriptionDetailrow,
		icd.is_gived,
		CASE    
					WHEN  is_gived = 0 THEN '".$notYerGiveLabel."'
					WHEN  is_gived = 1 THEN '".$givedLabel."'
				END AS isGiveLabel
		";
		
		$sql.=" FROM ln_material_include AS ic,
			ln_material_include_detail AS icd
		";
		
		$where.=" AND icd.materailinc_id=ic.id ";
		
		if (!empty($search['adv_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['adv_search']));
			$s_where[] = " ic.description LIKE '%{$s_search}%'";
			$s_where[] = " ic.house_id LIKE '%{$s_search}%'";
			$s_where[] = " ic.invoice LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT name_kh FROM `ln_client` WHERE ln_client.client_id =ic.client_id LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT land_address FROM `ln_properties` WHERE id=ic.house_id LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT street FROM `ln_properties` WHERE id=ic.house_id LIMIT 1) LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		
		if(!empty($search['land_id']) AND $search['land_id']>-1){
			$where.= " AND ic.house_id = ".$search['land_id'];
		}
		if($search['client_name']>0){
			$where.= " AND ic.client_id = ".$search['client_name'];
		}
		if($search['branch_id']>-0){
			$where.= " AND ic.branch_id = ".$search['branch_id'];
		}
		if(!empty($search['items_id'])){
			$where.= " AND icd.items_id = ".$search['items_id'];
		}
		if($search['is_gived']>-1){
			$where.= " AND icd.is_gived = ".$search['is_gived'];
		}
		$where.=$dbp->getAccessPermission("ic.branch_id");
		
		$order=" ORDER by ic.id desc ";
		return $db->fetchAll($sql.$where.$order);
	}
	
	public function getTotalPaymentByYear($search=null){
      	$year = date("Y-m",strtotime($search['start_date']));
      	$db = $this->getAdapter();
    	
    	$sql = "SELECT SUM(cr.recieve_amount) AS totalAmount,
				DATE_FORMAT(cr.date_pay,'%m') AS monthIndex 
				 FROM `ln_client_receipt_money` AS cr 
				WHERE 1 
				";    	

		$startDate=date("Y-m-d",strtotime($year."-01"));
		$endDate=date("Y-m-t",strtotime($year));
		
		$from_date	=" cr.date_pay >= '".$startDate." 00:00:00'";
		$to_date	=" cr.date_pay <= '".$endDate." 23:59:59'";
		$sql.= " AND ".$from_date." AND ".$to_date;
		
    	if(!empty($search['branch_id'])){
    		$sql.=" AND cr.branch_id = ".$search['branch_id'];
    	}
    	$sql.=" GROUP BY DATE_FORMAT(cr.date_pay,'%m') ";
    	return $db->fetchRow($sql);
      }
	 public function getTotalOtherIncomeByYear($search=null){
      	$year = date("Y-m",strtotime($search['start_date']));
      	$db = $this->getAdapter();
    	
    	$sql = "SELECT SUM(cr.total_amount) AS totalAmount,
				DATE_FORMAT(cr.date,'%m') AS monthIndex 
				 FROM `ln_income` AS cr 
				WHERE 1 
				";    	

		$startDate=date("Y-m-d",strtotime($year."-01"));
		$endDate=date("Y-m-t",strtotime($year));
		
		$from_date	=" cr.date >= '".$startDate." 00:00:00'";
		$to_date	=" cr.date <= '".$endDate." 23:59:59'";
		$sql.= " AND ".$from_date." AND ".$to_date;
		
    	if(!empty($search['branch_id'])){
    		$sql.=" AND cr.branch_id = ".$search['branch_id'];
    	}
    	$sql.=" GROUP BY DATE_FORMAT(cr.date,'%m') ";
    	return $db->fetchRow($sql);
     }
	 function groupByYear($search=null){
		 $db = $this->getAdapter();
		 $sql = "	SELECT
						DATE_FORMAT(cr.date_pay,'%Y') AS yearIndex 
					FROM `ln_client_receipt_money` AS cr 
					GROUP BY DATE_FORMAT(cr.date_pay,'%y') ORDER BY DATE_FORMAT(cr.date_pay,'%y') DESC
				";    
    	$row = $db->fetchAll($sql);
		if(empty($row)){
			$row = array('yearIndex'=>date("Y"));
		}else{
			$newYear = $row[0]['yearIndex']+1;
     		array_unshift($row, array ( 'yearIndex' => $newYear) );
		}
		return $row;
	 }
	 function getAllPropertiesprice($search=null){
	 	$db = $this->getAdapter();
	 	$to_enddate = (empty($search['end_date']))? '1': " s.buy_date <= '".$search['end_date']." 23:59:59'";
	 	$sql = "SELECT p.`id`,
			 	(SELECT project_name FROM ln_project WHERE br_id = pr.`branch_id` limit 1) AS branch_name,
			 	p.`land_code`,p.`land_address`,p.`property_type`,p.`street`,
			 	(SELECT t.type_nameen FROM `ln_properties_type` AS t WHERE t.id = p.`property_type` LIMIT 1) AS pro_type,
			 	
			 	pr.`old_landprice`,
	 			pr.old_houseprice,
	 	pr.old_price,
	 	pr.land_price,pr.house_price,pr.price,pr.note,pr.update_date, ";
	 
	 	$sql.=" (SELECT first_name FROM `rms_users` WHERE id=pr.user_id LIMIT 1) AS user_name
	 			FROM `ln_properties` AS p,
	 			`ln_property_price` AS pr
				 	 WHERE 
	 			p.id=pr.property_id
	 			AND p.`status`=1 ";
	 
	 			$from_date =(empty($search['start_date']))? '1': " update_date >= '".$search['start_date']." 00:00:00'";
	 			$to_date = (empty($search['end_date']))? '1': " update_date <= '".$search['end_date']." 23:59:59'";
	 			$where=" AND ".$from_date." AND ".$to_date;
	 
	 			$dbp = new Application_Model_DbTable_DbGlobal();
	 			$sql.=$dbp->getAccessPermission("p.`branch_id`");
	 
	 			if(!empty($search['property_type'])){
	 	$where.= " AND p.`property_type` = ".$search['property_type'];
	 	}
// 	 	if($search['type_property_sale']>-1){
// 	 		$where.= " AND p.`is_lock` = ".$search['type_property_sale'];
// 	 	}
	 if($search['branch_id']>0){
	 	$where.= " AND p.`branch_id` = ".$search['branch_id'];
	 }
	 if(!empty($search['adv_search'])){
		 $s_where=array();
		 $s_search= addslashes(trim($search['adv_search']));
		 $s_where[]=" p.`land_address` LIKE '%{$s_search}%'";
		 $s_where[]=" p.street LIKE '%{$s_search}%'";
		 $where.=' AND ('.implode(' OR ',$s_where).')';
	 }
	 if(!empty($search['streetlist'])){
	 	$where.= " AND street ='".$search['streetlist']."'";
	 }
	 
	 $where.= " ORDER BY p.branch_id,p.`property_type`,p.`street` ASC, LENGTH(land_address), land_address ASC  ";
// 	
	 
	 return $db->fetchAll($sql.$where);
	 }
	 
	 function submitUnclosingEngry($data){
      	$db = $this->getAdapter();
      	if(!empty($data['id_selected'])){
      		$ids = explode(',', $data['id_selected']);
      		$key = 1;
      		$arr = array(
      				"is_closed"=>0,
      		);
      		foreach ($ids as $i){
      			$this->_name="ln_client_receipt_money";
      			if (!empty($data['note_'.$i])){
      				$arr['closing_note']=$data['note_'.$i];
      			}
      			$where="id= ".$data['id_'.$i];
      			$this->update($arr, $where);
      		}
      	}
	}
	
	function getPayemtTotalByBankList(){
		$db = $this->getAdapter();
		$sql="
			SELECT 
				b.id
				,b.bank_name AS bankName
				,'0' AS totalBankRecieve
				,'0' AS totalCheckRecieve
		";
		$sql.=" FROM `st_bank` AS b ";
		$sql.=" WHERE b.status = 1 ";
		$sql.=" ORDER BY b.bank_name ASC ";
		return $db->fetchAll($sql);
	}
	
	
	function getSummaryDailyOperation($data){
		
		$expenseFeatureList = EXPENSE_FEATURE_LIST;
		$row = array();
		$rsPayment = $this->getSummaryDailyPayment($data);
		
		if(!empty($rsPayment)){
			$row = (object) array_merge((array) $row, (array) $rsPayment);
			$row = (array) $row;//sort by key Value DESC
			usort($row, function ($a, $b) {return $a['recordDate'] < $b['recordDate'];});
		}
		
		$rsOtherIncome = $this->getSummaryDailyIncomeOther($data);
		if(!empty($rsOtherIncome)){
			$row = (object) array_merge((array) $row, (array) $rsOtherIncome);
			$row = (array) $row;//sort by key Value DESC
			usort($row, function ($a, $b) {return $a['recordDate'] < $b['recordDate'];});
		}
		
		$data["repairHouse"] = 12;
		$rsIncomeRepairHouse = $this->getSummaryDailyIncomeRepairHouse($data);
		if(!empty($rsIncomeRepairHouse)){
			$row = (object) array_merge((array) $row, (array) $rsIncomeRepairHouse);
			$row = (array) $row;//sort by key Value DESC
			usort($row, function ($a, $b) {return $a['recordDate'] < $b['recordDate'];});
		}
		
		$data["repairHouse"] = 13;
		$rsExpenseRepairHouse = $this->getSummaryDailyIncomeRepairHouse($data);
		if(!empty($rsExpenseRepairHouse)){
			$row = (object) array_merge((array) $row, (array) $rsExpenseRepairHouse);
			$row = (array) $row;//sort by key Value DESC
			usort($row, function ($a, $b) {return $a['recordDate'] < $b['recordDate'];});
		}
		
		if($expenseFeatureList==1){
			$rsExpensePmt = $this->getSummaryDailyExpensePayment($data);
			if(!empty($rsExpensePmt)){
				$row = (object) array_merge((array) $row, (array) $rsExpensePmt);
				$row = (array) $row;//sort by key Value DESC
				usort($row, function ($a, $b) {return $a['recordDate'] < $b['recordDate'];});
			}
		}else{
			$rsExpense = $this->getSummaryDailyExpenseOther($data);
			if(!empty($rsExpense)){
				$row = (object) array_merge((array) $row, (array) $rsExpense);
				$row = (array) $row;//sort by key Value DESC
				usort($row, function ($a, $b) {return $a['recordDate'] < $b['recordDate'];});
			}
		}
		
		$rsExpenseCommision = $this->getSummaryDailyExpenseComission($data);
		if(!empty($rsExpenseCommision)){
			$row = (object) array_merge((array) $row, (array) $rsExpenseCommision);
			$row = (array) $row;//sort by key Value DESC
			usort($row, function ($a, $b) {return $a['recordDate'] < $b['recordDate'];});
		}
		
		$rsExpenseCommisionPmt = $this->getSummaryDailyExpenseComissionPayment($data);
		if(!empty($rsExpenseCommisionPmt)){
			$row = (object) array_merge((array) $row, (array) $rsExpenseCommisionPmt);
			$row = (array) $row;//sort by key Value DESC
			usort($row, function ($a, $b) {return $a['recordDate'] < $b['recordDate'];});
		}
		
		return $row;
	}
	function getSummaryDailyPayment($data){
		$db = $this->getAdapter();
		$sql="
			SELECT 
				crm.date_pay
				,crm.date_pay AS recordDate
				,'paymentRecord' AS recordType
				,SUM(crm.total_principal_permonthpaid) AS totalPinciple
				,SUM(crm.extra_payment) AS totalExtraPayment
				,SUM(crm.total_interest_permonthpaid) AS totalInterest
				,SUM(crm.penalize_amountpaid) AS totalPenalize
				,SUM(crm.recieve_amount) AS totalRecieve
				,'0' AS totalIncomeOther
				,'0' AS totalIncomeRepairHouse
				,'0' AS totalOtherExpense
				,'0' AS totalExpenseRepairHouse
				,'0' AS totalExpenseComission
				,'0' AS totalExpenseComissionPayment
		";
		$sql.=" FROM `ln_client_receipt_money` AS crm  ";
		$sql.=" WHERE 1 ";
		
		if(!empty($data['branch_id'])){
			$sql.= " AND crm.`branch_id` = ".$data['branch_id'];
		}
		$fromDate =(empty($data['start_date']))? '1': " crm.date_pay  >= '".$data['start_date']." 00:00:00'";
		$toDate = (empty($data['end_date']))? '1': " crm.date_pay  <= '".$data['end_date']." 23:59:59'";
		$sql.= " AND ".$fromDate." AND ".$toDate;
		 
		$sql.=" GROUP BY crm.date_pay 
				ORDER BY crm.date_pay ASC 
			";
		return $db->fetchAll($sql);
	}
	function getSummaryDailyIncomeOther($data){
		$db = $this->getAdapter();
		$sql="
			SELECT 
				inc.date
				,inc.date AS recordDate
				,'incomeOther' AS recordType
				,'0' AS totalPinciple
				,'0' AS totalExtraPayment
				,'0' AS totalInterest
				,'0' AS totalPenalize
				,'0' AS totalRecieve
				,SUM(inc.total_amount) AS totalIncomeOther
				,'0' AS totalIncomeRepairHouse
				,'0' AS totalOtherExpense
				,'0' AS totalExpenseRepairHouse
				,'0' AS totalExpenseComission
				,'0' AS totalExpenseComissionPayment
		";
		$sql.=" FROM `ln_income` AS inc  ";
		$sql.=" WHERE inc.status = 1 ";
		
		if(!empty($data['branch_id'])){
			$sql.= " AND inc.`branch_id` = ".$data['branch_id'];
		}
		$fromDate =(empty($data['start_date']))? '1': " inc.date  >= '".$data['start_date']." 00:00:00'";
		$toDate = (empty($data['end_date']))? '1': " inc.date  <= '".$data['end_date']." 23:59:59'";
		$sql.= " AND ".$fromDate." AND ".$toDate;
		
		$sql.=" GROUP BY inc.date 
				ORDER BY inc.date ASC 
			";
		return $db->fetchAll($sql);
	}
	function getSummaryDailyIncomeRepairHouse($data){
		$db = $this->getAdapter();
		
		$repairHouseType = empty($data["repairHouse"]) ? 13 : $data["repairHouse"];
		$recordType = 'expenseRepairHouse';
		$expenseSum = 'SUM(inc.total_paid)';
		$incomeSum = "'0'";
		if($repairHouseType==12){
			$recordType = 'incomeRepairHouse';
			$expenseSum = "'0'";
			$incomeSum = 'SUM(inc.total_paid)';
		}
		
		$sql="
			SELECT 
				inc.for_date
				,inc.for_date AS recordDate
				,'".$recordType."' AS recordType
				,'0' AS totalPinciple
				,'0' AS totalExtraPayment
				,'0' AS totalInterest
				,'0' AS totalPenalize
				,'0' AS totalRecieve
				,'0' AS totalIncomeOther
				,".$incomeSum." AS totalIncomeRepairHouse
				,'0' AS totalOtherExpense
				,".$expenseSum." AS totalExpenseRepairHouse
				,'0' AS totalExpenseComission
				,'0' AS totalExpenseComissionPayment
		";
		$sql.=" FROM `ln_otherincomepayment` AS inc  ";
		$sql.=" WHERE inc.status = 1 AND inc.cate_type = $repairHouseType ";
		
		if(!empty($data['branch_id'])){
			$sql.= " AND inc.`branch_id` = ".$data['branch_id'];
		}
		$fromDate =(empty($data['start_date']))? '1': " inc.for_date  >= '".$data['start_date']." 00:00:00'";
		$toDate = (empty($data['end_date']))? '1': " inc.for_date  <= '".$data['end_date']." 23:59:59'";
		$sql.= " AND ".$fromDate." AND ".$toDate;
		
		$sql.=" GROUP BY inc.for_date 
				ORDER BY inc.for_date ASC 
			";
		return $db->fetchAll($sql);
	}
	function getSummaryDailyExpenseOther($data){
		$db = $this->getAdapter();
		$sql="
			SELECT 
				ex.date
				,ex.date AS recordDate
				,'expenseOther' AS recordType
				,'0' AS totalPinciple
				,'0' AS totalExtraPayment
				,'0' AS totalInterest
				,'0' AS totalPenalize
				,'0' AS totalRecieve
				,'0' AS totalIncomeOther
				,'0' AS totalIncomeRepairHouse
				,SUM(ex.total_amount) AS totalOtherExpense
				,'0' AS totalExpenseRepairHouse
				,'0' AS totalExpenseComission
				,'0' AS totalExpenseComissionPayment
		";
		$sql.=" FROM `ln_expense` AS ex  ";
		$sql.=" WHERE ex.status = 1 ";
		
		if(!empty($data['branch_id'])){
			$sql.= " AND ex.`branch_id` = ".$data['branch_id'];
		}
		$fromDate =(empty($data['start_date']))? '1': " ex.date  >= '".$data['start_date']." 00:00:00'";
		$toDate = (empty($data['end_date']))? '1': " ex.date  <= '".$data['end_date']." 23:59:59'";
		$sql.= " AND ".$fromDate." AND ".$toDate;
		
		$sql.=" GROUP BY ex.date 
				ORDER BY ex.date ASC 
			";
		return $db->fetchAll($sql);
	}
	
	function getSummaryDailyExpensePayment($data){
		$db = $this->getAdapter();
		$sql="
			SELECT 
				ex.date_payment
				,ex.date_payment AS recordDate
				,'expensePayment' AS recordType
				,'0' AS totalPinciple
				,'0' AS totalExtraPayment
				,'0' AS totalInterest
				,'0' AS totalPenalize
				,'0' AS totalRecieve
				,'0' AS totalIncomeOther
				,'0' AS totalIncomeRepairHouse
				,SUM(ex.total_paid) AS totalOtherExpense
				,'0' AS totalExpenseRepairHouse
				,'0' AS totalExpenseComission
				,'0' AS totalExpenseComissionPayment
		";
		$sql.=" FROM `rms_expense_payment` AS ex  ";
		$sql.=" WHERE ex.status = 1 ";
		
		if(!empty($data['branch_id'])){
			$sql.= " AND ex.`branch_id` = ".$data['branch_id'];
		}
		$fromDate =(empty($data['start_date']))? '1': " ex.date_payment  >= '".$data['start_date']." 00:00:00'";
		$toDate = (empty($data['end_date']))? '1': " ex.date_payment  <= '".$data['end_date']." 23:59:59'";
		$sql.= " AND ".$fromDate." AND ".$toDate;
		
		$sql.=" GROUP BY ex.date_payment 
				ORDER BY ex.date_payment ASC 
			";
		return $db->fetchAll($sql);
	}
	function getSummaryDailyExpenseComission($data){
		$db = $this->getAdapter();
		$sql="
			SELECT 
				cm.for_date
				,cm.for_date AS recordDate
				,'commission' AS recordType
				,'0' AS totalPinciple
				,'0' AS totalExtraPayment
				,'0' AS totalInterest
				,'0' AS totalPenalize
				,'0' AS totalRecieve
				,'0' AS totalIncomeOther
				,'0' AS totalIncomeRepairHouse
				,'0' AS totalOtherExpense
				,'0' AS totalExpenseRepairHouse
				,SUM(cm.total_amount) AS totalExpenseComission
				,'0' AS totalExpenseComissionPayment
		";
		$sql.=" FROM `ln_comission` AS cm  ";
		$sql.=" WHERE cm.status = 1 ";
		
		if(!empty($data['branch_id'])){
			$sql.= " AND cm.`branch_id` = ".$data['branch_id'];
		}
		$fromDate =(empty($data['start_date']))? '1': " cm.for_date  >= '".$data['start_date']." 00:00:00'";
		$toDate = (empty($data['end_date']))? '1': " cm.for_date  <= '".$data['end_date']." 23:59:59'";
		$sql.= " AND ".$fromDate." AND ".$toDate;
		
		$sql.=" GROUP BY cm.for_date 
				ORDER BY cm.for_date ASC 
			";
		return $db->fetchAll($sql);
	}
	
	function getSummaryDailyExpenseComissionPayment($data){
		$db = $this->getAdapter();
		$sql="
			SELECT 
				cmp.date_payment
				,cmp.date_payment AS recordDate
				,'commission' AS recordType
				,'0' AS totalPinciple
				,'0' AS totalExtraPayment
				,'0' AS totalInterest
				,'0' AS totalPenalize
				,'0' AS totalRecieve
				,'0' AS totalIncomeOther
				,'0' AS totalIncomeRepairHouse
				,'0' AS totalOtherExpense
				,'0' AS totalExpenseRepairHouse
				,'0' AS totalExpenseComission
				,SUM(cmp.total_paid) AS totalExpenseComissionPayment
		";
		$sql.=" FROM `rms_commission_payment` AS cmp  ";
		$sql.=" WHERE cmp.status = 1 ";
		
		if(!empty($data['branch_id'])){
			$sql.= " AND cmp.`branch_id` = ".$data['branch_id'];
		}
		$fromDate =(empty($data['start_date']))? '1': " cmp.date_payment  >= '".$data['start_date']." 00:00:00'";
		$toDate = (empty($data['end_date']))? '1': " cmp.date_payment  <= '".$data['end_date']." 23:59:59'";
		$sql.= " AND ".$fromDate." AND ".$toDate;
		
		$sql.=" GROUP BY cmp.date_payment 
				ORDER BY cmp.date_payment ASC 
			";
		return $db->fetchAll($sql);
	}
	
	function getResulArrayBySpecificDate($recordDate,$arrayResultData){
		$filteredArray = array_filter($arrayResultData, function ($obj) use ($recordDate) {
							  return $obj["recordDate"] == $recordDate;
							});
		if(COUNT($filteredArray)>1){
			$newArray = array(
				"totalPinciple"=>0,
				"totalExtraPayment"=>0,
				"totalInterest"=>0,
				"totalPenalize"=>0,
				"totalRecieve"=>0,
				"totalIncomeOther"=>0,
				"totalIncomeRepairHouse"=>0,
				"totalOtherExpense"=>0,
				"totalExpenseRepairHouse"=>0,
				"totalExpenseComission"=>0,
				"totalExpenseComissionPayment"=>0,
			);
			foreach($filteredArray as $rs){
				$newArray["recordDate"] 		= $rs["recordDate"];
				$newArray["totalPinciple"] 		= $newArray["totalPinciple"] +$rs["totalPinciple"];
				$newArray["totalExtraPayment"] 	= $newArray["totalExtraPayment"] +$rs["totalExtraPayment"];
				$newArray["totalInterest"] 		= $newArray["totalInterest"] +$rs["totalInterest"];
				$newArray["totalPenalize"] 		= $newArray["totalPenalize"] +$rs["totalPenalize"];
				$newArray["totalRecieve"] 		= $newArray["totalRecieve"] +$rs["totalRecieve"];
				$newArray["totalIncomeOther"] 	= $newArray["totalIncomeOther"] +$rs["totalIncomeOther"];
				$newArray["totalIncomeRepairHouse"] = $newArray["totalIncomeRepairHouse"] +$rs["totalIncomeRepairHouse"];
				$newArray["totalOtherExpense"] 			= $newArray["totalOtherExpense"] +$rs["totalOtherExpense"];
				
				$newArray["totalExpenseRepairHouse"] 	= $newArray["totalExpenseRepairHouse"] +$rs["totalExpenseRepairHouse"];
				$newArray["totalExpenseComission"] 		= $newArray["totalExpenseComission"] +$rs["totalExpenseComission"];
				$newArray["totalExpenseComissionPayment"] 	= $newArray["totalExpenseComissionPayment"] +$rs["totalExpenseComissionPayment"];
			}
			
			return $newArray;
		}else{
			return array();
		}
	}
	
	
	public function getVerificationSale($search,$reschedule =null){
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$edit_sale = $tr->translate("EDITSALEONLY");
    	$session_lang=new Zend_Session_Namespace('lang');
    	$lang = $session_lang->lang_id;
    	
    	$str = 'name_en';
    	if($lang==1){
    		$str = 'name_kh';
    	}
    	
    	$from_date =(empty($search['start_date']))? '1': " s.verifyDate >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " s.verifyDate <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	$sql=" 
    	SELECT 
			`s`.`id` AS `id`
			,(SELECT
				`ln_project`.`project_name`
			FROM `ln_project`
			WHERE (`ln_project`.`br_id` = `s`.`branch_id`)
			LIMIT 1 ) AS `branch_name`
			,`c`.`name_kh`         AS `name_kh`
			,`c`.`phone`         AS `phone`
			,`p`.`land_address`    AS `land_address`
			,`p`.`street`          AS `street`
			,(SELECT $str FROM `ln_view` WHERE key_code =s.payment_id AND type = 25 limit 1) AS paymenttype
			
			,vrf.*
			,vrf.`verifyDate` AS `verifyDate`
			,vrf.`priceBeforeNew`
			,vrf.`priceSoldNew`
			,vrf.`paidAmountNew`
			,vrf.`balanceNew`
			
			,(SELECT  first_name FROM rms_users WHERE id=vrf.user_id limit 1 ) AS user_name
			
			,vrf.`priceBefore`
			,vrf.`priceSold`
			,vrf.`paidAmount`
			,vrf.`balance`
			
			
			,vrf.status
		FROM 
			ln_verificaton_sale AS vrf 
			JOIN `ln_sale` `s` ON s.id = vrf.saleId
			LEFT JOIN ln_client AS c ON `c`.`client_id` = `s`.`client_id`
			LEFT JOIN ln_properties AS p ON `p`.`id` = `s`.`house_id`
		WHERE 
			1 
			AND s.is_verify = 1
	   
	   ";
    	
    	$db = $this->getAdapter();
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['adv_search']));
      	 	$s_where[] = " s.receipt_no LIKE '%{$s_search}%'";
      	 	$s_where[] = " s.sale_number LIKE '%{$s_search}%'";
      	 	$s_where[] = " p.land_code LIKE '%{$s_search}%'";
      	 	$s_where[] = " p.land_address LIKE '%{$s_search}%'";
      	 	$s_where[] = " c.client_number LIKE '%{$s_search}%'";
      	 	$s_where[] = " c.name_en LIKE '%{$s_search}%'";
      	 	$s_where[] = " c.name_kh LIKE '%{$s_search}%'";
      	 	$s_where[] = " c.phone LIKE '%{$s_search}%'";
      	 	$s_where[] = " s.price_sold LIKE '%{$s_search}%'";
      	 	$s_where[] = " s.comission LIKE '%{$s_search}%'";
      	 	$s_where[] = " s.total_duration LIKE '%{$s_search}%'";
      	 	$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	
    	if($search['branch_id']>0){
			$where.=" AND s.branch_id = ".$search['branch_id'];
		}
		if(!empty($search['streetlist']) AND $search['streetlist']>-1){
			$where.=" AND `p`.`street` = '".$search['streetlist']."'";
		}
		if($search['land_id']>0){
			$where.=" AND ( s.house_id = ".$search['land_id']." OR (SELECT p.old_land_id FROM `ln_properties` AS p WHERE p.id = s.house_id LIMIT 1) LIKE '%".$search['land_id']."%' )";
		}
		if($search['property_type']>0 AND $search['property_type']>0){
			$where.=" AND p.property_type = ".$search['property_type'];
		}
		if($search['client_name']!='' AND $search['client_name']>0){
			$where.=" AND `s`.`client_id` = ".$search['client_name'];
		}
    	$order = " ORDER BY vrf.id DESC";
		if(!empty($search['queryOrdering'])){
			if($search['queryOrdering']==1){
				$order =" ORDER BY `s`.buy_date ASC ";
			}else if($search['queryOrdering']==2){
				$order =" ORDER BY `s`.buy_date DESC ";
			}else if($search['queryOrdering']==3){
				$order =" ORDER BY vrf.id ASC ";
			}else if($search['queryOrdering']==4){
				$order =" ORDER BY vrf.id DESC ";
			}
			else if($search['queryOrdering']==5){
				$order =" ORDER BY `s`.client_id DESC ";
			}
		}
    	
    	$where.=$dbp->getAccessPermission("`s`.`branch_id`");
    	return $db->fetchAll($sql.$where.$order);
    }
	
	public function getVerificationDetail($verificationId){
		
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$session_lang=new Zend_Session_Namespace('lang');
    	$lang = $session_lang->lang_id;
    	if($lang==1){
    		$str = 'name_kh';
    	}
    	$sql=" 
    	SELECT 
			`s`.`id` AS saleId
			,(SELECT
				`ln_project`.`project_name`
			FROM `ln_project`
			WHERE (`ln_project`.`br_id` = `s`.`branch_id`)
			LIMIT 1 ) AS `branch_name`
			,`c`.`name_kh`         AS `name_kh`
			,`c`.`phone`         AS `phone`
			,`p`.`land_address`    AS `land_address`
			,`p`.`street`          AS `street`
			,(SELECT $str FROM `ln_view` WHERE key_code =s.payment_id AND type = 25 limit 1) AS paymenttype
			,(SELECT pt.type_nameen FROM `ln_properties_type` AS pt WHERE pt.id = (SELECT p.property_type  FROM `ln_properties` AS p WHERE p.id  = s.`house_id` LIMIT 1) LIMIT 1)AS property_type
				
			
			,vrf.`branch_id` AS `branch_id`
			,vrf.`buyDate` AS `buyDate`
			,vrf.`verifyDate` AS `verifyDate`
			,vrf.`priceBeforeNew`
			,vrf.`priceSoldNew`
			,vrf.`paidAmountNew`
			,vrf.`balanceNew`
			
			,(SELECT  first_name FROM rms_users WHERE id=vrf.user_id limit 1 ) AS user_name
			
			,vrf.`priceBefore`
			,vrf.`priceSold`
			,vrf.`paidAmount`
			,vrf.`balance`
			,vrf.status
		FROM 
			ln_verificaton_sale AS vrf 
			JOIN `ln_sale` `s` ON s.id = vrf.saleId
			LEFT JOIN ln_client AS c ON `c`.`client_id` = `s`.`client_id`
			LEFT JOIN ln_properties AS p ON `p`.`id` = `s`.`house_id`
		WHERE 
			1 
			AND s.is_verify = 1
	   
	   ";
    	$sql.=" AND vrf.id= ".$verificationId;
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission("vrf.`branch_id`");
		$sql.= " ORDER BY vrf.id DESC";
		$sql.= " LIMIT 1";
		$db = $this->getAdapter();
    	return $db->fetchRow($sql);
    }
		
 }
