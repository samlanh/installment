<?php

class Product_Model_DbTable_DbClosingStock extends Zend_Db_Table_Abstract
{
    protected $_name = 'st_closing';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getAllClosingStock($search){
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$approved = $tr->translate("APPROVED");
    	$reject =  $tr->translate("REJECTED");
    	$sql="SELECT 
    			cl.id,
    			(SELECT project_name FROM `ln_project` WHERE br_id=cl.projectId LIMIT 1) AS projectName,
		    	cl.closingDate,
		    	cl.toDate,
		    	cl.note,
		    	(SELECT adjustDate FROM `st_adjust_stock` WHERE st_adjust_stock.id= cl.adjustId LIMIT 1) AS adjustDate,
		    	(SELECT first_name FROM rms_users WHERE id=cl.userId LIMIT 1 ) AS user_name
    		FROM `st_closing` cl WHERE 1 ";
    	
    	$from_date =(empty($search['start_date']))? '1': " cl.closingDate >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " cl.closingDate <= '".$search['end_date']." 23:59:59'";
    	
    	$where_date = " AND ".$from_date." AND ".$to_date;
    	$where='';
    	
    	if($search['branch_id']>-1){
    		$where.= " AND cl.projectId = ".$search['branch_id'];
    	}
    	if(!empty($where['adjustDate'])){
    		$where.= " AND cl.adjustId = ".$search['adjustDate'];
    	}
    	
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	$where.= $dbg->getAccessPermission('cl.projectId');
    	
    	$order=' ORDER BY cl.id DESC  ';
    	$db = $this->getAdapter();
    	return $db->fetchAll($sql.$where_date.$where.$order);
    }
    function updatePreviousClosingEntry($branchId,$endDate){
    	$sql="SELECT id FROM $this->_name WHERE projectId=".$branchId." ORDER BY closingDate DESC LIMIT 1";
    	$db = $this->getAdapter();
    	$closedId = $db->fetchOne($sql);
    	
    	if(!empty($closedId)){
    		$arr = array(
    				'toDate'=>$endDate
    			);
    		$where='id='.$closedId;
    		$this->update($arr, $where);
    	}
    }
   
