<?php

class Home_Model_DbTable_DbDashboard extends Zend_Db_Table_Abstract
{

//     protected $_name = 'ln_properties';
//     public function getUserId(){
//     	$session_user=new Zend_Session_Namespace('auth');
//     	return $session_user->user_id;
//     }
	public function getAllProperty($soldpropery=null,$available=null){
		$db = $this->getAdapter();
		$sql="SELECT COUNT(p.`id`) AS totalProperty 
			FROM `ln_properties` AS p 
			WHERE p.`status` =1  ";
		if (!empty($soldpropery)){
			$sql.=" AND p.`is_lock` =1";
		}
		if (!empty($available)){
			$sql.=" AND p.`is_lock` =0";
		}
		return $db->fetchOne($sql);
	}
	
	function CountAllClient(){
		$db = $this->getAdapter();
		$sql="SELECT COUNT(p.`client_id`) AS total
			FROM `ln_client` AS p 
			WHERE p.`status` =1 ";
		return $db->fetchOne($sql);
	}
	
	function CountAllAgency(){
		$db = $this->getAdapter();
		$sql="SELECT COUNT(p.`co_id`) AS total
		FROM `ln_staff` AS p 
		WHERE p.`status` =1 ";
		return $db->fetchOne($sql);
	}
	
	function CountAllSale(){
		$db = $this->getAdapter();
		$sql="SELECT COUNT(p.`id`) AS total,SUM(p.`price_sold`) AS totalAmount
			FROM `ln_sale` AS p 
			WHERE p.`status` =1 ";
		return $db->fetchRow($sql);
	}
	
	function CountCompletedSale(){
		$db = $this->getAdapter();
		$sql="SELECT COUNT(p.`id`) AS total,SUM(p.`price_sold`) AS totalAmount
			FROM `ln_sale` AS p 
			WHERE p.`status` =1 AND p.is_completed =1";
		return $db->fetchRow($sql);
	}
	
	function CountCanceledSale(){
		$db = $this->getAdapter();
		$sql="SELECT COUNT(p.`id`) AS total
		FROM `ln_sale` AS p
		WHERE p.`status` =1 AND p.is_cancel =1";
		return $db->fetchOne($sql);
	}
	
	function TotalExpense($cancelProperty=null){
		$db = $this->getAdapter();
		$sql="SELECT SUM(p.`total_amount`) AS totalAmount
			FROM `ln_expense` AS p 
			WHERE p.`status` =1 ";
		if (!empty($cancelProperty)){
			$sql.=" AND p.`category_id`=4";
		}
		return $db->fetchOne($sql);
	}
	function getTotalOtherIncome(){
		$db = $this->getAdapter();
		$sql="SELECT SUM(total_amount) AS total
		FROM ln_income AS i
		WHERE i.`status`=1 ";
		return $db->fetchOne($sql);
	}
	
	function getTotalSaleIncome(){
		$db = $this->getAdapter();
		$sql="SELECT  SUM(v.`amount_recieve`) AS total FROM v_getcollectmoney AS v WHERE v.`status`=1";
		return $db->fetchOne($sql);
	}
}