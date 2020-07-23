<?php
class Report_Model_DbTable_DbloanCollect extends Zend_Db_Table_Abstract
{
      
       protected  $db_name='ln_loanmember_funddetail';
//     public function getUserId(){
//     	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
//     	return $session_user->user_id;
//     }
    public function getAllLnClient($search=null){
    	$db=$this->getAdapter();
   		$end_date = $search['end_date'];
    	$sql = "SELECT v.*,
	    	SUM(v.principal_permonthafter) AS principal_permonthafter,
	    	SUM(v.total_interest_after) AS total_interest_after,
			(SELECT sch.ispay_bank FROM `ln_saleschedule` AS sch WHERE sch.id = v.id LIMIT 1  ) AS ispay_bank,
			(SELECT date_input FROM `ln_client_receipt_money` WHERE sale_id=v.sale_id ORDER BY date_input DESC LIMIT 1) As last_pay_date,
			(SELECT s.expect_income_note FROM ln_sale AS s WHERE s.id = v.sale_id LIMIT 1) AS expect_income_note,
			(SELECT ln_view.name_kh FROM ln_view WHERE ln_view.type =29 AND key_code = (SELECT sch.ispay_bank FROM `ln_saleschedule` AS sch WHERE sch.id = v.id LIMIT 1  ) LIMIT 1) AS payment_type
			 FROM v_newloancolect AS v WHERE 1 ";
    	$where ='';
    	
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission("v.branch_id");
    	
    	$to_date = (empty($search['end_date']))? '1': " v.date_payment <= '".$search['end_date']." 23:59:59'";
    	
    	if($search['client_name']>0){
    		$where.=" AND v.client_id = ".$search['client_name'];
    	}
    	if($search['branch_id']>0){
    		$where.=" AND v.branch_id = ".$search['branch_id'];
    	}
    	if($search['stepoption']>0){
    		$where.=" AND (SELECT sch.ispay_bank FROM `ln_saleschedule` AS sch WHERE sch.id = v.id LIMIT 1  ) = ".$search['stepoption'];
    	}else{
    		$where.= " AND ".$to_date;
    		if ($search['stepoption']==0){
    			$where.= " AND (SELECT sch.ispay_bank FROM `ln_saleschedule` AS sch WHERE sch.id = v.id LIMIT 1  )=0";
    		}
    	}
    	
    	if($search['last_optiontype']>-1){
    		$where.=" AND v.last_optiontype = ".$search['last_optiontype'];
    	}
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = trim(addslashes($search['adv_search']));
    		$s_where[] = " v.sale_number LIKE '%{$s_search}%'";
    		$s_where[] = " v.client_number LIKE '%{$s_search}%'";
    		$s_where[] = " v.phone_number LIKE '%{$s_search}%'";
    		$s_where[] = " v.client_name LIKE '%{$s_search}%'";
    		$s_where[] = " v.land_code LIKE '%{$s_search}%'";
    		$s_where[] = " v.land_address LIKE '%{$s_search}%'";
    		$s_where[] = " v.street LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	$order=" GROUP BY v.sale_id,(SELECT sch.ispay_bank FROM `ln_saleschedule` AS sch WHERE sch.id = v.id LIMIT 1) ORDER BY  v.date_payment ASC ";
    	return $db->fetchAll($sql.$where.$order);
    }
    function getCustomerNearlyPayment(){
    	$db=$this->getAdapter();
    	$search['start_date'] = "";
    	$search['end_date']= date('Y-m-d');
    	
    	$dbgb = new Setting_Model_DbTable_DbGeneral();
    	$alert = $dbgb->geLabelByKeyName('payment_day_alert');
    	$search['end_date']= date('Y-m-d');
    	if (!empty($alert['keyValue'])){
    		$amt_day = $alert['keyValue'];
    		$search['end_date']= date('Y-m-d',strtotime("+$amt_day day"));
    	}
    	
    	$sql = "SELECT *,
    		SUM(principal_permonthafter) AS principal_permonthafter,
			SUM(total_interest_after) AS total_interest_after,
			SUM(service_charge) AS service_charge,
			COUNT(id) AS amount_late,
				(SELECT(c.phone) FROM `ln_client` c WHERE c.client_id =v_newloancolect.client_id LIMIT 1) AS phone
    		FROM v_newloancolect WHERE last_optiontype=1 AND is_completed = 0 ";
    	$where ='';
    	$from_date =(empty($search['start_date']))? '1': " date_payment <= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " date_payment <= '".$search['end_date']." 23:59:59'";
    	$where= " AND ".$from_date." AND ".$to_date;
    	$where.= " AND (SELECT sch.ispay_bank FROM `ln_saleschedule` AS sch WHERE sch.id = v_newloancolect.id LIMIT 1  )=0";
    	
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission("branch_id");
    	
    	$order=" GROUP BY sale_id ORDER BY date_payment DESC ,sale_id ASC, id ASC";
    	return $db->fetchAll($sql.$where.$order);
    }
    function getCustomerNearAgreement(){
    	$db=$this->getAdapter();
    	$dbgb = new Setting_Model_DbTable_DbGeneral();
    	$alert = $dbgb->geLabelByKeyName('agree_day_alert');
    	$search['end_date']= date('Y-m-d');
    	if (!empty($alert['keyValue'])){
    		$amt_day = $alert['keyValue'];
    		$search['end_date']= date('Y-m-d',strtotime("+$amt_day day"));
    	}
    	
    	$sql="SELECT
			  `s`.`id`               AS `id`,
			  `s`.`price_sold`       AS `price_sold`,
			  `s`.`second_depostit`         AS `second_depostit`,
			  `s`.`end_line`         AS `end_line`,
			  `p`.`land_address`     AS `land_address`,
			  `p`.`street`           AS `street`,
			   c.phone,
			  `c`.`name_kh`          AS `name_kh`,
  				(SELECT pr.project_name FROM `ln_project` AS pr WHERE pr.br_id = `p`.`branch_id` LIMIT 1 ) AS branch_name
			FROM ((`ln_sale` `s`
			    JOIN `ln_client` `c`)
			   JOIN `ln_properties` `p`)
			WHERE ((`c`.`client_id` = `s`.`client_id`)
			       AND (`p`.`id` = `s`.`house_id`)
			       AND (`s`.`status` = 1)
    			   AND s.payment_id=1 and s.is_cancel=0	) ";
    	$where ='';
    	$to_date = (empty($search['end_date']))? '1': " end_line <= '".$search['end_date']." 23:59:59'";
    	$where= " AND ".$to_date;
    	
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission("s.branch_id");
    	
    	$order=" ORDER BY end_line ASC";
    	return $db->fetchAll($sql.$where.$order);
    }
	public function latepayment($search=null){
		$db=$this->getAdapter();
		$pay_date = $search['payment_date'];
		$late_date = $search['late_date'];
		$sql="SELECT * FROM v_getloancollects WHERE 1";
		$late_pay_date=$late_date-$pay_date;
	}
	