    function addClosingEntry($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try
    	{
    		$projectId = $data['branch_id'];
    		$this->updatePreviousClosingEntry($projectId, $data['date']);
    		
    		$dbs = new Application_Model_DbTable_DbGlobalStock();
    		$arr = array(
    				'projectId'=>$projectId,
    				'adjustId'=>$data['adjustDate'],
    				'closingDate'=>$data['date'],
    				'fromDate'=>$data['date'],
    				'note'=>$data['note'],
    				'userId'=>$this->getUserId(),
    				'createDate'=>date('Y-m-d h:s'),
    			);
    		$closeId = $this->insert($arr);
    		
    		$arr = array(
    				'isLocked'=>1
    			);
    		
    		$this->_name='st_stockout';
    		$where="projectId=".$projectId;
    		$this->update($arr, $where);
    		
    		$this->_name='st_receive_stock';
    		$this->update($arr, $where);
    		
    		$this->_name='st_transfer_receive';
    		$this->update($arr, $where);
    		
    		$this->_name='st_adjust_stock';
    		$this->update($arr, $where);
    		
    		$param = array(
    			'branch_id'=>$projectId,
    			'isCountStock'=>1
    		);
    		
    		$dbcountStock = new Product_Model_DbTable_DbPreCountProduct();
    		$results = $dbs->getProductLocationbyProId($param);
    		
    		if(!empty($results)){
    			
    			foreach($results as $result){
    				$param = array(
    						'projectId'=>$data['branch_id'],
    						'proId'=>$result['id'],
    						'isClosed'=>0,
    						'getSigleRow'=>1,
    						);
    				$adjustResult = $dbcountStock->getProductExistingCount($param);
    				if(!empty($adjustResult)){
    					$differ = $adjustResult['currentQty']-$adjustResult['countQty'];
    					
	    				$arr = array(
	    					'closingId'=>$closeId,
	    					'projectId'=>$data['branch_id'],
	    					'proId'=>$result['id'],
	    					'qtyBegining'=>$result['currentQty']-$differ,
	    					'qtyAdjust'=>$differ,
	    					'costing'=>$result['costing'],
	    				);
	    				
	    				$this->_name='st_closing_detail';
	    				$id = $this->insert($arr);
	    				
// 	    				$results = $this->getDataAllRow($data['id']);
// 	    				if(!empty($results)){
// 	    					foreach($results as $result){
	    						$arr = array(
	    								'qty'=>$result['currentQty']-$differ,
	    						);
	    							
	    						$this->_name='st_product_location';
	    						$where = 'projectId='.$data['branch_id']." AND proId=".$result['id'];
	    						$this->update($arr, $where);
	    				
	    						$param = array(
    								'branch_id'=>$data['branch_id'],
    								'productId'=>$result['id'],
	    						);
	    				
	    						$qtyDifferent = $result['currentQty']- $differ;
	    						$dbs->addProductHistoryQty($data['branch_id'],$result['id'],7,$qtyDifferent,$adjustResult['id']);//movement'
// 	    					}
// 	    				}
	    				
	    				$this->_name='st_precount_product_detail';
	    				$_arr = array('isClosed'=>1);
	    				$where ='id='.$adjustResult['id'];
	    				$this->update($_arr, $where);
    				}
    			}
    		}
    		$db->commit();
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    		Application_Form_FrmMessage::Sucessfull("INSERT_FAIL", "/product/closingentry/add",2);
    	}
    }
    function upateAdjustStock($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try
    	{
    		$row = $this->getDataRow($data['id']);
    		$projectId = $row['projectId'];
    		
    		$dbs = new Application_Model_DbTable_DbGlobalStock();
    		$arr = array(
    				'isApproved'=>1,
    				'approvedBy'=>$this->getUserId(),
    				'approvedDate'=>date('Y-m-d'),
    			);
    		$where = 'id='.$data['id'];
    		$this->_name='st_adjust_stock';
    		$this->update($arr, $where);
    
    		
    		$results = $this->getDataAllRow($data['id']);
    		if(!empty($results)){
    			foreach($results as $result){
    				$arr = array(
    					'qty'=>$result['exactQty'],
    				);
    					
    				$this->_name='st_product_location';
    				$where = 'projectId='.$projectId." AND proId=".$result['proId'];
    				$this->update($arr, $where);
    				
    				$param = array(
    					'branch_id'=>$projectId,
    					'productId'=>$result['proId'],
    				);
    				
					$qtyDifferent = $result['exactQty']- $result['currentQty'];
   					$dbs->addProductHistoryQty($projectId,$result['proId'],7,$qtyDifferent,$result['id']);//movement'
    			}
    		}
    		$db->commit();
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    		Application_Form_FrmMessage::Sucessfull("INSERT_FAIL", "/stockinout/adjuststock/add",2);
    	}
    }
    function getDataRow($recordId){
    	$db = $this->getAdapter();
    	$this->_name='st_adjust_stock';
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$approved = $tr->translate("APPROVED");
    	$reject =  $tr->translate("REJECTED");
    	
    	$sql=" SELECT 
    		id,
    		projectId,
    		isApproved,
			(SELECT project_name FROM `ln_project` WHERE br_id=projectId LIMIT 1) AS projectName,
			(SELECT first_name FROM rms_users WHERE id=userId LIMIT 1 ) AS user_name,
			note,
    		DATE_FORMAT(adjustDate,'%d-%m-%Y') AS adjustDate,
	    	CASE WHEN isApproved=1 THEN '$approved'
	    		ELSE '$reject'
	    	END AS status,
		   (SELECT first_name FROM rms_users WHERE id=approvedBy LIMIT 1) approvedBy
		    	
    	FROM $this->_name WHERE id=".$recordId;
    	
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	$sql.= $dbg->getAccessPermission('projectId');
    	
    	$sql.=" LIMIT 1";
    	return $db->fetchRow($sql);
    }
    function getDataAllRow($recordId){
    	$db = $this->getAdapter();
    	$this->_name='st_adjust_detail';
    	$sql=" SELECT 
    				ad.id,
    				 ad.currentQty,
    				 ad.exactQty,
    				 ad.note,
    				 ad.proId,
    				 (SELECT `proCode` FROM `st_product` WHERE st_product.`proId`=ad.proId LIMIT 1) AS proCode,
					 (SELECT `proName` FROM `st_product` WHERE st_product.`proId`=ad.proId LIMIT 1) AS proName,
					 (SELECT measureLabel FROM st_product p WHERE p.proId=ad.proId LIMIT 1) measureLabel
    		FROM $this->_name as ad WHERE ad.adjustId=".$recordId." ";
    	return $db->fetchAll($sql);
    }
    function getAllAdjusted($data){
    	$db = $this->getAdapter();
    	$this->_name='st_adjust_stock';
    	
    	$sql=" SELECT 
    		id,
    		DATE_FORMAT(adjustDate,'%d-%m-%Y') AS name
    	FROM $this->_name WHERE 1 ";
    	
    	if(isset($data['isApproved'])){
    		$sql.=" AND isApproved=".$data['isApproved'];
    	}
    	if(isset($data['branch_id'])){
    		$sql.=" AND projectId=".$data['branch_id'];
    	}
    	
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	$sql.= $dbg->getAccessPermission('projectId');
    	
    	return $db->fetchAll($sql);
    }
    
    function getAllClosingDate($search){
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	
    	$sql="SELECT
		cl.id,
		CONCAT( COALESCE(DATE_FORMAT(cl.closingDate,'%d-%m-%Y'), ''), ' /', COALESCE( DATE_FORMAT(cl.toDate,'%d-%m-%Y'), '')) AS name
		FROM `st_closing` cl WHERE 1";
    	
    	$where='';
    	if($search['branch_id']>-1){
    		$where.= " AND cl.projectId = ".$search['branch_id'];
    	}
    	 
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	$where.= $dbg->getAccessPermission('cl.projectId');
    	 
    	$order=' ORDER BY cl.id DESC  ';
    	$db = $this->getAdapter();
    	return $db->fetchAll($sql.$where.$order);
    }
}