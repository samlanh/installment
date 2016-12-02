<?php
class Report_Model_DbTable_DbLandreport extends Zend_Db_Table_Abstract
{
      public function getAllLoan($search = null){//rpt-loan-released/
      	 $db = $this->getAdapter();
      	 $sql = " SELECT * ,
      	 (SELECT name_en FROM `ln_view` WHERE key_code =v_soldreport.payment_id AND type = 25 limit 1) AS paymenttype
      	 FROM v_soldreport WHERE 1 ";
      	 
      	 $where ='';
      
	    $from_date =(empty($search['start_date']))? '1': " buy_date >= '".$search['start_date']." 00:00:00'";
	    $to_date = (empty($search['end_date']))? '1': " buy_date <= '".$search['end_date']." 23:59:59'";
	    $where.= " AND ".$from_date." AND ".$to_date;

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
      	 	$where .=' AND ( '.implode(' OR ',$s_where).')';
      	 }
      	 if($search['branch_id']>0){
      	 	$where.=" AND branch_id = ".$search['branch_id'];
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
      	 $order = " ORDER BY id DESC ";
//       	 echo $sql.$where.$order;
      	 return $db->fetchAll($sql.$where.$order);
      }
      public function getAllLoanCo($search = null){//rpt-loan-released
      	$db = $this->getAdapter();

      	$sql = "SELECT * FROM v_released_co WHERE 1";
      	$where ='';
      	$from_date =(empty($search['start_date']))? '1': " date_buy >= '".$search['start_date']." 00:00:00'";
      	$to_date = (empty($search['end_date']))? '1': " date_buy <= '".$search['end_date']." 23:59:59'";
      	$where.= " AND ".$from_date." AND ".$to_date;
      	
      	if($search['member']>0){
      		$where.=" AND client_id = ".$search['member'];
      	}
      	if($search['co_id']>0){
      		$where.=" AND staff_id = ".$search['co_id'];
      	}

      	if(!empty($search['adv_search'])){
      		$s_where = array();
      		$s_search = addslashes(trim($search['adv_search']));
//       		$s_where[] = " loan_number LIKE '%{$s_search}%'";
      		$s_where[] = " client_number LIKE '%{$s_search}%'";
      		$s_where[] = " commission LIKE '%{$s_search}%'";
      		$s_where[] = " client_name LIKE '%{$s_search}%'";
      		$s_where[] = " price LIKE '%{$s_search}%'";
      		$s_where[] = " amount_month LIKE '%{$s_search}%'";
      		$s_where[] = " interest_rate LIKE '%{$s_search}%'";
      		$where .=' AND ('.implode(' OR ',$s_where).')';
      	}
      	$order = " ORDER BY staff_id DESC";
//       	echo $sql.$where.$order;
      	return $db->fetchAll($sql.$where.$order);
      }
      public function getAllOutstadingLoan($search=null){
      	$db = $this->getAdapter();
      	$where="";
      	$to_date = (empty($search['end_date']))? '1': " date_release <= '".$search['end_date']." 23:59:59'";
      	$where.= "  AND ".$to_date;
      	$sql="SELECT * FROM v_loanoutstanding WHERE 1 ";//IF BAD LOAN STILL GET IT
      	
      	if($search['member']>0){
           		$where.=" AND client_id = ".$search['member'];
      	}
      	if($search['co_id']>0){
      		$where.=" AND co_id = ".$search['co_id'];
      	}
      	if(!empty($search['adv_search'])){
      		$s_where = array();
      		$s_search = addslashes(trim($search['adv_search']));
      		$s_where[] = " land_address LIKE '%{$s_search}%'";
      		$s_where[] = " client_number LIKE '%{$s_search}%'";
      		$s_where[] = " client_number LIKE '%{$s_search}%'";
      		$s_where[] = " client_kh LIKE '%{$s_search}%'";
      		$s_where[] = " co_name LIKE '%{$s_search}%'";
      		$s_where[] = " total_capital LIKE '%{$s_search}%'";
      		$s_where[] = " total_duration LIKE '%{$s_search}%'";
      	   $where .=' AND ('.implode(' OR ',$s_where).')';
      	}
      	return $db->fetchAll($sql.$where);
      }
      function getAmountReceiveByLoanNumber($land_id,$client_id){
      	 $db = $this->getAdapter();
      	 $sql="
      	     SELECT 
				SUM(`crm`.`total_principal_permonthpaid`)
				 FROM  `ln_client_receipt_money` AS crm
      	 	WHERE crm.`client_id`=$client_id AND  crm.`land_id`='".$land_id."' GROUP BY crm.land_id ,crm.client_id ";
      	 $row =  $db->fetchOne($sql);
      	 if(!empty($row)){
      	 	$alltotal =  $db->fetchOne($sql);
      	 }else{
      	 	$alltotal=0;
      	 }
      	 //echo $alltotal;
      	 return $alltotal;
      }
      
