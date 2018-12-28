<?php

class Api_Model_DbTable_Dbapi extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_properties';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authinstall');
    	return $session_user->user_id;
    }
    function getAllSold(){
    	$sql="SELECT * ,
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
      	FROM v_getcollectmoney WHERE status=1  limit 100 ";
    	$sql.= " ORDER BY id DESC ";
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
					(SELECT SUM(total_principal_permonthpaid+extra_payment) FROM `ln_client_receipt_money` WHERE STATUS=1 AND sale_id=v_loanoutstanding.id) AS paid_amount
	      	FROM v_loanoutstanding WHERE 1 ";//IF BAD LOAN STILL GET IT
	      	$where.=" LIMIT 100";
	      	return $db->fetchAll($sql.$where);
	}
    public function getALLLoanExpectIncome($search=null){
    	$search = array(
    			'start_date'=> date('Y-m-d'),
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
}