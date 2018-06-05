<?php
class Report_Model_DbTable_Dbbug extends Zend_Db_Table_Abstract
{
      public function getRealDatescheduleandSoldprice($search = null){//ផ្ទៀងផ្ទាត់ រវាងប្រាក់លក់និង ប្រាក់ដើមក្នុងតារាងត្រូវបង់ប្រាក់សរុប
      	 $db = $this->getAdapter();
      	 $sql = " SELECT s.id,
				(SELECT name_kh FROM `ln_client` WHERE client_id=s.client_id LIMIT 1 )AS client_name,
				(SELECT  CONCAT(`land_address`,',',`street`) FROM `ln_properties` WHERE ln_properties.id=s.house_id  LIMIT 1 )AS land_name,
				s.house_id,
				s.price_sold,
				(SELECT SUM(`principal_permonth`) FROM `ln_saleschedule` WHERE ln_saleschedule.sale_id=s.id) AS
				 sold_schedule
      	 FROM `ln_sale`  AS s WHERE s.status =1 AND s.is_cancel=0 AND s.payment_id!=1 AND s.payment_id!=2";
      	 return $db->fetchAll($sql);
      }
      function getRealPaid(){//ប្រាក់បានបង់ និងប្រាក់ដែលដកក្នុងតារាង
      	$db = $this->getAdapter();
      	$sql="
      	SELECT s.id,
		  (SELECT name_kh FROM `ln_client` WHERE client_id=s.client_id LIMIT 1 )AS client_name,
		  s.payment_id,
		  s.client_id,s.price_sold,s.house_id,
	     (SELECT  CONCAT(`land_address`,',',`street`) FROM `ln_properties` WHERE ln_properties.id=s.house_id  LIMIT 1 )AS land_name,
	     (SELECT SUM(`cr`.`total_principal_permonthpaid`+`cr`.`extra_payment`) FROM `ln_client_receipt_money` `cr` WHERE (`cr`.`sale_id` = `s`.`id`) LIMIT 1) AS `paid_amount`,
	     (SELECT `cr`.`id` FROM `ln_client_receipt_money` `cr` WHERE (`cr`.`sale_id` = `s`.`id`) ORDER BY `cr`.`id` DESC LIMIT 1) AS `crm_id`,	     
	     (SELECT SUM(ss.principal_permonth) FROM `ln_saleschedule` AS ss WHERE ss.sale_id= `s`.`id` AND  is_completed=1  LIMIT 1) AS realpaid,
	     (SELECT SUM(ss.principal_permonth-principal_permonthafter) FROM `ln_saleschedule` AS ss WHERE ss.sale_id= `s`.`id` AND ss.status=1 AND principal_permonth!=principal_permonthafter LIMIT 1) AS principal_remain,
	     (SELECT COUNT(ss.id) FROM `ln_saleschedule` AS ss WHERE ss.sale_id= `s`.`id` AND is_completed=1 AND STATUS=1 LIMIT 1) AS countpaid
	  	FROM `ln_sale` AS s WHERE is_completed=0 AND STATUS=1 AND is_cancel=0 AND s.payment_id!=1 AND s.payment_id!=2 ";
      	return $db->fetchAll($sql);
      }
      function getScheduleCompletednotUpdate(){
      	$db = $this->getAdapter();
      	$sql="SELECT s.id,
				(SELECT name_kh FROM `ln_client` WHERE client_id=s.client_id LIMIT 1 )AS client_name,
				(SELECT  CONCAT(`land_address`,',',`street`) FROM `ln_properties` WHERE ln_properties.id=s.house_id  LIMIT 1 )AS land_name,
				s.house_id,
				ss.id AS schedule_id,
				s.price_sold
			      	 FROM 
			      	 `ln_sale` AS s,`ln_saleschedule` AS ss 
			      	 WHERE 
			      	 s.status =1 
			      	 AND s.is_cancel=0 
			      	 AND s.payment_id!=1 
			      	 AND s.payment_id!=2
			      	 AND ss.principal_permonth<=0 
      				 AND ss.total_payment_after=0 
      				 AND ss.is_completed=0 
      				group by ss.id";
      	return $db->fetchAll($sql);
      }
      function getBeginingBalance(){
      	$db = $this->getAdapter();
      	$sql="SELECT s.id,
      (SELECT name_kh FROM `ln_client` WHERE client_id=s.client_id LIMIT 1 )AS client_name,
      s.payment_id,
      s.client_id,s.price_sold,s.house_id,
      (SELECT  CONCAT(`land_address`,',',`street`) FROM `ln_properties` WHERE ln_properties.id=s.house_id  LIMIT 1 )AS land_name,
      (SELECT SUM(`cr`.`total_principal_permonthpaid`+`cr`.`extra_payment`) FROM `ln_client_receipt_money` `cr` WHERE (`cr`.`sale_id` = `s`.`id`)) AS `paid_amount`,
      (SELECT (ss.id) FROM `ln_saleschedule` AS ss WHERE ss.sale_id= `s`.`id` AND is_completed=1 AND STATUS=1 LIMIT 1) AS fund_id,
      (SELECT ss.begining_balance FROM `ln_saleschedule` AS ss WHERE ss.sale_id= `s`.`id` AND is_completed=0 AND STATUS=1 order by ss.no_installment ASC  LIMIT 1  ) AS begining_balance
      FROM `ln_sale` AS s WHERE is_completed=0 AND STATUS=1 AND is_cancel=0 AND s.payment_id!=1 AND s.payment_id!=2 ";
      	return $db->fetchAll($sql);
      }
      
      
 }

