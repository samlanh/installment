<?php

class Report_Model_DbTable_DbStockReports extends Zend_Db_Table_Abstract
{
    protected $_name = 'st_stockout';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getAllUsageStock($search){
    	$DATE_FORMAT = DATE_FORMAT_FOR_SQL;
    	$sql="SELECT id,
				(SELECT project_name FROM `ln_project` WHERE br_id=so.projectId LIMIT 1) AS projectName,
				so.requestNo,
				so.reqOutNo,
				DATE_FORMAT(so.requestDate,'$DATE_FORMAT') as requestDate,
				(SELECT w.staffName FROM `st_worker` w where w.id=so.staffId LIMIT 1) as staffName,
				(SELECT c.staffName FROM `st_contractor` c where c.id=so.contractor LIMIT 1) as contractor,
				so.workerName,
				(SELECT pt.type_nameen FROM `ln_properties_type` pt where pt.id=so.houseType LIMIT 1) as houseType,
				(SELECT p.land_address FROM `ln_properties` p where p.id=so.houseId LIMIT 1) as houseNo,
				(SELECT w.workTitle FROM `st_work_type` w where w.id=so.workType LIMIT 1) workType,
				so.typeofWork,
				(SELECT first_name FROM rms_users WHERE id=so.userId LIMIT 1 ) AS user_name,
				(SELECT name_en FROM ln_view WHERE type=3 and key_code = so.status LIMIT 1) AS status
								
			FROM `st_stockout` as so WHERE so.tranType=1 ";
    	
    	$from_date =(empty($search['start_date']))? '1': " so.createDate >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " so.createDate <= '".$search['end_date']." 23:59:59'";
    	
    	$where_date = " AND ".$from_date." AND ".$to_date;
    	$where='';
    	
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = (trim($search['adv_search']));
    		$s_where[] = " so.requestNo LIKE '%{$s_search}%'";
    		$s_where[] = " so.reqOutNo LIKE '%{$s_search}%'";
    		$s_where[] = " so.workerName LIKE '%{$s_search}%'";
    		$s_where[] = " so.typeofWork LIKE '%{$s_search}%'";
    		$s_where[] = " (SELECT p.id FROM `ln_properties` p WHERE p.id=so.houseId AND p.land_address LIKE '%{$s_search}%' LIMIT 1)";
    		
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if($search['branch_id']>-1){
    		$where.= " AND so.projectId = ".$search['branch_id'];
    	}
    	if($search['workType']>0){
    		$where.= " AND so.workType = ".$search['workType'];
    	}
    	if(!empty($search['propertyType'])){
    		$where.= " AND so.houseType = ".$search['propertyType'];
    	}
    	if($search['contractor']>0){
    		$where.= " AND so.contractor = ".$search['contractor'];
    	}
    	if($search['staffWithdraw']>0){
    		$where.= " AND so.staffId = ".$search['staffWithdraw'];
    	}
    	if($search['status']>-1){
    		$where.= " AND so.status = ".$search['status'];
    	}
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	$where.= $dbg->getAccessPermission('so.projectId');
    	
    	$order=' ORDER BY so.id DESC  ';
    	$db = $this->getAdapter();
    	return $db->fetchAll($sql.$where_date.$where.$order);
    }
   
   
    function getDataRow($recordId){
    	$db = $this->getAdapter();
    	$this->_name='st_stockout';
    	$sql=" SELECT id,so.projectId,
				(SELECT project_name FROM `ln_project` WHERE br_id=so.projectId LIMIT 1) AS projectName,
				so.requestNo,
				so.reqOutNo,
				DATE_FORMAT(so.requestDate,'%d-%m-%Y') AS requestDate,
				(SELECT w.staffName FROM `st_worker` w where w.id=so.staffId LIMIT 1) as staffName,
				(SELECT c.staffName FROM `st_contractor` c where c.id=so.contractor LIMIT 1) as contractor,
				so.workerName,
				(SELECT pt.type_nameen FROM `ln_properties_type` pt where pt.id=so.houseType LIMIT 1) as houseType,
				(SELECT p.land_address FROM `ln_properties` p where p.id=so.houseId LIMIT 1) as houseNo,
				(SELECT w.workTitle FROM `st_work_type` w where w.id=so.workType LIMIT 1) workType,
				so.typeofWork,
				(SELECT first_name FROM rms_users WHERE id=so.userId LIMIT 1 ) AS user_name
				
								
			FROM `st_stockout` as so WHERE so.tranType=1 AND so.id=".$recordId;
    	
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	$sql.= $dbg->getAccessPermission('projectId');
    	
    	$sql.=" LIMIT 1";
    	
    	return $db->fetchRow($sql);
    }
    function getDataAllRow($recordId){
    	$db = $this->getAdapter();
    	$this->_name='st_stockout_detail';
    	$sql="SELECT 
    				sd.*,
    				(SELECT `proCode` FROM `st_product` where st_product.`proId`=sd.proId LIMIT 1) AS proCode,
					(SELECT `proName` FROM `st_product` where st_product.`proId`=sd.proId LIMIT 1) AS proName,
					(SELECT `measureLabel` FROM `st_product` where st_product.`proId`=sd.proId LIMIT 1) AS measureLabel
    		FROM $this->_name as sd WHERE sd.stockoutId=".$recordId." ";
    	return $db->fetchAll($sql);
    }
    function getAllProductLocation($search){
    		if(!isset($search['btn_search'])){
    			return array();
    		}
    		$sql=" SELECT
			    		l.id,
			    		(SELECT project_name from `ln_project` WHERE br_id=l.projectId LIMIT 1) as projectName,
			    		CONCAT(COALESCE(p.proCode,''),' ',COALESCE(p.proName,'')) AS `name`,
			    		p.barCode,
			    		l.qty AS currentQty,
			    		p.measureLabel AS measureTitle,
			    		(SELECT i.budgetTitle FROM `st_budget_item` AS i WHERE i.id=p.budgetId LIMIT 1) budgetTitle,
			    		l.costing,
			    		(SELECT c.categoryName FROM `st_category` as c WHERE c.id=p.categoryId LIMIT 1) categoryName
	    		FROM
	    		`st_product` AS p ,
    			st_product_location AS l
    				WHERE
    				p.proId=l.proId 
    				AND isCountStock=1 ";
    		 
    		$where='';
    		 
    		if(!empty($search['adv_search'])){
    			$s_where = array();
	    		$s_search = (trim($search['adv_search']));
	    		$s_where[] = " p.proName LIKE '%{$s_search}%'";
	    		$s_where[] = " p.proCode LIKE '%{$s_search}%'";
	    		$s_where[] = " p.barCode LIKE '%{$s_search}%'";
	    		$s_where[] = " p.measureLabel LIKE '%{$s_search}%'";
	    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    		}
    		 
//     		if($search['isCountStock']>-1){
//     			$where.= " AND p.isCountStock = ".$search['isCountStock'];
//     		}
    		if($search['categoryId']>0){
    			$where.= " AND p.categoryId = ".$search['categoryId'];
    		}
    		if($search['branch_id']>0){
    			$where.= " AND l.projectId = ".$search['branch_id'];
    		}
    		if($search['measureId']>0){
    			$where.= " AND p.budgetId = ".$search['measureId'];
    		}
    			 
    		$dbg = new Application_Model_DbTable_DbGlobal();
    		$where.= $dbg->getAccessPermission('l.projectId');
    			 
    		$order=' ORDER BY l.id DESC ,p.proName ASC  ';
    			 
    		$db = $this->getAdapter();
    		return $db->fetchAll($sql.$where.$order);
    }
    function getAllReceiveStock($search){
    	$DATE_FORMAT = DATE_FORMAT_FOR_SQL;
    	$sql="SELECT r.id,
	    	(SELECT project_name FROM `ln_project` WHERE br_id=r.projectId LIMIT 1) AS projectName,
	    	(SELECT name_kh FROM `st_view` WHERE type=4 AND key_code=r.dnType LIMIT 1) dnType,
	    	r.dnNumber,
	    	(SELECT name_kh FROM `st_view` WHERE type=5 AND key_code=r.isIssueInvoice LIMIT 1) isIssueInvoice,
	    	r.plateNo,
	    	r.driverName,
	    	r.staffCounter,
	    	DATE_FORMAT(r.receiveDate,'$DATE_FORMAT') As receiveDate,
	    	(SELECT s.supplierName FROM st_supplier s WHERE s.id=r.supplierId LIMIT 1) AS supplierName,
	    	(SELECT purchaseNo FROM `st_purchasing` as p WHERE p.id=r.poId LIMIT 1) AS purchaseNo,
	    	(SELECT DATE_FORMAT(p.date,'$DATE_FORMAT') FROM `st_purchasing` as p WHERE p.id=r.poId LIMIT 1) AS purchaseDate,
	    	(SELECT requestNo FROM `st_request_po` AS s WHERE s.id=r.requestId LIMIT 1) AS requestNo,
	    	(SELECT first_name FROM rms_users WHERE id=r.userId LIMIT 1 ) AS user_name,
	    	(SELECT name_en FROM ln_view WHERE type=3 and key_code = r.status LIMIT 1) AS status
	    
    	FROM `st_receive_stock` r WHERE 1 ";
    	 
    	 
    	$from_date =(empty($search['start_date']))? '1': " r.receiveDate >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " r.receiveDate <= '".$search['end_date']." 23:59:59'";
    	$where='';
    	$where_date = " AND ".$from_date." AND ".$to_date;
    	 
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = addslashes((trim($search['adv_search'])));
    		$s_where[] = " r.dnNumber LIKE '%{$s_search}%'";
    		$s_where[] = " r.driverName LIKE '%{$s_search}%'";
    		$s_where[] = " r.plateNo LIKE '%{$s_search}%'";
    		$s_where[] = " r.staffCounter LIKE '%{$s_search}%'";
    
    		$s_where[] = " (SELECT p.id FROM `st_purchasing` AS p WHERE p.id=r.poId AND purchaseNo LIKE '%{$s_search}%')";
    		$s_where[] = " (SELECT s.id FROM `st_request_po` AS s WHERE s.id=r.requestId AND requestNo LIKE '%{$s_search}%')";
    
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
//     	if($search['status']>-1){
//     		$where.= " AND r.status = ".$search['status'];
//     	}
    	if($search['verifyStatus']>-1){
    		$where.= " AND r.isIssueInvoice = ".$search['verifyStatus'];
    	}
    	if($search['branch_id']>0){
    		$where.= " AND r.projectId = ".$search['branch_id'];
    	}
    	if($search['supplierId']>0){
    		$where.= " AND r.supplierId = ".$search['supplierId'];
    	}
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	$where.= $dbg->getAccessPermission('r.projectId');
    	 
    	$order=' ORDER BY r.id DESC  ';
    	 
    	$db = $this->getAdapter();
    	return $db->fetchAll($sql.$where.$where_date.$order);
    }
    function getAllAdjustStock($search){
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$approved = $tr->translate("APPROVED");
    	$reject =  $tr->translate("REJECTED");
    	
    	$DATE_FORMAT = DATE_FORMAT_FOR_SQL;
    	
    	$sql="SELECT
			    	sa.id,
			    	(SELECT project_name FROM `ln_project` WHERE br_id=sa.projectId LIMIT 1) AS projectName,
			    	DATE_FORMAT(sa.adjustDate,'$DATE_FORMAT') AS adjustDate,
			    	(SELECT first_name FROM rms_users WHERE id=sa.userId LIMIT 1 ) AS user_name,
			    	CASE WHEN sa.isApproved=1 THEN '$approved'
			    	ELSE '$reject'
			    	END AS status,
			    	sa.approvedDate,
			    	(SELECT first_name FROM rms_users WHERE id=sa.approvedBy LIMIT 1) approvedBy,
			    	(SELECT CONCAT(COALESCE(p.proCode,''),' ',COALESCE(p.proName,''))  FROM `st_product` AS p WHERE p.`proId`=ad.proId LIMIT 1) AS proName,
			    	ad.currentQty,
			    	ad.exactQty,
			    	ad.note
    		FROM `st_adjust_stock` sa ,
    			st_adjust_detail AS ad
    		WHERE sa.id=ad.adjustId ";
    	 
    	$from_date =(empty($search['start_date']))? '1': " sa.adjustDate >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " sa.adjustDate <= '".$search['end_date']." 23:59:59'";
    	 
    	$where_date = " AND ".$from_date." AND ".$to_date;
    	$where='';
    	 
    	if($search['branch_id']>-1){
    		$where.= " AND sa.projectId = ".$search['branch_id'];
   		}
   		
	    $dbg = new Application_Model_DbTable_DbGlobal();
	    $where.= $dbg->getAccessPermission('sa.projectId');
	     
	    $order=' ORDER BY sa.id DESC  ';
	    $db = $this->getAdapter();
	    return $db->fetchAll($sql.$where_date.$where.$order);
    }
    function getSummaryStockReport($search){
    	$sql="SELECT 
    			(SELECT project_name FROM `ln_project` WHERE br_id=cl.projectId LIMIT 1) AS projectName,
    			cl.projectId,
		    	cl.closingDate,
		    	cl.toDate,
		    	cd.proId,
		    	cl.adjustId,
		    	(SELECT adjustDate FROM `st_adjust_stock` WHERE st_adjust_stock.id= cl.adjustId LIMIT 1) AS adjustDate,
		    	(SELECT st_product.proCode FROM st_product WHERE st_product.proId=cd.proId LIMIT 1) AS proCode,
		    	(SELECT st_product.proName FROM st_product WHERE st_product.proId=cd.proId LIMIT 1) AS productName,
		    	(SELECT c.categoryName FROM `st_category` c,st_product p WHERE c.id=p.categoryId AND p.proId=cd.proId LIMIT 1) as categoryName,
		    	(SELECT m.name FROM `st_measure` m,st_product p WHERE m.id=p.measureId AND p.proId=cd.proId  LIMIT 1) as measureName,
		    	qtyBegining,
				costing
		    	
    		FROM `st_closing` cl,
				`st_closing_detail` AS cd
    		 WHERE cl.id=cd.closingId ";
    	
//     	$from_date =(empty($search['start_date']))? '1': " cl.closingDate >= '".$search['start_date']." 00:00:00'";
//     	$to_date = (empty($search['end_date']))? '1': " cl.closingDate <= '".$search['end_date']." 23:59:59'";
    	
//     	$where_date = " AND ".$from_date." AND ".$to_date;
    	$where='';
    	
    	if($search['branch_id']>-1){
    		$where.= " AND cl.projectId = ".$search['branch_id'];
    	}
    	if(!empty($search['reportDate'])){
    		$where.= " AND cl.id = ".$search['reportDate'];
    	}
    	if(!empty($search['productId'])){
    		$where.= " AND cd.proId = ".$search['productId'];
    	}
    	 
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	$where.= $dbg->getAccessPermission('cl.projectId');
    	
    	$order=' GROUP BY cl.id DESC, cd.proId ORDER BY cd.id DESC  ';
    	
    	$results =  $this->getAdapter()->fetchAll($sql.$where.$order);
    	$records = array();
    	if(!empty($results)){
    		foreach($results as $key=> $result){
    			$records[$key]['projectName']=$result['projectName'];
    			$records[$key]['closingDate']=$result['closingDate'];
    			$records[$key]['adjustDate']=$result['adjustDate'];
    			$records[$key]['productName']=$result['productName'];
    			$records[$key]['proCode']=$result['proCode'];
    			
    			$records[$key]['categoryName']=$result['categoryName'];
    			$records[$key]['measureName']=$result['measureName'];
    			
    			
    			$records[$key]['qtyBegining']=$result['qtyBegining'];
    			$records[$key]['costing']=$result['costing'];
    			
    			$records[$key]['qtyReceive']=0;
    			$records[$key]['qtyUsage']=0;
    			$records[$key]['qtySale']=0;
    			$records[$key]['qtyAdjust']=0;
    			$records[$key]['qtyTransferOut']=0;
    			$records[$key]['qtyReceivedTransfer']=0;
    			
    			$records[$key]['qtyTransferInpending']=0;
    			
    			$qtyAdjust = $this->getAdjustEntry($result['adjustId'],$result['proId']);
    			if(!empty($qtyAdjust)){
    				$records[$key]['qtyAdjust']=$qtyAdjust;
    			}
    			
    			$param = array(
    					'projectId'=>$result['projectId'],
    					'proId'=>$result['proId'],
    					'start_date'=>$result['closingDate'],
    					'end_date'=>$result['toDate'],//less 1 day
    					);
    			
    			
    			$qtyReceive = $this->getPurchasebyClosingEntry($param);
    			
    			if(!empty($qtyReceive)){
    				$records[$key]['qtyReceive']=$qtyReceive;
    			}
    			
    			$qtyTransfer = $this->getTransferClosingEntry($param);
    			if(!empty($qtyTransfer)){
    				$records[$key]['qtyTransferOut']=$qtyTransfer;
    			}
    			
    			$qtyReceivedTransfer = $this->getReceiveTransferClosingEntry($param);
    			if(!empty($qtyReceivedTransfer)){
    				$records[$key]['qtyReceivedTransfer']=$qtyReceivedTransfer;
    			}
    			
    			$param['toProjectId']=$result['projectId'];
    			$param['tranType']=1;
    			$qtyRequest = $this->getUsageClosingEntry($param);
    			if(!empty($qtyRequest)){
    				$records[$key]['qtyUsage']=$qtyRequest;
    			}
    			
    			$param['tranType']=2;
    			$qtySale = $this->getUsageClosingEntry($param);
    			if(!empty($qtyRequest)){
    				$records[$key]['qtySale']=$qtySale;
    			}
    		
    			$param['toProjectId']=$result['projectId'];
    			$param['column']='qtyAppAfter';
    			
    			//comlumn
    			unset($param['projectId']);
    			
    			
    			$qtyTransferPending = $this->getTransferClosingEntry($param);
    			if(!empty($qtyTransferPending)){
    				$records[$key]['qtyTransferInpending']=$qtyTransferPending;
    			}
    			
    			
    		}
    	}
    	return $records;
    	
    }
    function getPurchasebyClosingEntry($data){
    	$sql=" SELECT
		    		SUM(rd.qtyReceive) AS qtyPurchased
		    	FROM 
			    	st_receive_stock AS rs,
			    	`st_receive_stock_detail` rd
		    	WHERE rs.id=rd.receiveId ";
    	
    	
    	if(!empty($data['start_date'])){
    		$from_date =(empty($data['start_date']))? '1': " rs.receiveDate >= '".$data['start_date']." 00:00:00'";
    		$to_date = (empty($data['end_date']))? '1': " rs.receiveDate < '".$data['end_date']." 00:00:00'";
    		$sql.= " AND ".$from_date." AND ".$to_date;
    	}
    	if(!empty($data['projectId'])){
    		$sql.= " AND rs.projectId=".$data['projectId'];
    	}
    	if(!empty($data['proId'])){
    		$sql.= " AND rd.proId=".$data['proId'];
    	}
    	$sql.=" GROUP BY rd.proId ";
    	
    	return $this->getAdapter()->fetchOne($sql);
    }
    function getUsageClosingEntry($data){//usage and sale
    	$sql=" SELECT
    				SUM(sd.qtyRequest) AS qtyRequest
    		FROM
		    	st_stockout AS s,
		    	`st_stockout_detail` sd
    		WHERE s.id=sd.stockoutId ";
    	
    	
    	if(!empty($data['tranType'])){
    		$sql.= " AND s.tranType=".$data['tranType'];//1usage,2sale,
    	}
    	 
    	if(!empty($data['start_date'])){
    		$from_date =(empty($data['start_date']))? '1': " s.requestDate >= '".$data['start_date']." 00:00:00'";
    		$to_date = (empty($data['end_date']))? '1': " s.requestDate < '".$data['end_date']." 00:00:00'";
    		$sql.= " AND ".$from_date." AND ".$to_date;
    	}
    	
    	if(!empty($data['projectId'])){
    		$sql.= " AND s.projectId=".$data['projectId'];
    	}
    	if(!empty($data['proId'])){
    		$sql.= " AND sd.proId=".$data['proId'];
    	}
    	$sql.=" GROUP BY sd.proId ";
    	 
    	return $this->getAdapter()->fetchOne($sql);
    }
    function getAdjustEntry($adjustId,$proId){//adjust for closing
    	$sql=" SELECT
    		(ad.exactQty-ad.currentQty) AS qtyAdjust
    	FROM
	    	`st_adjust_detail` ad
    	WHERE  ad.id=$adjustId  AND proId=".$proId;
    
    	return $this->getAdapter()->fetchOne($sql);
    }
    
