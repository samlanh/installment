<?php

class Api_Model_DbTable_Dbapi extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_properties';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getAllSold(){
    	$sql="SELECT * ,
		FORMAT(price_before,2) AS price_before,
		FORMAT(price_sold,2) AS price_sold,
		FORMAT(balance,2) AS balance,
		FORMAT(paid_amount,2) AS paid_amount,
		DATE_FORMAT(buy_date, '%d-%m-%Y') AS  buy_date,
		DATE_FORMAT(validate_date, '%d-%m-%Y') AS  validate_date,
		DATE_FORMAT(agreement_date, '%d-%m-%Y') AS  agreement_date,
		
    	FORMAT((price_before-price_sold),2) AS total_discount,
      	(SELECT first_name FROM `rms_users` WHERE id=v_soldreport.user_id LIMIT 1) AS user_name,
      	(SELECT name_kh FROM `ln_view` WHERE key_code =v_soldreport.payment_id AND type = 25 limit 1) AS paymenttype
      FROM v_soldreport WHERE 1 ORDER BY id DESC limit 100 ";
    	$db = $this->getAdapter();
    	return $db->fetchAll($sql);
    }
   	function getSalebyType(){
   		$sql=" SELECT
		  COUNT(`s`.`id` )              AS `count`,	
		  (SELECT name_kh FROM `ln_view` WHERE TYPE=25 AND key_code = s.payment_id LIMIT 1) payment_type,
		  s.payment_id,
		  SUM(`s`.`price_sold`)       AS `price_sold`
		   FROM `ln_sale` `s` WHERE `s`.`status` = 1 AND is_cancel=0  GROUP BY s.payment_id
		   ORDER BY s.payment_id ASC LIMIT 6 ";
   		 $rs = $this->getAdapter()->fetchAll($sql);
   		 $result = array();
   		 $result_summary = array();
   		 $t_count = 0;
   		 $t_amount =0;
   		
   		 $total_paid = 0;
   		 
   		 if(!empty($rs)){
   		 	foreach($rs as $index => $r){
   		 		$t_count = $t_count+$r['count'];
   		 		$t_amount = $t_amount+$r['price_sold'];
   		 		$total_paid = $total_paid+$r['price_sold'];
   		 		
   		 		$result[$index]['payment_id']=$r['payment_id'];
   		 		$result[$index]['count']=$r['count'];
   		 		$result[$index]['payment_type']=$r['payment_type'];
   		 		$result[$index]['price_sold']=number_format($r['price_sold'],2);
   		 	}
   		 	$result_summary['paid_amount']=$total_paid;
   		 	$result[$index+1]['count']=$t_count;
   		 	$result[$index+1]['payment_id']=7;
   		 	$result[$index+1]['payment_type']='លក់សរុប';
   		 	$result[$index+1]['price_sold']=number_format($t_amount,2);
   		 }
   		 //$include_array =array('main_sale'=>$result_summary,'sumary_sale'=>$result);
   		return $result;
   	}
    function getAllIncome(){
    	$sql="SELECT *,
		DATE_FORMAT(date_pay, '%d-%m-%Y') AS  date_pay,
		DATE_FORMAT(date_payment, '%d-%m-%Y') AS  date_payment,
		FORMAT(amount_recieve,2) AS amount_recieve,
			(SELECT first_name FROM `rms_users` WHERE id=v_getcollectmoney.user_id LIMIT 1) AS user_name,
			(SELECT s.price_sold FROM `ln_sale` AS s WHERE s.id = sale_id LIMIT 1) AS sold_price
      	FROM v_getcollectmoney WHERE status=1 ";
    	$sql.= " ORDER BY id DESC  limit 100 ";
    	$db = $this->getAdapter();
    	return $db->fetchAll($sql);
    }
    function getExpenseType(){
    	$db = $this->getAdapter();
    	$sql="SELECT id,
    		(SELECT project_name FROM `ln_project` WHERE ln_project.br_id =branch_id LIMIT 1) AS branch_name,
    		(SELECT name_kh FROM `ln_view` WHERE type=13 and key_code=category_id limit 1) AS category_name,
    		other_invoice,
    		cheque,
    		 FORMAT(SUM(total_amount),2) AS total_amount
    		FROM ln_expense WHERE status=1 AND total_amount>0 group by category_id 
			ORDER BY (SELECT v.parent_id FROM `ln_view` AS v WHERE v.type =13 AND v.key_code = `category_id` LIMIT 1) ASC,
    				`category_id` ASC ,date DESC
    	limit 100 ";
    	return $db->fetchAll($sql);
    }
   function getAllComissionbyType(){
   	$sql="SELECT co.`category_id` AS id,
    		 FORMAT(SUM(total_amount),2) AS total_amount,
    		 (SELECT v.name_kh FROM `ln_view` AS v WHERE v.type =13 AND v.key_code = co.`category_id` LIMIT 1) AS category_name,
    		 co.`date` FROM `ln_comission` AS co WHERE 1 AND co.status=1 ";
   	return $this->getAdapter()->fetchAll($sql);
   	
   }
    function getAllDetailExpense(){
    	$sql=" SELECT id,
    		(SELECT project_name FROM `ln_project` WHERE ln_project.br_id =branch_id LIMIT 1) AS branch_name,
	    		CASE
					WHEN supplier_id=0 THEN 'N/A'
					WHEN supplier_id!=0 THEN (SELECT ls.name FROM `ln_supplier` AS ls WHERE ls.id = supplier_id LIMIT 1) 
				END as supplier_name,
    		(SELECT name_kh FROM `ln_view` WHERE type=26 and key_code=payment_id limit 1) AS payment_type,
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
    		 
    		$order=" order by branch_id DESC, id DESC  limit 100  ";
    		$db = $this->getAdapter();
    		return $db->fetchAll($sql.$order);
    		
    }
    function getAllComissionDetail(){
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
    	return $this->getAdapter()->fetchAll($sql);
    }
   
	public function getAllOutstadingLoan($search=null){
	      	$db = $this->getAdapter();
	      	$where="";
	      	$search['end_date']=date("Y-m-d");
	      	$to_date = (empty($search['end_date']))? '1': " date_release <= '".$search['end_date']." 23:59:59'";
	      	$where.= "  AND ".$to_date;
	      	$sql="SELECT *,
	      			FORMAT(price_sold,2) AS price_sold,
					FORMAT((SELECT SUM(total_principal_permonthpaid+extra_payment) FROM `ln_client_receipt_money` WHERE status=1 AND sale_id=v_loanoutstanding.id),2) AS paid_amount,
					FORMAT((price_sold-(SELECT SUM(total_principal_permonthpaid+extra_payment) FROM `ln_client_receipt_money` WHERE status=1 AND sale_id=v_loanoutstanding.id)),2) AS balance_amount
	      	FROM v_loanoutstanding WHERE 1 ";//IF BAD LOAN STILL GET IT
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
    	$sql = "SELECT * FROM `v_getexpectincome` WHERE 1 ";    	 
    	$group_by = " GROUP BY id,date_payment ORDER BY date_payment DESC ";
    	$row = $db->fetchAll($sql.$where.$group_by);
    	return $row;
    }
    public function getAllSaleCancel($search=null){
    	$search = array(
    			'start_date'=> date('Y-m-d'),
    			'end_date'=>date('Y-m-d')
    	);
//     	$from_date =(empty($search['start_date']))? '1': " date_payment >= '".$search['start_date']." 00:00:00'";
//     	$to_date = (empty($search['end_date']))? '1': " date_payment <= '".$search['end_date']." 23:59:59'";
//     	$where= " AND ".$from_date." AND ".$to_date;
    	$where="";
    	$db = $this->getAdapter();
    	$sql='SELECT 
    		    c.`id`,c.return_back,
    		    (SELECT project_name FROM `ln_project` WHERE br_id=c.`branch_id` LIMIT 1) AS `project_name`,
    		    c.installment_paid,
    		    c.reason,
    		    DATE_FORMAT(c.`create_date`,"%d-%m-%Y") AS create_date,
				s.`sale_number`,
				FORMAT(c.paid_amount,2) AS paid_amount,
				FORMAT(s.price_sold,2) AS price_sold,
				FORMAT(c.return_back,2) AS return_back,
				(clie.`name_kh`) AS client_name,
				pro.`land_code`,
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
    
    
}