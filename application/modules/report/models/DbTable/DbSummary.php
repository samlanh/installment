<?php
class Report_Model_DbTable_DbSummary extends Zend_Db_Table_Abstract
{
      
      function getAllSummaryData($data){
      	$resultProperty = $this->getPropertySummary($data);
      	$resultSale = $this->getSaleSummary($data);
      	$resultProject = $this->getProjectData($data);
      	$resultPayoff = $this->countPayoff($data);
      	
      	
      	$resultSummary = array(
      				'totalProProject'=>$resultProject['totalProject'],
      				'totalAgent'=>$resultProject['totalAgent'],
      				'totalOtherIncome'=>$resultProject['totalOtherIncome'],
      				'totalInformalIncome'=>$resultProject['totalInformalIncome'],
      				'totalCommision'=>$resultProject['totalCommision']+$resultProject['totalCommisionPayment'],
      				'totalExpense'=>$resultProject['totalExpense']+$resultProject['totalExpensebyStep'],
      			
	      			'receivedHouse'=>$resultProject['receivedHouse'],
	      			'receivedPlong'=>$resultProject['receivedPlong'],
      			
      				'totalpropertyPrice'=>$resultProperty['totalpropertyPrice'],
      				'totalpropertyPriceUnsold'=>$resultProperty['totalpropertyPriceUnsold'],
      				'totalpropertyPricesold'=>$resultProperty['totalpropertyPricesold'],
      				'totalProperties'=>$resultProperty['totalProperties'],
      				'propertySold'=>$resultProperty['propertySold'],
      				'propertyUnSold'=>$resultProperty['propertyUnSold'],
      			
      				'0BuildPercentage'=>$resultProperty['0BuildPercentage'],
      				'10BuildPercentage'=>$resultProperty['10BuildPercentage'],
      				'20BuildPercentage'=>$resultProperty['20BuildPercentage'],
      				'30BuildPercentage'=>$resultProperty['30BuildPercentage'],
      				'40BuildPercentage'=>$resultProperty['40BuildPercentage'],
      				'50BuildPercentage'=>$resultProperty['50BuildPercentage'],
      				'60BuildPercentage'=>$resultProperty['60BuildPercentage'],
      				'70BuildPercentage'=>$resultProperty['70BuildPercentage'],
      				'80BuildPercentage'=>$resultProperty['80BuildPercentage'],
      				'90BuildPercentage'=>$resultProperty['90BuildPercentage'],
      				'100BuildPercentage'=>$resultProperty['100BuildPercentage'],
      			
      				'totalClient'=>$resultSale['totalClient'],
      				'totalActiveSale'=>$resultSale['totalActiveSale'],
      				'totalActiveHouseSale'=>$resultSale['totalActiveHouseSale'],
      				'totalBooking'=>$resultSale['totalBooking'],
      				'totalSalePrice'=>$resultSale['totalSalePrice'],
      				'fullComission'=>$resultSale['fullComission'],
      				'paidAmount'=>$resultSale['paidAmount'],
      				'totalExpectedInterest'=>$resultSale['totalExpectedInterest'],
      				'totalSaleLate'=>$resultSale['totalSaleLate'],
      				'cancelPaidAmount'=>$resultSale['cancelPaidAmount'],
      				'totalInterestPaid'=>$resultSale['totalInterestPaid'],
      				'totalCancelHouse'=>$resultSale['totalCancelHouse'],
      				'totalLatePayment'=>$resultSale['totalLatePayment'],
      			
      				'salePayoff'=>$resultPayoff['salePayoff'],
      				'payoffAmount'=>$resultPayoff['payoffAmount'],
      			
      			);
      	return $resultSummary;
      }
      public function getProjectData($data){
      	$db = $this->getAdapter();
      	$sql="  SELECT
			      	COUNT(`br_id`) AS totalProject,
			      	(SELECT COUNT(co_id) FROM `ln_staff` WHERE `status`=1) totalAgent,
			      	(SELECT SUM(total_amount) FROM `ln_income` WHERE `status`=1 ) AS totalOtherIncome,
			      	(SELECT SUM(total_paid) FROM `ln_otherincomepayment` WHERE `status`=1) AS totalInformalIncome,
			      	(SELECT SUM(total_amount) FROM `ln_comission` WHERE `status`=1) AS totalCommision,
			      	(SELECT SUM(total_paid) FROM `rms_commission_payment` WHERE `status`=1) AS totalCommisionPayment,
			      	(SELECT SUM(total_amount) FROM `ln_expense` WHERE `status`=1) totalExpense,
			      	(SELECT SUM(total_paid) FROM `rms_expense_payment` WHERE `status`=1) totalExpensebyStep,
			      	(SELECT COUNT(id) FROM `ln_issue_house` WHERE `status`=1) receivedHouse,
					(SELECT COUNT(id) FROM `ln_receiveplong` WHERE `status`=1) receivedPlong
      		FROM `ln_project` WHERE `status`=1 ";
     	if(!empty($data['projectId'])){
      		$sql.=" AND br_id=".$data['projectId'];
      	}
      	return $db->fetchRow($sql);
      }
      