      public function getALLLoancollect($search = null){
      	$db = $this->getAdapter();
//       	$sql="SELECT id,
//       	(SELECT loan_number FROM ln_loan_member WHERE loan_number=(SELECT lm.loan_number FROM ln_loan_member AS lm  WHERE lm.member_id LIMIT 1) LIMIT 1 ) AS loan_number,
//       	(SELECT name_kh FROM ln_client WHERE client_id = (SELECT lm.client_id FROM ln_loan_member AS lm  WHERE lm.member_id LIMIT 1) LIMIT 1 ) AS client_name
//       	,(SELECT branch_namekh FROM ln_branch WHERE br_id= branch_id LIMIT 1) AS branch_id,
//       	(SELECT co_khname FROM ln_co WHERE co_id=(SELECT co_id FROM ln_loan_group WHERE g_id=(SELECT lm.client_id FROM ln_loan_member AS lm  WHERE lm.member_id LIMIT 1) LIMIT 1 )LIMIT 1 ) AS co,
//       	total_principal,total_interest,STATUS
//       	,total_payment,date_payment FROM ln_loanmember_funddetail WHERE 1 ";
      	
      	$from_date =(empty($search['start_date']))? '1': "f.date_payment >= '".$search['start_date']." 00:00:00'";
      	$to_date = (empty($search['end_date']))? '1': "f.date_payment <= '".$search['end_date']." 23:59:59'";
      	$where = " AND ".$from_date." AND ".$to_date;
      	
      	$Other =" ORDER BY co_name ,id DESC ";
      	$sql = " SELECT 
				  f.id ,
				  f.total_principal ,
				  f.total_interest ,
				  f.status ,
				  f.total_payment ,
				  f.date_payment ,
				  m.loan_number ,  
				  (SELECT name_kh FROM ln_client WHERE client_id=m.client_id) AS client_name , 
				  (SELECT branch_namekh FROM ln_branch WHERE br_id= m.branch_id LIMIT 1) AS branch_id ,
				  (SELECT co_khname FROM ln_co WHERE co_id=(SELECT co_id FROM ln_loan_group WHERE g_id= m.group_id LIMIT 1) LIMIT 1) AS co,
				  (SELECT co_firstname FROM ln_co WHERE co_id=(SELECT co_id FROM ln_loan_group WHERE g_id= m.group_id LIMIT 1) LIMIT 1) AS co_name
				  FROM `ln_loanmember_funddetail` AS f ,`ln_loan_member` AS m WHERE m.member_id = f.member_id 
				  AND f.is_completed=0 AND f.status=1 AND m.is_completed=0 ";
      	if(!empty($search['txtsearch'])){
      		$s_where = array();
      		$s_search = addslashes(trim($search['txtsearch']));
      		$s_where[] = " loan_number LIKE '%{$s_search}%'";
      		$s_where[]=" client_name LIKE '%{$s_search}%'";
      		$where .=' AND ('.implode(' OR ',$s_where).')';
      	
      	}
      	//echo $sql.$where.$Other;
      	return $db->fetchAll($sql.$where.$Other);
      }
     