    function getTransferClosingEntry($data){//transfer and receive for closing
    	$column = empty($data['column'])?'qtyApproved':$data['column'];
    	$sql=" SELECT
    				SUM(td.$column) AS qtyTransfer
    		FROM
	    		st_transferstock AS t,
	    		`st_transferstock_detail` td
    		WHERE t.id=td.transferId ";
    
    	if(!empty($data['start_date'])){
    		$from_date =(empty($data['start_date']))? '1': " t.transferDate >= '".$data['start_date']." 00:00:00'";
    		$to_date = (empty($data['end_date']))? '1': " t.transferDate < '".$data['end_date']." 00:00:00'";
    		$sql.= " AND ".$from_date." AND ".$to_date;
    	}
    	 
    	if(!empty($data['projectId'])){
    		$sql.= " AND t.fromProjectId=".$data['projectId'];
    	}
    	if(!empty($data['toProjectId'])){//received
    		$sql.= " AND t.toProjectId=".$data['toProjectId'];
    	}
    	if(!empty($data['proId'])){
    		$sql.= " AND td.proId=".$data['proId'];
    	}
    	$sql.=" GROUP BY td.proId ";
    	return $this->getAdapter()->fetchOne($sql);
    }
    function getReceiveTransferClosingEntry($data){//transfer and receive for closing
    	$sql=" SELECT
    		SUM(td.qtyReceive) AS qtyReceive
    	FROM
    		st_transfer_receive AS t,
    		`st_transfer_receive_detail` td
    	WHERE t.id=td.receiveId ";
    
    	if(!empty($data['start_date'])){
    	$from_date =(empty($data['start_date']))? '1': " t.receiveDate >= '".$data['start_date']." 00:00:00'";
    	$to_date = (empty($data['end_date']))? '1': " t.receiveDate < '".$data['end_date']." 00:00:00'";
    	$sql.= " AND ".$from_date." AND ".$to_date;
    	}
    
    	if(!empty($data['projectId'])){
    		$sql.= " AND t.projectId=".$data['projectId'];
    	}
    	if(!empty($data['proId'])){
    			$sql.= " AND td.proId=".$data['proId'];
   		 }
	    $sql.=" GROUP BY td.proId ";
	    return $this->getAdapter()->fetchOne($sql);
    }
    
