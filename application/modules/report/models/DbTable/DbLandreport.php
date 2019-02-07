<?php
class Report_Model_DbTable_DbLandreport extends Zend_Db_Table_Abstract
{
      public function getAllLoan($search = null){//rpt-loan-released/
      	 $db = $this->getAdapter();
      	 $session_lang=new Zend_Session_Namespace('lang');
      	 $lang = $session_lang->lang_id;
      	 $str = 'name_en';
      	 if($lang==1){
      	 	$str = 'name_kh';
      	 }
      	 $sql = " SELECT * ,
      	 (SELECT COUNT(id) FROM `ln_saleschedule` WHERE sale_id=v_soldreport.id AND status=1 ) AS times,
      	 (SELECT first_name FROM `rms_users` WHERE id=v_soldreport.user_id LIMIT 1) AS user_name,
      	 (SELECT $str FROM `ln_view` WHERE key_code =v_soldreport.payment_id AND type = 25 limit 1) AS paymenttype
      	 FROM v_soldreport WHERE 1 ";

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
	    $from_date =(empty($search['start_date']))? '1': " $str >= '".$search['start_date']." 00:00:00'";
	    $to_date = (empty($search['end_date']))? '1': " $str <= '".$search['end_date']." 23:59:59'";
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
      	 $order = " ORDER BY is_cancel ASC,payment_id DESC ";
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
      	$sql = " SELECT * ,
      	(SELECT COUNT(id) FROM `ln_saleschedule` WHERE sale_id=v_soldreport.id ) AS times,
      	(SELECT first_name FROM `rms_users` WHERE id=v_soldreport.user_id LIMIT 1) AS user_name,
      	(SELECT $str FROM `ln_view` WHERE key_code =v_soldreport.payment_id AND type = 25 limit 1) AS paymenttype
      	FROM v_soldreport WHERE payment_id=1 AND is_cancel=0 ";
      
      	$where ='';
      	$to_date = (empty($search['end_date']))? '1': " validate_date <= '".$search['end_date']." 23:59:59'";
      	$where= " AND ".$to_date;
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
      $order = " ORDER BY payment_id DESC ";
      return $db->fetchAll($sql.$where.$order);
      }
      public function getAlertDeposit($search = null){//rpt-loan-released/
      		$db = $this->getAdapter();
	      	$sql =" SELECT * ,
		      	(SELECT COUNT(id) FROM `ln_saleschedule` WHERE sale_id=v_soldreport.id ) AS times,
		      	(SELECT name_en FROM `ln_view` WHERE key_code =v_soldreport.payment_id AND type = 25 limit 1) AS paymenttype
		      	FROM v_soldreport WHERE payment_id=1 AND is_cancel=0 ";
	      	$where ='';
	      	$from_date =(empty($search['start_date']))? '1': " validate_date >= '".$search['start_date']." 00:00:00'";
	      	$to_date = (empty($search['end_date']))? '1': " validate_date <= '".$search['end_date']." 23:59:59'";
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
	      		$s_where[] = " street LIKE '%{$s_search}%'";
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
	      	$order = " ORDER BY payment_id DESC ";
	      	
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
      	$sql="SELECT *,
			(SELECT SUM(s.total_interest_after) FROM `ln_saleschedule` AS s 
				WHERE s.total_interest_after> 0 AND  s.is_completed=0 AND s.sale_id = v_loanoutstanding.id LIMIT 1 ) as balance_interest
      	FROM v_loanoutstanding WHERE 1 ";//IF BAD LOAN STILL GET IT
      	
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
				SUM(`crm`.`total_principal_permonthpaid`+crm.extra_payment)
				 FROM  `ln_client_receipt_money` AS crm
      	 	WHERE crm.`sale_id`='".$land_id."' AND crm.status=1 LIMIT 1 ";
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
//       	(SELECT co_khname FROM ln_staff WHERE co_id=(SELECT co_id FROM ln_loan_group WHERE g_id=(SELECT lm.client_id FROM ln_loan_member AS lm  WHERE lm.member_id LIMIT 1) LIMIT 1 )LIMIT 1 ) AS co,
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
				  (SELECT co_khname FROM ln_staff WHERE co_id=(SELECT co_id FROM ln_loan_group WHERE g_id= m.group_id LIMIT 1) LIMIT 1) AS co,
				  (SELECT co_firstname FROM ln_staff WHERE co_id=(SELECT co_id FROM ln_loan_group WHERE g_id= m.group_id LIMIT 1) LIMIT 1) AS co_name
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
				  sd.no_installment,
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
		
        $group_by = " Group by s.id order by sd.date_payment ASC ";
//         echo $sql.$where.$group_by;exit();
      	return $db->fetchAll($sql.$where.$group_by);
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
		return $db->fetchAll($sql.$where.$order);
      }
      public function getALLLoanPayment($search=null,$order11=0){
      	$search['is_closed']='';
      	$db = $this->getAdapter();
      	$sql="SELECT *,
			(SELECT first_name FROM `rms_users` WHERE id=v_getcollectmoney.user_id LIMIT 1) AS user_name,
			(SELECT s.price_sold FROM `ln_sale` AS s WHERE s.id = sale_id LIMIT 1) AS sold_price,
			(SELECT COUNT(id) FROM `ln_saleschedule` WHERE sale_id=v_getcollectmoney.sale_id LIMIT 1) As times
      	FROM v_getcollectmoney WHERE status=1 ";
      	
      	$from_date =(empty($search['start_date']))? '1': " date_pay >= '".$search['start_date']." 00:00:00'";
      	$to_date = (empty($search['end_date']))? '1': " date_pay <= '".$search['end_date']." 23:59:59'";
      	$where = " AND ".$from_date." AND ".$to_date;
      	
      	if(!empty($search['user_id']) AND $search['user_id']>0){
      		$where.=" AND user_id = ".$search['user_id'];
      	}
      	if($search['client_name']>0){
      		$where.=" AND client_id = ".$search['client_name'];
      	} 
		if($search['branch_id']>0){
		        $where.=" AND branch_id = ".$search['branch_id'];
		}
		if(!empty($search['land_id']) AND $search['land_id']>0){
			$where.=" AND hous_id = ".$search['land_id'];
		}
		if(@$search['payment_method']>0){
			$where.=" AND payment_methodid = ".$search['payment_method'];
		}
		if (!empty($search['streetlist'])){
			$where.=" AND street = '".$search['streetlist']."'";
		}
		if ($search['is_closed']!=""){
			$where.=" AND is_closed = '".$search['is_closed']."'";
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
            $s_where[] = " payment_method LIKE '%{$s_search}%'";
      		$s_where[] = " penalize_amountpaid LIKE '%{$s_search}%'";  
      		$s_where[] = " service_chargepaid LIKE '%{$s_search}%'";
      		$s_where[] = " amount_payment LIKE '%{$s_search}%'";
      		$s_where[] = " receipt_no LIKE '%{$s_search}%'";
      		$where .=' AND ('.implode(' OR ',$s_where).')';
      	}
		$order = " ORDER BY id DESC ";
		if($order11==1){//for history
			$order = " ORDER BY client_id DESC ,sale_id DESC , id ASC";
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
      			$where="id= ".$i;
      			$this->update($arr, $where);
      		}
      	}
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
// 				    (SELECT co.`co_khname` FROM `ln_staff` AS co WHERE co.`co_id`=lcrm.`co_id`) AS co_name,
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
				  (SELECT CONCAT(c.`co_code`,'-',c.`co_khname`,'-',c.`co_firstname`,' ',c.`co_lastname`) FROM ln_staff AS c WHERE c.`co_id`=crm.`co_id`) AS co_name,
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
     