	public function getLoanCollectionByCo($search=null){
		$db = $this->getAdapter();
		$start_date = $search['start_date'];
		$end_date = $search['end_date'];
		try{
			$sql="SELECT 
					  b.`branch_namekh`,
					  CONCAT(co.`co_code`, '-',co.`co_khname`,',',co.`co_firstname`,' ',co.`co_lastname`) AS co_name,
					  c.`receipt_no`,
					  c.`date_input`,
					  c.`principal_amount`,
					  c.`recieve_amount`,
					  c.`return_amount`,
					  c.`penalize_amount`,
					  c.`service_charge`,
					  c.`total_interest`,
					  c.`total_payment`,
					  c.`amount_payment`,
					  c.`total_principal_permonth`,
					  cm.`date_payment`,
					  cm.`principal_permonth`,
					  cm.`remain_capital`,
					  cm.`capital`,
					  cm.`penelize_amount`,
					  cm.`service_charge`,
					  cm.`total_interest`,
					  cm.`total_payment`,
					  cm.`loan_number`,
					  cm.`total_recieve`,
					  cm.`pay_after`,
					  lc.`name_kh`,
					  lc.`phone`,
					  lc.`client_number`,
					  lm.`total_capital`,
					  lm.`interest_rate`,
					  lg.`total_duration`,
					  lg.`date_release`,
					  (SELECT
					     `ln_view`.`name_en`
					   FROM `ln_view`
					   WHERE ((`ln_view`.`type` = 14)
					          AND (`ln_view`.`key_code` = `lg`.`pay_term`))) AS `Term Borrow`
					FROM
					  `ln_client_receipt_money` AS c,
					  `ln_staff` AS co,
					  `ln_branch` AS b ,
					  `ln_client_receipt_money_detail` AS cm,
					  `ln_client` AS lc,
					  `ln_loan_member` AS lm,
					  `ln_loan_group` AS lg
					WHERE c.`co_id` = co.`co_id` 
					AND c.id=cm.`crm_id`
					  AND c.`branch_id`=b.`br_id`
					  ";
			
			$where ='';
	      	if(!empty($search['advance_search'])){
	      		//print_r($search);
	      		$s_where = array();
	      		$s_search = $search['advance_search'];
	      		$s_where[] = "lcrm.`loan_number` LIKE '%{$s_search}%'";
	      		$s_where[] = " lcrm.`receipt_no` LIKE '%{$s_search}%'";
	      		$s_where[] = " lcrm.`total_payment` LIKE '%{$s_search}%'";
	      		$s_where[] = " lcrm.`total_interest` LIKE '%{$s_search}%'";
	      		$s_where[] = " lcrm.`penalize_amount` LIKE '%{$s_search}%'";
	      		$s_where[] = " lcrm.`service_charge` LIKE '%{$s_search}%'";
	      		$where .=' AND ('.implode(' OR ',$s_where).')';
	      	}
	      	if($search['status']!=""){
	      		$where.= " AND status = ".$search['status'];
	      	}
	      	 
	      	if(!empty($search['start_date']) or !empty($search['end_date'])){
	      		$where.=" c.`date_input` BETWEEN '$start_date' AND '$end_date'";
	      	}
	      	if($search['branch_id']>0){
	      		$where.=" AND lcrm.`branch_id`= ".$search['branch_id'];
	      	}
	      	if($search['co_id']>0){
	      		$where.=" AND co.`co_id`= ".$search['co_id'];
	      	}
	      	if($search['paymnet_type']>0){
	      		$where.=" AND lcrm.`payment_option`= ".$search['paymnet_type'];
	      	}
	      	 
	      	//$where='';
	      	$order = " ORDER BY lcrm.currency_type";
	      	//echo $sql.$where.$order;
	      	return $db->fetchAll($sql.$where.$order);
			
		}catch (Exception $e){
		}
	}
	
