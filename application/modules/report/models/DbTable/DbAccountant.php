<?php
class Report_Model_DbTable_DbAccountant extends Zend_Db_Table_Abstract
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
    		$s_where[] = " po.note LIKE '%{$s_search}%'";
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
				rq.requestNo,
				rq.purpose AS purposeRequest,
				rq.requestNoLetter  AS requestNoLetter ,
				rq.date AS requestDate,
				rq.note AS requestNote,
				(SELECT  CONCAT(COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.checkingBy LIMIT 1 ) AS checkingByName,
				(SELECT  CONCAT(COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.pCheckingBy LIMIT 1 ) AS pCheckingByName,
				(SELECT  CONCAT(COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.approveBy LIMIT 1 ) AS approveByName,
				(SELECT  CONCAT(COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.userId LIMIT 1 ) AS requestName,
				(SELECT  CONCAT(COALESCE(u.last_name,''),' ',COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=po.userId LIMIT 1 ) AS byUser
			FROM $this->_name AS po JOIN `st_supplier` AS spp ON spp.id = po.supplierId 
					LEFT JOIN st_request_po AS rq ON rq.id =po.requestId 
			WHERE po.id=".$recordId;
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
	
	function getAllPayment($search){
    	$db = $this->getAdapter();
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$dbGBstock = new Application_Model_DbTable_DbGlobalStock();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$sql="
			SELECT 
				pt.*,
				(SELECT p.project_name FROM `ln_project` AS p WHERE p.br_id = pt.projectId LIMIT 1) AS branch_name,
				spp.supplierName,
				spp.address 		AS supplierAddress,
				spp.supplierTel 	AS supplierTel,
				spp.contactName 	AS supplierContactName,
				spp.contactNumber 	AS supplierContactNumber,
				(SELECT vi.name_kh FROM `ln_view` AS vi WHERE vi.type=2 AND vi.key_code=pt.`paymentMethod` LIMIT 1) AS paymentMethodTitle,
				(SELECT ba.bank_name FROM `st_bank` AS ba WHERE ba.id=pt.`bankId` LIMIT 1) AS bankName,
				(SELECT GROUP_CONCAT((SELECT inv.invoiceNo FROM `st_invoice` AS inv WHERE inv.id = pd.invoiceId LIMIT 1)) FROM `st_payment_detail` AS pd WHERE pd.paymentId =pt.id) AS invoiceNoList,
				(SELECT GROUP_CONCAT((SELECT inv.supplierInvoiceNo FROM `st_invoice` AS inv WHERE inv.id = pd.invoiceId LIMIT 1)) FROM `st_payment_detail` AS pd WHERE pd.paymentId =pt.id) AS supplierInvoiceNoList,
				CASE
					WHEN  pt.status = 1 THEN ''
					WHEN  pt.status = 0 THEN '".$tr->translate("VOID")."'
					END AS statusTitle,
				CASE
					WHEN  pt.isClosed = 1 THEN '".$tr->translate("CLOSED")."'
					WHEN  pt.isClosed = 0 THEN '".$tr->translate("UNCLOSE")."'
					END AS isClosedTitle
			";
		$sql.=",(SELECT  CONCAT(COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=pt.userId LIMIT 1 ) AS byUser";
		$sql.=" FROM `st_payment` AS pt
					LEFT JOIN `st_supplier` AS spp ON spp.id = pt.supplierId 
				WHERE 1 
		";
		
    	$where = "";
    	$from_date =(empty($search['start_date']))? '1': " pt.paymentDate >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " pt.paymentDate <= '".$search['end_date']." 23:59:59'";
    	
    	$where.= " AND ".$from_date." AND ".$to_date;
    	
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = (trim($search['adv_search']));
    		$s_where[] = " pt.paymentNo LIKE '%{$s_search}%'";
    		$s_where[] = " pt.accNameAndChequeNo LIKE '%{$s_search}%'";
    		$s_where[] = " pt.purchaseNo LIKE '%{$s_search}%'";
    		$s_where[] = " pt.note LIKE '%{$s_search}%'";
    		$s_where[] = " spp.totalAmount LIKE '%{$s_search}%'";
			
    		$s_where[] = " (SELECT ba.bank_name FROM `st_bank` AS ba WHERE ba.id=pt.`bankId` LIMIT 1) LIKE '%{$s_search}%'";
    		$s_where[] = " (SELECT vi.name_kh FROM `ln_view` AS vi WHERE vi.type=2 AND vi.key_code=pt.`paymentMethod` LIMIT 1) LIKE '%{$s_search}%'";
			
			$s_where[] = " (SELECT GROUP_CONCAT((SELECT inv.invoiceNo FROM `st_invoice` AS inv WHERE inv.id = pd.invoiceId LIMIT 1)) FROM `st_payment_detail` AS pd WHERE pd.paymentId =pt.id) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT GROUP_CONCAT((SELECT inv.supplierInvoiceNo FROM `st_invoice` AS inv WHERE inv.id = pd.invoiceId LIMIT 1)) FROM `st_payment_detail` AS pd WHERE pd.paymentId =pt.id) LIKE '%{$s_search}%'";
			
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if($search['statusAcc']>-1){
    		$where.= " AND pt.status = ".$search['statusAcc'];
    	}
    	if(($search['branch_id'])>0){
    		$where.= " AND pt.projectId = ".$search['branch_id'];
    	}
		if(!empty($search['supplierId'])){
    		$where.= " AND pt.supplierId = ".$search['supplierId'];
    	}
		if(!empty($search['paymentMethod'])){
    		$where.= " AND pt.paymentMethod = ".$search['paymentMethod'];
    	}
		if(!empty($search['bankId'])){
    		$where.= " AND pt.bankId = ".$search['bankId'];
    	}
		if($search['closingStatus']>-1){
    		$where.= " AND pt.isClosed = ".$search['closingStatus'];
    	}
    	$order=' ORDER BY pt.id DESC  ';
    	$where.=$dbGb->getAccessPermission("pt.projectId");
    	return $db->fetchAll($sql.$where.$order);
    }
	
	 function submitClosingPayment($data){
      	$db = $this->getAdapter();
      	if(!empty($data['id_selected'])){
      		$ids = explode(',', $data['id_selected']);
      		$key = 1;
      		$arr = array(
      				"isClosed"=>1,
      		);
      		foreach ($ids as $i){
      			$this->_name="st_payment";
      			//if (!empty($data['note_'.$i])){
      				//$arr['closing_note']=$data['note_'.$i];
      			//}
      			$where="id= ".$data['id_'.$i];
      			$this->update($arr, $where);
      		}
      	}
      }
	  
	 function getAllIssueChequePayment($search){
    	$db = $this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$dbGBstock = new Application_Model_DbTable_DbGlobalStock();
		
		$sql="
			SELECT 
				reCh.*,
				(SELECT p.project_name FROM `ln_project` AS p WHERE p.br_id = reCh.projectId LIMIT 1) AS branch_name,
				pt.paymentNo,
				pt.paymentDate,
				pt.accNameAndChequeNo ,
				(SELECT vi.name_kh FROM `ln_view` AS vi WHERE vi.type=2 AND vi.key_code=pt.`paymentMethod` LIMIT 1) AS paymentMethodTitle,
				(SELECT ba.bank_name FROM `st_bank` AS ba WHERE ba.id=pt.`bankId` LIMIT 1) AS bankName,
				(SELECT GROUP_CONCAT((SELECT inv.invoiceNo FROM `st_invoice` AS inv WHERE inv.id = pd.invoiceId LIMIT 1)) FROM `st_payment_detail` AS pd WHERE pd.paymentId =pt.id) AS invoiceNoList,
				(SELECT GROUP_CONCAT((SELECT inv.supplierInvoiceNo FROM `st_invoice` AS inv WHERE inv.id = pd.invoiceId LIMIT 1)) FROM `st_payment_detail` AS pd WHERE pd.paymentId =pt.id) AS supplierInvoiceNoList,
				spp.supplierName
				,CASE
					WHEN  reCh.statusWithdraw = 0 THEN '".$tr->translate("NOT_YET_WITHDRAW")."'
					WHEN  reCh.statusWithdraw= 1 THEN '".$tr->translate("WITHDRAWN")."'
					END AS statusWithdrawTitle 
				
			";
		$sql.=",(SELECT  CONCAT(COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=reCh.userId LIMIT 1 ) AS byUser";
		$sql.=",(SELECT  CONCAT(COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=reCh.drawUserId LIMIT 1 ) AS drawUserIdUser";
		$sql.=" FROM `st_receive_cheque` AS reCh
					LEFT JOIN `st_payment` AS pt ON pt.id = reCh.paymentId 
					LEFT JOIN `st_supplier` AS spp ON spp.id = pt.supplierId 
				WHERE reCh.status=1  
		";
		
    	$where = "";
    	$from_date =(empty($search['start_date']))? '1': " reCh.receiveDate >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " reCh.receiveDate <= '".$search['end_date']." 23:59:59'";
    	
    	$where.= " AND ".$from_date." AND ".$to_date;
    	
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = (trim($search['adv_search']));
    		$s_where[] = " reCh.receiverName LIKE '%{$s_search}%'";
    		$s_where[] = " pt.paymentNo LIKE '%{$s_search}%'";
    		$s_where[] = " spp.supplierName LIKE '%{$s_search}%'";
    		
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	
    	if(($search['branch_id'])>0){
    		$where.= " AND reCh.projectId = ".$search['branch_id'];
    	}
		if(!empty($search['supplierId'])){
    		$where.= " AND pt.supplierId = ".$search['supplierId'];
    	}
		if(!empty($search['statusWithdraw'])){
    		$where.= " AND reCh.statusWithdraw = ".$search['statusWithdraw'];
    	}
    	$order=' ORDER BY reCh.id DESC  ';
    	$where.=$dbGb->getAccessPermission("reCh.projectId");
    	return $db->fetchAll($sql.$where.$order);
    }
}