      public function getALLLoanExpectIncome($search=null){
      	$from_date =(empty($search['start_date']))? '1': " date_payment >= '".$search['start_date']." 00:00:00'";
      	$to_date = (empty($search['end_date']))? '1': " date_payment <= '".$search['end_date']." 23:59:59'";
      	$where="";
      	
      	$db = $this->getAdapter();
      	$sql = "SELECT *,
      	(SELECT sch.ispay_bank FROM `ln_saleschedule` AS sch WHERE sch.id = v_getexpectincome.id LIMIT 1  ) AS ispay_bank,
(SELECT ln_view.name_kh FROM ln_view WHERE ln_view.type =29 AND key_code = (SELECT sch.ispay_bank FROM `ln_saleschedule` AS sch WHERE sch.id = v_getexpectincome.id LIMIT 1  ) LIMIT 1) AS payment_type
      	FROM `v_getexpectincome` WHERE 1 ";
      	
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
      	if($search['schedule_opt']>0){
      		$where.= " AND payment_id = ".$search['schedule_opt'];
      	}
      	if($search['client_name']>0){
      		$where.= " AND client_id = ".$search['client_name'];
      	}
      	if($search['branch_id']>0){
      		$where.= " AND branch_id = ".$search['branch_id'];
      	}
      	if($search['stepoption']>0){
      		$where.=" AND (SELECT sch.ispay_bank FROM `ln_saleschedule` AS sch WHERE sch.id = v_getexpectincome.id LIMIT 1  ) = ".$search['stepoption'];
      	}else{
      		$where.= " AND ".$from_date." AND ".$to_date;
      		if ($search['stepoption']==0){
      			$where.= " AND (SELECT sch.ispay_bank FROM `ln_saleschedule` AS sch WHERE sch.id = v_getexpectincome.id LIMIT 1  )=0";
      		}
      	}
      //GROUP BY id,date_payment
      	$group_by = " GROUP BY id ORDER BY id ASC,date_payment DESC ";
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
//       public function getALLNPLLoan($search=null){
//       	    $db = $this->getAdapter();
//       		$end_date =(empty($search['end_date']))? '1': " date_payment <= '".$search['end_date']." 23:59:59'";
//       	    $db = $this->getAdapter();

//       		$sql="SELECT * FROM v_getnplloan ";
//       		$where=" WHERE ".$end_date;
//       		if(!empty($search['adv_search'])){
      			
//       			$s_where = array();
//       			$s_search = addslashes(trim($search['adv_search']));
//       			$s_where[] = " branch_name LIKE '%{$s_search}%'";
//       			$s_where[] = " `loan_number` LIKE '%{$s_search}%'";
//       			$s_where[] = " `client_number` LIKE '%{$s_search}%'";
//       			$s_where[] = " `name_kh` LIKE '%{$s_search}%'";
//       			$s_where[] = " `total_capital` LIKE '%{$s_search}%'";
//       			$s_where[] = " `interest_rate` LIKE '%{$s_search}%'";
//       			$s_where[] = " `total_duration` LIKE '%{$s_search}%'";
//       			$s_where[] = " `term_borrow` LIKE '%{$s_search}%'";
//       			$s_where[] = " `total_principal` LIKE '%{$s_search}%'";
      			
//       			$where .=' AND ('.implode(' OR ',$s_where).')';
//       		}
//       		if($search['branch_id']>0){
//       			$where.=" AND `branch_id` = ".$search['branch_id'];
//       		}
//       		if(!empty($search['cash_type'])){
//       			$where.=" AND `curr_type` = ".$search['cash_type'];
//       		}
//       		return $db->fetchAll($sql.$where);
//       }
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
				  `ln_staff` AS co,
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
		  $sql="SELECT *,
		  		(SELECT project_name FROM `ln_project` WHERE br_id=crm.branch_id LIMIT 1) AS project_name,
				(SELECT p.land_address  FROM `ln_properties` AS p WHERE p.id  = crm.`land_id` LIMIT 1) AS land_address,
				(SELECT p.old_land_id  FROM `ln_properties` AS p WHERE p.id  = crm.`land_id` LIMIT 1) AS landlot_amount,
				(SELECT pt.type_nameen FROM `ln_properties_type` AS pt WHERE pt.id = (SELECT p.property_type  FROM `ln_properties` AS p WHERE p.id  = crm.`land_id` LIMIT 1) LIMIT 1)AS property_type,
				(SELECT p.street  FROM `ln_properties` AS p WHERE p.id  = crm.`land_id` LIMIT 1) AS street,
				(SELECT s.sale_number FROM `ln_sale` AS s WHERE s.id = crm.sale_id LIMIT 1) AS sale_number,
				(SELECT s.land_price FROM `ln_sale` AS s WHERE s.id = crm.sale_id LIMIT 1) AS land_price,
				(SELECT s.price_sold FROM `ln_sale` AS s WHERE s.id = crm.sale_id LIMIT 1) AS price_sold,
				(SELECT s.total_duration FROM `ln_sale` AS s WHERE s.id = crm.sale_id LIMIT 1) AS total_duration,
				(SELECT date_payment FROM `ln_saleschedule` WHERE sale_id= crm.sale_id AND status=1 AND no_installment>payment_times ORDER BY date_payment ASC LIMIT 1) as nextdate_payment,
				(SELECT c.name_kh FROM `ln_client` AS c WHERE c.client_id = crm.client_id LIMIT 1) AS name_kh,
				(SELECT c.phone FROM `ln_client` AS c WHERE c.client_id = crm.client_id LIMIT 1) AS phone,
				(SELECT
			     	`d`.`date_payment`
			   		FROM `ln_client_receipt_money_detail` `d`
			   		WHERE (`crm`.`id` = `d`.`crm_id`)
			   		ORDER BY `d`.`date_payment` ASC
			   LIMIT 1) AS `date_payment`,
			   crm.payment_method as payment_methodid,
				(SELECT `ln_view`.`name_kh` FROM `ln_view` WHERE ((`ln_view`.`key_code` = `crm`.`payment_method`)
         		 AND (`ln_view`.`type` = 2))LIMIT 1) AS `payment_method`,
				(SELECT c.hname_kh FROM `ln_client` AS c WHERE c.client_id = crm.client_id LIMIT 1) AS hname_kh,
				(SELECT CONCAT(last_name,' ',first_name) FROM `rms_users` WHERE rms_users.id=crm.`user_id` LIMIT 1) As by_user
			 FROM `ln_client_receipt_money` AS crm WHERE crm.`id`=".$id;
		  $rs = $db->fetchRow($sql);
		  $rs['property_type']=ltrim(strstr($rs['property_type'], '('), '.');
		  if(empty($rs)){return ''; }else{
				return $rs;
			}
		
	  }
	  
	  
public static function getUserId(){
	  	$session_user=new Zend_Session_Namespace('authinstall');
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
		return round($value,2);
	}
}
function updateReceipt($data){
	$db= $this->getAdapter();
	$db->beginTransaction();
	try{
	$session_user=new Zend_Session_Namespace('authinstall');
	$user_id = $session_user->user_id;
	
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
// 			'user_id'						=>	$user_id,
			'status'						=>	1,
// 			'is_completed'					=>	$is_compleated,
// 			'field3'						=>3,
// 			'payment_option'				=>	1,
			'payment_method'				=>	$data['payment_method'],
// 			'land_id'						=>	$data['property_id'],
// 			'branch_id'						=>	$data["branch_id"],
// 			'service_charge'				=>	$data["service_charge"],
// 			'service_chargepaid'			=>	$service,// okបានបង
			'extra_payment' 				=> $data["extra_payment"],
			'payment_times'					=>$data['paid_times']
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
//     		'crm_id'				=>$id,
//     		'lfd_id'				=>$row['id'],
//     		'service_charge'		=>0,
//     		'old_total_payment'	 =>$row["total_payment_after"],
//     	    'is_completed'			=>$statuscomplete,
//     	    'status'				=>1,
//     		'land_id'				=>$data['loan_number'],
//     		'date_payment'			=>$row['date_payment'],
    );
	$where = " crm_id = ".$data['id'];
    $this->_name="ln_client_receipt_money_detail";
    $this->update($array, $where);
    $db->commit();
	}catch (Exception $e){
		$db->rollBack();
		Application_Form_FrmMessage::message("INSERT_FAIL");
		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	}
	
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
// 	  									'id'=>$data['fundid_'.$j],//good
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
	  				$interest_paymonth = $remain_principal*(($data['interest_rate']/12)/100);//fixed 30day
	  				$interest_paymonth = $this->round_up_currency($curr_type, $interest_paymonth);
	  				if($data['install_type']==2){
	  					$pri_permonth=$data['total_remain']/($data['period']*$term_types);
	  					$pri_permonth =$this->round_up_currency(2, $pri_permonth);
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
// 	  							'note'=>$data['remark'.$i],
	  							'percent'=>$data['percent'.$i],
	  							'percent_agree'=>$data['percent_agreement'.$i],
	  							'is_installment'=>1,
	  							'no_installment'=>$key,
	  							'last_optiontype'=>$paid_receivehouse,
	  							'ispay_bank'=>$data['pay_with'.$i],
	  					);
	  					if($payment_method==5){//with bank
	  						//$datapayment['ispay_bank']= $data['pay_with'.$i];
	  					}
	  					
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
	  			}
	  			if($payment_method==3 OR $payment_method==4){//បង់ថេរនឹងរំលស់
	  				$old_remain_principal =$old_remain_principal+$remain_principal;
	  				$old_pri_permonth = $old_pri_permonth+$pri_permonth;
	  				$old_interest_paymonth = $this->round_up_currency($curr_type,($old_interest_paymonth+$interest_paymonth));
	  				$cum_interest = $cum_interest+$old_interest_paymonth;
	  				$old_amount_day =$old_amount_day+ $amount_day;
	  				$this->_name="ln_saleschedule";
	  				$datapayment = array(
// 	  						'branch_id'=>$data['branch_id'],
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
	  					//$next_payment = $dbtable->getNextPayment($str_next, $next_payment, 1,3,$data['first_payment']);
	  				}else{
	  					//$next_payment = $dbtable->checkFirstHoliday($next_payment,3);//normal day
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
// 	  									'id'=>$data['fundid_'.$j],//good
	  									'sale_id'=>$data['id'],//good
	  									'begining_balance'=> $old_remain_principal,//good
	  									//'begining_balance_after'=> $old_remain_principal,//good
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
	  									//'percent'=>$data['percent'.$j],
	  									//'is_installment'=>1,
	  									//'no_installment'=>$key,
	  									//'last_optiontype'=>$paid_receivehouse,
	  							);
	  							$key = $key+1;
	  							$installment_paid = $installment_paid+$data['principal_permonth_'.$j];
	  							if($data['payment_option'.$j]==1 OR !empty($data['paid_amount_'.$j])){//complete or paid
	  								$is_completed = 0;
	  								if($data['payment_option'.$j]==1){$is_completed=1;}
// 	  								$datapayment = array(
// 	  										'sale_id'=>$data['id'],
// 	  										'branch_id'=>$data['branch_id'],
// 	  										'begining_balance'=> $old_remain_principal,//good
// 	  										'begining_balance_after'=> $old_remain_principal,//good
// 	  										'principal_permonth'=> $data['principal_permonth_'.$j],//good
// 	  										'principal_permonthafter'=>$old_pri_permonth-$data['paid_principal'.$j],//good
// 	  										'total_interest'=>$old_interest_paymonth,//good
// 	  										'total_interest_after'=>$old_interest_paymonth,//good
// // 	  									'total_payment'=>$old_interest_paymonth+$old_pri_permonth-$data['paid_amount_'.$j],//good
// 	  										'total_payment_after'=>$old_interest_paymonth+$old_pri_permonth-$data['paid_amount_'.$j],//good
// 	  										'ending_balance'=>$old_remain_principal-$old_pri_permonth,
// // 	  									'is_completed'=>$is_completed,//good
// 	  										'is_installment'=>1,
// 	  										'no_installment'=>$key,
// 	  										'date_payment'=>$data['date_payment'.$j],
// 	  								);
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
	  									//$datapayment['is_installment']=1;
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
	  				$interest_paymonth = $remain_principal*(($data['interest_rate']/12)/100);//fixed 30day
	  				$interest_paymonth = $this->round_up_currency($curr_type, $interest_paymonth);
	  				if($data['install_type']==2){
	  					$pri_permonth=$data['total_remain']/($data['period']*$term_types);
	  					$pri_permonth =$this->round_up_currency(2, $pri_permonth);
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
// 	  							'begining_balance_after'=> $old_remain_principal,//good
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
// 	  							'note'=>$data['remark'.$i],
// 	  							'percent'=>$data['percent'.$i],
	  							//'is_installment'=>1,
	  							//'no_installment'=>$key,
	  							//'last_optiontype'=>$paid_receivehouse,
	  					);
	  					$from_date = $data['date_payment'.$i];
	  					$key = $key+1;
	  					if($data['payment_option'.$i]==1 OR !empty($data['paid_amount_'.$i])){
	  						//$datapayment['is_installment']=1;
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
	  			}
	  			if($payment_method==3 OR $payment_method==4){//បង់ថេរនឹងរំលស់
	  				$old_remain_principal =$old_remain_principal+$remain_principal;
	  				$old_pri_permonth = $old_pri_permonth+$pri_permonth;
	  				$old_interest_paymonth = $this->round_up_currency($curr_type,($old_interest_paymonth+$interest_paymonth));
	  				$cum_interest = $cum_interest+$old_interest_paymonth;
	  				$old_amount_day =$old_amount_day+ $amount_day;
	  				//if($data['payment_option'.$i]==1 OR !empty($data['paid_amount_'.$i])){ continue;}
	  				$this->_name="ln_saleschedule_test";
	  				$datapayment = array(
	  				// 	  						'branch_id'=>$data['branch_id'],
	  						'sale_id'=>$data['id'],//good
	  						'begining_balance'=> $old_remain_principal,//good
	  						//'begining_balance_after'=> $old_remain_principal-$paid_principal,//good
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
	  						//'no_installment'=>$i+$j,
	  					//	'last_optiontype'=>$paid_receivehouse,
	  				);
	  				if($payment_method==3){//បង់ថេរ
	  					if($old_remain_principal-$old_pri_permonth<0){
	  						break;
	  					}
	  					if($data['payment_option'.$i]==1 OR !empty($data['paid_amount_'.$i])){//ផ្តាច់
	  						//$datapayment['is_installment']=1;
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
	  							//$datapayment['is_installment']=1;
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
	  		//
// 	  		$dbtable = new Application_Model_DbTable_DbGlobal();
// 	  		$dbtable->updateLateRecordSaleschedule($data['id']);
	  		
	  		if($payment_method==3 OR $payment_method==4 OR $payment_method==6 OR $payment_method==5){
		  		$sql = " SELECT t.* , DATE_FORMAT(t.date_payment, '%d-%m-%Y') AS date_payments,
		  		DATE_FORMAT(t.date_payment, '%Y-%m-%d') AS date_name FROM
		  		ln_saleschedule_test AS t WHERE t.sale_id = ".$data['id']." ORDER BY date_payment asc";
		  		$rows = $db->fetchAll($sql);
	  		}else{
	  			$sql = " SELECT *,'row_id' FROM ln_sale_test WHERE id = ".$data['id'];
	  			$rows = $db->fetchRow($sql);
	  		}
// 	  		print_r($rows);
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
  					$where = "id = ".$data['fundid_'.$i];
  					$this->update($datapayment, $where);
	  		}	
		$db->commit();
	  	}catch (Exception $e){
	  		$db->rollBack();
	  		return $e->getMessage();
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
      function updatePaid(){
      	$db = $this->getAdapter();
      	$sql=" SELECT s.id,
      	(SELECT name_kh FROM `ln_client` WHERE client_id=s.client_id LIMIT 1 )AS client_name,
      	s.payment_id,
      	s.client_id,s.price_sold,s.house_id,
      	(SELECT SUM(`cr`.`total_principal_permonthpaid`) FROM `ln_client_receipt_money` `cr` WHERE (`cr`.`sale_id` = `s`.`id`)) AS `paid_amount`,
      	(SELECT (`cr`.`total_principal_permonthpaid`) FROM `ln_client_receipt_money` `cr` WHERE (`cr`.`sale_id` = `s`.`id`) ORDER BY `cr`.`id` DESC LIMIT 1) AS `paid_lastamount`,
      	(SELECT `cr`.`id` FROM `ln_client_receipt_money` `cr` WHERE (`cr`.`sale_id` = `s`.`id`) ORDER BY `cr`.`id` DESC LIMIT 1) AS `crm_id`,
      	(SELECT SUM(ss.principal_permonth) FROM `ln_saleschedule` AS ss WHERE ss.sale_id= `s`.`id` AND is_completed=1 AND STATUS=1 LIMIT 1) AS realpaid,
      	(SELECT count(ss.id) FROM `ln_saleschedule` AS ss WHERE ss.sale_id= `s`.`id` AND is_completed=1 AND STATUS=1 LIMIT 1) AS countpaid
      	FROM `ln_sale` AS s WHERE is_completed=0 AND STATUS=1 AND is_cancel=0 AND s.payment_id!=1 AND s.payment_id!=2";
      	$rs=$db->fetchAll($sql);
      	if(!empty($rs)){
      		foreach($rs as $ss){
      			$ss['paid_amount']=0;
      			if($ss['realpaid']>$ss['paid_amount']){
      				$session_user=new Zend_Session_Namespace('authinstall');
      				$user_id = $session_user->user_id;
      				$dbre = new Application_Model_DbTable_DbGlobal();
      				$receipt = $dbre->getReceiptByBranch(array("branch_id"=>1));
      					
      				$total_paid = $ss['realpaid'];//-$ss['paid_amount'];
      				$outstanding = $ss['price_sold']-$ss['realpaid'];
      				$arr_client_pay = array(
      						'sale_id'=>$ss['id'],
      						'branch_id'=>1,
      						'land_id'=>$ss['house_id'],
      						'receipt_no'					=>	$receipt,
      						'date_pay'					    =>	date("Y-m-d"),
      						'date_input'					=>	date("Y-m-d"),
      						'client_id'                     =>	$ss['client_id'],
      						'outstanding'                   =>	$outstanding,//ប្រាក់ដើមមុនបង់
      						'total_principal_permonth'		=>	$total_paid,//ប្រាក់ដើមត្រូវបង់
      						'total_interest_permonth'		=>	0,
      						'penalize_amount'				=>	0,
      						'principal_amount'				=>	$outstanding-$total_paid,//ប្រាក់ដើមនៅសល់បន្ទប់ពីបង់
      						'total_principal_permonthpaid'	=>	$total_paid,//ok ប្រាក់ដើមបានបង
      						'total_interest_permonthpaid'	=>	0,//ok ការប្រាក់បានបង
      						'penalize_amountpaid'			=>	0,// ok បានបង
      						'balance'						=>	0,
      						'total_payment'					=>	$total_paid,//ប្រាក់ត្រូវបង់ok
      						'recieve_amount'				=>	$total_paid,//ok
      						'amount_payment'				=>	$total_paid,//brak ban borng
      						'return_amount'					=>	0,//ok
      						'note'							=>	"ចេញពីSystem",
      						'cheque'						=>	"",
      						'user_id'						=>	$user_id,
      						'status'						=>	1,
      						'extra_payment' 				=>  0,
      						'from_system'					=>  1,
      						'payment_times'					=> $ss['countpaid']+1);
      				$this->_name="ln_client_receipt_money";
      				$id = $this->insert($arr_client_pay);
      					
      				$array = array(
      						'crm_id'=>$id,
      						'client_id'				 =>$ss['client_id'],
      						'paid_date'              => date("Y-m-d"),
      						'capital'				 => $outstanding,
      						'remain_capital'		 => $outstanding-$total_paid,
      						'principal_permonth'	 => $total_paid,
      						'total_interest'		 => 0,
      						'total_payment'			 => $total_paid,
      						'total_recieve'			 => $total_paid,
      						'penelize_amount'		 => 0,
      						'old_interest'			 => 0,
      						'old_principal_permonth' => 0,
      						'last_pay_date'          => date("Y-m-d"),
      				);
      				$this->_name="ln_client_receipt_money_detail";
      				$this->insert($array);
      			}
      			/*
      			 if($ss['paid_amount']>$ss['realpaid']){
      			$rail_amount =$ss['paid_amount']-$ss['realpaid'];
      			$real_paid = $ss['paid_lastamount']-($ss['paid_amount']-$ss['realpaid']); ;
      			$session_user=new Zend_Session_Namespace('authinstall');
      			$user_id = $session_user->user_id;
      			$dbre = new Application_Model_DbTable_DbGlobal();
      			$receipt = $dbre->getReceiptByBranch(array("branch_id"=>1));
      			//$total_paid = $ss['realpaid']-$ss['paid_amount'];
      			//$outstanding = $ss['price_sold']-$ss['realpaid'];
      			$arr_client_pay = array(
      					//'sale_id'=>$ss['id'],
      					//'branch_id'=>1,
      					// 	  						'land_id'=>$ss['house_id'],
      					// 	  						'receipt_no'					=>	$receipt,
      					// 	  						'date_pay'					    =>	date("Y-m-d"),
      					// 	  						'date_input'					=>	date("Y-m-d"),
      					// 	  						'client_id'                     =>	$ss['client_id'],
      					// 	  						'outstanding'                   =>	$outstanding,//ប្រាក់ដើមមុនបង់
      					'total_principal_permonth'		=>	$real_paid,//ប្រាក់ដើមត្រូវបង់
      					'total_interest_permonth'		=>	0,
      					'penalize_amount'				=>	0,
      					// 	  						'principal_amount'				=>	$outstanding-$total_paid,//ប្រាក់ដើមនៅសល់បន្ទប់ពីបង់
      					'total_principal_permonthpaid'	=>	$real_paid,//ok ប្រាក់ដើមបានបង
      					'total_interest_permonthpaid'	=>	0,//ok ការប្រាក់បានបង
      					'penalize_amountpaid'			=>	0,// ok បានបង
      					'balance'						=>	0,
      					'total_payment'					=>	$real_paid,//ប្រាក់ត្រូវបង់ok
      					'recieve_amount'				=>	$real_paid,//ok
      					'amount_payment'				=>	$real_paid,//brak ban borng
      					'return_amount'					=>	0,//ok
      					'note'							=>	"ពីSystemលើកទី3",
      					'cheque'						=>	"",
      					'user_id'						=>	$user_id,
      					'status'						=>	1,
      					'extra_payment' 				=>  0,
      					'from_system'					=>  1,
      			);
      			$this->_name="ln_client_receipt_money";
      			$where=" id = ".$ss['crm_id'];
      			$this->update($arr_client_pay, $where);
      
      			$array = array(
      					// 	  						'crm_id'=>$id,
      					// 	  						'client_id'				 =>$ss['client_id'],
      					// 	  						'paid_date'              => date("Y-m-d"),
      					// 	  						'capital'				 => $outstanding,
      					// 	  						'remain_capital'		 => $outstanding-$total_paid,
      					'principal_permonth'	 => $real_paid,
      					'total_interest'		 => 0,
      					'total_payment'			 => $real_paid,
      					'total_recieve'			 => $real_paid,
      					'penelize_amount'		 => 0,
      					'old_interest'			 => 0,
      					'old_principal_permonth' => 0,
      					// 	  						'last_pay_date'          => date("Y-m-d"),
      			);
      			$this->_name="ln_client_receipt_money_detail";
      			$where=" crm_id = ".$ss['crm_id'];
      			$this->update($array, $where);
      			// 	  				$this->insert($array);
      			}*/
      
      			// 	  			if($ss['realpaid']>$ss['paid_amount']){
      			// 	  				$session_user=new Zend_Session_Namespace('authinstall');
      			// 	  				$user_id = $session_user->user_id;
      			// 	  				$dbre = new Application_Model_DbTable_DbGlobal();
      			// 	  				$receipt = $dbre->getReceiptByBranch(array("branch_id"=>1));
      			// 	  				$total_paid = $ss['realpaid']-$ss['paid_amount'];
      			// 	  				$outstanding = $ss['price_sold']-$ss['realpaid'];
      			// 	  				$arr_client_pay = array(
      					// 	  						'sale_id'=>$ss['id'],
      					// 	  						'branch_id'=>1,
      					// 	  						'land_id'=>$ss['house_id'],
      					// 	  						'receipt_no'					=>	$receipt,
      					// 	  						'date_pay'					    =>	date("Y-m-d"),
      					// 	  						'date_input'					=>	date("Y-m-d"),
      					// 	  						'client_id'                     =>	$ss['client_id'],
      					// 	  						'outstanding'                   =>	$outstanding,//ប្រាក់ដើមមុនបង់
      					// 	  						'total_principal_permonth'		=>	$total_paid,//ប្រាក់ដើមត្រូវបង់
      					// 	  						'total_interest_permonth'		=>	0,
      					// 	  						'penalize_amount'				=>	0,
      					// 	  						'principal_amount'				=>	$outstanding-$total_paid,//ប្រាក់ដើមនៅសល់បន្ទប់ពីបង់
      					// 	  						'total_principal_permonthpaid'	=>	$total_paid,//ok ប្រាក់ដើមបានបង
      					// 	  						'total_interest_permonthpaid'	=>	0,//ok ការប្រាក់បានបង
      					// 	  						'penalize_amountpaid'			=>	0,// ok បានបង
      					// 	  						'balance'						=>	0,
      					// 	  						'total_payment'					=>	$total_paid,//ប្រាក់ត្រូវបង់ok
      					// 	  						'recieve_amount'				=>	$total_paid,//ok
      					// 	  						'amount_payment'				=>	$total_paid,//brak ban borng
      					// 	  						'return_amount'					=>	0,//ok
      					// 	  						'note'							=>	"ពីSystemលើកទី២",
      					// 	  						'cheque'						=>	"",
      					// 	  						'user_id'						=>	$user_id,
      					// 	  						'status'						=>	1,
      					// 	  						'extra_payment' 				=>  0,
      					// 	  						'from_system'					=>  1,
      					// 	  						'payment_times'					=> $ss['countpaid']+1);
      			// 	  				$this->_name="ln_client_receipt_money";
      			// 	  				$id = $this->insert($arr_client_pay);
      	  	
      			// 	  				$array = array(
      					// 	  						'crm_id'=>$id,
      					// 	  						'client_id'				 =>$ss['client_id'],
      					// 	  						'paid_date'              => date("Y-m-d"),
      					// 	  						'capital'				 => $outstanding,
      					// 	  						'remain_capital'		 => $outstanding-$total_paid,
      					// 	  						'principal_permonth'	 => $total_paid,
      					// 	  						'total_interest'		 => 0,
      					// 	  						'total_payment'			 => $total_paid,
      					// 	  						'total_recieve'			 => $total_paid,
      					// 	  						'penelize_amount'		 => 0,
      					// 	  						'old_interest'			 => 0,
      					// 	  						'old_principal_permonth' => 0,
      					// 	  						'last_pay_date'          => date("Y-m-d"),
      					// 	  				);
      			// 	  				$this->_name="ln_client_receipt_money_detail";
      			// 	  				$this->insert($array);
      			// 	  			}
      		}
      	}
      }
      public function getPaymentSaleid($sale_id){
      	$db = $this->getAdapter();
      	$sql="SELECT *,
      	(SELECT first_name FROM `rms_users` WHERE id=v_getcollectmoney.user_id) AS user_name
      	FROM v_getcollectmoney WHERE status=1 AND sale_id= ".$sale_id;
      	$order=" ORDER BY id DESC ";
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
      	 
      	$from_date =(empty($search['start_date']))? '1': " w.change_date >= '".$search['start_date']." 00:00:00'";
      	$to_date = (empty($search['end_date']))? '1': " w.change_date <= '".$search['end_date']." 23:59:59'";
      	$where = " AND ".$from_date." AND ".$to_date;
      	if(!empty($search['adv_search'])){
      		$s_where = array();
      	}
//       	if($search['status']>-1){
//       		$where.= " AND w.status = ".$search['status'];
//       	}
      	if(($search['branch_id'])>0){
      		$where.= " AND w.branch_id=".$search['branch_id'];
      	}
//       	if(($search['client_name'])>0){
//       		$where.= " AND ( w.from_customer= ".$search['client_name']." OR w.to_customer = ".$search['client_name']." )";
//       	}
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
   public function getSaleSummary($search = null){//rpt-loan-released/
   	$db = $this->getAdapter();
   	$session_lang=new Zend_Session_Namespace('lang');
   	$lang = $session_lang->lang_id;
   	$str = 'name_en';
   	if($lang==1){
   		$str = 'name_kh';
   	}
   	$sql = "SELECT * ,
   				(SELECT SUM(extra_payment) FROM `ln_client_receipt_money` WHERE STATUS=1  AND sale_id = v_soldreport.id LIMIT 1) AS extra_payment,
   				(SELECT SUM(total_interest_permonthpaid) FROM `ln_client_receipt_money` WHERE STATUS=1  AND sale_id = v_soldreport.id LIMIT 1) AS total_interest_permonthpaid,
   				(SELECT SUM(penalize_amountpaid) FROM `ln_client_receipt_money` WHERE STATUS=1  AND sale_id = v_soldreport.id LIMIT 1) AS penalize_amountpaid,
   				
   				
			   	(SELECT COUNT(id) FROM `ln_saleschedule` WHERE sale_id=v_soldreport.id AND status=1 ) AS times,
			   	(SELECT first_name FROM `rms_users` WHERE id=v_soldreport.user_id LIMIT 1) AS user_name,
			   	(SELECT $str FROM `ln_view` WHERE key_code =v_soldreport.payment_id AND type = 25 limit 1) AS paymenttype
   		FROM v_soldreport WHERE is_cancel=0 ";
   
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
   		$from_date =(empty($search['start_date']))? '1': " $str >= '".$search['start_date']." 00:00:00'";
   		$to_date = (empty($search['end_date']))? '1': " $str <= '".$search['end_date']." 23:59:59'";
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
   		$s_where[] = " street LIKE '%{$s_search}%'";
   		$where .=' AND ( '.implode(' OR ',$s_where).')';
   		}
   		if($search['branch_id']>0){
   		$where.=" AND branch_id = ".$search['branch_id'];
   		}
   		if(!empty($search['streetlist']) AND $search['streetlist']>-1){
   			$where.=" AND street = '".$search['streetlist']."'";
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
	   $order = " ORDER BY is_cancel ASC,payment_id DESC ";
	   
	   return $db->fetchAll($sql.$where.$order);
   }
   
   function getAllIncomeOtherDetail($search=null){
   	$db = $this->getAdapter();
   	$session_user=new Zend_Session_Namespace('authinstall');
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
		
   	 ";
   
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
//    	if(!empty($search['payment_method'])){
//    		$where.= " AND payment_method = ".$search['payment_method'];
//    	}
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
   
   function UpdatePaytimeBooking(){
   	$db = $this->getAdapter();
	   	$sql="SELECT crm.sale_id,crm.id,crm.payment_times
	FROM `ln_client_receipt_money` AS crm WHERE crm.field3 =1
	ORDER BY crm.sale_id,crm.id ASC";
	$row = $db->fetchAll($sql);
	   	$sale=""; $i=0;
   	if (!empty($row)) foreach ($row as $ddd){
   		if ($sale!=$ddd['sale_id']){
   			$i=0;
   		}
   		$i++;
   		$array =  array(
   				'payment_times'	=>$i,
   		);
   		$where=" id = ".$ddd['id'];
   		$this->_name="ln_client_receipt_money";
   		$this->update($array, $where);
   		
   		$sale = $ddd['sale_id'];
   	}
   }
   public function getAllRemainMonth($search = null){//rpt-loan-released/
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
 }