	function checkSalePenalty($sale_id,$end_date,$ispay_bank=0){
		if ($ispay_bank!=0){
			return 0;
		}
		$key = new Application_Model_DbTable_DbKeycode();
		$data=$key->getKeyCodeMiniInv(TRUE);
		$ps = $data["penalty_value"];//ការប្រាក់ពិន័យ
		$penalty_type = $data["penalty_type"];//ប្រភេទពិន័យ
		$graice_pariod_late = $data["graice_pariod_late"];//ប្រភេទពិន័យ
		$db = $this->getAdapter();
		
		$sql="SELECT 
				DATEDIFF('$end_date',sh.date_payment) AS total_latedate
			FROM `ln_saleschedule` AS sh 
			WHERE sh.date_payment <= '$end_date 23:59:59' 
			AND sh.sale_id = $sale_id 
			AND sh.ispay_bank =0
			AND sh.is_completed=0
			GROUP BY sh.sale_id";
		$total_latedate = $db->fetchOne($sql);
		$latedate = $total_latedate - $graice_pariod_late;
		if($latedate<=0){
			return 0;
		}
		
		if($penalty_type==1){
		$sql="SELECT 
			SUM(((($ps/100)/30)*sh.total_payment_after*DATEDIFF('$end_date',sh.date_payment))) AS penalty_record
			 FROM `ln_saleschedule` AS sh 
			WHERE sh.date_payment <= '$end_date 23:59:59' 
			AND sh.sale_id = $sale_id 
			AND sh.ispay_bank =0
			AND sh.is_completed=0
			GROUP BY sh.sale_id";
		}else{
			$sql="SELECT
			SUM(($ps*DATEDIFF('$end_date',sh.date_payment))) AS penalty_record
			FROM `ln_saleschedule` AS sh
			WHERE sh.date_payment <= '$end_date 23:59:59'
			AND sh.sale_id = $sale_id
			AND sh.ispay_bank =0
			AND sh.is_completed=0
			GROUP BY sh.sale_id";
		}
		/*
		 * sh.date_payment,
			DATEDIFF('$end_date',sh.date_payment) AS defday
		 * */
		$penalty = $db->fetchOne($sql);
		return $penalty;
	}
}

