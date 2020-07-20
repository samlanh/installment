<?php

class Api_Model_DbTable_Dbapi extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_properties';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getAllSold($data){
    	$sql="SELECT 
					 `s`.`id`               AS `id`,
					  (SELECT
					     `ln_project`.`project_name`
					   FROM `ln_project`
					   WHERE (`ln_project`.`br_id` = `s`.`branch_id`)
					   LIMIT 1) AS `branch_name`,
					  `s`.`branch_id`        AS `branch_id`,
					  `s`.`client_id`        AS `client_id`,
					 
					  	FORMAT(price_before,2) AS price_before,
						FORMAT(price_sold,2) AS price_sold,
					  `s`.`discount_amount`  AS `discount_amount`,
					  `s`.`discount_percent` AS `discount_percent`,
					  FORMAT((price_before-price_sold),2) AS total_discount,
					  (SELECT first_name FROM `rms_users` WHERE id=s.user_id LIMIT 1) AS user_name,
		      		  (SELECT name_kh FROM `ln_view` WHERE key_code =s.payment_id AND type = 25 limit 1) AS paymenttype,
					  FORMAT((SELECT
					     SUM((`cr`.`total_principal_permonthpaid` + `cr`.`extra_payment`))
					   FROM `ln_client_receipt_money` `cr`
					   WHERE (`cr`.`sale_id` = `s`.`id`)
					   LIMIT 1),2) AS `paid_amount`,
					   DATE_FORMAT(buy_date, '%d-%m-%Y') AS  buy_date,
					   DATE_FORMAT(validate_date, '%d-%m-%Y') AS  validate_date,
					   DATE_FORMAT(agreement_date, '%d-%m-%Y') AS  agreement_date,
					  `s`.`create_date`      AS `create_date`,
					  `s`.`startcal_date`    AS `startcal_date`,
					  `s`.`first_payment`    AS `first_payment`,
					  `s`.`end_line`         AS `end_line`,
					  `s`.`interest_rate`    AS `interest_rate`,
					  `s`.`total_duration`   AS `total_duration`,
					  `p`.`land_address`     AS `land_address`,
					  `p`.`street`           AS `street`,
					  (SELECT
					     `ln_properties_type`.`type_nameen`
					   FROM `ln_properties_type`
					   WHERE (`ln_properties_type`.`id` = `p`.`property_type`)
					   LIMIT 1) AS `propertype`,
					  `c`.`name_kh`          AS `name_kh`,
					  `c`.`name_en`          AS `name_en`,
					  `c`.`phone`            AS `phone`
		      	
				FROM ((`ln_sale` `s`
				    	JOIN `ln_client` `c`)
				   		JOIN `ln_properties` `p`)
				WHERE (`c`.`client_id` = `s`.`client_id`)
				       AND `p`.`id` = `s`.`house_id`
				       AND `s`.`status` = 1   ";
    	$where='';
    	$from_date =(empty($data['start_date']))? '1': " buy_date >= '".$data['start_date']." 00:00:00'";
    	$to_date = (empty($data['end_date']))? '1': " buy_date <= '".$data['end_date']." 23:59:59'";
    	$where_date = " AND ".$from_date." AND ".$to_date;
    	if(!empty($data['adv_search'])){
    		$s_where = array();
    		$s_search = (trim($data['adv_search']));
    		$s_where[] = " p.land_address LIKE '%{$s_search}%'";
    		$s_where[] = " c.name_en LIKE '%{$s_search}%'";
    		$s_where[] = " c.name_kh LIKE '%{$s_search}%'";
    		$s_where[] = " c.phone LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if($data['branch_id']>0){
    		$where.= " AND s.branch_id = ".$data['branch_id'];
    	}
    	 $where.=' ORDER BY id DESC limit 100 ';
    	
    	$db = $this->getAdapter();
    	$sale_detail =  $db->fetchAll($sql.$where_date.$where);//sale_detail
    	
    	$sql_sale=" SELECT
	    	COUNT(`s`.`id` )  AS `count`,
	    	(SELECT name_kh FROM `ln_view` WHERE TYPE=25 AND key_code = s.payment_id LIMIT 1) payment_type,
	    	s.payment_id,
	    	SUM(`s`.`price_sold`)       AS `price_sold`
    	FROM `ln_sale` `s` WHERE `s`.`status` = 1 AND is_cancel=0  ";
    	if($data['branch_id']>0){
    		$sql_sale.= " AND s.branch_id = ".$data['branch_id'];
    	}
    	$sql_saleorder=' GROUP BY s.payment_id
    	ORDER BY s.payment_id ASC LIMIT 100 ';
    	$rs_sold = $this->getAdapter()->fetchAll($sql_sale.$where_date.$sql_saleorder);
    	 
    	$sql_paid=' SELECT s.payment_id, SUM(total_principal_permonthpaid+extra_payment) AS total_paid
    	FROM `ln_client_receipt_money` AS crm,
    	`ln_sale` AS `s`
    	WHERE
    	crm.sale_id=s.id
    	AND s.status=1 AND s.is_cancel=0 ';
    	if($data['branch_id']>0){
    		$sql_paid.= " AND s.branch_id = ".$data['branch_id'];
    	}
    	$sql_paidorder=" GROUP BY s.payment_id
    			ORDER BY s.payment_id ASC LIMIT 100 ";
    	$rs_paid = $this->getAdapter()->fetchAll($sql_paid.$where_date.$sql_paidorder);
    	 
    	$result = array();
    	$result_summary = array();
    	$t_count = 0;
    	$t_amount =0;
    	 
    	$total_paid = 0;
    	
    	if(!empty($rs_sold)){
    		foreach($rs_sold as $index => $r){
    			$t_count = $t_count+$r['count'];
    			$t_amount = $t_amount+$r['price_sold'];
    			$total_paid = $total_paid+$rs_paid[$index]['total_paid'];
    	
    			$result[$index]['payment_id']=$r['payment_id'];
    			$result[$index]['count']=$r['count'];
    			$result[$index]['payment_type']=$r['payment_type'];
    			$result[$index]['price_sold']=number_format($r['price_sold'],2);
    		}
    		$result_summary['price_sold'] = number_format($t_amount,2);
    		$result_summary['paid_amount']=number_format($total_paid,2);
    		$result_summary['balance']=number_format($t_amount-$total_paid,2);
    		$result[$index+1]['count']=$t_count;
    		$result[$index+1]['payment_id']=7;
    		$result[$index+1]['payment_type']='លក់សរុប';
    		$result[$index+1]['price_sold']=number_format($t_amount,2);
    	}
    	$include_array =array('main_sale'=>$result_summary,'sumary_sale'=>$result);
    	return array('sale_bytype'=>$include_array,'sale_detail'=>$sale_detail);
    }
    function getAllIncome($data){
    	$sql="SELECT
			  (SELECT
			     `ln_project`.`project_name`
			   FROM `ln_project`
			   WHERE (`ln_project`.`br_id` = `crm`.`branch_id`)
			   LIMIT 1) AS `branch_name`,
			  `c`.`name_kh`                        AS `name_kh`,
			  `c`.`name_en`                        AS `client_name`, 
			  `crm`.`receipt_no`                   AS `receipt_no`,
		      FORMAT(recieve_amount,2) AS amount_recieve,
			  `crm`.`note`                         AS `note`,
			  `crm`.`payment_option`               AS `payment_option`,
			  `crm`.`is_payoff`                    AS `is_payoff`,
			  `crm`.`total_principal_permonthpaid` AS `total_principal_permonthpaid`,
			  `crm`.`total_interest_permonthpaid`  AS `total_interest_permonthpaid`,
			  `crm`.`penalize_amountpaid`          AS `penalize_amountpaid`,
			  `crm`.`service_chargepaid`           AS `service_chargepaid`,
			  `crm`.`extra_payment`                AS `extra_payment`,
			  `crm`.`payment_times`                AS `payment_times`,
			  (SELECT COUNT(ln_saleschedule.id) FROM `ln_saleschedule` WHERE ln_saleschedule.sale_id=`crm`.`sale_id` LIMIT 1) AS times,
			  `l`.`land_code`                      AS `land_code`,
			  `l`.`land_address`                   AS `land_address`,
			  `l`.`street`                         AS `street`,
			  (SELECT
			  		DATE_FORMAT(`d`.`date_payment`, '%d-%m-%Y') AS date_payment
			   FROM `ln_client_receipt_money_detail` `d`
			   		WHERE (`crm`.`id` = `d`.`crm_id`)
			   			ORDER BY `d`.`date_payment` DESC
			   				LIMIT 1) AS `date_payment`,
			   DATE_FORMAT(date_pay, '%d-%m-%Y') AS  date_pay,
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
			   (SELECT first_name FROM `rms_users` WHERE rms_users.id=crm.user_id LIMIT 1) AS user_name
			   
			FROM (((`ln_client_receipt_money` `crm`
			     JOIN `ln_properties` `l`)
			    JOIN `ln_sale` `sl`)
			   JOIN `ln_client` `c`)
			WHERE ((`crm`.`client_id` = `c`.`client_id`)
			       AND (`sl`.`id` = `crm`.`sale_id`)
			       AND (`l`.`id` = `sl`.`house_id`)) ";
    	
    	$from_date =(empty($data['start_date']))? '1': " date_pay >= '".$data['start_date']." 00:00:00'";
    	$to_date = (empty($data['end_date']))? '1': " date_pay <= '".$data['end_date']." 23:59:59'";
    	$where_date = " AND ".$from_date." AND ".$to_date;
    	$where="";
    	if(!empty($data['adv_search'])){
    		$s_where = array();
    		$s_search = addslashes(trim($data['adv_search']));
    		$s_where[] = " `l`.`land_code`  LIKE '%{$s_search}%'";
    		$s_where[] = " `l`.`land_address`  LIKE '%{$s_search}%'";
    		$s_where[] = " `c`.`client_number` LIKE '%{$s_search}%'";
    		$s_where[] = " `c`.`name_en`  LIKE '%{$s_search}%'";
    		$s_where[] = " `crm`.`receipt_no` LIKE '%{$s_search}%'";
    		$where .=' AND ('.implode(' OR ',$s_where).')';
    	}
    	if($data['branch_id']>0){
    		$where.= " AND crm.branch_id = ".$data['branch_id'];
    	}
    	$order= " ORDER BY crm.id DESC  limit 100 ";
    	$db = $this->getAdapter();
    	$rs_incomedetail = $db->fetchAll($sql.$where_date.$where.$order);
    	
    	$sql_bytype="SELECT
    			SUM(crm.total_principal_permonthpaid+crm.extra_payment+crm.total_interest_permonthpaid+crm.penalize_amountpaid) AS paid_amount,
    			crm.`payment_method` AS `payment_methodid`
    		FROM  (((`ln_client_receipt_money` `crm`
			     JOIN `ln_properties` `l`)
			    JOIN `ln_sale` `sl`)
			   JOIN `ln_client` `c`)
			WHERE ((`crm`.`client_id` = `c`.`client_id`)
			       AND (`sl`.`id` = `crm`.`sale_id`)
			       AND (`l`.`id` = `sl`.`house_id`))  ";
    	$order=" GROUP BY crm.payment_method LIMIT 3";
    	$rs_incomebytype =  $db->fetchAll($sql_bytype.$where.$where_date.$order);
    	$total_cash=0;$total_bank=0;$total_cheque=0;
    	if(!empty($rs_incomebytype)){
    		foreach ($rs_incomebytype as $rs){
    			if($rs['payment_methodid']==1){//cash
    				$total_cash = number_format($rs['paid_amount'],2);
    			}elseif($rs['payment_methodid']==2){//bank
    				$total_bank = number_format($rs['paid_amount'],2);
    			}else{//cheque
    				$total_cheque = number_format($rs['paid_amount'],2);
    			}
    		}
    	}
    	$rs_incometype = array('total_cash'=>$total_cash,'total_bank'=>$total_bank,'total_cheque'=>$total_cheque);
    	return array('income_bytype'=>$rs_incometype,'income_detail'=>$rs_incomedetail);
    }
    function getExpenseType($data){
    	$db = $this->getAdapter();
    	$sql="SELECT 
	    		 id,
	    		 (SELECT name_kh FROM `ln_view` WHERE type=13 and key_code=category_id limit 1) AS category_name,
	    		 FORMAT(SUM(total_amount),2) AS total_amount,
	    		 SUM(total_amount) AS total_expense
    		FROM ln_expense WHERE status=1 AND total_amount>0 ";
    	
    	$from_date =(empty($data['start_date']))? '1': " ln_expense.date >= '".$data['start_date']." 00:00:00'";
    	$to_date = (empty($data['end_date']))? '1': " ln_expense.date <= '".$data['end_date']." 23:59:59'";
    	$where_date = " AND ".$from_date." AND ".$to_date;
    	$where="";
    	if(!empty($data['adv_search'])){
    		$s_where = array();
    		$s_search = addslashes(trim($data['adv_search']));
    		$s_where[] = " `invoice`  LIKE '%{$s_search}%'";
    		$s_where[] = " `other_invoice`  LIKE '%{$s_search}%'";
    		$where .=' AND ('.implode(' OR ',$s_where).')';
    	}
    	if($data['branch_id']>0){
    		$where.= " AND branch_id = ".$data['branch_id'];
    	}
    	$order= " GROUP BY category_id  ORDER BY (SELECT v.parent_id FROM `ln_view` AS v WHERE v.type =13 AND v.key_code = `category_id` LIMIT 1) ASC,
    				`category_id` ASC ,date DESC  ";
    	$rs_expense =  $db->fetchAll($sql.$where.$where_date.$order);
    	
    	
    	$sql="SELECT co.`category_id` AS id,
    	FORMAT(SUM(total_amount),2) AS total_amount,
    	SUM(total_amount) AS total_expense,
    	(SELECT v.name_kh FROM `ln_view` AS v WHERE v.type =13 AND v.key_code = co.`category_id` LIMIT 1) AS category_name,
    	co.`date` 
    	FROM `ln_comission` AS co WHERE co.status=1 AND total_amount>0 ";
    	
    	$from_date =(empty($data['start_date']))? '1': " co.date >= '".$data['start_date']." 00:00:00'";
    	$to_date = (empty($data['end_date']))? '1': " co.date <= '".$data['end_date']." 23:59:59'";
    	$where_date = " AND ".$from_date." AND ".$to_date;
    	
    	$order=" GROUP BY co.`category_id` ";
    	$rs_comission =  $db->fetchAll($sql.$where.$where_date.$order);
    	$rs_detail = array_merge($rs_expense,$rs_comission);
    	
    	$total_expense = 0;
    	if(!empty($rs_detail)){
    		foreach($rs_detail as $r){
    			$total_expense = $total_expense+$r['total_expense'];
    		}
    	}
    	$total_expense = array('total_expense'=>number_format($total_expense,2));
    	$rs = array('total_expense'=>$total_expense,'expense_bytype'=>$rs_detail);
    	
    	$result_detail = $this->getAllDetailExpense($data);
    	return array('result_bytype'=>$rs,'result_detail'=>$result_detail);
    	
    }
    function getAllDetailExpense($data){
    	$sql=" SELECT id,
    		(SELECT project_name FROM `ln_project` WHERE ln_project.br_id =branch_id LIMIT 1) AS branch_name,
	    		CASE
					WHEN supplier_id=0 THEN 'N/A'
					WHEN supplier_id!=0 THEN (SELECT ls.name FROM `ln_supplier` AS ls WHERE ls.id = supplier_id LIMIT 1) 
				END as supplier_name,
    		(SELECT name_kh FROM `ln_view` WHERE type=2 and key_code=payment_id limit 1) AS payment_type,
    		title,invoice,
	    		CASE 
					WHEN other_invoice IS NULL THEN 'N/A'
					ELSE other_invoice
				END AS other_invoice,
				CASE 
					WHEN cheque IS NULL THEN ''
					ELSE cheque
				END AS cheque,
    		(SELECT name_kh FROM `ln_view` WHERE type=13 and key_code=category_id limit 1) AS category_name,
    		cheque,FORMAT(total_amount,2) total_amount,description,
			DATE_FORMAT(date,'%d-%m-%Y') date,
    		(SELECT  first_name FROM rms_users WHERE id=user_id limit 1 ) AS user_name,
    		status FROM ln_expense WHERE status=1 ";
    	
	    	$from_date =(empty($data['start_date']))? '1': " ln_expense.date >= '".$data['start_date']." 00:00:00'";
	    	$to_date = (empty($data['end_date']))? '1': " ln_expense.date <= '".$data['end_date']." 23:59:59'";
	    	$where_date = " AND ".$from_date." AND ".$to_date;
	    	$where="";
	    	if(!empty($data['adv_search'])){
	    		$s_where = array();
	    		$s_search = addslashes(trim($data['adv_search']));
	    		$s_where[] = " `invoice`  LIKE '%{$s_search}%'";
	    		$s_where[] = " `other_invoice`  LIKE '%{$s_search}%'";
	    		$where .=' AND ('.implode(' OR ',$s_where).')';
	    	}
	    	if($data['branch_id']>0){
	    		$where.= " AND branch_id = ".$data['branch_id'];
	    	}
    		$order=" order by branch_id DESC, id DESC  limit 100  ";
    		
    		$db = $this->getAdapter();
    		$rs_expense =  $db->fetchAll($sql.$where.$where_date.$order);
    		
    		$sql="SELECT c.`id`,
		    		p.project_name AS `branch_name`,
		    		(SELECT co_khname FROM `ln_staff` WHERE co_id=c.staff_id LIMIT 1 ) AS supplier_name,
		    		(SELECT name_kh FROM `ln_view` WHERE TYPE=13 AND key_code=c.category_id LIMIT 1) AS category_name,
		    		c.total_amount,
		    		c.invoice,
		    		DATE_FORMAT(for_date,'%d-%m-%Y') AS `date`,
		    		'សាច់ប្រាក់' AS payment_type,
		    		'' AS other_invoice,
		    		'' AS cheque
	    		FROM `ln_comission` AS c ,
		    		`ln_sale` AS s,
		    		`ln_project` AS p,
		    		`ln_properties` AS pro,
		    		`ln_client` AS clie
	    		WHERE
		    		s.`id` = c.`sale_id`
		    		AND p.`br_id` = c.`branch_id`
		    		AND pro.`id` = s.`house_id`
		    		AND clie.`client_id` = s.`client_id`
		    		AND c.status=1
		    		AND c.total_amount>0 ";
    		
    		$from_date =(empty($data['start_date']))? '1': " c.for_date >= '".$data['start_date']." 00:00:00'";
    		$to_date = (empty($data['end_date']))? '1': " c.for_date <= '".$data['end_date']." 23:59:59'";
    		$where_date = " AND ".$from_date." AND ".$to_date;
    		$order="";
    		
    		$rs_comission =  $db->fetchAll($sql.$where.$where_date.$order);
    		return array_merge($rs_expense,$rs_comission);
    }
