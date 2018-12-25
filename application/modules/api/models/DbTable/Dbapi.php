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
      	 FROM v_soldreport WHERE 1 ORDER BY id DESC ";
    	$db = $this->getAdapter();
    	return $db->fetchAll($sql);
    }
    function getAllIncome(){
    	$sql="SELECT *,
			(SELECT first_name FROM `rms_users` WHERE id=v_getcollectmoney.user_id LIMIT 1) AS user_name,
			(SELECT s.price_sold FROM `ln_sale` AS s WHERE s.id = sale_id LIMIT 1) AS sold_price
      	FROM v_getcollectmoney WHERE status=1 ";
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
    		FROM ln_expense WHERE status=1 AND total_amount>0 group by category_id order by date desc";
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
    		 
    		$order=" order by branch_id DESC, id DESC ";
    		$db = $this->getAdapter();
    		return $db->fetchAll($sql.$order);
    }
    function getAllOutstanding(){
    	
    }
}