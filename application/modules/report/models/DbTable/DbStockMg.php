<?php
class Report_Model_DbTable_DbStockMg extends Zend_Db_Table_Abstract
{
	public function getAllRequestPOList($search){
    	$db= $this->getAdapter();
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$dbGbSt = new Application_Model_DbTable_DbGlobalStock();
		$tr=Application_Form_FrmLanguages::getCurrentlanguage();
		
    	$sql="
			SELECT 
				rq.*,
				CASE
					WHEN  rq.checkingStatus= 0 THEN '".$tr->translate("PENDING")."'
					WHEN  rq.checkingStatus = 1 THEN '".$tr->translate("APPROVED")."'
					WHEN  rq.checkingStatus = 2 THEN '".$tr->translate("REJECTED")."'
				END AS checkingStatus,
				CASE
					WHEN  rq.pCheckingStatus= 0 THEN '".$tr->translate("PENDING")."'
					WHEN  rq.pCheckingStatus = 1 THEN '".$tr->translate("APPROVED")."'
					WHEN  rq.pCheckingStatus = 2 THEN '".$tr->translate("REJECTED")."'
				END AS pCheckingStatus,
				CASE
					WHEN  rq.approveStatus= 0 THEN '".$tr->translate("PENDING")."'
					WHEN  rq.approveStatus = 1 THEN '".$tr->translate("APPROVED")."'
					WHEN  rq.approveStatus = 2 THEN '".$tr->translate("REJECTED")."'
				END AS approveStatus,
				(SELECT p.project_name FROM `ln_project` AS p WHERE p.br_id = rq.projectId LIMIT 1) AS branch_name,
				(SELECT  CONCAT(COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.checkingBy LIMIT 1 ) AS checkingByName,
				(SELECT  CONCAT(COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.pCheckingBy LIMIT 1 ) AS pCheckingByName,
				(SELECT  CONCAT(COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.approveBy LIMIT 1 ) AS approveByName,
				(SELECT  CONCAT(COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.userId LIMIT 1 ) AS user_name
				
		";
		
		$arrStep = array(
			'stepNum'=>"rq.processingStatus",
			'typeStep'=>3,
		);
		$sql.= $dbGbSt->requestingProccess($arrStep);
		$sql.=" FROM `st_request_po` AS rq WHERE rq.status=1 ";
		
    	$where = "";
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
		
    	if(($search['branch_id'])>0){
    		$where.= " AND rq.projectId = ".$search['branch_id'];
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
		$where.=$dbGb->getAccessPermission("rq.projectId");
    	$order=" ORDER BY rq.id DESC";
    	return $db->fetchAll($sql.$where.$order);
    }
	function getRequestPOById($id=null){
		$db = $this->getAdapter();
		$sql=" 
			SELECT rq.*,
				(SELECT p.project_name FROM `ln_project` AS p WHERE p.br_id = rq.projectId LIMIT 1) AS branch_name,
				(SELECT  CONCAT(COALESCE(u.last_name,''),' ',COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.checkingBy LIMIT 1 ) AS checkingByName,
				(SELECT  CONCAT(COALESCE(u.last_name,''),' ',COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.pCheckingBy LIMIT 1 ) AS pCheckingByName,
				(SELECT  CONCAT(COALESCE(u.last_name,''),' ',COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.approveBy LIMIT 1 ) AS approveByName,
				(SELECT  CONCAT(COALESCE(u.last_name,''),' ',COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.userId LIMIT 1 ) AS user_name

			FROM st_request_po AS rq WHERE 1 ";
		$sql.=" AND rq.status = 1 ";
		if (!empty($id)){
			$sql.=" AND rq.id = $id LIMIT 1";
		}
		return $db->fetchRow($sql);
	}
	
	function getRequestPODetailById($rsData=null){
		$db = $this->getAdapter();
		
		$id=empty($rsData['id'])?0:$rsData['id'];
		$sql=" 	SELECT 
					rqd.*
					,p.proCode
					,p.proName
					,(SELECT c.categoryName FROM st_category AS c WHERE c.id = p.categoryId LIMIT 1 ) AS categoryTitle
					,(SELECT pl.qty FROM st_product_location AS pl WHERE pl.proId=p.proId AND pl.projectId= rq.projectId LIMIT 1) AS currentQty
					,p.measureLabel AS measureTitle
					
					,(SELECT SUM(pod1.qty) FROM st_purchasing AS po,st_purchasing_detail AS pod1 WHERE pod1.purchaseId=po.id AND pod1.proId = rqd.proId AND rqd.requestId=po.requestId  AND  po.status =1 ) AS purchaseQty
					,(SELECT GROUP_CONCAT(po.purchaseNo) FROM st_purchasing AS po,st_purchasing_detail AS pod1 WHERE pod1.purchaseId=po.id AND pod1.proId = rqd.proId AND rqd.requestId=po.requestId  AND  po.status =1 ) AS purchaseNoList
					,(SELECT GROUP_CONCAT(DATE_FORMAT(po.date,'".DATE_FORMAT_FOR_SQL."')) FROM st_purchasing AS po,st_purchasing_detail AS pod1 WHERE pod1.purchaseId=po.id AND pod1.proId = rqd.proId AND rqd.requestId=po.requestId  AND  po.status =1 ) AS purchaseDateList
					,(SELECT GROUP_CONCAT((SELECT spp.supplierName FROM st_supplier AS spp WHERE spp.id = po.supplierId LIMIT 1 )) FROM st_purchasing AS po,st_purchasing_detail AS pod1 WHERE pod1.purchaseId=po.id AND pod1.proId = rqd.proId AND rqd.requestId=po.requestId  AND  po.status =1 ) AS supplierNameList
					
					,(SELECT SUM(rsd1.qtyReceive) FROM st_receive_stock AS rs,st_receive_stock_detail AS rsd1 WHERE rsd1.receiveId=rs.id AND rsd1.proId = rqd.proId AND rqd.requestId=rs.requestId AND rs.status =1 ) AS totalReceiveQty
					,(SELECT GROUP_CONCAT(rs.dnNumber) FROM st_receive_stock AS rs,st_receive_stock_detail AS rsd1 WHERE rsd1.receiveId=rs.id AND rsd1.proId = rqd.proId AND rqd.requestId=rs.requestId AND rs.status =1 ) AS dnNumberList
					,(SELECT GROUP_CONCAT(DATE_FORMAT(rs.receiveDate,'".DATE_FORMAT_FOR_SQL."')) FROM st_receive_stock AS rs,st_receive_stock_detail AS rsd1 WHERE rsd1.receiveId=rs.id AND rsd1.proId = rqd.proId AND rqd.requestId=rs.requestId AND rs.status =1 ) AS dnReceiveDateList
				";
				
		$sql.="		FROM 
					`st_request_po_detail` as rqd
					JOIN `st_request_po` AS rq ON rq.id = rqd.requestId
					LEFT JOIN `st_product` AS p  ON p.proId = rqd.proId 
			";
		$sql.="WHERE 1 AND rqd.requestId = $id ";//AND rqd.approvedStatus=1 
		if (!empty($rsData['pCheckingRequest']) OR !empty($rsData['approvedrequest'])){
			$sql.=" AND rqd.adjustStatus = 1 ";
		}
			
		return $db->fetchAll($sql);
	}
	
	function getAllPurchasing($search){
    	$db = $this->getAdapter();
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$dbGbSt = new Application_Model_DbTable_DbGlobalStock();
		$sql="
			SELECT 
				po.*,
				(SELECT p.project_name FROM `ln_project` AS p WHERE p.br_id = po.projectId LIMIT 1) AS branch_name,
				spp.supplierName,
				rq.requestNo,
				rq.purpose AS purposeRequest,
				rq.requestNoLetter  AS requestNoLetter ,
				rq.date AS requestDate,
				rq.note AS requestNote,
				(SELECT  CONCAT(COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.checkingBy LIMIT 1 ) AS checkingByName,
				(SELECT  CONCAT(COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.pCheckingBy LIMIT 1 ) AS pCheckingByName,
				(SELECT  CONCAT(COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.approveBy LIMIT 1 ) AS approveByName,
				(SELECT  CONCAT(COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.userId LIMIT 1 ) AS requestName
		";
		$sql.=",(SELECT  CONCAT(COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=po.userId LIMIT 1 ) AS byUser";
		
		$arrStep = array(
			'keyIndex'=>"po.purchaseType",
			'typeKeyIndex'=>3,
		);
		$sql.= $dbGbSt->purchasingTypeKey($arrStep);
		
		$sql.=" FROM `st_purchasing` AS po 
					JOIN `st_supplier` AS spp ON spp.id = po.supplierId 
					LEFT JOIN st_request_po AS rq ON rq.id =po.requestId 
				WHERE 
					1 
		";
    	$where = "";
    	$from_date =(empty($search['start_date']))? '1': " po.date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " po.date <= '".$search['end_date']." 23:59:59'";
    	
    	$where.= " AND ".$from_date." AND ".$to_date;
    	$where.= " AND po.status = 1 ";
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = (trim($search['adv_search']));
    		$s_where[] = " po.purchaseNo LIKE '%{$s_search}%'";
    		$s_where[] = " po.purpose LIKE '%{$s_search}%'";
    		$s_where[] = " spp.supplierName LIKE '%{$s_search}%'";
    		$s_where[] = " rq.requestNo LIKE '%{$s_search}%'";
    		$s_where[] = " rq.purpose LIKE '%{$s_search}%'";
    		$s_where[] = " po.total LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	
    	if(($search['branch_id'])>0){
    		$where.= " AND po.projectId = ".$search['branch_id'];
    	}
		if(!empty($search['supplierId'])){
    		$where.= " AND po.supplierId = ".$search['supplierId'];
    	}
		if(!empty($search['purchaseType'])){
    		$where.= " AND po.purchaseType = ".$search['purchaseType'];
    	}
    	$order=' ORDER BY po.id DESC  ';
    	$where.=$dbGb->getAccessPermission("po.projectId");
    	return $db->fetchAll($sql.$where.$order);
    }
}