    function getTransferAllReport($data){
		
    	$DATE_FORMAT = DATE_FORMAT_FOR_SQL;
		$tr=Application_Form_FrmLanguages::getCurrentlanguage();
		
    	$sql="
	    	SELECT 
				t.id,
				(SELECT project_name from `ln_project` WHERE br_id=t.fromProjectId LIMIT 1) fromProject,
				t.transferNo,
				t.driver AS driverName,
				t.transferer,
				DATE_FORMAT(t.transferDate,'$DATE_FORMAT') as transferDate,
				(SELECT project_name from `ln_project` WHERE br_id=toProjectId LIMIT 1) toProject,
				t.receiverId,
				t.userFor AS useFor,
				t.isCompleted,
				t.isApproved,
				t.note,
				proId,
				td.qtyRequest
				,CASE
						WHEN  COALESCE((SELECT trsd.isCompleted FROM `st_transferstock_detail` AS trsd WHERE trsd.transferId =t.id   ORDER BY trsd.isCompleted ASC LIMIT 1 ),0) = 1 THEN '".$tr->translate("RECEIVED")."'
						ELSE   '".$tr->translate("PENDING")."'
					END AS isCompleted
			FROM 
				`st_transferstock` t,
				`st_transferstock_detail` td
			WHERE t.id=td.transferId
    	";
    	if(!empty($data['start_date'])){
    		$from_date =(empty($data['start_date']))? '1': " t.transferDate >= '".$data['start_date']." 00:00:00'";
    		$to_date = (empty($data['end_date']))? '1': " t.transferDate <= '".$data['end_date']." 00:00:00'";
    		$sql.= " AND ".$from_date." AND ".$to_date;
    	}
    	
    	if(!empty($data['projectId'])){
    		$sql.= " AND t.fromProjectId=".$data['projectId'];
    	}
    	if(!empty($data['toProjectId'])){//received
    		$sql.= " AND t.toProjectId=".$data['toProjectId'];
    	}

    	$sql.=" GROUP BY t.id DESC";
    	return $this->getAdapter()->fetchAll($sql);
    }



