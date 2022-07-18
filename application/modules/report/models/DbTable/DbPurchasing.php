<?php
class Report_Model_DbTable_DbPurchasing extends Zend_Db_Table_Abstract
{
	
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
	
	function getPurchasingById($recordId){
    	$db = $this->getAdapter();
		$dbGb = new Application_Model_DbTable_DbGlobal();		
			$this->_name='st_purchasing';
			$sql=" 
			SELECT po.*,
			(SELECT p.project_name FROM `ln_project` AS p WHERE p.br_id = po.projectId LIMIT 1) AS branch_name,
				spp.supplierName,
				spp.address,
				spp.supplierTel,
				spp.contactName,
				spp.contactNumber,
				(SELECT  CONCAT(COALESCE(u.last_name,''),' ',COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=po.userId LIMIT 1 ) AS byUser
			FROM $this->_name AS po JOIN `st_supplier` AS spp ON spp.id = po.supplierId WHERE po.id=".$recordId;
			$sql.=$dbGb->getAccessPermission("po.projectId");
			$sql.=" LIMIT 1 ";
    	return $db->fetchRow($sql);
    }
	function getPODetailById($recordId){
		$db = $this->getAdapter();
		$sql=" 	SELECT 
					pod.*,p.proCode,
					p.proName,
					(SELECT c.categoryName FROM st_category AS c WHERE c.id = p.categoryId LIMIT 1 ) AS categoryTitle,
					(SELECT pl.qty FROM st_product_location AS pl WHERE pl.proId=p.proId AND pl.projectId= po.projectId LIMIT 1) AS currentQty,
					p.measureLabel AS measureTitle
					";
		$sql.="
				,
				rqd.isCompletedPO,
				rqd.dateReqStockIn,
				rqd.note AS requestItemsNote,
				rqd.qtyApproved AS qtyApproved,
				(COALESCE(pod.qty,0)+COALESCE(rqd.qtyApprovedAfter,0)) AS qtyApprovedAfter
			";
			
		$sql.="		FROM 
					`st_purchasing_detail` as pod
					JOIN `st_purchasing` AS po ON po.id = pod.purchaseId
					LEFT JOIN `st_product` AS p  ON p.proId = pod.proId 
					LEFT JOIN `st_request_po_detail` AS rqd  ON rqd.proId = pod.proId AND rqd.requestId=po.requestId
			";
		
			
		$sql.=" WHERE pod.purchaseId = $recordId";
		return $db->fetchAll($sql);
	}
}