//     function getAllComissionDetail(){
//     	$sql="SELECT c.`id`,
// 	    		p.project_name AS `branch_name`,
// 					(SELECT co_khname FROM `ln_staff` WHERE co_id=c.staff_id LIMIT 1 ) AS supplier_name,
// 					(SELECT name_kh FROM `ln_view` WHERE TYPE=13 AND key_code=c.category_id LIMIT 1) AS category_name,
// 	    		c.total_amount,
// 	    		c.invoice,
// 	    		DATE_FORMAT(for_date,'%d-%m-%Y') AS `date`,
// 	    		'សាច់ប្រាក់' AS payment_type,
// 	    		'' AS other_invoice,
// 	    		'' AS cheque
// 	    		FROM `ln_comission` AS c ,
// 	    			`ln_sale` AS s,
// 	    			`ln_project` AS p,
// 	    			`ln_properties` AS pro,
// 	    		`ln_client` AS clie
// 	    		WHERE 
// 			    		s.`id` = c.`sale_id` 
// 			    		AND p.`br_id` = c.`branch_id` 
// 			    		AND pro.`id` = s.`house_id` 
// 			    		AND clie.`client_id` = s.`client_id` 
// 			    		AND c.status=1
// 		    			AND c.total_amount>0 ";
//     	return $this->getAdapter()->fetchAll($sql);
//     }
   
	public function getAllOutstadingLoan($search=null){
	      	$db = $this->getAdapter();
	      	$where="";
	      	$to_date = (empty($search['end_date']))? '1': " date_release <= '".$search['end_date']." 23:59:59'";
	      	$where.= "  AND ".$to_date;
	      	$sql="SELECT *,
	      			FORMAT(price_sold,2) AS price_sold,
					FORMAT((SELECT SUM(total_principal_permonthpaid+extra_payment) FROM `ln_client_receipt_money` WHERE status=1 AND sale_id=v_loanoutstanding.id),2) AS paid_amount,
					FORMAT((price_sold-(SELECT SUM(total_principal_permonthpaid+extra_payment) FROM `ln_client_receipt_money` WHERE status=1 AND sale_id=v_loanoutstanding.id)),2) AS balance_amount
	      	FROM v_loanoutstanding WHERE 1 ";//IF BAD LOAN STILL GET IT
	      	
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
	      	
	      	$where.=" LIMIT 100";
	      	return $db->fetchAll($sql.$where);
	}
    public function getALLLoanExpectIncome($search=null){
    	$search = array(
    			'start_date'=> date('Y-m-1'),
    			'end_date'=>date('Y-m-d')
    			);
    	$from_date =(empty($search['start_date']))? '1': " date_payment >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " date_payment <= '".$search['end_date']." 23:59:59'";
    	$where= " AND ".$from_date." AND ".$to_date;
    	 
    	$db = $this->getAdapter();
    	$sql = "SELECT
				  `c`.`name_kh`                  AS `name_kh`,
				  `c`.`phone`                    AS `phone`,
				  (SELECT
				     `ln_project`.`project_name`
				   FROM `ln_project`
				   WHERE (`ln_project`.`br_id` = `s`.`branch_id`)
				   LIMIT 1) AS `branch_name`,
				   
				  `l`.`land_address`             AS `land_address`,
				  `l`.`street`                   AS `street`,
				  `sd`.`penelize`                AS `penelize`,
				  `sd`.`date_payment`            AS `date_payment`,
				  `sd`.`status`                  AS `status`,
				  `sd`.`is_completed`            AS `is_completed`,
				  
				  `sd`.`principal_permonthafter` AS `principal_permonthafter`,
				  `sd`.`total_interest_after`    AS `total_interest_after`,
				  `sd`.`total_payment_after`     AS `total_payment_after`,
				  
				  `sd`.`principal_permonth`      AS `principal_permonth`,
				  `sd`.`total_interest`          AS `total_interest`,
				  `sd`.`total_payment`           AS `total_payment`,
				  `sd`.`service_charge`          AS `service_charge`,
				  `sd`.`no_installment`          AS `no_installment`,
				  
				  (SELECT
				     `ln_client_receipt_money`.`date_input`
				   FROM `ln_client_receipt_money`
					   WHERE (`ln_client_receipt_money`.`land_id` = 1)
					   ORDER BY `ln_client_receipt_money`.`date_input` DESC
				   		LIMIT 1) AS `last_pay_date`
				   		
				FROM (((`ln_sale` `s`
				    JOIN `ln_saleschedule` `sd`)
				    JOIN `ln_properties` `l`)
				    JOIN `ln_client` `c`)
				WHERE ((`s`.`id` = `sd`.`sale_id`)
				       AND (`l`.`id` = `s`.`house_id`)
				       AND (`s`.`status` = 1)
				       AND (`s`.`is_cancel` = 0)
				       AND (`sd`.`status` = 1)
				       AND (`c`.`client_id` = `s`.`client_id`)) ";    	 
    	$group_by =" GROUP BY `s`.`id`,`sd`.`date_payment` ORDER BY date_payment DESC ";
    	$row = $db->fetchAll($sql.$where.$group_by);
    	return $row;
    }
    public function getAllSaleCancel($search=null){
    	
    	$from_date =(empty($search['start_date']))? '1': " c.create_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " c.create_date <= '".$search['end_date']." 23:59:59'";
    	$where= " AND ".$from_date." AND ".$to_date;
    	$db = $this->getAdapter();
    	$sql='SELECT 
    		    c.`id`,c.return_back,
    		    (SELECT project_name FROM `ln_project` WHERE br_id=c.`branch_id` LIMIT 1) AS `project_name`,
    		    c.installment_paid,
    		    c.reason,
    		    DATE_FORMAT(c.`create_date`,"%d-%m-%Y") AS create_date,
				FORMAT(c.paid_amount,2) AS paid_amount,
				FORMAT(s.price_sold,2) AS price_sold,
				FORMAT(c.return_back,2) AS return_back,
				(clie.`name_kh`) AS client_name,clie.phone,
				(SELECT pt.`type_nameen` FROM `ln_properties_type` AS pt WHERE pt.`id` = pro.`property_type` LIMIT 1) AS type_name,
				pro.`property_type`,pro.`land_address`,pro.`street`,
				(SELECT first_name FROM `rms_users` WHERE id=c.user_id LIMIT 1) AS user_name
				FROM 
					`ln_sale_cancel` AS c, 
					`ln_sale` AS s, 
					`ln_properties` AS pro,
					`ln_client` AS clie
				WHERE s.`id` = c.`sale_id` AND pro.`id` = c.`property_id` AND
				clie.`client_id` = s.`client_id` ';
		    	if(!empty($search['adv_search'])){
		    		$s_where = array();
		    		$s_search = (trim($search['adv_search']));
		    		$s_where[] = " p.land_address LIKE '%{$s_search}%'";
		    		$s_where[] = " p.street LIKE '%{$s_search}%'";
		    		$s_where[] = " c.client_name LIKE '%{$s_search}%'";
		    		$s_where[] = " c.phone LIKE '%{$s_search}%'";
		    		$where .=' AND ( '.implode(' OR ',$s_where).')';
		    	}
		    	
		    	if($search['branch_id']>0){
		    		$where.= " AND s.branch_id = ".$search['branch_id'];
		    	}
    	
    		$order = " ORDER BY c.`branch_id` DESC,c.id DESC ";
	    	$row = $db->fetchAll($sql.$where.$order);
	    	return $row;
    }
    public function getDailyIncome($search=null){
    	$search = array(
    			'start_date'=> date('Y-m-d'),
    	);
    	$db = $this->getAdapter();
    	$sql='SELECT 
    			SUM(total_principal_permonthpaid+extra_payment+total_interest_permonthpaid+penalize_amountpaid) 
    			FROM `ln_client_receipt_money` WHERE status=1 AND ';
    	$curr_date = (empty($search['start_date']))? '1': " date_input <= '".$search['start_date']." 23:59:59'";
    	$row = $db->fetchOne($sql.$curr_date);
    	return $row;
    }
    public function getDailyExpense($search=null){
    	$search = array(
    			'start_date'=> date('Y-m-d'),
    	);
    	$db = $this->getAdapter();
    	$sql='SELECT
    	SUM(total_amount)
    	FROM `ln_expense` WHERE status=1 AND ';
    	$curr_date = (empty($search['start_date']))? '1': " date <= '".$search['start_date']." 23:59:59'";
    	$row = $db->fetchOne($sql.$curr_date);
    	return $row;
    }
    public function getAllCollectPayment($search=null){
    	$db=$this->getAdapter();
    	$search['end_date']=date("Y-m-d");
    	$sql = "SELECT v.*,
    	
    	FORMAT(v.total_payment_after,2) AS total_payment_after,
    	DATE_FORMAT(v.date_payment,'%d-%m-%Y') AS date_payment,
    	
    	(SELECT sch.ispay_bank FROM `ln_saleschedule` AS sch WHERE sch.id = v.id LIMIT 1  ) AS ispay_bank,
    	(SELECT ln_view.name_kh FROM ln_view WHERE ln_view.type =29 AND key_code = (SELECT sch.ispay_bank FROM `ln_saleschedule` AS sch WHERE sch.id = v.id LIMIT 1  ) LIMIT 1) AS payment_type
    	FROM v_newloancolect AS v WHERE  ";
    	$where = (empty($search['end_date']))? '1': " v.date_payment <= '".$search['end_date']." 23:59:59'";
    	 
//     	if($search['client_name']>0){
//     		$where.=" AND v.client_id = ".$search['client_name'];
//     	}
//     	if($search['branch_id']>0){
//     		$where.=" AND v.branch_id = ".$search['branch_id'];
//     	}
//     	if($search['stepoption']>0){
//     		$where.=" AND (SELECT sch.ispay_bank FROM `ln_saleschedule` AS sch WHERE sch.id = v.id LIMIT 1  ) = ".$search['stepoption'];
//     	}else{
//     		$where.= " AND ".$to_date;
//     		if ($search['stepoption']==0){
//     			$where.= " AND (SELECT sch.ispay_bank FROM `ln_saleschedule` AS sch WHERE sch.id = v.id LIMIT 1  )=0";
//     		}
//     	}
    	 
//     	if($search['last_optiontype']>-1){
//     		$where.=" AND v.last_optiontype = ".$search['last_optiontype'];
//     	}
//     	if(!empty($search['adv_search'])){
//     		$s_where = array();
//     		$s_search = trim(addslashes($search['adv_search']));
//     		$s_where[] = " v.sale_number LIKE '%{$s_search}%'";
//     		$s_where[] = " v.client_number LIKE '%{$s_search}%'";
//     		$s_where[] = " v.phone_number LIKE '%{$s_search}%'";
//     		$s_where[] = " v.client_name LIKE '%{$s_search}%'";
//     		$s_where[] = " v.land_code LIKE '%{$s_search}%'";
//     		$s_where[] = " v.land_address LIKE '%{$s_search}%'";
//     		$s_where[] = " v.street LIKE '%{$s_search}%'";
//     		$where .=' AND ( '.implode(' OR ',$s_where).')';
//     	}
    	$order=" ORDER BY v.date_payment ASC ";
    	 
    	return $db->fetchAll($sql.$where.$order);
    }
    public function getAllSaleAgreement($search=null){
    	$db = $this->getAdapter();
		$session_lang=new Zend_Session_Namespace('lang');
		$lang = $session_lang->lang_id;
		$str = 'name_en';
		if($lang==1){
			$str = 'name_kh';
		}
		$from_date =(empty($search['start_date']))? '1': " date_pay >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " date_pay <= '".$search['end_date']." 23:59:59'";
		
		$sql=" SELECT
		  	  `s`.`id`               AS `id`,
			  (SELECT
			     `ln_project`.`project_name`
			   FROM `ln_project`
			   WHERE (`ln_project`.`br_id` = `s`.`branch_id`)
			   LIMIT 1) AS `branch_name`,
			  FORMAT(`s`.`price_sold`,2)       AS `price_sold`,
			  DATE_FORMAT(s.buy_date,'%d-%m-%Y') AS buy_date,
			  DATE_FORMAT(s.validate_date,'%d-%m-%Y') AS validate_date,
			  `p`.`land_address`     AS `land_address`,
			  `p`.`street`           AS `street`,
			  `c`.`name_kh`          AS `name_kh`,
			  `c`.`name_en`          AS `name_en`,
			  `c`.`phone`            AS `phone` 
		   FROM ((`ln_sale` `s`
		    JOIN `ln_client` `c`)
		   JOIN `ln_properties` `p`)
	  				WHERE ((`c`.`client_id` = `s`.`client_id`)
			       	AND (`p`.`id` = `s`.`house_id`)
			       	AND (`s`.`status` = 1)) 
					AND s.payment_id=1 AND s.is_cancel=0";
	
		
		$from_date =(empty($search['start_date']))? '1': " date_pay >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " date_pay <= '".$search['end_date']." 23:59:59'";
		
		$to_date = (empty($search['end_date']))? '1': " s.end_line <= '".$search['end_date']." 23:59:59'";
		$where="";
		$where.= " AND ".$to_date;
		
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[] = " `p`.`land_address` LIKE '%{$s_search}%'";
			$s_where[] = " `p`.`street` LIKE '%{$s_search}%'";
			$s_where[] = " `c`.`name_en`  LIKE '%{$s_search}%'";
			$s_where[] = " `c`.`name_kh`  LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if($search['branch_id']>0){
			$where.=" AND s.branch_id = ".$search['branch_id'];
		}

		$order = " ORDER BY s.id ASC,s.payment_id DESC ";
		return $db->fetchAll($sql.$where.$order);
    }
    function getAllCommission($search=null){
    	$db = $this->getAdapter();
    	$from_date =(empty($search['start_date']))? '1': "c.`for_date` >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "c.`for_date` <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	$sql ='SELECT 
			    	c.`id`,
			    	s.id AS saleid,
			    	p.`project_name`,
			    	clie.`name_kh` AS client_name,
			    	pro.`land_address`,pro.`street`,
			    	(SELECT co_khname FROM `ln_staff` WHERE co_id=c.staff_id LIMIT 1) AS staff_name,
			    	(SELECT sex FROM `ln_staff` WHERE co_id=c.staff_id LIMIT 1) AS sex,
			    	(SELECT tel FROM `ln_staff` WHERE co_id=c.staff_id LIMIT 1) AS tel,
			    	FORMAT(c.total_amount,2) as total_amount,
			    	DATE_FORMAT(c.for_date,"%d-%m-%Y") as create_date,
			    	c.invoice,
			    	(SELECT  first_name FROM rms_users WHERE id = c.user_id LIMIT 1 ) AS user_name
		    	FROM `ln_comission` AS c ,
			    	`ln_sale` AS s,
			    	`ln_project` AS p,
			    	`ln_properties` AS pro,
			    	`ln_client` AS clie
		    	WHERE
	    	s.`id` = c.`sale_id`
	    	AND p.`br_id` = c.`branch_id`
	    	AND pro.`id` = s.`house_id`
	    	AND clie.`client_id` = s.`client_id`
	    	AND c.status=1
	    	AND c.total_amount>0 ';
    
//     	if($search['branch_id']>0){
//     		$where.= " AND c.branch_id = ".$search['branch_id'];
//     	}
//     	if(!empty($search['co_khname']) AND $search['co_khname']>0){
//     		$where.= " AND c.staff_id = ".$search['co_khname'];
//     	}
//     	if(!empty($search['land_id']) AND $search['land_id']>0){
//     		$where.= " AND s.house_id = ".$search['land_id'];
//     	}
//     	if(!empty($search['category_id_expense']) AND $search['category_id_expense']>0){
//     		$where.= " AND c.category_id = ".$search['category_id_expense'];
//     	}
//     	if(!empty($search['adv_search'])){
//     		$s_where = array();
//     		$s_search = addslashes(trim($search['adv_search']));
//     		$s_where[] = " clie.`client_number` LIKE '%{$s_search}%'";
//     		$s_where[] = " clie.`name_kh` LIKE '%{$s_search}%'";
//     		$s_where[] = " c.`description` LIKE '%{$s_search}%'";
//     		$s_where[] = " s.`sale_number` LIKE '%{$s_search}%'";
//     		$s_where[] = " pro.`land_address` LIKE '%{$s_search}%'";
//     		$s_where[] = " pro.`land_code` LIKE '%{$s_search}%'";
//     		$s_where[] = " pro.`street` LIKE '%{$s_search}%'";
//     		$where .=' AND ('.implode(' OR ',$s_where).')';
//     	}
    	$rsd_commission = $db->fetchAll($sql.$where);
    	
    	$from_date =(empty($search['start_date']))? '1': "c.`for_date` >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': "c.`for_date` <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	$sql ='
    	SELECT
	    	FORMAT(SUM(c.total_amount),2) as total_amount
	    		FROM `ln_comission` AS c
	    	WHERE
		    	c.status=1
	    		AND c.total_amount>0 ';
    	$rst_commision = $db->fetchRow($sql.$where);
    	return array('rsd_commission'=>$rsd_commission,'rst_commision'=>$rst_commision);
    }
    function projectData(){
    	$db = new Application_Model_DbTable_DbGlobal();
    	$result = $db->getAllBranchByUser($branch_id=null,$opt=null);
    	array_unshift($result,array(
    		"id" =>"-1",
    		"name" =>"គម្រោងទាំងអស់",
    	));
    	return $result;
    }
}