<?php

class Home_Model_DbTable_DbRequestFront extends Zend_Db_Table_Abstract
{

public function getAllRequestPO($search){
    	$db= $this->getAdapter();
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$tr=Application_Form_FrmLanguages::getCurrentlanguage();
    	$sql="
			SELECT 
				rq.*,
				(SELECT p.project_name FROM `ln_project` AS p WHERE p.br_id = rq.projectId LIMIT 1) AS branch_name,
				
				CASE
					WHEN  rq.checkingStatus= 0 THEN '".$tr->translate("PENDING")."'
					WHEN  rq.checkingStatus = 1 THEN '".$tr->translate("APPROVED")."'
					WHEN  rq.checkingStatus = 2 THEN '".$tr->translate("REJECTED")."'
				END AS checkingStatusTitle,
				(SELECT  CONCAT(COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.checkingBy LIMIT 1 ) AS checkingByName,
				
				CASE
					WHEN  rq.pCheckingStatus= 0 THEN '".$tr->translate("PENDING")."'
					WHEN  rq.pCheckingStatus = 1 THEN '".$tr->translate("APPROVED")."'
					WHEN  rq.pCheckingStatus = 2 THEN '".$tr->translate("REJECTED")."'
				END AS pCheckingStatusTitle,
				(SELECT  CONCAT(COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.pCheckingBy LIMIT 1 ) AS pCheckingByName,
				
				CASE
					WHEN  rq.approveStatus= 0 THEN '".$tr->translate("PENDING")."'
					WHEN  rq.approveStatus = 1 THEN '".$tr->translate("APPROVED")."'
					WHEN  rq.approveStatus = 2 THEN '".$tr->translate("REJECTED")."'
				END AS approveStatusTitle,
				(SELECT  CONCAT(COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.approveBy LIMIT 1 ) AS approveByName,
				
				
				(SELECT  u.first_name FROM rms_users AS u WHERE u.id=rq.userId LIMIT 1 ) AS requestByName,
				(SELECT rqd.isCompletedPO FROM `st_request_po_detail` AS rqd WHERE rqd.requestId =rq.id AND rqd.approvedStatus=1 ORDER BY rqd.isCompletedPO ASC LIMIT 1 ) AS isCompletedPO
				
				,(SELECT GROUP_CONCAT(po.purchaseNo) FROM `st_purchasing` AS po WHERE po.requestId=rq.id AND po.status=1 ) AS purchaseNoList
				,(SELECT GROUP_CONCAT(po.id) FROM `st_purchasing` AS po WHERE po.requestId=rq.id AND po.status=1 ) AS purchaseIdList
				,(SELECT GROUP_CONCAT(DATE_FORMAT(po.date,'".DATE_FORMAT_FOR_SQL."')) FROM `st_purchasing` AS po WHERE po.requestId=rq.id AND po.status=1 ) AS purchaseDateList
				,(SELECT GROUP_CONCAT(rst.dnNumber) FROM `st_receive_stock` AS rst WHERE rst.requestId=rq.id AND rst.status=1 ) AS dnNumberList
				,(SELECT GROUP_CONCAT(rst.id) FROM `st_receive_stock` AS rst WHERE rst.requestId=rq.id AND rst.status=1 ) AS dnIdList
				,(SELECT GROUP_CONCAT(DATE_FORMAT(rst.receiveDate,'".DATE_FORMAT_FOR_SQL."')) FROM `st_receive_stock` AS rst WHERE rst.requestId=rq.id AND rst.status=1 ) AS receiveDateList
		";
		
		$dbGbSt = new Application_Model_DbTable_DbGlobalStock();
		$arrStep = array(
			'stepNum'=>"rq.processingStatus",
			'typeStep'=>3,
		);
		$sql.= $dbGbSt->requestingProccess($arrStep);
		$sql.=" ,(SELECT CASE
					WHEN COALESCE((SELECT sp.id FROM `st_purchasing` AS sp WHERE  sp.status = 1 AND sp.requestId =rqd.requestId LIMIT 1),0) = 0 
					THEN '".$tr->translate("NOT_YET_PO")."'
					WHEN  rqd.isCompletedPO = 1 THEN '".$tr->translate("COMPLETED_PO")."'
					ELSE   '".$tr->translate("UPCOMPLETED_PO")."'
					END 
				FROM `st_request_po_detail` AS rqd WHERE rqd.requestId =rq.id AND rqd.approvedStatus=1 ORDER BY rqd.isCompletedPO ASC LIMIT 1 ) AS isCompletedPOTitle  ";
				
		$sql.=" FROM `st_request_po` AS rq WHERE 1 ";
		
    	$where = "";
		$where.= " AND rq.status = 1 ";
		$from_date =(empty($search['start_date']))? '1': " rq.date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " rq.date <= '".$search['end_date']." 23:59:59'";
    	$where.= " AND ".$from_date." AND ".$to_date;
    	if(!empty($search['adv_search'])){
    		$s_where=array();
    		$s_search=addslashes(trim($search['adv_search']));
    		$s_where[]= " rq.requestNo LIKE '%{$s_search}%'";
    		$s_where[]= " rq.requestNoLetter LIKE '%{$s_search}%'";
    		$s_where[]= " rq.purpose LIKE '%{$s_search}%'";
    		$s_where[]= " rq.note LIKE '%{$s_search}%'";
    		$s_where[]= " rq.checkingNote LIKE '%{$s_search}%'";
    		$s_where[]= " rq.pCheckingNote LIKE '%{$s_search}%'";
    		$s_where[]= " rq.approveNote LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
		if(!empty($search['checkingStatus'])){
    		$where.= " AND rq.checkingStatus = ".$search['checkingStatus'];
    	}
		if(!empty($search['pCheckingStatus'])){
    		$where.= " AND rq.pCheckingStatus = ".$search['pCheckingStatus'];
    	}
		if(!empty($search['approveStatus'])){
    		$where.= " AND rq.approveStatus = ".$search['approveStatus'];
    	}
		if(!empty($search['processingStatus'])){
    		$where.= " AND rq.processingStatus = ".$search['processingStatus'];
    	}
    	if(($search['branch_id'])>0){
    		$where.= " AND rq.projectId = ".$search['branch_id'];
    	}
		if($search['reqPOStatus']>-1 AND $search['reqPOStatus']!=''){
    		$where.= " AND (SELECT rqd.isCompletedPO FROM `st_request_po_detail` AS rqd WHERE rqd.requestId =rq.id AND rqd.approvedStatus=1 ORDER BY rqd.isCompletedPO ASC LIMIT 1 )= ".$search['reqPOStatus'];
    	}
		$where.=$dbGb->getAccessPermission("rq.projectId");
    	$order=" ORDER BY rq.id DESC";
    	return $db->fetchAll($sql.$where.$order);
    }
}