      function getPropertySummary($data){
      	$sql="SELECT 
				SUM(IF(`status` = '1',price, NULL)) AS totalpropertyPrice,
				SUM(IF(`status` = '1' AND is_lock = '0',price, NULL)) AS totalpropertyPriceUnsold,
				SUM(IF(`status` = '1' AND is_lock = '1',price, NULL)) AS totalpropertyPricesold,
				COUNT(IF(`status`= '1',id, NULL)) AS totalProperties,
				COUNT(IF(`status` = '1' AND is_lock='1',id, NULL)) AS propertySold,
				COUNT(IF(`status` = '1' AND is_lock='0',id, NULL)) AS propertyUnSold,
				
				COUNT(IF(`buildPercentage` = '0',id, NULL)) AS 0BuildPercentage,
				COUNT(IF(`buildPercentage` = '10',id, NULL)) AS 10BuildPercentage,
				COUNT(IF(`buildPercentage` = '20',id, NULL)) AS 20BuildPercentage,
				COUNT(IF(`buildPercentage` = '30',id, NULL)) AS 30BuildPercentage,
				COUNT(IF(`buildPercentage` = '40',id, NULL)) AS 40BuildPercentage,
				COUNT(IF(`buildPercentage` = '50',id, NULL)) AS 50BuildPercentage,
				COUNT(IF(`buildPercentage` = '60',id, NULL)) AS 60BuildPercentage,
				COUNT(IF(`buildPercentage` = '70',id, NULL)) AS 70BuildPercentage,
				COUNT(IF(`buildPercentage` = '80',id, NULL)) AS 80BuildPercentage,
				COUNT(IF(`buildPercentage` = '90',id, NULL)) AS 90BuildPercentage,
				COUNT(IF(`buildPercentage` = '100',id, NULL)) AS 100BuildPercentage

			FROM `ln_properties` WHERE 1";
      	if(!empty($data['projectId'])){
      		$sql.=" AND branch_id=".$data['projectId'];
      	}
      	$db = $this->getAdapter();
      	return $db->fetchRow($sql);
      }
      function getSaleSummary($data){
      	
      	$currentDate = date('Y-m-d');
      	$strBranchId = "";
      	if(!empty($data['projectId'])){
      		$strBranchId=" AND branch_id=".$data['projectId'];
      	}
      	$sql="SELECT 
				COUNT(DISTINCT(IF(`status`= '1' AND is_cancel='0' ,client_id, NULL))) AS totalClient,
				COUNT(IF(`status`= '1' AND is_cancel='0' ,id, NULL)) AS totalActiveSale,
				COUNT(IF(`status`= '1' AND is_cancel='0' ,house_id, NULL)) AS totalActiveHouseSale,/*change to count house*/
				COUNT(IF(`status`= '1' AND is_cancel='0' AND is_reschedule='0' ,id, NULL)) AS totalBooking,
				SUM(IF(`status` = '1' AND is_cancel = '0',price_sold, NULL)) AS totalSalePrice,
				SUM(IF(`status` = '1' AND is_cancel = '0',full_commission, NULL)) AS fullComission,
				(SELECT SUM(total_interest_after) FROM `ln_saleschedule` ss WHERE ss.status=1 AND ss.is_completed=0 AND ss.total_interest_after>0) AS totalExpectedInterest,
				
				(SELECT COUNT(DISTINCT(ss.sale_id)) FROM `ln_saleschedule` ss WHERE ss.status=1 AND ss.is_completed=0 AND ss.total_interest_after>0 LIMIT 1) AS totalSaleLate,
				(SELECT SUM(total_payment_after) FROM `ln_saleschedule` ss WHERE ss.status=1 AND ss.is_completed=0 AND ss.total_payment_after>0 AND date_payment<='$currentDate' ) AS totalLatePayment,
				
				(SELECT SUM(total_principal_permonthpaid+extra_payment) FROM `ln_client_receipt_money` crm WHERE s.status=1 AND s.is_cancel=0 $strBranchId LIMIT 1) AS paidAmount,
				(SELECT SUM(total_principal_permonthpaid+extra_payment) FROM `ln_client_receipt_money` crm WHERE s.status=1 AND s.is_cancel=1 $strBranchId LIMIT 1) AS cancelPaidAmount,
				(SELECT SUM(total_interest_permonthpaid+penalize_amountpaid) FROM `ln_client_receipt_money` WHERE s.status=1 AND s.is_cancel=1 $strBranchId LIMIT 1) AS totalInterestPaid,
				COUNT(IF(`status`= '1' AND is_cancel='1' ,house_id, NULL)) AS totalCancelHouse
			FROM `ln_sale` s WHERE  `status`= 1 ";
      	if(!empty($data['projectId'])){
      		$sql.=" AND branch_id=".$data['projectId'];
      	}
      	$db = $this->getAdapter();
      	return $db->fetchRow($sql);
      }
      function countPayoff($data){
      	$sql="SELECT 
					COUNT(sale_id) AS salePayoff,
					SUM(payoffAmount) AS payoffAmount
				FROM  
				(SELECT 
					(s.id) AS sale_id,
					s.price_sold,
					SUM(c.total_principal_permonthpaid+extra_payment) AS payoffAmount
				 FROM ln_sale s,
			 		ln_client_receipt_money c
			WHERE 
				s.id=c.`sale_id`
				AND s.status=1 AND s.is_cancel=0 ";
      	if(!empty($data['projectId'])){
      		$sql.=" AND s.branch_id=".$data['projectId'];
      	}
      	$sql.=" GROUP BY s.id
				HAVING s.price_sold<=SUM(c.total_principal_permonthpaid+extra_payment)) AS main_table";
      	$db = $this->getAdapter();
      	return $db->fetchRow($sql);
      }
 }