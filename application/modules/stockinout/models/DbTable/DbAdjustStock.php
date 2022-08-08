<?php

class Stockinout_Model_DbTable_DbAdjustStock extends Zend_Db_Table_Abstract
{
    protected $_name = 'st_adjust_stock';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getAllAdjustStock($search){
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$approved = $tr->translate("APPROVED");
    	$reject =  $tr->translate("REJECTED");
    	$sql="SELECT 
    			sa.id,
    			(SELECT project_name FROM `ln_project` WHERE br_id=sa.projectId LIMIT 1) AS projectName,
		    	 sa.adjustDate,
		    	(SELECT first_name FROM rms_users WHERE id=sa.userId LIMIT 1 ) AS user_name,
			    	CASE WHEN sa.isApproved=1 THEN '$approved'
			    		ELSE '$reject'
			    	END AS status,
			    	sa.approvedDate,
		    	(SELECT first_name FROM rms_users WHERE id=sa.approvedBy LIMIT 1) approvedBy
    		FROM `st_adjust_stock` sa WHERE 1 ";
    	
    	$from_date =(empty($search['start_date']))? '1': " sa.createDate >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " sa.createDate <= '".$search['end_date']." 23:59:59'";
    	
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
   
    function addAdjustStock($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try
    	{
    		$dbs = new Application_Model_DbTable_DbGlobalStock();
    		$arr = array(
    				'projectId'=>$data['branch_id'],
    				'adjustDate'=>$data['date'],
    				'userId'=>$this->getUserId(),
    				'createDate'=>date('Y-m-d'),
    			);
    		$stockId = $this->insert($arr);
    		
    		$ids = explode(',',$data['identity']);
    		if(!empty($ids)){
    			foreach($ids as $i){
    				$param = array(
    					'branch_id'=>$data['branch_id'],
    					'productId'=>$data['proId'.$i],
    				);
    				
    				$resultStock = $dbs->getProductInfoByLocation($param);
    				$currentQty = !empty($resultStock['currentQty'])? $resultStock['currentQty']:0;
    				
    				$arr = array(
    					'adjustId'=>$stockId,
    					'proId'=>$data['proId'.$i],
    					'currentQty'=>$currentQty,
    					'exactQty'=>$data['qtyRequest'.$i],
    					'note'=>$data['note'.$i],
    				);
    				$this->_name='st_adjust_detail';
    				$id = $this->insert($arr);
    			}
    		}
    		$db->commit();
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    		Application_Form_FrmMessage::Sucessfull("INSERT_FAIL", "/stockinout/adjuststock/add");
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
    		Application_Form_FrmMessage::Sucessfull("INSERT_FAIL", "/stockinout/adjuststock/add");
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
}