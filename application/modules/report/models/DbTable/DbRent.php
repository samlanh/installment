<?php
class Report_Model_DbTable_DbRent extends Zend_Db_Table_Abstract
{
	function soldreportSqlStatement(){
		$sql="
		SELECT
		`s`.`id`               AS `id`,
		(SELECT
		`ln_project`.`project_name`
		FROM `ln_project`
		WHERE (`ln_project`.`br_id` = `s`.`branch_id`)
		LIMIT 1) AS `branch_name`,
		`s`.`sale_number`      AS `sale_number`,
		`s`.`branch_id`        AS `branch_id`,
		`s`.`client_id`        AS `client_id`,
		`s`.`house_id`         AS `house_id`,
		`s`.`price_before`     AS `price_before`,
		`s`.`price_sold`       AS `price_sold`,
		`s`.`discount_amount`  AS `discount_amount`,
		`s`.`discount_percent` AS `discount_percent`,
		s.verify_by,
		(SELECT
		SUM((`cr`.`total_principal_permonthpaid`))
		FROM `ln_rent_receipt_money` `cr`
		WHERE (`cr`.`sale_id` = `s`.`id`)
		LIMIT 1) AS `paid_amount`,
		`s`.`create_date`      AS `create_date`,
		`s`.`buy_date`         AS `buy_date`,
		`s`.`startcal_date`    AS `startcal_date`,
		`s`.`first_payment`    AS `first_payment`,
		`s`.`validate_date`    AS `validate_date`,
		`s`.`end_line`         AS `end_line`,
		`s`.`interest_rate`    AS `interest_rate`,
		`s`.`total_duration`   AS `total_duration`,
		`s`.`payment_id`       AS `payment_id`,
		`s`.`staff_id`         AS `staff_id`,
			
		`s`.`receipt_no`       AS `receipt_no`,
		`s`.`agreement_date`   AS `agreement_date`,
		`s`.`is_cancel`        AS `is_cancel`,
		`s`.`user_id`          AS `user_id`,
		s.note,
		`p`.`land_code`        AS `land_code`,
		`p`.`land_address`     AS `land_address`,
		`p`.`land_size`        AS `land_size`,
		`p`.`street`           AS `street`,
		(SELECT
		`ln_properties_type`.`type_nameen`
		FROM `ln_properties_type`
		WHERE (`ln_properties_type`.`id` = `p`.`property_type`)
		LIMIT 1) AS `propertype`,
		`p`.`property_type`    AS `property_type`,
		`c`.`client_number`    AS `client_number`,
		`c`.`name_kh`          AS `name_kh`,
		`c`.`name_en`          AS `name_en`,
		`c`.`phone`            AS `phone`,
		(SELECT
		`ln_staff`.`co_khname`
		FROM `ln_staff`
		WHERE (`ln_staff`.`co_id` = `s`.`staff_id`)
		LIMIT 1) AS `staff_name` ";
		$where="
		FROM ((`ln_rent_property` `s`
		JOIN `ln_client` `c`)
		JOIN `ln_properties` `p`)
		WHERE ((`c`.`client_id` = `s`.`client_id`)
		AND (`p`.`id` = `s`.`house_id`)
		AND (`s`.`status` = 1)) ";
		$araa = array(
				'sql'=>$sql,
				'where'=>$where,
		);
		return $araa;
	}
	public function getAllLoan($search = null){//rpt-loan-released/
		$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
		$session_lang=new Zend_Session_Namespace('lang');
		$lang = $session_lang->lang_id;
		$str = 'name_en';
		if($lang==1){
			$str = 'name_kh';
		}
		$from_date =(empty($search['start_date']))? '1': " date_pay >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " date_pay <= '".$search['end_date']." 23:59:59'";
		
		$statement = $this->soldreportSqlStatement();
		$sql= $statement['sql'];
		$sql.="
		,(SELECT SUM(total_principal_permonthpaid) FROM `ln_rent_receipt_money` WHERE sale_id=s.id AND s.status=1 AND $from_date AND $to_date LIMIT 1) AS paid_amount,
		(SELECT SUM(total_interest_permonthpaid) FROM `ln_rent_receipt_money` WHERE status=1 AND $from_date AND $to_date  AND sale_id = s.id LIMIT 1) AS total_interest_permonthpaid,
		(SELECT SUM(penalize_amountpaid) FROM `ln_rent_receipt_money` WHERE status=1 AND $from_date AND $to_date  AND sale_id = s.id LIMIT 1) AS penalize_amountpaid,
		(SELECT COUNT(id) FROM `ln_rentschedule` WHERE sale_id=s.id AND status=1 ) AS times,
		(SELECT first_name FROM `rms_users` WHERE id=s.user_id LIMIT 1) AS user_name,
		(SELECT $str FROM `ln_view` WHERE key_code =s.payment_id AND type = 25 limit 1) AS paymenttype,
		(SELECT p.old_land_id FROM `ln_properties` AS p WHERE p.id = s.house_id LIMIT 1) AS old_land_id
		";
		$where = $statement['where'];
		$where.=$dbp->getAccessPermission("s.`branch_id`");
		$str = '`s`.`buy_date`';
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
		$order = " ORDER BY s.is_cancel ASC,s.payment_id DESC ";
		return $db->fetchAll($sql.$where.$order);
	
	}
	function getCollectPaymentSqlSt(){
		$sql=" SELECT
		(SELECT
		`ln_project`.`project_name`
		FROM `ln_project`
		WHERE (`ln_project`.`br_id` = `crm`.`branch_id`)
		LIMIT 1) AS `branch_name`,
		`c`.`client_id`                      AS `client_id`,
		`c`.`client_number`                  AS `client_number`,
		`c`.`name_kh`                        AS `name_kh`,
		`c`.`name_en`                        AS `client_name`,
		`crm`.`id`                           AS `id`,
		`crm`.`sale_id`                      AS `sale_id`,
		`crm`.`branch_id`                    AS `branch_id`,
		`crm`.`receipt_no`                   AS `receipt_no`,
		`crm`.`date_payment`                 AS `date_payment`,
		`crm`.`date_pay`                     AS `date_pay`,
		`crm`.`date_input`                   AS `date_input`,
		`crm`.`note`                         AS `note`,
		`crm`.`user_id`                      AS `user_id`,
		`crm`.`status`                       AS `status`,
		`crm`.`payment_option`               AS `payment_option`,
		`crm`.`is_payoff`                    AS `is_payoff`,
		`crm`.`total_principal_permonth`     AS `total_principal_permonth`,
		`crm`.`total_principal_permonthpaid` AS `total_principal_permonthpaid`,
		`crm`.`total_interest_permonth`      AS `total_interest_permonth`,
		`crm`.`total_interest_permonthpaid`  AS `total_interest_permonthpaid`,
		`crm`.`penalize_amount`              AS `penalize_amount`,
		`crm`.`penalize_amountpaid`          AS `penalize_amountpaid`,
		`crm`.`amount_payment`               AS `amount_payment`,
		`crm`.`total_payment`                AS `total_payment`,
		`crm`.`recieve_amount`               AS `amount_recieve`,
		`crm`.`penalize_amount`              AS `penelize`,
		`crm`.`payment_times`                AS `payment_times`,
		`crm`.`field3`                       AS `field3`,
		`crm`.`is_closed`                    AS `is_closed`,
		`crm`.`closing_note`                    AS `closing_note`,
		`sl`.`sale_number`                   AS `sale_number`,
		`sl`.`price_sold`                   AS `sold_price`,
		(SELECT COUNT(ln_rentschedule.id) FROM `ln_rentschedule` WHERE ln_rentschedule.sale_id=`crm`.`sale_id` LIMIT 1) As times,
			
		`l`.`land_code`                      AS `land_code`,
		`l`.`land_address`                   AS `land_address`,
		`l`.`land_size`                      AS `land_size`,
		`l`.`street`                         AS `street`,
		`l`.`id`                             AS `hous_id`,
		
		
		`crm`.`payment_method`               AS `payment_methodid`,
		(SELECT
		`ln_view`.`name_kh`
		FROM `ln_view`
		WHERE ((`ln_view`.`key_code` = `crm`.`payment_method`)
		AND (`ln_view`.`type` = 2))
		LIMIT 1) AS `payment_method`,
		(SELECT
		`ln_view`.`name_en`
		FROM `ln_view`
		WHERE ((`ln_view`.`key_code` = `crm`.`payment_option`)
		AND (`ln_view`.`type` = 7))
		LIMIT 1) AS `paymentoption`,
		(SELECT first_name FROM `rms_users` WHERE id=crm.user_id LIMIT 1) AS user_name,
		(SELECT
		`ln_staff`.`co_khname`
		FROM `ln_staff`
		WHERE (`ln_staff`.`co_id` = `sl`.`staff_id`)
		LIMIT 1) AS `staff_name`
		 
		FROM (((`ln_rent_receipt_money` `crm`
		JOIN `ln_properties` `l`)
		JOIN `ln_rent_property` `sl`)
		JOIN `ln_client` `c`)
		WHERE ((`crm`.`client_id` = `c`.`client_id`)
		AND (`sl`.`id` = `crm`.`sale_id`)
		AND (`l`.`id` = `sl`.`house_id`))
		";
		return $sql;
	}
 	public function getPaymentSaleid($sale_id){
      	$db = $this->getAdapter();
      	$dbp = new Application_Model_DbTable_DbGlobal();
      	$sql = $this->getCollectPaymentSqlSt();
      	$sql.=" AND crm.status= 1
			AND crm.sale_id= $sale_id ";
      	$sql.=$dbp->getAccessPermission("crm.branch_id");
      	$order=" ORDER BY crm.id DESC ";
      	return $db->fetchAll($sql.$order);
    }
    public function getALLLoanPayment($search=null,$order11=0){
    	$search['is_closed']='';
    	$db = $this->getAdapter();
    	 
    	$sql = $this->getCollectPaymentSqlSt();
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
    	if ($search['is_closed']!=""){
    		$where.=" AND `crm`.`is_closed` = '".$search['is_closed']."'";
    	}
    
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['adv_search']));
    		$s_where[] = " `sl`.`sale_number`  LIKE '%{$s_search}%'";
    		$s_where[] = " `l`.`land_code`  LIKE '%{$s_search}%'";
    		$s_where[] = " `l`.`land_address`  LIKE '%{$s_search}%'";
    		$s_where[] = " `c`.`client_number` LIKE '%{$s_search}%'";
    		$s_where[] = " `c`.`name_en`  LIKE '%{$s_search}%'";
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
    		$where .=' AND ('.implode(' OR ',$s_where).')';
    	}
    	$order =" ORDER BY `crm`.id DESC ";
    	if($order11==1){//for history
    		$order =" ORDER BY `crm`.`client_id` DESC ,`crm`.`sale_id` DESC , crm.id ASC";
    	}
    	return $db->fetchAll($sql.$where.$order);
    }
    public function getClientByMemberId($id){
    	$sql="SELECT
	    	`s`.`branch_id`       AS `branch_id`,
	    	`s`.`client_id`       AS `client_id`,
	    	`s`.`house_id`        AS `house_id`,
	    	`s`.`price_before`    AS `price_before`,
	    	`s`.`price_sold`      AS `price_sold`,
	    	s.lastpayment_amount,
	    	`s`.`discount_amount` AS `discount_amount`,
	    	s.land_price ,
	    	s.discount_percent,
	    	s.agreement_date,
	    	s.full_commission,
	    	s.commission_amt,
	    	s.commission_times,
	    	`s`.`admin_fee`       AS `admin_fee`,
	    	`s`.`other_fee`       AS `other_fee`,
	    	`s`.`paid_amount`     AS `paid_amount`,
	    	`s`.`balance`         AS `balance`,
	    	`s`.`create_date`     AS `create_date`,
	    	`s`.`buy_date`        AS `buy_date`,
	    	`s`.`startcal_date`   AS `startcal_date`,
	    	`s`.`first_payment`   AS `first_payment`,
	    	`s`.`validate_date`   AS `validate_date`,
	    	`s`.`end_line`        AS `end_line`,
	    	`s`.`interest_rate`   AS `interest_rate`,
	    	`s`.`total_duration`  AS `total_duration`,
	    	`s`.`payment_id`      AS `payment_id`,
	    	`s`.`staff_id`        AS `staff_id`,
	    	`s`.`comission`       AS `comission`,
	    	`s`.`receipt_no`      AS `receipt_no`,
	    	s.total_installamount,
	    	(SELECT project_name FROM `ln_project` WHERE br_id =s.branch_id LIMIT 1) AS branch_name,
	    	(SELECT p_manager_namekh FROM `ln_project` WHERE br_id =s.branch_id LIMIT 1) AS project_manager_namekh,
	    	(SELECT w_manager_namekh FROM `ln_project` WHERE br_id =s.branch_id LIMIT 1) AS w_manager_namekh,
	    	(SELECT logo FROM `ln_project` WHERE br_id =s.branch_id LIMIT 1) AS project_logo,
	    	(SELECT client_number FROM `ln_client` WHERE client_id = s.client_id LIMIT 1) AS client_number,
	    	(SELECT name_kh FROM `ln_client` WHERE client_id = s.client_id LIMIT 1) AS client_name_kh,
	    	(SELECT hname_kh FROM `ln_client` WHERE client_id = s.client_id LIMIT 1) AS hname_kh,
	    	(SELECT name_en FROM `ln_client` WHERE client_id = s.client_id LIMIT 1) AS client_name_en,
	    	(SELECT phone FROM `ln_client` WHERE client_id = s.client_id LIMIT 1) AS tel,
	    	(SELECT CONCAT(last_name ,' ',first_name)  FROM `rms_users` WHERE id = s.user_id LIMIT 1) AS user_name,
	    	`p`.`land_code`       AS `land_code`,
	    	`p`.`land_address`    AS `land_address`,
	    	`p`.`land_size`       AS `land_size`,
	    	`p`.`street`           AS `stree`,
	    	(SELECT
	    	`ln_properties_type`.`type_nameen`
	    	FROM `ln_properties_type`
	    	WHERE (`ln_properties_type`.`id` = `p`.`property_type`)
	    	LIMIT 1) AS `propertype`
	    	FROM
	    	`ln_rent_property` AS s,
	    	`ln_properties` AS p
	    	WHERE `p`.`id` = `s`.`house_id` AND s.id=$id ";
    	 
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission("`s`.`branch_id`");
    	$sql.=" LIMIT 1 ";
    	 
    	$db=$this->getAdapter();
    	return $db->fetchRow($sql);
    }
    public function getPaymentSchedule($id,$payment_id=null){
    	$db=$this->getAdapter();
    	$sql = "SELECT *,
    	paid_date as date_paid,
    	(SELECT name_kh FROM ln_view WHERE type =29 AND key_code = ln_rentschedule.ispay_bank LIMIT 1) AS payment_type,
    	(SELECT (paid_date) FROM `ln_rent_receipt_money_detail` WHERE lfd_id=ln_rentschedule.id limit 1) as paid_date,
    	(SELECT SUM(total_recieve) FROM `ln_rent_receipt_money_detail` WHERE lfd_id=ln_rentschedule.id limit 1) as total_recieve,
    	(SELECT SUM(total_interest) FROM `ln_rent_receipt_money_detail` WHERE lfd_id=ln_rentschedule.id limit 1) as total_interestpaid,
    	(SELECT SUM(principal_permonth) FROM `ln_rent_receipt_money_detail` WHERE lfd_id=ln_rentschedule.id limit 1) as principal_paid,
    	(SELECT  first_name FROM rms_users WHERE id = ln_rentschedule.received_userid LIMIT 1 ) AS received_by
    	FROM `ln_rentschedule`
    	WHERE sale_id= $id AND (status=1 OR (status=0 AND collect_by=2)) ";
    	if($payment_id==4){
    		$sql.=" AND is_installment=1 ";
    	};
    	$sql.=" ORDER BY no_installment ASC,date_payment ASC, collect_by ASC, status DESC ";
    	return $db->fetchAll($sql);
    }
    function getRentReceiptByID($id){//total_principal_permonth
    	$db = $this->getAdapter();
    	$sql="
    	SELECT *,
	    	(SELECT s.payment_id FROM `ln_rent_property` AS s WHERE s.id=crm.sale_id LIMIT 1 ) AS payment_option,
	    	(SELECT project_name FROM `ln_project` WHERE br_id=crm.branch_id LIMIT 1) AS project_name,
	    	(SELECT p.land_address  FROM `ln_properties` AS p WHERE p.id  = crm.`land_id` LIMIT 1) AS land_address,
	    	(SELECT p.old_land_id  FROM `ln_properties` AS p WHERE p.id  = crm.`land_id` LIMIT 1) AS landlot_amount,
	    	(SELECT pt.type_nameen FROM `ln_properties_type` AS pt WHERE pt.id = (SELECT p.property_type  FROM `ln_properties` AS p WHERE p.id  = crm.`land_id` LIMIT 1) LIMIT 1)AS property_type,
	    	(SELECT p.street  FROM `ln_properties` AS p WHERE p.id  = crm.`land_id` LIMIT 1) AS street,
	    	(SELECT s.sale_number FROM `ln_rent_property` AS s WHERE s.id = crm.sale_id LIMIT 1) AS sale_number,
	    	(SELECT s.land_price FROM `ln_rent_property` AS s WHERE s.id = crm.sale_id LIMIT 1) AS land_price,
	    	(SELECT s.price_sold FROM `ln_rent_property` AS s WHERE s.id = crm.sale_id LIMIT 1) AS price_sold,
	    	(SELECT s.total_duration FROM `ln_rent_property` AS s WHERE s.id = crm.sale_id LIMIT 1) AS total_duration,
	    	(SELECT date_payment FROM `ln_rentschedule` WHERE sale_id= crm.sale_id AND status=1 AND no_installment>payment_times ORDER BY date_payment ASC LIMIT 1) as nextdate_payment,
	    	(SELECT c.name_kh FROM `ln_client` AS c WHERE c.client_id = crm.client_id LIMIT 1) AS name_kh,
	    	(SELECT c.client_number FROM `ln_client` AS c WHERE c.client_id = crm.client_id LIMIT 1) AS client_number,
	    	(SELECT c.phone FROM `ln_client` AS c WHERE c.client_id = crm.client_id LIMIT 1) AS phone,
	    	
	    	crm.date_payment as date_payment,
    		crm.payment_method as payment_methodid,
    	(SELECT `ln_view`.`name_kh` FROM `ln_view` WHERE ((`ln_view`.`key_code` = `crm`.`payment_method`)
    	AND (`ln_view`.`type` = 2))LIMIT 1) AS `payment_method`,
    	(SELECT c.hname_kh FROM `ln_client` AS c WHERE c.client_id = crm.client_id LIMIT 1) AS hname_kh,
    	(SELECT CONCAT(last_name,' ',first_name) FROM `rms_users` WHERE rms_users.id=crm.`user_id` LIMIT 1) As by_user
    	FROM `ln_rent_receipt_money` AS crm WHERE crm.`id`=".$id;
    	$rs = $db->fetchRow($sql);
    	$rs['property_type']=ltrim(strstr($rs['property_type'], '('), '.');
    	if(empty($rs)){
    		return '';
    	}else{
    		return $rs;
    	}
    }
    function getAgreementByRentID($id=null){//bppt,natha,longny,moul mith
    	$db = $this->getAdapter();
    	$sql="
    	SELECT
    	`s`.`id`              AS `id`,
    	`s`.`sale_number`     AS `sale_number`,
    	`s`.`payment_id`      AS `payment_id`,
    	`s`.`branch_id`       AS `branch_id`,
    	`s`.`client_id`       AS `client_id`,
    	`s`.`price_before`    AS `price_before`,
    	`s`.`discount_amount` AS `discount_amount`,
    	s.discount_percent,
    	`s`.`price_sold`      AS `price_sold`,
    	`s`.`other_fee`       AS `other_fee`,
    	`s`.`admin_fee`       AS `admin_fee`,
    	`s`.`paid_amount`     AS `paid_amount`,
    	`s`.`balance`         AS `balance`,
    	`s`.`amount_collect`  AS `amount_collect`,
    	`s`.`interest_rate`   AS `interest_rate`,
    	`s`.`total_duration`  AS `total_duration`,
    	`s`.`first_payment`  AS `first_payment`,
    	s.is_reschedule,
    	s.land_price,
    	s.amount_build,
    	s.build_start,
    	s.buy_date,
    	s.`end_line`,
    	s.validate_date,
    	s.agreement_date,
    	s.note_agreement,
    	s.is_verify,
    	s.second_depostit,
    	s.setting_opt,
    	s.times_deposite,
    	(SELECT CONCAT(last_name,' ',first_name) FROM rms_users WHERE id = s.user_id LIMIT 1 ) AS user_name,
    	(SELECT co_khname FROM `ln_staff` WHERE co_id=s.staff_id LIMIT 1) AS staff_name,
    	(SELECT name_kh FROM `ln_view` WHERE type=25 and key_code=s.payment_id limit 1) AS payment_type,
    	`p`.`project_name`,
    	`p`.`logo` AS project_logo,
    	`p`.`branch_tel`,
    	`p`.`other` as p_other,
    	p.p_sex,
    	`p`.`br_address` AS `project_location`,
    	`p`.`p_manager_namekh` AS `project_manager_namekh`,
    	`p`.`p_manager_nationality` AS `project_manager_nationality`,
    	`p`.`p_manager_nation_id` AS `project_manager_nation_id`,
    	`p`.`p_current_address` AS `project_manager_p_current_address`,
    	p.w_manager_namekh ,
    	p.`position`,
    	p.w_manager_nation_id,
    	p.p_dob as manager_dob,
    	p.`p_nationid_issue`,
    	p.w_manager_namekh ,
    	p.w_manager_nation_id,
    	p.`w_manager_nationality`,
    	p.`w_sex`,
    	 
    	p.w_manager_nation_id,
    	p.w_manager_position,
    	p.w_manager_tel,
    	p.w_manager_tel AS manager_tel,
    	 
    	p.w_managername1,
    	p.w_manager_position1,
    	p.w_manager_tel1,
    	p.w_manager_tel1 AS with_manager_tel,
    	 
    	(SELECT name_kh FROM `ln_view` WHERE TYPE=11 AND key_code=p.`w_sex` LIMIT 1) AS sc_manager_sex,
    	p.`w_dob`,
    	p.`w_current_address`,
    	p.`w_nation_id_issue`,
    	(SELECT name_kh FROM `ln_view` WHERE type=11 and key_code=p.p_sex limit 1) AS manager_sex ,
    	`c`.`client_number` AS `client_code`,
    	`c`.`name_kh` AS `client_namekh`,
    	`c`.`name_en` AS `client_nameen`,
    	c.dob,
    	c.dob AS client_dob ,
    	c.hname_kh,
    	c.hname_kh AS with_client_name,
    	c.sex,
    	c.ksex,
    	c.join_type,
    	(SELECT name_kh FROM `ln_view` WHERE type=11 and key_code=c.ksex limit 1) AS partner_gender,
    	c.dob_buywith,
    	c.rid_no,
    	c.rid_no AS with_client_nation_id,
    	(SELECT name_kh FROM `ln_view` WHERE TYPE=11 AND key_code=c.`sex` LIMIT 1) AS client_sex,
    	(SELECT name_kh FROM `ln_view` WHERE type=11 and key_code=c.sex limit 1) AS sexKh,
    	(SELECT name_kh FROM `ln_view` WHERE type=23 and key_code=c.joint_doc_type limit 1) AS joint_doc_type,
    	(SELECT name_kh FROM `ln_view` WHERE type=23 and key_code=c.client_d_type limit 1) AS client_d_type,
    		
    	c.p_nationality,
    	c.p_nationality AS with_client_nationlity ,
    	`c`.`nationality` AS `client_nationality`,
    	`c`.`nation_id` AS `client_nation_id`,
    	c.client_issuedateid,
    	c.join_issuedateid,
    	`c`.`phone` AS `client_phone`,
    	`c`.`house` AS `client_house_no`,
    	`c`.`street` AS `client_street`,
    	c.phone,
    	c.lphone as with_phone,
    	c.ghouse as with_house,
    	c.dstreet AS w_street,
    	c.arid_no AS witnesses,
    	(SELECT
    	`village`.`village_namekh`
    	FROM `ln_village` `village`
    	WHERE (`village`.`vill_id` = `c`.`qvillage`)
    	LIMIT 1) AS `joint_village`,
    	(SELECT
    	`comm`.`commune_namekh` FROM `ln_commune` `comm`
    	WHERE (`comm`.`com_id` = `c`.`dcommune`)
    	LIMIT 1) AS `join_commune`,
    	(SELECT
    	`dist`.`district_namekh`
    	FROM `ln_district` `dist`
    	WHERE (`dist`.`dis_id` = `c`.`adistrict`) LIMIT 1) AS `join_district`,
    	(SELECT
    	`provi`.`province_kh_name`
    	FROM `ln_province` `provi`
    	WHERE (`provi`.`province_id` = `c`.`pro_id`) LIMIT 1) AS `join_province`,
    
    	(SELECT
    	`village`.`village_namekh`
    	FROM `ln_village` `village`
    	WHERE (`village`.`vill_id` = `c`.`village_id`
    	)
    	LIMIT 1) AS `client_village_kh`,
    	(SELECT
    	`comm`.`commune_namekh` FROM `ln_commune` `comm`
    	WHERE (`comm`.`com_id` = `c`.`com_id`)
    	LIMIT 1) AS `client_commune_kh`,
    	(SELECT
    	`dist`.`district_namekh`
    	FROM `ln_district` `dist`
    	WHERE (`dist`.`dis_id` = `c`.`dis_id`)
    	LIMIT 1) AS `client_districtkh`,
    	(SELECT
    	`provi`.`province_kh_name`
    	FROM `ln_province` `provi`
    	WHERE (`provi`.`province_id` = `c`.`pro_id`)
    	LIMIT 1) AS `client_province_kh`,
    	(SELECT name_kh FROM `ln_view` WHERE TYPE=11 AND key_code=c.`ksex` LIMIT 1) AS client_buywith_sex,
    	(SELECT name_kh FROM `ln_view` WHERE TYPE=11 AND key_code=c.`ksex` LIMIT 1) AS with_client_sex ,
    	c.hname_kh AS w_client_namekh,
    	c.dob_buywith AS w_client_dob_buywith,
    	c.p_nationality AS w_client_nationality,
    	c.`ghouse` AS w_client_house,
    	c.lphone AS w_client_phone,
    	(SELECT
    	`village`.`village_name`
    	FROM `ln_village` `village`
    	WHERE (`village`.`vill_id` = `c`.`qvillage`)
    	LIMIT 1) AS `w_client_village_en`,
    	(SELECT
    	`village`.`village_namekh`
    	FROM `ln_village` `village`
    	WHERE (`village`.`vill_id` = `c`.`qvillage`
    	)
    	LIMIT 1) AS `w_client_village_kh`,
    	(SELECT
    	`comm`.`commune_name` FROM `ln_commune` `comm`
    	WHERE (`comm`.`com_id` = `c`.`dcommune`)
    	LIMIT 1) AS `w_client_commune_en`,
    		
    	(SELECT
    	`comm`.`commune_namekh` FROM `ln_commune` `comm`
    	WHERE (`comm`.`com_id` = `c`.`dcommune`)
    	LIMIT 1) AS `w_client_commune_kh`,
    	(SELECT
    	`dist`.`district_name`
    	FROM `ln_district` `dist`
    	WHERE (`dist`.`dis_id` = `c`.`adistrict`) LIMIT 1) AS `w_client_district`,
    	(SELECT
    	`dist`.`district_namekh`
    	FROM `ln_district` `dist`
    	WHERE (`dist`.`dis_id` = `c`.`adistrict`)
    	LIMIT 1) AS `w_client_districtkh`,
    	(SELECT
    	`provi`.`province_en_name`
    	FROM `ln_province` `provi`
    	WHERE (`provi`.`province_id` = `c`.`cprovince`) LIMIT 1) AS `w_client_province_en`,
    	(SELECT
    	`provi`.`province_kh_name`
    	FROM `ln_province` `provi`
    	WHERE (`provi`.`province_id` = `c`.`cprovince`)
    	LIMIT 1) AS `w_client_province_kh`,
    
    	(SELECT
    	`prope_type`.`type_nameen`
    	FROM `ln_properties_type` `prope_type`
    	WHERE (`prope_type`.`id` =`pp`.`property_type`)
    	LIMIT 1) AS `property_type_en`,
    	(SELECT
    	`prope_type`.`type_namekh`
    	FROM `ln_properties_type` `prope_type`
    	WHERE `prope_type`.`id` = `pp`.`property_type` LIMIT 1) AS `property_type_kh`,
    	`pp`.`land_size` AS `property_land_size`,
    		
    	`pp`.`width` AS `property_width`,
    	`pp`.`height` AS `property_height`,
    	pp.`land_size`,
    	 
    	`pp`.`property_type`,
    	`pp`.`type_tob`,
    	`pp`.`land_code` AS `property_code`,
    	`pp`.`land_address` AS `property_title`,
    	pp.`street` AS `property_street`,
    		
    	pp.land_width,
    	pp.land_height,
    	pp.`full_size`,
    		
    	pp.floor,
    	pp.living,
    	pp.`bedroom`,
    	pp.dinnerroom,
    	pp.buidingyear,
    	pp.`parkingspace`,
    	pp.`note` as `property_note`,
    	(SELECT
    	`property`.`land_size`
    	FROM `ln_properties` `property`
    	WHERE (`property`.`id` = `s`.`house_id`)
    	LIMIT 1) AS `property_size`,
    	pp.`north` AS border_north,
    	pp.`south` AS border_south,
    	pp.`east` AS border_east,
    	pp.`west` AS border_west,
    	pp.`old_land_id`
    	FROM
    	`ln_rent_property` AS `s`,
    	ln_project AS p ,
    	`ln_client` AS c,
    	ln_properties as pp
    	WHERE
    	`p`.`br_id` = `s`.`branch_id`
    	AND `c`.`client_id` = `s`.`client_id`
    	AND `pp`.`id` = `s`.`house_id`
    	AND s.id=".$id;
    
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission("`s`.`branch_id`");
    
    	return $db->fetchRow($sql);
    }
    function getDepositAgreement($sale_id){
    	$db = $this->getAdapter();
    	$sql="SELECT sum(crm.`recieve_amount`) AS recieve_amount,
    	(SELECT validate_date FROM  ln_rent_property WHERE id = $sale_id ) AS  validate_date
    	FROM `ln_rent_receipt_money` AS crm WHERE crm.status=1 AND crm.field3=1
    	AND sale_id = $sale_id ORDER BY crm.id ASC";
    	return $db->fetchRow($sql);
    }
    
    
    public function getALLLoanlate($search = null){
    	$db = $this->getAdapter();
    	$sql=" 
    	SELECT
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
	    	(SELECT date_input FROM `ln_rent_receipt_money` WHERE land_id=1 ORDER BY date_input DESC LIMIT 1)
	    	As last_pay_date
    	FROM
	    	`ln_rent_property` AS s,
	    	`ln_rentschedule` AS sd,
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
    
    public function getAllLnClient($search=null){
    	$db=$this->getAdapter();
    	$end_date = $search['end_date'];
    	
    	$sql="
    	SELECT pd.*,
	    	SUM(`pd`.principal_permonthafter) AS principal_permonthafter,
	    	SUM(`pd`.total_interest_after) AS total_interest_after,
	    	SUM(`pd`.total_payment_after) AS total_payment_after,
	    	SUM(`pd`.service_charge) AS service_charge,
	    	COUNT(pd.id) AS amount_late,
    		s.total_duration,
	    	(SELECT `ln_project`.`project_name` FROM `ln_project`  WHERE (`ln_project`.`br_id` = `s`.`branch_id`) LIMIT 1) AS `branch_name`,
	    	`l`.`land_code`                AS `land_code`,
	    	`l`.`land_address`             AS `land_address`,
	    	`l`.`street`                   AS `street`,
    	
	    	(SELECT `c`.`client_number`  FROM `ln_client` `c` WHERE (`c`.`client_id` = `s`.`client_id`) LIMIT 1) AS `client_number`,
	    	(SELECT `c`.`name_kh` FROM `ln_client` `c` WHERE (`c`.`client_id` = `s`.`client_id`) LIMIT 1) AS `client_name`,
	    	(SELECT `c`.`phone` FROM `ln_client` `c` WHERE (`c`.`client_id` = `s`.`client_id`) LIMIT 1) AS `phone_number`,
	    	(SELECT ln_view.name_kh FROM ln_view WHERE ln_view.type =29 AND key_code = pd.ispay_bank LIMIT 1) AS payment_type
    	FROM ((`ln_rentschedule` `pd`
	    	JOIN `ln_rent_property` `s`)
	    	JOIN `ln_properties` `l`)
    	WHERE `s`.`house_id` = `l`.`id`
	    	AND `pd`.`is_completed` = 0
	    	AND `s`.`status` = 1
	    	AND `s`.`is_cancel` = 0
	    	AND `pd`.`sale_id` = `s`.`id`
	    	AND `pd`.`status` = 1
	    	AND `pd`.`last_optiontype` = 1
	    	AND `pd`.`is_completed` = 0
    	";
    	$where ='';
    	 
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission("s.branch_id");
    	 
    	$to_date = (empty($search['end_date']))? '1': " pd.date_payment <= '".$search['end_date']." 23:59:59'";
    	$where.= " AND ".$to_date;
    	if($search['client_name']>0){
    		$where.=" AND s.client_id = ".$search['client_name'];
    	}
    	if($search['branch_id']>0){
    		$where.=" AND s.branch_id = ".$search['branch_id'];
    	}
    	
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = trim(addslashes($search['adv_search']));
    		$s_where[] = " s.sale_number LIKE '%{$s_search}%'";
    		$s_where[] = " (SELECT `c`.`client_number` FROM `ln_client` `c` WHERE (`c`.`client_id` = `s`.`client_id`) LIMIT 1) LIKE '%{$s_search}%'";
    		$s_where[] = " (SELECT `c`.`phone` FROM `ln_client` `c` WHERE (`c`.`client_id` = `s`.`client_id`) LIMIT 1) LIKE '%{$s_search}%'";
    		$s_where[] = " (SELECT `c`.`name_kh` FROM `ln_client` `c` WHERE (`c`.`client_id` = `s`.`client_id`) LIMIT 1) LIKE '%{$s_search}%'";
    		$s_where[] = " `l`.land_code LIKE '%{$s_search}%'";
    		$s_where[] = " `l`.`land_address` LIKE '%{$s_search}%'";
    		$s_where[] = " `l`.`street`  LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	$order=" GROUP BY pd.sale_id,pd.ispay_bank ORDER BY  pd.date_payment ASC ";
    	return $db->fetchAll($sql.$where.$order);
    }
    function getRentNearlyPayment(){
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
    	$sql="
    	SELECT pd.*,
	    	SUM(`pd`.principal_permonthafter) AS principal_permonthafter,
	    	SUM(`pd`.total_interest_after) AS total_interest_after,
	    	SUM(`pd`.total_payment_after) AS total_payment_after,
	    	SUM(`pd`.service_charge) AS service_charge,
	    	COUNT(pd.id) AS amount_late,
    	 
	    	(SELECT `ln_project`.`project_name` FROM `ln_project`  WHERE (`ln_project`.`br_id` = `s`.`branch_id`) LIMIT 1) AS `branch_name`,
	    	`l`.`land_code`                AS `land_code`,
	    	`l`.`land_address`             AS `land_address`,
	    	`l`.`street`                   AS `street`,
    
	    	(SELECT `c`.`client_number`  FROM `ln_client` `c` WHERE (`c`.`client_id` = `s`.`client_id`) LIMIT 1) AS `client_number`,
	    	(SELECT `c`.`name_kh` FROM `ln_client` `c` WHERE (`c`.`client_id` = `s`.`client_id`) LIMIT 1) AS `client_name`,
	    	(SELECT `c`.`phone` FROM `ln_client` `c` WHERE (`c`.`client_id` = `s`.`client_id`) LIMIT 1) AS `phone`
	    	FROM ((`ln_rentschedule` `pd`
	    	JOIN `ln_rent_property` `s`)
	    	JOIN `ln_properties` `l`)
	    	WHERE `s`.`house_id` = `l`.`id`
	    	AND `pd`.`is_completed` = 0
	    	AND `s`.`status` = 1
	    	AND `s`.`is_cancel` = 0
	    	AND `pd`.`sale_id` = `s`.`id`
	    	AND `pd`.`status` = 1
	    	AND `pd`.`last_optiontype` = 1
	    	AND `pd`.`is_completed` = 0
    	";
    	$where ='';
    	$from_date =(empty($search['start_date']))? '1': " `pd`.date_payment <= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " `pd`.date_payment <= '".$search['end_date']." 23:59:59'";
    	$where= " AND ".$from_date." AND ".$to_date;
    	$where.= " AND pd.ispay_bank=0";
    	$order=" GROUP BY sale_id ORDER BY date_payment DESC ,sale_id ASC, `pd`.id ASC";
    
    	return $db->fetchAll($sql.$where.$order);
    }
    
    function getAllChangeOwner($search=null){
    	$db = $this->getAdapter();
    	$dbp = new Application_Model_DbTable_DbGlobal();
    
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	$from_date =(empty($search['start_date']))? '1': " rc.change_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " rc.change_date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    
    	$sql=" SELECT
    	rc.*,
    	(SELECT `p`.`project_name` FROM `ln_project` AS p WHERE `p`.`br_id` = `rc`.`branch_id` LIMIT 1) AS `branch_name`,
    	CONCAT(p.land_address,',',p.street) AS property,
    	(SELECT c.name_kh FROM ln_client AS c WHERE c.client_id=rc.`from_customer` LIMIT 1) AS from_customer,
    	(SELECT c.name_kh FROM ln_client AS c WHERE c.client_id=rc.`to_customer` LIMIT 1) AS to_customer,
    	(SELECT  first_name FROM rms_users WHERE id=rc.user_id LIMIT 1 ) AS user_name
    	";
    	$sql.=" FROM `ln_rent_changeowner` AS rc,
    	`ln_rent_property` AS rp,
    	ln_properties AS p
    	WHERE
    	rc.rent_id = rp.id
    	AND p.id = rc.property_id 
    	AND rc.status=1  ";
    
    	if (!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = trim(addslashes($search['adv_search']));
    		$s_where[] = " (SELECT c.name_kh FROM ln_client AS c WHERE c.client_id=rc.`from_customer` LIMIT 1) LIKE '%{$s_search}%'";
    		$s_where[] = " (SELECT c.name_kh FROM ln_client AS c WHERE c.client_id=rc.`to_customer` LIMIT 1) LIKE '%{$s_search}%'";
    		$s_where[] = " `p`.`land_address` LIKE '%{$s_search}%'";
    		$s_where[] = " `p`.`street` LIKE '%{$s_search}%'";
    		$where .=' AND ('.implode(' OR ',$s_where).')';
    	}
    
    	if(!empty($search['branch_id'])){
    		$where.= " AND `rc`.`branch_id` = ".$search['branch_id'];
    	}
    	if(!empty($search['from_customer'])){
    		$where.= " AND `rc`.`from_customer` = ".$search['from_customer'];
    	}
    	if(!empty($search['to_customer'])){
    		$where.= " AND `rc`.`to_customer` = ".$search['to_customer'];
    	}
    	$where.=$dbp->getAccessPermission("rc.branch_id");
    	$order=" ORDER BY rc.id DESC ";
    	return $db->fetchAll($sql.$where.$order);
    }
    function getCollectPayment($search=null){
    	$db= $this->getAdapter();
    	
    	$sql = $this->getCollectPaymentSqlSt();
    	$sql.= " AND crm.status= 1 ";
    	$from_date =(empty($search['start_date']))? '1': " `crm`.`date_pay` >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " `crm`.`date_pay` <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	 
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$where.=$dbp->getAccessPermission("`crm`.branch_id");
    	if($search['branch_id']>0){
    		$where.= " AND `crm`.branch_id = ".$search['branch_id'];
    	}
    	if(!empty($search['user_id']) AND $search['user_id']>0){
    		$where.= " AND `crm`.user_id = ".$search['user_id'];
    	}
    	if($search['client_name']>0){
    		$where.=" AND `crm`.client_id = ".$search['client_name'];
    	}
    	if($search['payment_method']>0){
    		$where.=" AND `crm`.`payment_method` = ".$search['payment_method'];
    	}
    	 
    	if (!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = trim(addslashes($search['adv_search']));
    		$s_where[] = " `c`.`client_number`  LIKE '%{$s_search}%'";
    		$s_where[] = " `c`.`name_kh`   LIKE '%{$s_search}%'";
    		$s_where[] = " `c`.`name_en` LIKE '%{$s_search}%'";
    		$s_where[] = " `crm`.`receipt_no` LIKE '%{$s_search}%'";
    		$where .=' AND ('.implode(' OR ',$s_where).')';
    	}
    	if (!empty($search['land_id']) AND $search['land_id']>0){
    		$where.=" AND `sl`.`house_id` = '".$search['land_id']."'";
    	}
    	if (!empty($search['streetlist'])){
    		$where.=" AND `l`.`street` = '".$search['streetlist']."'";
    	}
    	return $db->fetchAll($sql.$where);
    }
    function getAllRefund($search=null){
    	$db = $this->getAdapter();
    	$dbp = new Application_Model_DbTable_DbGlobal();
    
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	$from_date =(empty($search['start_date']))? '1': " re.refund_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " re.refund_date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    
    	$sql=" SELECT
    	re.*,
    	(SELECT `p`.`project_name` FROM `ln_project` AS p WHERE `p`.`br_id` = `re`.`branch_id` LIMIT 1) AS `branch_name`,
    	CONCAT((SELECT `c`.`name_kh` FROM ln_client AS c WHERE c.client_id = `re`.`customer_id` LIMIT 1),' ',`p`.`land_address`,'-',`p`.`street`) AS rent_no,
    	(SELECT name_kh FROM `ln_view` WHERE TYPE=26 AND key_code=re.payment_method LIMIT 1) AS payment_type,
    	(SELECT  first_name FROM rms_users WHERE id=re.user_id LIMIT 1 ) AS user_name
    	";
    	$sql.=" FROM
    	`ln_rent_refund` AS re,
    	`ln_rent_property` AS rp,
    	ln_properties AS p
    	WHERE
    	rp.id = re.rent_id
    	AND rp.house_id = p.id  
    	AND `re`.`status`=1 ";
    
    	if (!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = trim(addslashes($search['adv_search']));
    		$s_where[] = " (SELECT `c`.`name_kh` FROM ln_client AS c WHERE c.client_id = `rp`.`client_id` LIMIT 1) LIKE '%{$s_search}%'";
    		$s_where[] = " `p`.`land_address` LIKE '%{$s_search}%'";
    		$s_where[] = " `p`.`street` LIKE '%{$s_search}%'";
    		$s_where[] = " re.total_amount LIKE '%{$s_search}%'";
    		$s_where[] = " re.cheque LIKE '%{$s_search}%'";
    		$where .=' AND ('.implode(' OR ',$s_where).')';
    	}
    
    	if($search['branch_id']>0){
    		$where.= " AND `re`.`branch_id` = ".$search['branch_id'];
    	}
    	if(!empty($search['land_id'])){
    		$where.= " AND rp.house_id = ".$search['land_id'];
    	}
    	if(!empty($search['client_name'])){
    		$where.= " AND `re`.`customer_id` = ".$search['client_name'];
    	}
    	if($search['payment_method']>0){
    		$where.= " AND re.payment_method = ".$search['payment_method'];
    	}
    	if (!empty($search['cheque_issuer_search'])){
    		$where.= " AND re.cheque_issuer = '".$search['cheque_issuer_search']."'";
    	}
    	$where.=$dbp->getAccessPermission("re.branch_id");
    	$order=" ORDER BY re.id DESC ";
    	return $db->fetchAll($sql.$where.$order);
    }
 }
