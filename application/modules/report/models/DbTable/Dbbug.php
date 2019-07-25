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
			(SELECT SUM(`principal_permonth`) FROM `ln_saleschedule` WHERE (ln_saleschedule.sale_id=s.id AND status=1) GROUP BY ln_saleschedule.sale_id LIMIT 1) AS sold_schedule,
			COALESCE((SELECT SUM(`principal_permonth`) FROM `ln_saleschedule` WHERE ln_saleschedule.sale_id=s.id AND ln_saleschedule.collect_by=2 AND ln_saleschedule.status=0 GROUP BY ln_saleschedule.sale_id LIMIT 1),0) AS extra_principal
      	 FROM `ln_sale`  AS s WHERE s.status =1 AND s.is_cancel=0 AND s.payment_id!=1 AND s.payment_id!=2  ";
      	 //New codiction make query fastest than before
      	 $sql.=" AND s.price_sold != (SELECT SUM(COALESCE(`principal_permonth`,0)) FROM `ln_saleschedule` WHERE ln_saleschedule.sale_id=s.id  GROUP BY ln_saleschedule.sale_id LIMIT 1)";
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
	     (SELECT SUM(`cr`.`total_principal_permonthpaid`+`cr`.`extra_payment`) FROM `ln_client_receipt_money` `cr` WHERE `cr`.`sale_id` = `s`.`id` AND status=1 LIMIT 1) AS `paid_amount`,
	     (SELECT `cr`.`id` FROM `ln_client_receipt_money` `cr` WHERE (`cr`.`sale_id` = `s`.`id`) ORDER BY `cr`.`id` DESC LIMIT 1) AS `crm_id`,	     
	     (SELECT SUM(ss.principal_permonth) FROM `ln_saleschedule` AS ss WHERE ss.sale_id= `s`.`id` AND  is_completed=1 AND status=1 LIMIT 1) AS realpaid,
	     (SELECT SUM(`principal_permonth`) FROM `ln_saleschedule` WHERE ln_saleschedule.sale_id=s.id AND ln_saleschedule.collect_by=2 AND ln_saleschedule.status=0 LIMIT 1) AS extra_principal,
	     (SELECT SUM(ss.principal_permonth-principal_permonthafter) FROM `ln_saleschedule` AS ss WHERE ss.sale_id= `s`.`id` AND ss.status=1 AND principal_permonth!=principal_permonthafter LIMIT 1) AS principal_remain,
	     (SELECT (ss.principal_permonth-ss.principal_permonthafter) FROM `ln_saleschedule` AS ss WHERE ss.sale_id= `s`.`id` AND is_completed=0 AND STATUS=1 order by ss.no_installment ASC  LIMIT 1  ) AS printcipal_permonthlast,
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
			      	 s.id= ss.sale_id
			      	 AND s.status =1 
			      	 AND s.is_cancel=0 
			      	 AND s.payment_id!=1 
			      	 AND s.payment_id!=2
			      	 AND ss.principal_permonth<=0 
      				 AND ss.total_payment_after=0 
      				 AND ss.is_completed=0 
      				GROUP BY s.id  ";
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
      (SELECT ss.begining_balance FROM `ln_saleschedule` AS ss WHERE ss.sale_id= `s`.`id` AND is_completed=0 AND STATUS=1 order by ss.no_installment ASC  LIMIT 1  ) AS begining_balance,
      (SELECT (ss.principal_permonth-ss.principal_permonthafter) FROM `ln_saleschedule` AS ss WHERE ss.sale_id= `s`.`id` AND is_completed=0 AND status=1 AND ss.principal_permonthafter>0 order by ss.no_installment ASC  LIMIT 1  ) AS printcipal_permonthlast
      FROM 
      		`ln_sale` AS s WHERE is_completed=0 
      		AND status=1 
      		AND is_cancel=0 
      		AND s.payment_id!=1 
      		AND s.payment_id!=2 ";
      	//New codiction make query fastest than before
      	$sql.="
      	AND ((s.price_sold - (SELECT SUM(`cr`.`total_principal_permonthpaid`+`cr`.`extra_payment`) FROM `ln_client_receipt_money` `cr` WHERE (`cr`.`sale_id` = `s`.`id`))) - ( COALESCE((SELECT ss.begining_balance FROM `ln_saleschedule` AS ss WHERE ss.sale_id= `s`.`id` AND is_completed=0 AND STATUS=1 ORDER BY ss.no_installment ASC  LIMIT 1  ),0) - COALESCE((SELECT (COALESCE(ss.principal_permonth,0)-COALESCE(ss.principal_permonthafter,0)) FROM `ln_saleschedule` AS ss WHERE ss.sale_id= `s`.`id` AND is_completed=0 AND STATUS=1 AND ss.principal_permonthafter>0 ORDER BY ss.no_installment ASC  LIMIT 1  ),0) ) ) !=0
      	";
      	return $db->fetchAll($sql);
      }
      
      
 }