      public function getALLGroupDisburse($id=null){
      	$db = $this->getAdapter();
      	$sql="SELECT *  
				FROM
				`v_loangroupmember` WHERE `group_id`= $id";
      	
      	//$Other =" ORDER BY member_id ASC";
//       	$where = '';
//       	if(!empty($search['txtsearch'])){
//       		$s_where = array();
//       		$s_search = $search['txtsearch'];
//       		$s_where[] = " chart_id LIKE '%{$s_search}%'";
//       		$s_where[]=" group_id LIKE '%{$s_search}%'";
//       		$where .=' AND '.implode(' OR ',$s_where).'';      		 
      //	}
      	return $db->fetchAll($sql);
      }
      public function getALLPayment(){
      	$db = $this->getAdapter();
      	$sql="select * from ln_client_receipt_money";
      	return $db->fetchAll($sql);
      }
      public function getALLLoanlate($search = null){
   		$end_date = $search['end_date'];
      	$db = $this->getAdapter();
		$sql=" SELECT 
				  c.`client_number`,
				  c.`name_kh`,
				  c.`phone`,
				  (SELECT project_name FROM `ln_project` WHERE br_id=s.branch_id LIMIT 1 ) As branch_name ,
				  `l`.`land_code`               AS `land_code`,
				  `l`.`land_address`             AS `land_address`,
				  `l`.`street`                   AS `street`,
				  `s`.`sale_number`              AS `sale_number`,
				  `s`.`client_id`                AS `client_id`,
				  `s`.`price_before`             AS `price_before`,
				  `s`.`price_sold`               AS `price_sold`,
				  `s`.`discount_amount`          AS `discount_amount`,
				  `s`.`other_fee`                AS `other_fee`,
				  `s`.`paid_amount`              AS `paid_amount`,
				  `s`.`balance`                  AS `balance`,
				  `s`.`buy_date`                 AS `buy_date`,
				  `s`.`startcal_date`            AS `startcal_date`,
				  `s`.`first_payment`            AS `first_payment`,
				  `s`.`validate_date`            AS `validate_date`,
				  `s`.`end_line`                 AS `end_line`,
				  `s`.`interest_rate`            AS `interest_rate`,
				  `s`.`total_duration`           AS `total_duration`,
				  `s`.`payment_id`               AS `payment_id`,
				  `sd`.`id`                      AS `id`,
				  `sd`.`penelize`                AS `penelize`,
				  `sd`.`date_payment`            AS `date_payment`,
				  `sd`.`amount_day`              AS `amount_day`,
				  `sd`.`status`                  AS `status`,
				  `sd`.`is_completed`            AS `is_completed`,
				  `sd`.`begining_balance`        AS `begining_balance`,
				  `sd`.`principal_permonthafter` AS `principal_permonthafter`,
				  `sd`.`total_interest_after`    AS `total_interest_after`,
				  `sd`.`total_payment_after`     AS `total_payment_after`,
				  `sd`.`ending_balance`          AS `ending_balance`,
				  `sd`.`service_charge`          AS `service_charge`,
				  sd.begining_balance_after,
				  (SELECT date_input FROM `ln_client_receipt_money` WHERE land_id=1 ORDER BY date_input DESC LIMIT 1) 
				  	As last_pay_date
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
				  AND (`s`.`is_cancel` = 0)
				  AND c.`client_id` = s.`client_id` ";
      	$where='';
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
      	if(!empty($search['end_date'])){
			$where.=" AND sd.date_payment < '$end_date'";
		}
		if($search['client_name']>0){
			$where.=" AND s.`client_id` =".$search['client_name'];
		}
		
        $group_by = " GROUP BY sd.`date_payment` ORDER BY sd.`date_payment` ASC";
//         echo $sql.$where.$group_by;exit();
      	return $db->fetchAll($sql.$where.$group_by);
      }
      public function getALLLoandateline(){
      	//$to_date = (empty($search['to_date']))? '1': "date_payment <= '".$search['to_date']." 23:59:59'";
      	$db = $this->getAdapter();
//       	$sql="select g.level,(select first_name from rms_users where id=g.group_id) as first_name,(select last_name from rms_users where id=g.co_id)as last_name
// 		,g.zone_id,g.date_release,g.date_line,g.create_date,g.total_duration,g.first_payment,g.time_collect
// 		,g.collect_typeterm,g.pay_term,g.payment_method,g.holiday,g.is_renew,g.branch_id,g.loan_type,g.status,g.is_verify,g.is_badloan,g.teller_id
// 		,m.chart_id,m.member_id,m.loan_number,m.currency_type,m.total_capital,m.admin_fee,m.interest_rate,m.loan_cycle,m.loan_purpose,m.pay_before
// 		,m.pay_after,m.graice_period,m.amount_collect_principal,m.show_barcode,m.is_completed,m.semi from ln_loan_group as g,ln_loan_member as m where m.group_id = g.g_id";
      	$sql="SELECT 
      	g.level,(SELECT first_name FROM rms_users WHERE id=g.group_id) AS first_name,(SELECT last_name FROM rms_users WHERE id=g.co_id)AS last_name
		,g.zone_id,g.date_release,g.`date_release` AS date_line,g.create_date,g.total_duration,g.first_payment,g.time_collect
		,g.collect_typeterm,g.pay_term,g.payment_method,g.holiday,g.is_renew,g.branch_id,g.loan_type,g.status,g.is_verify,g.is_badloan,g.teller_id
		,m.chart_id,m.member_id,m.loan_number,m.currency_type,m.total_capital,m.admin_fee,m.interest_rate,m.loan_cycle,m.loan_purpose,m.pay_before
		,m.pay_after,m.graice_period,m.amount_collect_principal,m.show_barcode,m.is_completed FROM ln_loan_group AS g,ln_loan_member AS m WHERE m.group_id = g.g_id";
      	return $db->fetchAll($sql);
      }
      public function getALLLoanTotalcollect($search=null){
//       	$to_date = (empty($search['to_date']))? '1': "date_payment <= '".$search['to_date']." 23:59:59'";
       	$db = $this->getAdapter();
        $start_date = $search['start_date'];
   		$end_date = $search['end_date'];
		$sql="SELECT * FROM v_getcollect WHERE is_completed = 0 ";
		$where ='';		
		if(!empty($search['start_date']) or !empty($search['end_date'])){
			$where.=" AND date_payment BETWEEN '$start_date' AND '$end_date'";
		}
		if($search['branch_id']>0){
			$where.=" AND branch_id= ".$search['branch_id'];
		}
		if($search['client_name']>0){
			$where.=" AND client_id = ".$search['client_name'];
		}
        if($search['co_id']>0){
			$where.=" AND collect_by = ".$search['co_id'];
		}
		if(!empty($search['adv_search'])){
			//print_r($search);
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[] = " branch_name LIKE '%{$s_search}%'";
			$s_where[] = " client_name LIKE '%{$s_search}%'";
			$s_where[] = " co_name LIKE '%{$s_search}%'";
			$s_where[] = " total_principal LIKE '%{$s_search}%'";
			$s_where[] = " principal_permonth LIKE '%{$s_search}%'";
			$s_where[] = " total_interest LIKE '%{$s_search}%'";
			$s_where[] = " total_payment LIKE '%{$s_search}%'";
			$s_where[] = " amount_day LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		$order=" ORDER BY currency_type DESC ";
		//echo $sql.$where;
		return $db->fetchAll($sql.$where.$order);
      }
      public function getALLLoanPayment($search=null){
      	$db = $this->getAdapter();
      	$sql="SELECT * FROM v_getcollectmoney WHERE status=1 ";
      	
      	$from_date =(empty($search['start_date']))? '1': " date_pay >= '".$search['start_date']." 00:00:00'";
      	$to_date = (empty($search['end_date']))? '1': " date_pay <= '".$search['end_date']." 23:59:59'";
      	$where = " AND ".$from_date." AND ".$to_date;
      	
      	if($search['client_name']>0){
      		$where.=" AND client_id = ".$search['client_name'];
      	} 
		if($search['branch_id']>0){
		        $where.=" AND branch_id = ".$search['branch_id'];
		}
      	if(!empty($search['adv_search'])){
      		$s_where = array();
      		$s_search = addslashes(trim($search['adv_search']));
      		$s_where[] = " sale_number LIKE '%{$s_search}%'";
      		$s_where[] = " land_code LIKE '%{$s_search}%'";
      		$s_where[] = " land_address LIKE '%{$s_search}%'";
      		$s_where[] = " client_number LIKE '%{$s_search}%'";
      		$s_where[] = " client_name LIKE '%{$s_search}%'";
      		$s_where[] = " total_principal_permonthpaid LIKE '%{$s_search}%'";
      		$s_where[] = " total_interest_permonthpaid LIKE '%{$s_search}%'";
//       		$s_where[] = " penalize_amount LIKE '%{$s_search}%'";
      		$s_where[] = " penalize_amountpaid LIKE '%{$s_search}%'";  
      		$s_where[] = " service_chargepaid LIKE '%{$s_search}%'";
      		$s_where[] = " amount_payment LIKE '%{$s_search}%'";
      		$s_where[] = " receipt_no LIKE '%{$s_search}%'";
      		$where .=' AND ('.implode(' OR ',$s_where).')';
      	}
      	$order=" ORDER BY id DESC ";
//       	echo $sql.$where.$order;
      	return $db->fetchAll($sql.$where.$order);
      	 
      }
      public function getALLLoanIcome($search=null){
		$start_date = $search['start_date'];
    	$end_date = $search['end_date'];
    	
    	$db = $this->getAdapter();
    	$sql = " SELECT * FROM v_getcollectmoney where status=1 ";
//     	$sql = "SELECT lcrm.`id`,
// 					lcrm.`receipt_no`,
// 					lcrm.`loan_number`,lcrm.service_charge,
// 					(SELECT c.`name_kh` FROM `ln_client` AS c WHERE c.`client_id`=lcrm.`group_id`) AS team_group ,
// 					lcrm.`total_principal_permonth`,
// 					lcrm.`total_payment`,
//     			  (SELECT symbol FROM `ln_currency` WHERE id =lcrm.currency_type) AS currency_typeshow ,lcrm.currency_type,
// 					lcrm.`recieve_amount`,
// 					lcrm.`total_interest`,lcrm.amount_payment,
// 					lcrm.`penalize_amount`,
// 					lcrm.`date_pay`,
// 					lcrm.`date_input`,
// 				    (SELECT co.`co_khname` FROM `ln_co` AS co WHERE co.`co_id`=lcrm.`co_id`) AS co_name,
//     				(SELECT b.`branch_namekh` FROM `ln_branch` AS b WHERE b.`br_id`=lcrm.`branch_id`) AS branch
// 				FROM `ln_client_receipt_money` AS lcrm WHERE lcrm.is_group=0 AND lcrm.`status`=1";
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
      
      public function getALLLoanCollectionco($search=null){
      	$start_date = $search['start_date'];
      	$end_date = $search['end_date'];
      	 
      	$db = $this->getAdapter();
		$sql =" SELECT 
				  crm.`receipt_no`,
				  crm.`date_input`,
				  crm.`co_id`,
				  crm.`payment_option`,
				  crm.`recieve_amount`,
				  crmd.`loan_number`,
				  (SELECT c.`phone` FROM ln_client AS c WHERE c.`client_id`=crmd.`client_id`) AS phone,
				  (SELECT b.`branch_namekh` FROM `ln_branch` AS b WHERE b.`br_id`=crm.`branch_id`) AS branch,
				  (SELECT CONCAT(c.`co_code`,'-',c.`co_khname`,'-',c.`co_firstname`,' ',c.`co_lastname`) FROM ln_co AS c WHERE c.`co_id`=crm.`co_id`) AS co_name,
				  (SELECT c.`client_number` FROM ln_client AS c WHERE c.`client_id`=crmd.`client_id`) AS client_code,
				  (SELECT c.`name_kh` FROM ln_client AS c WHERE c.`client_id`=crmd.`client_id`) AS client_name,
				  lg.`loan_type`,
				  lg.`total_duration`,
				  lg.`time_collect`,
				  lg.`collect_typeterm`,
				  lg.`date_release`,
				  lg.`date_line`,
				  lm.`interest_rate`,
				  lm.`total_capital` as capital,
				 `crm`.`total_principal_permonth` AS `principle_amount`,
				 
				 (crm.`total_interest`) AS interest,
				 (crm.`penalize_amount`) AS penelize,
				 (crm.`service_charge`) AS service,
				 
				 crm.`currency_type` AS curr_type,
				 crmd.`date_payment`,
				 
				SUM(crm.`return_amount`) AS return_amount,
				SUM(crm.`recieve_amount`) AS amount_recieve,
				SUM(`crm`.`total_payment`) AS `payment`,
				
				SUM(crmd.`capital`) AS total_printciple,
				SUM(crmd.`principal_permonth`) AS total_principal_permonth,
				SUM(crmd.`total_payment`) AS total_payment,
				SUM(crmd.`total_interest`) AS total_interest,
				SUM(crmd.`total_recieve`) AS recieve_amount,
				SUM(crmd.`penelize_amount`) AS penelize_amount,
				SUM(crmd.`service_charge`) AS service_charge,
				
				(SELECT `ln_currency`.`symbol` FROM `ln_currency` WHERE (`ln_currency`.`id` = crm.`currency_type`)) AS `currency_type`,
      			(SELECT `ln_view`.`name_en` FROM `ln_view` WHERE ((`ln_view`.`type` = 14) AND (`ln_view`.`key_code` = (SELECT lg.`pay_term` FROM `ln_loan_group` AS lg WHERE lg.`g_id`=(SELECT `group_id` FROM `ln_loan_member` AS lm WHERE lm.`member_id`=(SELECT f.`member_id` FROM `ln_loanmember_funddetail` AS f WHERE f.`id`=crmd.`lfd_id`)))))) AS name_en
				FROM
				  `ln_client_receipt_money` AS crm,
				  `ln_client_receipt_money_detail` AS crmd,
				  `ln_loan_member` AS lm,
				  `ln_loan_group` AS lg,
				  `ln_loanmember_funddetail` AS lf 
				WHERE crmd.`lfd_id` = lf.`id` 
				AND crmd.`crm_id`=crm.`id`
				  AND lf.`member_id`=lm.`member_id`
				  AND lm.`group_id`=lg.`g_id` ";
      	$where ='';
      	if(!empty($search['advance_search'])){
      		//print_r($search);
      		$s_where = array();
      		$s_search = addslashes(trim($search['advance_search']));
      		$s_where[] = " crmd.`loan_number` LIKE '%{$s_search}%'";
      		$s_where[] = " crm.`receipt_no` LIKE '%{$s_search}%'";
      		$s_where[] = " crmd.`total_payment` LIKE '%{$s_search}%'";
      		$s_where[] = " crmd.`total_interest` LIKE '%{$s_search}%'";
      		$s_where[] = " crmd.`penelize_amount` LIKE '%{$s_search}%'";
      		$s_where[] = " crmd.`service_charge` LIKE '%{$s_search}%'";
      		$where .=' AND ('.implode(' OR ',$s_where).')';
      	}
      	if($search['status']!=""){
      		$where.= " AND crm.status = ".$search['status'];
      	}
      	 
      	if(!empty($search['start_date']) or !empty($search['end_date'])){
      		$where.=" AND crm.`date_input` BETWEEN '$start_date' AND '$end_date'";
      	}
      	if($search['client_name']>0){
      		$where.=" AND crmd.`client_id`= ".$search['client_name'];
      	}
      	if($search['branch_id']>0){
      		$where.=" AND crm.`branch_id`= ".$search['branch_id'];
      	}
      	if($search['co_id']>0){
      		$where.=" AND crm.`co_id`= ".$search['co_id'];
      	}
      	if($search['paymnet_type']>0){
      		$where.=" AND crm.`payment_option`= ".$search['paymnet_type'];
      	}
      	 
      	$groupby=" GROUP BY lm.`group_id`,crm.`date_input` ORDER BY crm.`co_id` , crm.`date_input` DESC ";
      	return $db->fetchAll($sql.$where.$groupby);
      }
      public function getALLLFee($search=null){
      	$start_date = $search['start_date'];
      	$end_date = $search['end_date'];
      	 
      	$db = $this->getAdapter();
      	$sql = "SELECT * FROM 
      				v_loanreleased WHERE 1 ";
		$where ='';
      	if(!empty($search['advance_search'])){
      		$s_where = array();
      		$s_search = addslashes(trim($search['advance_search']));
      		$s_where[] = " land_code LIKE '%{$s_search}%'";
      		$s_where[] = " land_address LIKE '%{$s_search}%'";
      		
      		$s_where[] = " client_number LIKE '%{$s_search}%'";
      		$s_where[] = " client_name LIKE '%{$s_search}%'";
      		$s_where[] = " price LIKE '%{$s_search}%'";
      		$where .=' AND ('.implode(' OR ',$s_where).')';
      	}
      	if($search['client_name']>0){
      		$where.= " AND client_id = ".$search['client_name'];
      	}
//       	if(!empty($search['start_date']) or !empty($search['end_date'])){
//       		$where.=" AND date_buy BETWEEN '$start_date' AND '$end_date'";
//       	}
      	$from_date =(empty($search['start_date']))? '1': " date_buy >= '".$search['start_date']." 00:00:00'";
      	$to_date = (empty($search['end_date']))? '1': " date_buy <= '".$search['end_date']." 23:59:59'";
      	$where.= " AND ".$from_date." AND ".$to_date;
      	
      	//$where='';
      	$order = " ";
      	return $db->fetchAll($sql.$where.$order);
      }
      public function getALLLoanPayoff($search=null){
     
      
      	$db = $this->getAdapter();
      	$sql = " SELECT * FROM v_getloanpayoff WHERE 1 ";

      	$where ='';
      	$from_date =(empty($search['start_date']))? '1': " date_payment >= '".$search['start_date']." 00:00:00'";
      	$to_date = (empty($search['end_date']))? '1': " date_payment <= '".$search['end_date']." 23:59:59'";
      	$where= " AND ".$from_date." AND ".$to_date;
      	 
      	if(!empty($search['advance_search'])){

      		$s_where = array();
      		$s_search = trim(addslashes($search['advance_search']));
//       		echo print_r($search['advance_search']);
      		$s_where[] = " `loan_number` LIKE '%{$s_search}%'";
//       		$s_where[] = " `receipt_no` LIKE '%{$s_search}%'";
      		$s_where[] = " `total_payment` LIKE '%{$s_search}%'";
      		$s_where[] = " `total_interest` LIKE '%{$s_search}%'";
//       		$s_where[] = " `penalize_amount` LIKE '%{$s_search}%'";
      		$where .=' AND ('.implode(' OR ',$s_where).')';
      	}
      	if($search['status']!=""){
      		$where.= " AND status = ".$search['status'];
      	}
      	if($search['client_name']>0){
      		$where.=" AND `group_id`= ".$search['client_name'];
      	}
      	if($search['branch_id']>0){
      		$where.=" AND `branch_id`= ".$search['branch_id'];
      	}
      	if($search['co_id']>0){
      		$where.=" AND `co_id`= ".$search['co_id'];
      	}
//       	if($search['paymnet_type']>0){
//       		$where.=" AND lcrm.`payment_option`= ".$search['paymnet_type'];
//       	}
      
      	//$where='';
      	
      	
      	$order = " ORDER BY id DESC ";
      	return $db->fetchAll($sql.$where.$order);
      }
      public function getALLLoanExpectIncome($search=null){
      	$from_date =(empty($search['start_date']))? '1': " date_payment >= '".$search['start_date']." 00:00:00'";
      	$to_date = (empty($search['end_date']))? '1': " date_payment <= '".$search['end_date']." 23:59:59'";
      	$where= " AND ".$from_date." AND ".$to_date;
      	
      	$db = $this->getAdapter();
      	$sql = "SELECT * FROM `v_getexpectincome` WHERE 1 ";
      	
      	if(!empty($search['adv_search'])){
			$s_search = addslashes(trim($search['adv_search']));
      	 	$s_where[] = " sale_number LIKE '%{$s_search}%'";
      	 	$s_where[] = " land_code LIKE '%{$s_search}%'";
      	 	$s_where[] = " land_address LIKE '%{$s_search}%'";
      	 	$s_where[] = " street LIKE '%{$s_search}%'";
      	 	$s_where[] = " client_number LIKE '%{$s_search}%'";
      	 	$s_where[] = " phone LIKE '%{$s_search}%'";
      	 	$s_where[] = " name_kh LIKE '%{$s_search}%'";
      	 	$s_where[] = " price_sold LIKE '%{$s_search}%'";
      	 	$s_where[] = " total_duration LIKE '%{$s_search}%'";
      	 	$where .=' AND ( '.implode(' OR ',$s_where).')';
      	}
      	if($search['status']>-1){
      		$where.= " AND status = ".$search['status'];
      	}
      	if($search['branch_id']>0){
      		$where.= " AND status = ".$search['status'];
      	}
      	$group_by = " GROUP BY id,date_payment ORDER BY date_payment DESC ";
//       	echo $sql.$where.$group_by;exit();
        $row = $db->fetchAll($sql.$where.$group_by);
        return $row;
      }
      public function getALLBadloan($search=null){
      	$start_date = $search['start_date'];
      	$end_date = $search['end_date'];
      
      	$db = $this->getAdapter();
    	
    	$sql = "SELECT l.id,loan_number,b.branch_namekh,
    	CONCAT((SELECT client_number FROM `ln_client` WHERE client_id = l.client_code LIMIT 1),' - ',		
    	(SELECT name_en FROM `ln_client` WHERE client_id = l.client_code LIMIT 1)) AS client_name_en,
  		l.loss_date, l.`cash_type`,(SELECT c.symbol FROM `ln_currency` AS c WHERE c.status = 1 AND c.id = l.`cash_type`) AS currency_typeshow,
		l.total_amount ,l.intrest_amount ,CONCAT (l.tem,' Days')as tem,l.note,l.date,l.status FROM `ln_badloan` AS l,ln_branch AS b 
		WHERE b.br_id = l.branch AND l.is_writoff= 0";    	
    	$where='';
    	if(($search['status']>0)){
    		$where.=" AND l.status =".$search['status'];
    	}
    	if(!empty($search['start_date']) or !empty($search['end_date'])){
    		$where.=" AND l.date BETWEEN '$start_date' AND '$end_date'";
    	}
    	if(!empty($search['branch'])){
    		$where.=" AND b.br_id = ".$search['branch'];
    	}
    	if(!empty($search['client_name'])){
    		$where.=" AND l.client_code = ".$search['client_name'];
    	}
    	if(!empty($search['client_code'])){
    		$where.=" AND l.client_code = ".$search['client_code'];
    	}
    	if(!empty($search['Term'])){
    		$where.=" AND l.tem = ".$search['Term'];
    	}
    	if(!empty($search['cash_type'])){
    		$where.=" AND l.`cash_type` = ".$search['cash_type'];
    	}
    	if(!empty($search['adv_search'])){
    		$s_where=array();
    		$s_search=addslashes(trim($search['adv_search']));
    		$s_where[]=" l.note LIKE '%{$s_search}%'";
    		$s_where[]=" total_amount LIKE '%{$s_search}%'";
    		$s_where[]=" intrest_amount LIKE '%{$s_search}%'";
    		$s_where[]=" l.tem = '{$s_search}' ";
    		$where .=' AND ('.implode(' OR ',$s_where).' )';
    	}
    	$order = ' ORDER BY l.`cash_type` ';
//     	echo $sql.$where;exit();
    	return $db->fetchAll($sql.$where.$order);
      }
      public function getALLWritoff($search=null){
      	
      	$db = $this->getAdapter();
      	 $sql = " 	SELECT * FROM  v_badloan WHERE 1 ";
//       	$sql = " SELECT l.id,b.branch_namekh,
// 			    	CONCAT((SELECT client_number FROM `ln_client` WHERE client_id = l.client_code LIMIT 1),' - ',
// 			    	(SELECT name_en FROM `ln_client` WHERE client_id = l.client_code LIMIT 1)) AS client_name_en,
// 			  		l.loss_date, l.`cash_type`,(SELECT c.symbol FROM `ln_currency` AS c WHERE c.status = 1 AND c.id = l.`cash_type`) AS currency_typeshow,
// 					l.total_amount ,l.intrest_amount ,CONCAT (l.tem,' Days')as tem,l.note,l.date,l.status 
// 		   FROM `ln_badloan` AS l,ln_branch AS b
// 		WHERE b.br_id = l.branch AND l.is_writoff = 1 ";
      	$where='';
      	$from_date =(empty($search['start_date']))? '1': " payof_date >= '".$search['start_date']." 00:00:00'";
      	$to_date = (empty($search['end_date']))? '1': " payof_date <= '".$search['end_date']." 23:59:59'";
      	
      	$where.= " AND ".$from_date." AND ".$to_date;

      	if(!empty($search['branch'])){
      		$where.=" AND br_id = ".$search['branch'];
      	}
      	if(!empty($search['client_name'])){
      		$where.=" AND client_code = ".$search['client_name'];
      	}
      	if(!empty($search['client_code'])){
      		$where.=" AND client_code = ".$search['client_code'];
      	}
      	if(!empty($search['Term'])){
      		$where.=" AND tem = ".$search['Term'];
      	}
      	if(!empty($search['cash_type'])){
      		$where.=" AND `curr_type` = ".$search['cash_type'];
      	}
      	if(!empty($search['adv_search'])){
      		$s_where=array();
      		$s_search=addslashes(trim($search['adv_search']));
      		$s_where[] = " branch_name LIKE '%{$s_search}%'";
      		$s_where[] = " loan_number LIKE '%{$s_search}%'";
      		$s_where[] = " client_number LIKE '%{$s_search}%'";
      		$s_where[] = " client_name LIKE '%{$s_search}%'";
      		$s_where[] = " co_name LIKE '%{$s_search}%'";
      		$s_where[] = " total_capital LIKE '%{$s_search}%'";
      		$s_where[] = " other_fee LIKE '%{$s_search}%'";
      		$s_where[] = " admin_fee LIKE '%{$s_search}%'";
      		$s_where[] = " interest_rate LIKE '%{$s_search}%'";
      		$s_where[] = " loan_type LIKE '%{$s_search}%'";
      		
      		$where .=' AND ('.implode(' OR ',$s_where).' )';
      	}
//       	$order = ' ORDER BY `cash_type` ';
//echo $sql.$where;
      	return $db->fetchAll($sql.$where);
      }
      public function getALLNPLLoan($search=null){
      	    $db = $this->getAdapter();
      		$end_date =(empty($search['end_date']))? '1': " date_payment <= '".$search['end_date']." 23:59:59'";
      	    $db = $this->getAdapter();

      		$sql="SELECT * FROM v_getnplloan ";
      		$where=" WHERE ".$end_date;
      		if(!empty($search['adv_search'])){
      			
      			$s_where = array();
      			$s_search = addslashes(trim($search['adv_search']));
      			$s_where[] = " branch_name LIKE '%{$s_search}%'";
      			$s_where[] = " `loan_number` LIKE '%{$s_search}%'";
      			$s_where[] = " `client_number` LIKE '%{$s_search}%'";
      			$s_where[] = " `name_kh` LIKE '%{$s_search}%'";
      			$s_where[] = " `total_capital` LIKE '%{$s_search}%'";
      			$s_where[] = " `interest_rate` LIKE '%{$s_search}%'";
      			$s_where[] = " `total_duration` LIKE '%{$s_search}%'";
      			$s_where[] = " `term_borrow` LIKE '%{$s_search}%'";
      			$s_where[] = " `total_principal` LIKE '%{$s_search}%'";
      			
      			$where .=' AND ('.implode(' OR ',$s_where).')';
      		}
      		if($search['branch_id']>0){
      			$where.=" AND `branch_id` = ".$search['branch_id'];
      		}
      		if(!empty($search['cash_type'])){
      			$where.=" AND `curr_type` = ".$search['cash_type'];
      		}
      		return $db->fetchAll($sql.$where);
      }
      public function getAllxchange($search = null){
      	$db = $this->getAdapter();
      	$sql = "SELECT * FROM `v_xchange` WHERE 1";
      	$where ='';
      	$from_date =(empty($search['start_date']))? '1': "statusDate >= '".$search['start_date']." 00:00:00'";
      	$to_date = (empty($search['end_date']))? '1': "statusDate <= '".$search['end_date']." 23:59:59'";
      	$where.= " AND ".$from_date." AND ".$to_date;
      	
//       	if($search['branch_id']>0){
//       		$where.=" AND branch_id = ".$search['branch_id'];
//       	}
//       	if($search['client_name']>0){
//       		$where.=" AND client_id = ".$search['client_name'];
//       	}
      	if(!empty($search['adv_search'])){
      		$s_where = array();
      		$s_search = trim(addcslashes($search['adv_search']));
      		$s_where[] = " branch_name LIKE '%{$s_search}%'";
      		$s_where[] = " client_name LIKE '%{$s_search}%'";
      		$s_where[] = " changedAmount LIKE '%{$s_search}%'";
      		$s_where[]=" fromAmount LIKE '%{$s_search}%'";
      		$s_where[] = " rate LIKE '%{$s_search}%'";
      		$s_where[]=" recieptNo LIKE '%{$s_search}%'";
      		$s_where[] = " recievedAmount LIKE '%{$s_search}%'";
      		$s_where[]=" status_in LIKE '%{$s_search}%'";
      		$s_where[] = " statusDate LIKE '%{$s_search}%'";
      		$s_where[]=" toAmount LIKE '%{$s_search}%'";
      		$s_where[]=" toAmountType LIKE '%{$s_search}%'";
      		$s_where[]=" fromAmountType LIKE '%{$s_search}%'";
      		$s_where[]=" from_to LIKE '%{$s_search}%'";
      		$s_where[]=" recievedType LIKE '%{$s_search}%'";
      		$s_where[]=" specail_customer LIKE '%{$s_search}%'";
      		
      		$where .=' AND ('.implode(' OR ',$s_where).')';
      	
      	}
      	$order=" ORDER BY id DESC";
//       	echo $sql.$where;
      	return $db->fetchAll($sql.$where.$order);
      	
      } 
      public function getRescheduleLoan($search = null){//rpt-loan-released/
      	$db = $this->getAdapter();
      	$sql = "SELECT * FROM v_rescheduleloan WHERE 1";
      	$where ='';
      
      	$from_date =(empty($search['start_date']))? '1': " reschedule_date >= '".$search['start_date']." 00:00:00'";
      	$to_date = (empty($search['end_date']))? '1': " reschedule_date <= '".$search['end_date']." 23:59:59'";
      	$where.= " AND ".$from_date." AND ".$to_date;
      
      	if($search['branch_id']>0){
      		$where.=" AND branch_id = ".$search['branch_id'];
      	}
      	if($search['client_name']>0){
      		$where.=" AND client_id = ".$search['client_name'];
      	}
      	
      	if($search['pay_every']>0){
      		$where.=" AND pay_term_id = ".$search['pay_every'];
      	}
      	if(!empty($search['adv_search'])){
      		$s_where = array();
      		$s_search = addslashes(trim($search['adv_search']));
      		$s_where[] = " branch_name LIKE '%{$s_search}%'";
      		$s_where[] = " re_loan_number LIKE '%{$s_search}%'";
      		$s_where[] = " client_number LIKE '%{$s_search}%'";
      		$s_where[] = " client_name LIKE '%{$s_search}%'";
      		
      		$s_where[] = " total_capital LIKE '%{$s_search}%'";
      		$s_where[] = " re_amount LIKE '%{$s_search}%'";
      		$s_where[] = " re_interest_rate LIKE '%{$s_search}%'";
      		
      		$s_where[] = " loan_type LIKE '%{$s_search}%'";
      		$where .=' AND ('.implode(' OR ',$s_where).')';
      	}
      	$order=" ORDER BY id DESC";
      	//echo $sql.$where;
      	return $db->fetchAll($sql.$where.$order);
      }
      public function getAllLoanByCo($search=null){
      	$start_date = $search['start_date'];
      	$end_date = $search['end_date'];
      	$db = $this->getAdapter();
      	$sql=" SELECT 
 				CONCAT(co.`co_code`,',',co.`co_khname`,'-',co.`co_firstname`,' ',co.`co_lastname`) AS co_name ,
				  co.`co_id`,
				  c.`client_number`,
				  c.`name_kh`,
				  c.`phone`,
				  p.`price`,
				  p.`interest_rate`,
				  p.`amount_month`,
				  p.multypanelty,
				  SUM(pd.`outstanding`) AS outstanding,
				  SUM(pd.`principal_after`) AS principle_after,
				  SUM(pd.`total_interest_after`) AS total_interest_after,
				  SUM(pd.`total_payment_after`) AS total_payment_after,
				  SUM(pd.`penelize`) AS penelize,
				  SUM(pd.`service_charge`) AS service_charge,
				  pd.`date_payment` ,
				  (SELECT `crm`.`date_input` FROM (`ln_client_receipt_money` `crm` JOIN `ln_client_receipt_money_detail` `crmd`)
				   WHERE ( (`crm`.`id` = `crmd`.`crm_id`) AND (`crmd`.`lfd_id` = pd.`id`)) ORDER BY `crm`.`date_input` DESC LIMIT 1) AS `last_pay_date`
								          
				FROM
				  `ln_paymentschedule` AS p,
				  `ln_paymentschedule_detail` AS pd,
				  `ln_co` AS co,
				  `ln_client` AS c
				WHERE pd.`is_completed` = 0 
				  AND p.`id` = pd.`paymentid` 
				  AND p.`status` = 1 
				  AND pd.`status` = 1 
				  AND co.`co_id` = p.`staff_id` 
				  AND c.`client_id` = p.`client_id` ";
      	$where ='';
      	$group_by=" GROUP BY lm.`group_id`,f.`date_payment` ";
      	$order = " ORDER BY lg.`group_id`";
      if(!empty($search['start_date']) or !empty($search['end_date'])){
      		$where.=" AND f.`date_payment` BETWEEN '$start_date' AND '$end_date'";
      	}
      	if($search['client_name']!=""){
      		$where.=" AND lg.`group_id`= ".$search['client_name'];
      	}
      	if($search['branch_id']>-1){
      		$where.=" AND f.`branch_id`= ".$search['branch_id'];
      	}
      	if($search['co_id']!=""){
      		$where.=" AND co.`co_id` = ".$search['co_id'];
      	}
      	if($search['status']!=""){
      		$where.=" AND lm.`status`=".$search['status'];
      	}
      	if(!empty($search['advance_search'])){
      		$s_where = array();
      		$s_search = addslashes(trim($search['advance_search']));
      		$s_where[] = " b.branch_namekh LIKE '%{$s_search}%'";
      		$s_where[] = " lm.`loan_number` LIKE '%{$s_search}%'";
      		$s_where[] = " name_kh LIKE '%{$s_search}%'";
      		$s_where[] = " lm.total_capital LIKE '%{$s_search}%'";
      		$where .=' AND ('.implode(' OR ',$s_where).')';
      	}
       	//echo $sql.$where.$group_by.$order;
      	return $db->fetchAll($sql.$where.$group_by.$order);
      }
      public function getAllTransferoan($search = null){//rpt-loan-released/
      	$db = $this->getAdapter();
      	$sql = "SELECT * FROM v_gettransferloan WHERE 1";
      	$where ='';
      
      	$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
      	$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
      	$where.= " AND ".$from_date." AND ".$to_date;
      
      	if($search['branch_id']>0){
      		$where.=" AND branch_id = ".$search['branch_id'];
      	}
      	if($search['client_name']>0){
      		$where.=" AND client_id = ".$search['client_name'];
      	}
      	if($search['co_id']>0){
      		$where.=" AND ( `from` = ".$search['co_id']." OR `to` = ".$search['co_id'].") ";
      	}
//       	if($search['pay_every']>0){
//       		$where.=" AND pay_term_id = ".$search['pay_every'];
//       	}
      	if(!empty($search['adv_search'])){
      		$s_where = array();
      		$s_search = addslashes(trim($search['adv_search']));
      		$s_where[] = " branch_name LIKE '%{$s_search}%'";
      		$s_where[] = " loan_number LIKE '%{$s_search}%'";
      		$s_where[] = " client_number LIKE '%{$s_search}%'";
      		$s_where[] = " client_name LIKE '%{$s_search}%'";
      		$s_where[] = " from_coname LIKE '%{$s_search}%'";
      		$s_where[] = " to_coname LIKE '%{$s_search}%'";
//       		$s_where[] = " other_fee LIKE '%{$s_search}%'";
//       		$s_where[] = " admin_fee LIKE '%{$s_search}%'";
//       		$s_where[] = " interest_rate LIKE '%{$s_search}%'";
//       		$s_where[] = " loan_type LIKE '%{$s_search}%'";
      		$where .=' AND ('.implode(' OR ',$s_where).')';
      	}
      	return $db->fetchAll($sql.$where);
      }
      public function getClientLoanCo($search = null){//rpt-loan-released
      	$db = $this->getAdapter();
      
      	$sql = "SELECT *,sum(total_capital) as alltotal_principle,count(level) as totallevel FROM v_released_co WHERE 1";
      	$where ='';
      	$from_date =(empty($search['start_date']))? '1': " date_release >= '".$search['start_date']." 00:00:00'";
      	$to_date = (empty($search['end_date']))? '1': " date_release <= '".$search['end_date']." 23:59:59'";
      	$where.= " AND ".$from_date." AND ".$to_date;
      	 
      	if($search['branch_id']>0){
      		$where.=" AND branch_id = ".$search['branch_id'];
      	}
      	if($search['member']>0){
      		$where.=" AND client_id = ".$search['member'];
      	}
      	if($search['co_id']>0){
      		$where.=" AND co_id = ".$search['co_id'];
      	}
      	if($search['pay_every']>0){
      		$where.=" AND pay_term_id = ".$search['pay_every'];
      	}
      	if(!empty($search['adv_search'])){
      		$s_where = array();
      		$s_search = addslashes(trim($search['adv_search']));
      		$s_where[] = " branch_name LIKE '%{$s_search}%'";
      		
      		$s_where[] = " client_number LIKE '%{$s_search}%'";
      		$s_where[] = " client_name LIKE '%{$s_search}%'";
//       		$s_where[] = " total_capital LIKE '%{$s_search}%'";
//       		$s_where[] = " other_fee LIKE '%{$s_search}%'";
//       		$s_where[] = " admin_fee LIKE '%{$s_search}%'";
      		$s_where[] = " name_en LIKE '%{$s_search}%'";
      		$s_where[] = " client_khname LIKE '%{$s_search}%'";
      		
      		$s_where[] = " loan_type LIKE '%{$s_search}%'";
      		$where .=' AND ('.implode(' OR ',$s_where).')';
      	}
      	$order = " GROUP BY client_id ORDER BY co_id DESC,totallevel DESC ";
      	return $db->fetchAll($sql.$where.$order);
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
 }

