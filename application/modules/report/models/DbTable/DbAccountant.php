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
	function getAllPurchasingSumByType($search){
    	$db = $this->getAdapter();
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$dbGbSt = new Application_Model_DbTable_DbGlobalStock();
		$sql="
			SELECT 
				po.*,
				
				COUNT(po.id) AS amountRow,
				SUM(po.total) AS totalAmount
				
		";
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
		$where.= " AND po.purchaseType = ".$search['rowPurchaseType'];
    	$order=' ORDER BY po.id DESC  ';
    	$where.=$dbGb->getAccessPermission("po.projectId");
    	return $db->fetchRow($sql.$where.$order);
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
    		$s_where[] = " pt.accNameAndChequeNo LIKE '%{$s_search}%'";
    		$s_where[] = " spp.supplierName LIKE '%{$s_search}%'";
    		
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	
    	if(($search['branch_id'])>0){
    		$where.= " AND reCh.projectId = ".$search['branch_id'];
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
		if(!empty($search['statusWithdraw'])){
			if($search['statusWithdraw']==2){$search['statusWithdraw']=0;}
    		$where.= " AND reCh.statusWithdraw = ".$search['statusWithdraw'];
    	}
    	$order=' ORDER BY reCh.id DESC  ';
    	$where.=$dbGb->getAccessPermission("reCh.projectId");
    	return $db->fetchAll($sql.$where.$order);
    }
	
	
	 function getPaymentInvoiceById($paymentId){
    	$db = $this->getAdapter();
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$dbGBstock = new Application_Model_DbTable_DbGlobalStock();
		$sql="
			SELECT 
				pt.*,
				(SELECT p.project_name FROM `ln_project` AS p WHERE p.br_id = pt.projectId LIMIT 1) AS branch_name,
				spp.supplierName,
				spp.contactNumber,
				spp.contactName,
				spp.address,
				spp.supplierTel,
				(SELECT vi.name_kh FROM `ln_view` AS vi WHERE vi.type=2 AND vi.key_code=pt.`paymentMethod` LIMIT 1) AS paymentMethodTitle,
				(SELECT ba.bank_name FROM `st_bank` AS ba WHERE ba.id=pt.`bankId` LIMIT 1) AS bankName
			";
		$sql.=",(SELECT  CONCAT(COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=pt.userId LIMIT 1 ) AS byUser";
		$sql.=" FROM `st_payment` AS pt
					LEFT JOIN `st_supplier` AS spp ON spp.id = pt.supplierId 
				WHERE pt.id = $paymentId
		";
		
    	$sql.= "";
    	
    	
    	$sql.=$dbGb->getAccessPermission("pt.projectId");
    	return $db->fetchRow($sql);
    }
	function getPaymentDetail($paymentId){
		$db = $this->getAdapter();
    	$sql="SELECT pd.*
					,po.purchaseNo 
					,po.date AS purchaseDate 
					
					,rq.requestNo 
					,rq.requestNoLetter 
					,rq.purpose AS requestPurpose 		
					,inv.invoiceNo AS invoiceNo 		
					,inv.invoiceDate AS invoiceDate 		
					,inv.supplierInvoiceNo AS supplierInvoiceNo 		
					,inv.receiveIvDate AS receiveIvDate
					,inv.totalAmountExternal AS totalAmountExternal 
					
			FROM `st_payment_detail` AS pd 
				LEFT JOIN `st_invoice` AS inv ON inv.id = pd.invoiceId
				LEFT JOIN st_purchasing AS po ON po.id = inv.purId 
				LEFT JOIN st_request_po AS rq ON rq.id = po.requestId 
			WHERE pd.paymentId =$paymentId ";
		return $db->fetchAll($sql);
	}
	
	function getAllInvoice($search){
    	$db = $this->getAdapter();
		$dbGb = new Application_Model_DbTable_DbGlobal();
		
		$dbGBstock = new Application_Model_DbTable_DbGlobalStock();
		
		$sql="
			SELECT 
				inv.*,
				(SELECT p.project_name FROM `ln_project` AS p WHERE p.br_id = inv.projectId LIMIT 1) AS branch_name,
				po.purchaseNo,
				po.date AS purchaseDate,
				spp.supplierName
				,
				po.requestId,
				rq.requestNo,
				rq.purpose AS purposeRequest,
				rq.requestNoLetter  AS requestNoLetter ,
				rq.date AS requestDate,
				rq.note AS requestNote,
				(SELECT  COALESCE(SUM(pmd.paymentAmount),0) FROM st_payment_detail AS pmd,st_payment AS pm WHERE pm.id=pmd.paymentId AND pm.status=1 AND pmd.invoiceId=inv.id LIMIT 1 ) AS totalPaid,
				
				(SELECT  CONCAT(COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.checkingBy LIMIT 1 ) AS checkingByName,
				(SELECT  CONCAT(COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.pCheckingBy LIMIT 1 ) AS pCheckingByName,
				(SELECT  CONCAT(COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.approveBy LIMIT 1 ) AS approveByName,
				(SELECT  CONCAT(COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.userId LIMIT 1 ) AS requestName
			";
		
		$arrStep = array(
			'keyIndex'=>"inv.ivType",
			'typeKeyIndex'=>3,
		);
		$sql.= $dbGBstock->invoiceTypeKey($arrStep);
		
		$sql.=",(SELECT  CONCAT(COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=inv.userId LIMIT 1 ) AS byUser";
		$sql.=" FROM `st_invoice` AS inv 
					JOIN `st_purchasing` AS po ON po.id = inv.purId 
					LEFT JOIN `st_supplier` AS spp ON spp.id = inv.supplierId 
					LEFT JOIN `st_request_po` AS rq ON rq.id = po.requestId 
				WHERE inv.status=1
		";
		
    	$where = "";
    	$from_date =(empty($search['start_date']))? '1': " inv.receiveIvDate >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " inv.receiveIvDate <= '".$search['end_date']." 23:59:59'";
    	
    	$where.= " AND ".$from_date." AND ".$to_date;
    	
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = (trim($search['adv_search']));
    		$s_where[] = " inv.invoiceNo LIKE '%{$s_search}%'";
    		$s_where[] = " inv.supplierInvoiceNo LIKE '%{$s_search}%'";
    		$s_where[] = " po.purchaseNo LIKE '%{$s_search}%'";
    		$s_where[] = " spp.supplierName LIKE '%{$s_search}%'";
    		$s_where[] = " po.purpose LIKE '%{$s_search}%'";
			
    		$s_where[] = " inv.totalInternal LIKE '%{$s_search}%'";
    		$s_where[] = " inv.vatInternal LIKE '%{$s_search}%'";
			
    		$s_where[] = " inv.totalAmount LIKE '%{$s_search}%'";
    		$s_where[] = " inv.vatExternal LIKE '%{$s_search}%'";
    		$s_where[] = " inv.otherFeeExternal LIKE '%{$s_search}%'";
			
    		$s_where[] = " inv.totalExternal LIKE '%{$s_search}%'";
    		$s_where[] = " inv.totalAmountExternal LIKE '%{$s_search}%'";
			
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	
    	if(($search['branch_id'])>0){
    		$where.= " AND inv.projectId = ".$search['branch_id'];
    	}
		if(!empty($search['supplierId'])){
    		$where.= " AND inv.supplierId = ".$search['supplierId'];
    	}
		if(!empty($search['invoiceType'])){
    		$where.= " AND inv.ivType = ".$search['invoiceType'];
    	}
		if(!empty($search['isPaidStatus'])){
			if($search['isPaidStatus']==1){
				$where.= " AND inv.isPaid =0 AND inv.totalAmountExternalAfter >= inv.totalAmountExternal ";
			}else if($search['isPaidStatus']==2){
				$where.= " AND inv.isPaid =1 ";
			}else if($search['isPaidStatus']==3){
				$where.= " AND inv.isPaid =0 AND inv.totalAmountExternalAfter < inv.totalAmountExternal ";
			}
    		
    	}
    	$order=' ORDER BY inv.id DESC  ';
    	$where.=$dbGb->getAccessPermission("inv.projectId");
		
    	return $db->fetchAll($sql.$where.$order);
    }
	function getAllInvoiceSumByType($search){
    	$db = $this->getAdapter();
		$dbGb = new Application_Model_DbTable_DbGlobal();
		
		$dbGBstock = new Application_Model_DbTable_DbGlobalStock();
		
		$sql="
			SELECT 
				COUNT(inv.id) AS amountRow,
				SUM(inv.totalAmountExternal) AS totalAmount,
				SUM((SELECT  COALESCE(SUM(pmd.paymentAmount),0) FROM st_payment_detail AS pmd,st_payment AS pm WHERE pm.id=pmd.paymentId AND pm.status=1 AND pmd.invoiceId=inv.id LIMIT 1 )) AS totalPaid
			";
		$sql.=" FROM `st_invoice` AS inv 
					JOIN `st_purchasing` AS po ON po.id = inv.purId 
					LEFT JOIN `st_supplier` AS spp ON spp.id = inv.supplierId 
					LEFT JOIN `st_request_po` AS rq ON rq.id = po.requestId 
				WHERE inv.status=1
		";
		
    	$where = "";
    	$from_date =(empty($search['start_date']))? '1': " inv.receiveIvDate >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " inv.receiveIvDate <= '".$search['end_date']." 23:59:59'";
    	
    	$where.= " AND ".$from_date." AND ".$to_date;
    	
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = (trim($search['adv_search']));
    		$s_where[] = " inv.invoiceNo LIKE '%{$s_search}%'";
    		$s_where[] = " inv.supplierInvoiceNo LIKE '%{$s_search}%'";
    		$s_where[] = " po.purchaseNo LIKE '%{$s_search}%'";
    		$s_where[] = " spp.supplierName LIKE '%{$s_search}%'";
    		$s_where[] = " po.purpose LIKE '%{$s_search}%'";
			
    		$s_where[] = " inv.totalInternal LIKE '%{$s_search}%'";
    		$s_where[] = " inv.vatInternal LIKE '%{$s_search}%'";
			
    		$s_where[] = " inv.totalAmount LIKE '%{$s_search}%'";
    		$s_where[] = " inv.vatExternal LIKE '%{$s_search}%'";
    		$s_where[] = " inv.otherFeeExternal LIKE '%{$s_search}%'";
			
    		$s_where[] = " inv.totalExternal LIKE '%{$s_search}%'";
    		$s_where[] = " inv.totalAmountExternal LIKE '%{$s_search}%'";
			
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	
    	if(($search['branch_id'])>0){
    		$where.= " AND inv.projectId = ".$search['branch_id'];
    	}
		if(!empty($search['supplierId'])){
    		$where.= " AND inv.supplierId = ".$search['supplierId'];
    	}
		if(!empty($search['invoiceType'])){
    		$where.= " AND inv.ivType = ".$search['invoiceType'];
    	}
		$where.= " AND inv.ivType = ".$search['rowInvoiceType'];
    	$order=' ORDER BY inv.id DESC  ';
    	$where.=$dbGb->getAccessPermission("inv.projectId");
    	return $db->fetchRow($sql.$where.$order);
    }
	
	
	function getDataRowInvoice($recordId){
    	$db = $this->getAdapter();
		$dbGb = new Application_Model_DbTable_DbGlobal();		
			$this->_name='st_invoice';
			$sql=" SELECT inv.*  ";
			$sql.="
				,(SELECT p.project_name FROM `ln_project` AS p WHERE p.br_id = inv.projectId LIMIT 1) AS branch_name,
				spp.supplierName,
				spp.address,
				spp.supplierTel,
				spp.contactName,
				spp.contactNumber,
				po.requestId,
				po.date AS purchaseDate,
				po.purchaseNo,
				po.date AS purchaseDate,
				rq.requestNo,
				rq.purpose AS purposeRequest,
				rq.requestNoLetter  AS requestNoLetter ,
				rq.date AS requestDate,
				rq.note AS requestNote,
				(SELECT  CONCAT(COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.checkingBy LIMIT 1 ) AS checkingByName,
				(SELECT  CONCAT(COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.pCheckingBy LIMIT 1 ) AS pCheckingByName,
				(SELECT  CONCAT(COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.approveBy LIMIT 1 ) AS approveByName,
				(SELECT  CONCAT(COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.userId LIMIT 1 ) AS requestName,
				(SELECT  CONCAT(COALESCE(u.last_name,''),' ',COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=inv.userId LIMIT 1 ) AS byUser
			";
			$sql.=" 
				FROM $this->_name AS inv 
					JOIN `st_purchasing` AS po ON po.id = inv.purId 
					LEFT JOIN `st_supplier` AS spp ON spp.id = inv.supplierId 
					LEFT JOIN `st_request_po` AS rq ON rq.id = po.requestId 
			";
			$sql.=" WHERE inv.id= ".$recordId;
			$sql.=$dbGb->getAccessPermission("inv.projectId");
			$sql.=" LIMIT 1 ";
    	return $db->fetchRow($sql);
    }
	function getInvoiceDetailById($data){
		$recordId = empty($data['id'])?0:$data['id'];
		$isService = empty($data['isService'])?0:$data['isService'];
		$db = $this->getAdapter();
		$sql=" 	SELECT 
					invd.*,p.proCode,
					p.proName,
					p.isService AS serviceOrProType,
					(SELECT pl.qty FROM st_product_location AS pl WHERE pl.proId=p.proId AND pl.projectId= inv.projectId LIMIT 1) AS currentQty,
					p.measureLabel AS measureTitle
					";
			
		$sql.="		FROM 
					`st_invoice_detail` as invd
					JOIN `st_invoice` AS inv ON inv.id = invd.invId
					LEFT JOIN `st_product` AS p  ON p.proId = invd.proId 
			";
		$sql.=" WHERE invd.invId = $recordId";
		if(empty($data['getAllRecord'])){
			$sql.=" AND invd.type = $isService ";
		}
		
		return $db->fetchAll($sql);
	}
	function getDNByListOfInvoice($dnList){
		$db = $this->getAdapter();
		$sql="SELECT GROUP_CONCAT(rst.dnNumber)	AS DNNumberList
			FROM st_receive_stock AS rst 
			 WHERE rst.status=1 
			AND rst.id  IN ($dnList) LIMIT 1";
		return $db->fetchRow($sql);
	}
	
	function getInvoicePaymentHistory($invoiceId){
		$db = $this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		
		$sql="SELECT pmd.* 
				,pm.paymentNo 
				,pm.paymentDate 
				,pm.accNameAndChequeNo 
				,(SELECT vi.name_kh FROM `ln_view` AS vi WHERE vi.type=2 AND vi.key_code=pm.`paymentMethod` LIMIT 1) AS paymentMethodTitle
				,(SELECT ba.bank_name FROM `st_bank` AS ba WHERE ba.id=pm.`bankId` LIMIT 1) AS bankName			
				,CASE 
					WHEN pmd.remain=0 THEN '".$tr->translate("COMPLETED_PAID")."'
					WHEN pmd.remain<0 THEN '".$tr->translate("COMPLETED_PAID")."'
					WHEN pmd.remain>0 THEN '".$tr->translate("SOME_PAID")."'
					ELSE '".$tr->translate("NOT_YET_PAID")."'
				END AS paymentStatus
				,CASE 
					WHEN pm.status=0 THEN '".$tr->translate("VOID")."'
					ELSE ''
				END AS statusTitle
			FROM `st_payment_detail` AS pmd 
				JOIN `st_payment` AS pm ON pm.id = pmd.paymentId 
			 AND pmd.invoiceId =$invoiceId 
			 ORDER BY pmd.id DESC
			";
		return $db->fetchAll($sql);
	}

}
