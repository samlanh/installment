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
    	$sql=" SELECT id,
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
	    $where.= $dbg->getAccessPermission('so.projectId');
	     
	    $order=' ORDER BY sa.id DESC  ';
	    $db = $this->getAdapter();
	    return $db->fetchAll($sql.$where_date.$where.$order);
    }
}