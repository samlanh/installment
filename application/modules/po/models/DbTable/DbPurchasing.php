<?php

class Po_Model_DbTable_DbPurchasing extends Zend_Db_Table_Abstract
{
    protected $_name = 'st_purchasing';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getAllPO($search){
    	$db = $this->getAdapter();
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$sql="
			SELECT 
				po.id,
				(SELECT p.project_name FROM `ln_project` AS p WHERE p.br_id = po.projectId LIMIT 1) AS branch_name,
				po.purchaseNo,
				spp.supplierName,
				po.date,
				rq.requestNo,
				rq.date AS requestId,
				(SELECT  CONCAT(COALESCE(u.last_name,''),' ',COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.userId LIMIT 1 ) AS requestName,
				po.total
		";
    	$sql.=$dbGb->caseStatusShowImage("po.status");
		$sql.=",(SELECT  CONCAT(COALESCE(u.last_name,''),' ',COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=po.userId LIMIT 1 ) AS byUser";
		$sql.=" FROM `st_purchasing` AS po 
					JOIN `st_supplier` AS spp 
					LEFT JOIN st_request_po AS rq ON rq.id =po.requestId 
				WHERE spp.id = po.supplierId 
					AND po.purchaseType=".$search['purchaseType']."
		";
    	$where = "";
    	$from_date =(empty($search['start_date']))? '1': " po.date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " po.date <= '".$search['end_date']." 23:59:59'";
    	
    	$where.= " AND ".$from_date." AND ".$to_date;
    	
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = (trim($search['adv_search']));
    		$s_where[] = " po.purchaseNo LIKE '%{$s_search}%'";
    		$s_where[] = " spp.supplierName LIKE '%{$s_search}%'";
    		$s_where[] = " rq.requestNo LIKE '%{$s_search}%'";
    		$s_where[] = " po.total LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if($search['status']>-1){
    		$where.= " AND po.status = ".$search['status'];
    	}
    	if(($search['branch_id'])>0){
    		$where.= " AND po.projectId = ".$search['branch_id'];
    	}
    	$order=' ORDER BY po.id DESC  ';
    	$where.=$dbGb->getAccessPermission("po.projectId");
    	//echo $sql.$where.$order;exit();
    	return $db->fetchAll($sql.$where.$order);
    }
   
    function addPurchasingRequest($data){
    	
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try
    	{
			$dbGBstock = new Application_Model_DbTable_DbGlobalStock();
			$data['dateRequest']=$data['requestDate'];
			$purchaseNo =$dbGBstock->generatePurchaseNo($data);
			
			$requestId = $data['requestId'];
			
			
			
    		$arr = array(
    				'projectId'			=>$data['branch_id'],
    				'purchaseType'		=>$data['purchaseType'],
    				'purchaseNo'		=>$purchaseNo,
    				'requestId'			=>$requestId,
    				'supplierId'		=>$data['supplierId'],
    				'date'				=>$data['date'],
    				'note'				=>$data['note'],
    				'total'				=>$data['total'],
    				
					'status'			=>1,
    				'createDate'		=>date("Y-m-d H:i:s"),
    				'modifyDate'		=>date("Y-m-d H:i:s"),
    				'userId'			=>$this->getUserId(),
    				
    				);
    		$this->_name='st_purchasing';
    		$id = $this->insert($arr);
			
			$dbGbSt = new Application_Model_DbTable_DbGlobalStock();
			$arrStep = array(
				'stepNum'=>$data['stepNum'],
				'typeStep'=>1,
			);
			$processingStatus = $dbGbSt->requestingProccess($arrStep);
			$arrRequestPO = array(
					'processingStatus'		=>$processingStatus,
				);
			$this->_name='st_request_po';	
			$whereRequestPO=" id = $requestId ";
			$this->update($arrRequestPO,$whereRequestPO);
					
			if(!empty($data['identity'])){
				$ids = explode(',', $data['identity']);
				foreach ($ids as $i){
					$isCompletedPO=0;
					if($data['isCompletedPO'.$i]==1){
						$isCompletedPO=1;
					}else{
						if($data['qty'.$i]>=$data['qtyApprovedAfter'.$i]){
							$isCompletedPO=1;
						}
					}
					
					$qtyApprovedAfter = $data['qtyApprovedAfter'.$i]-$data['qty'.$i];
					$arrRequestPODetail = array(
							'isCompletedPO'		=>$isCompletedPO,
							'qtyApprovedAfter'	=>$qtyApprovedAfter,
							'modifyDate'		=>date("Y-m-d H:i:s"),
						);
					$this->_name='st_request_po_detail';	
					$whereRequestPODetail=" requestId = $requestId AND proId=".$data['proId'.$i]." ";
					$this->update($arrRequestPODetail,$whereRequestPODetail);
					
					$arr = array(
							'purchaseId'		=>$id,
							'proId'				=>$data['proId'.$i],
								
							'qty'				=>$data['qty'.$i],
							'qtyAfter'			=>$data['qty'.$i],
							'unitPrice'			=>$data['unitPrice'.$i],
							'discountAmount'	=>$data['discountAmount'.$i],
							'subTotal'			=>$data['subTotal'.$i],
							'note'				=>$data['note'.$i],
						);
					$this->_name='st_purchasing_detail';	
					$this->insert($arr);
				}
    		}
			
    		$db->commit();
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    	}
    }
    function updateData($data){
    	 
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try
    	{
    		$arr = array(
    				''=>''
    
    		);
    		
    		//$this->_name='';
    		$where = 'client_id = '.$data['id'];
			$this->update($arr, $where);
    		$db->commit();
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    	}
    }
    function getDataRow($recordId){
    	$db = $this->getAdapter();
    	
    	$sql=" SELECT * FROM $this->_name WHERE id=".$recordId." LIMIT 1";
    	return $db->fetchRow($sql);
    }
   
}