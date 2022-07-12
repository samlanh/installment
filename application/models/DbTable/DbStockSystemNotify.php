<?php

class Application_Model_DbTable_DbStockSystemNotify extends Zend_Db_Table_Abstract
{
	
	function getNotifyRequest($_data=array()){
		$db=$this->getAdapter();
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$tr=Application_Form_FrmLanguages::getCurrentlanguage();
    	$sql="
			SELECT 
				rq.*,
				CASE
					WHEN  rq.checkingStatus= 0 THEN '".$tr->translate("PENDING")."'
					WHEN  rq.checkingStatus = 1 THEN '".$tr->translate("APPROVED")."'
					WHEN  rq.checkingStatus = 2 THEN '".$tr->translate("REJECTED")."'
				END AS checkingStatusTitle,
				CASE
					WHEN  rq.pCheckingStatus= 0 THEN '".$tr->translate("PENDING")."'
					WHEN  rq.pCheckingStatus = 1 THEN '".$tr->translate("APPROVED")."'
					WHEN  rq.pCheckingStatus = 2 THEN '".$tr->translate("REJECTED")."'
				END AS pCheckingStatusTitle,
				CASE
					WHEN  rq.approveStatus= 0 THEN '".$tr->translate("PENDING")."'
					WHEN  rq.approveStatus = 1 THEN '".$tr->translate("APPROVED")."'
					WHEN  rq.approveStatus = 2 THEN '".$tr->translate("REJECTED")."'
				END AS approveStatusTitle,
				(SELECT p.project_name FROM `ln_project` AS p WHERE p.br_id = rq.projectId LIMIT 1) AS branch_name,
				(SELECT  CONCAT(COALESCE(u.last_name,''),' ',COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.checkingBy LIMIT 1 ) AS checkingByName,
				(SELECT  CONCAT(COALESCE(u.last_name,''),' ',COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.pCheckingBy LIMIT 1 ) AS pCheckingByName,
				(SELECT  CONCAT(COALESCE(u.last_name,''),' ',COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.approveBy LIMIT 1 ) AS approveByName,
				(SELECT  CONCAT(COALESCE(u.last_name,''),' ',COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.userId LIMIT 1 ) AS user_name	
		";
		
		$dbGbSt = new Application_Model_DbTable_DbGlobalStock();
		$arrStep = array(
			'stepNum'=>"rq.processingStatus",
			'typeStep'=>3,
		);
		$sql.= $dbGbSt->requestingProccess($arrStep);
		
		$sql.=" FROM `st_request_po` AS rq WHERE rq.status=1 ";
	/*
		if(!empty($_data['forWarehouse'])){
			$sql.=" AND rq.processingStatus=0 ";
		}
		if(!empty($_data['forPurchaseDept'])){
			$sql.=" AND rq.processingStatus=1 AND rq.checkingStatus=1 ";
		}
		if(!empty($_data['forApproved'])){
			$sql.=" AND rq.processingStatus=2 AND rq.pCheckingStatus=1 ";
		}
	*/
		
		$dbUser = new Application_Model_DbTable_DbUsers();
		$processingStatus=null;
		$rs = $dbUser->getAccessUrl("requesting","checkingrequest","add");
		if(!empty($rs)){
			if(is_null($processingStatus)){
				$processingStatus =0; //forWarehouse
			}
			
		}
		$rs = $dbUser->getAccessUrl("requesting","pcheckingrequest","add");
		if(!empty($rs)){
			//forPurchaseDept
			if(is_null($processingStatus)){
				$processingStatus =1; 
			}else{
				$processingStatus =$processingStatus.",1";
			}
			$sql.=" AND rq.checkingStatus!=2 ";
		}
		$rs = $dbUser->getAccessUrl("requesting","approvedrequest","add");
		if(!empty($rs)){
			//forApproved
			if(is_null($processingStatus)){
				$processingStatus =2; 
			}else{
				$processingStatus =$processingStatus.",2";
			}
			$sql.=" AND rq.pCheckingStatus!=2 ";
		}
		
		if(is_null($processingStatus)){
			return array();
		}else{
			$sql.=" AND rq.processingStatus IN ($processingStatus) ";
		}
	
		/*
		if(!empty($_data['forPurchaseDept'])){
			$sql.=" AND rq.checkingStatus=1 AND rq.pCheckingStatus=0 ";
		}
		if(!empty($_data['forApproved'])){
			$sql.=" AND rq.pCheckingStatus=1 AND rq.approveStatus=0 ";
		}
		*/
		$sql.=$dbGb->getAccessPermission("rq.projectId");
    	$sql.=" ORDER BY rq.id DESC";
		//echo $sql;exit();
    	return $db->fetchAll($sql);
	}
	
	
}
?>