	function getTransferRow($recordId){
    	$db = $this->getAdapter();
    	$this->_name='st_transferstock';
    	$sql="SELECT 
			id,tr.fromProjectID
			,(SELECT project_name FROM `ln_project` WHERE br_id=tr.fromProjectID LIMIT 1) AS projectName
			,(SELECT project_name FROM `ln_project` WHERE br_id=tr.toProjectID LIMIT 1) AS ReceiveBranch
			,tr.transferNo
			, tr.ReceiverId,
			DATE_FORMAT(tr.transferDate,'%d-%m-%Y') AS TransferDate
			,tr.userFor
			,tr.transferer
			,tr.driver AS driverName				
		FROM `st_transferstock` AS tr WHERE tr.status=1 AND tr.id=".$recordId;
    	
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	$sql.= $dbg->getAccessPermission('tr.fromProjectId');
    	
    	$sql.=" LIMIT 1";
    	
    	return $db->fetchRow($sql);
    }

	function getTransferAllRow($recordId){
    	$db = $this->getAdapter();
    	$this->_name='st_transferstock_detail';
    	$sql="SELECT 
			td.*
			,(SELECT `proCode` FROM `st_product` WHERE st_product.`proId`=td.proId LIMIT 1) AS proCode
			,(SELECT `proName` FROM `st_product` WHERE st_product.`proId`=td.proId LIMIT 1) AS proName
			
			,(SELECT `measureLabel` FROM `st_product` WHERE st_product.`proId`=td.proId LIMIT 1) AS MeasureLabel	
			,COALESCE(SUM(rtrd.qtyReceive),0) AS totalReceiveQty
			,td.isCompleted
		FROM 
			`st_transferstock_detail` AS td 
			LEFT JOIN ( st_transfer_receive_detail AS rtrd JOIN st_transfer_receive As rtr ON rtr.id = rtrd.receiveId AND rtr.status=1 )
			ON td.proId = rtrd.proId AND td.transferId = rtr.transferId
		WHERE td.transferId=".$recordId." ";
		
		$sql.=" GROUP BY td.proId ";
    	return $db->fetchAll($sql);
    }
    function getBudgetList($search){
    	$sql="SELECT 
    			bi.id,
    			bi.budgetTypeId,
		    	bi.budgetTitle,
		    	(SELECT p.budgetTitle FROM st_budget_item AS p WHERE p.id=bi.parentId LIMIT 1) AS parentTitle,
		    	(SELECT b.budgetTitle FROM st_budget_type AS b WHERE b.id=bi.budgetTypeId LIMIT 1) AS budgetType,
		    	bi.createDate,
		    	(SELECT first_name FROM rms_users AS u WHERE u.id = bi.userId LIMIT 1) AS USER ,
		    	(SELECT name_en FROM ln_view WHERE TYPE=3 AND key_code = bi.status LIMIT 1) AS STATUS,
		    	bp.projectId,
		    	bp.totalBudget
		    	FROM 
		    		st_budget_item AS bi,
		    		st_budget_project_item bp
    		WHERE bi.id=bp.budgetId ";
    	 
    	 
    	$from_date =(empty($search['start_date']))? '1': " bi.createDate >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " bi.createDate <= '".$search['end_date']." 23:59:59'";
    	$where='';
    	$where_date = " AND ".$from_date." AND ".$to_date;
    	 
    	if(!empty($search['adv_search'])){
    	$s_where = array();
    	$s_search = (trim($search['adv_search']));
    	$s_where[] = " bi.budgetTitle LIKE '%{$s_search}%'";
    	$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
//     	if($search['status']>-1){
//     		$where.= " AND bi.status = ".$search['status'];
//     	}
    	if($search['branch_id']>-1){
    		$where.= " AND bi.status = ".$search['branch_id'];
    	}
    	
    	 
    	$order=' ORDER BY bi.id DESC  ';
    		 
    		$db = $this->getAdapter();
    		return $db->fetchAll($sql.$where_date.$order);
    }
    function getExpensByMonth($data,$branch,$date,$monthlytype=1){
    	$db = $this->getAdapter();
    	if ($data['monthlytype']==1){//month
    		$date = date("Y-m",strtotime($date));
    		$sql="SELECT 
					SUM(total)
				FROM `st_budget_expense` be,
					`st_budget_expense_detail` bed
			WHERE 
				be.id=bed.budgetExpenseId
				
    		";
    		if(!empty($data['budgetItemId'])){
    			$sql.=" AND bed.budgetItemId=".$data['budgetItemId'];
    		}
    		if(!empty($data['projectId'])){
    			$sql.=" AND be.projectId=".$data['projectId'];
    		}
    		if(!empty($data['date'])){
    			$sql.=" AND DATE_FORMAT(be.createDate,'%Y-%m')='".$data['date']."'";
    		}
    		$sql.=" GROUP BY bed.budgetItemId ";
//     		$sql="SELECT
// 	    				SUM(ex.total_amount) AS totalbymonth
// 	    			FROM 
// 	    				`ln_expense` AS ex
//     				WHERE ex.status=1 AND DATE_FORMAT(ex.date,'%Y-%m') ='$date'";
    		$totatBudget = $db->fetchOne($sql);
    		if(empty($totatBudget)){
    			$totatBudget=0;
    		}
    		
    		return $totatBudget;
    	}else{//year
    			//$date = date("Y",strtotime($date));
    			
// 	    		$sql="SELECT
// 			    		SUM(ex.total_amount) AS totalbymonth
// 			    		FROM `ln_expense` AS ex
// 	    			WHERE ex.status=1 AND DATE_FORMAT(ex.date,'%Y') ='$date'";
	    		
	    		$date = date("Y",strtotime($data['date']));
	    		$sql="SELECT
				    		SUM(total)
				    	FROM `st_budget_expense` be,
				    		`st_budget_expense_detail` bed
	    				WHERE
	    				be.id=bed.budgetExpenseId ";
	    		if(!empty($data['budgetItemId'])){
	    			$sql.=" AND bed.budgetItemId=".$data['budgetItemId'];
	    		}
	    		if(!empty($data['projectId'])){
	    			$sql.=" AND be.projectId=".$data['projectId'];
	    		}
	    		if(!empty($data['date'])){
	    			$sql.=" AND DATE_FORMAT(be.createDate,'%Y')='".$date."'";
	    		}
	    		$sql.=" GROUP BY bed.budgetItemId ";
	    		$totatBudget = $db->fetchOne($sql);
	    		if(empty($totatBudget)){
	    			$totatBudget=0;
	    		}
	    		
	    		return $totatBudget;
    		}
    
    	}
	
	function getAllReceiveTransferStock($search){
		
		
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$tr=Application_Form_FrmLanguages::getCurrentlanguage();
    	$sql="
			SELECT 
				rts.*
				,(SELECT project_name FROM `ln_project` WHERE br_id=rts.projectId LIMIT 1) AS projectName
				,(SELECT project_name FROM `ln_project` WHERE br_id=rts.fromProjectId LIMIT 1) AS fromProjectName
				,trs.transferNo
				,trs.transferDate
				,trs.driver
				,trs.transferer
				,trs.userFor
				,(SELECT u.first_name FROM rms_users AS u WHERE u.id=rts.userId LIMIT 1 ) AS userName
				
			";
    	$sql.=$dbGb->caseStatusShowImage("rts.status");
    	$sql.="
			FROM `st_transfer_receive` AS rts 
				LEFT JOIN `st_transferstock` AS trs ON trs.id = rts.transferId AND trs.toProjectId =rts.projectId
			WHERE  rts.status =1 
		";
    	
    	$from_date =(empty($search['start_date']))? '1': " rts.receiveDate >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " rts.receiveDate <= '".$search['end_date']." 23:59:59'";
    	$where='';
    	$where_date = " AND ".$from_date." AND ".$to_date;
    	
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = addslashes((trim($search['adv_search'])));
    		$s_where[] = " rts.receiveNo LIKE '%{$s_search}%'";
    		$s_where[] = " trs.transferNo LIKE '%{$s_search}%'";
    		$s_where[] = " trs.driver LIKE '%{$s_search}%'";
    		$s_where[] = " trs.transferer LIKE '%{$s_search}%'";
    		$s_where[] = " trs.userFor LIKE '%{$s_search}%'";
    		
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	
    	
    	if($search['branch_id']>0){
    		$where.= " AND rts.projectId = ".$search['branch_id'];
    	}
    	
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	$where.= $dbg->getAccessPermission('rts.projectId');
    	
    	$order=' ORDER BY rts.id DESC  ';
    	$db = $this->getAdapter();
    	return $db->fetchAll($sql.$where.$where_date.$order);
    }
	
	function getReceivedTransferRow($recordId){
    	$db = $this->getAdapter();
		
		$DATE_FORMAT = DATE_FORMAT_FOR_SQL;
		
    	$this->_name='st_transferstock';
    	$sql="
			SELECT 
				rtr.*
				,(SELECT project_name FROM `ln_project` WHERE br_id=rtr.projectId LIMIT 1) AS projectName
				,(SELECT project_name FROM `ln_project` WHERE br_id=rtr.fromProjectId LIMIT 1) AS fromProjectName
				,DATE_FORMAT(rtr.receiveDate,'".$DATE_FORMAT."') AS receiveDateFormat
			
				,tr.transferNo
				,tr.ReceiverId
				,DATE_FORMAT(tr.transferDate,'".$DATE_FORMAT."') AS transferDateFormat
				,tr.userFor
				,tr.transferer
				,tr.driver AS driverName				
			FROM 
				`st_transfer_receive` AS rtr 
				LEFT JOIN st_transferstock AS tr ON tr.id = rtr.transferId
			WHERE rtr.status=1 AND rtr.id=".$recordId;
    	
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	$sql.= $dbg->getAccessPermission('rtr.projectId');
    	
    	$sql.=" LIMIT 1";
    	
    	return $db->fetchRow($sql);
    }
	
	
	function getReceiveTransferDetail($recordId){
    	$db = $this->getAdapter();
    	$this->_name='st_transfer_receive_detail';
    	$sql="
		SELECT 
			rtrd.*
			,(SELECT `proCode` FROM `st_product` WHERE st_product.`proId`=rtrd.proId LIMIT 1) AS proCode
			,(SELECT `proName` FROM `st_product` WHERE st_product.`proId`=rtrd.proId LIMIT 1) AS proName
			,(SELECT `measureValue` FROM `st_product` WHERE st_product.`proId`=rtrd.proId LIMIT 1) AS StockQty
			,(SELECT `measureLabel` FROM `st_product` WHERE st_product.`proId`=rtrd.proId LIMIT 1) AS MeasureLabel
			,trd.qtyRequest
			,trd.qtyAppAfter
			,trd.isCompleted
		FROM 
			`st_transfer_receive_detail` AS rtrd 
			JOIN `st_transfer_receive` AS rtr ON rtr.id = rtrd.receiveId
			LEFT JOIN st_transferstock_detail AS trd ON trd.transferId = rtr.transferId AND trd.proId = rtrd.proId
		WHERE rtrd.receiveId=".$recordId." ";
    	return $db->fetchAll($sql);
    }
	

}