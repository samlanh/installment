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
    	  (price_before-price_sold) AS total_discount,
      	 (SELECT first_name FROM `rms_users` WHERE id=v_soldreport.user_id LIMIT 1) AS user_name,
      	 (SELECT name_kh FROM `ln_view` WHERE key_code =v_soldreport.payment_id AND type = 25 limit 1) AS paymenttype
      	 FROM v_soldreport WHERE 1 ORDER BY id DESC limit 100 ";
    	$db = $this->getAdapter();
    	return $db->fetchAll($sql);
    }
    function getAllIncome(){
    	$sql="SELECT *,
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
    		 SUM(total_amount) AS total_amount
    		FROM ln_expense WHERE status=1 AND total_amount>0 group by category_id order by date desc  limit 100 ";
    	return $db->fetchAll($sql);
    }
    function getAllDetailExpense(){
    	$sql=" SELECT id,
    		(SELECT project_name FROM `ln_project` WHERE ln_project.br_id =branch_id LIMIT 1) AS branch_name,
    		(SELECT ls.name FROM `ln_supplier` AS ls WHERE ls.id = supplier_id LIMIT 1) AS supplier_name,
    		(SELECT name_kh FROM `ln_view` WHERE type=26 and key_code=payment_id limit 1) AS payment_type,
    		title,invoice,
    	
    		(SELECT name_kh FROM `ln_view` WHERE type=13 and key_code=category_id limit 1) AS category_name,
    		cheque,total_amount,description,date,
    		(SELECT  first_name FROM rms_users WHERE id=user_id limit 1 ) AS user_name,
    		status FROM ln_expense WHERE status=1 ";
    		 
    		$order=" order by branch_id DESC, id DESC  limit 100  ";
    		$db = $this->getAdapter();
    		return $db->fetchAll($sql.$order);
    }
	public function getAllOutstadingLoan($search=null){
	      	$db = $this->getAdapter();
	      	$where="";
	      	$search['end_date']=date("Y-m-d");
	      	$to_date = (empty($search['end_date']))? '1': " date_release <= '".$search['end_date']." 23:59:59'";
	      	$where.= "  AND ".$to_date;
	      	$sql="SELECT *,
					(SELECT SUM(total_principal_permonthpaid+extra_payment) FROM `ln_client_receipt_money` WHERE status=1 AND sale_id=v_loanoutstanding.id) AS paid_amount,
					(price_sold-(SELECT SUM(total_principal_permonthpaid+extra_payment) FROM `ln_client_receipt_money` WHERE status=1 AND sale_id=v_loanoutstanding.id)) AS balance_amount
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
    		    c.paid_amount,
    		    c.installment_paid,
    		    c.reason,
    		    c.`create_date`,
				s.`sale_number`,
				s.price_sold,
				(clie.`name_kh`) AS client_name,
				pro.`land_code`,
				(SELECT pt.`type_nameen` FROM `ln_properties_type` AS pt WHERE pt.`id` = pro.`property_type` LIMIT 1) AS type_name,
				pro.`property_type`,pro.`land_address`,pro.`street`,
				(SELECT first_name FROM `rms_users` WHERE id=c.user_id LIMIT 1) AS user_name
				FROM `ln_sale_cancel` AS c, 
				`ln_sale` AS s, 
				`ln_properties` AS pro,
				`ln_client` AS clie
				WHERE s.`id` = c.`sale_id` AND pro.`id` = c.`property_id` AND
				clie.`client_id` = s.`client_id`';
    		$order = " ORDER BY c.`branch_id` DESC";
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