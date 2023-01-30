<?php

class Stockinout_Model_DbTable_DbTransfer extends Zend_Db_Table_Abstract
{
    protected $_name = 'st_transferstock';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function generateRequestUsageNo($_data=null){
    
    	$dbgb = new Application_Model_DbTable_DbGlobal();
    	$pre = "";
    
    	$branch_id = empty($_data['branch_id'])?0:$_data['branch_id'];
    	$pre = $dbgb->getPrefixCode($branch_id);
    
    	$db = $this->getAdapter();
    	$sql=" SELECT so.id  FROM $this->_name AS so WHERE so.projectId = $branch_id  ORDER BY so.id DESC LIMIT 1 ";
    	$acc_no = $db->fetchOne($sql);
    	$new_acc_no= (int)$acc_no+1;
    
    	$dateRequest = empty($_data['createDate'])?date("Y-m-d"):$_data['createDate'];
    
    	$pre=$pre.date("dmy",strtotime($dateRequest));
    	$pre=$pre."R";
    	$numberLenght= strlen((int)$new_acc_no);
    	for($i = $numberLenght;$i<4;$i++){
    		$pre.='0';
    	}
    	return $pre.$new_acc_no;
    }
    function getAllTransfer($search){
		
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$tr=Application_Form_FrmLanguages::getCurrentlanguage();
		
    	$sql="SELECT t.id,
					(SELECT project_name FROM `ln_project` WHERE br_id=t.fromProjectId LIMIT 1) AS projectName,
					t.transferNo,
					t.transferDate,
					
					t.driver,
					t.transferer,
					(SELECT project_name FROM `ln_project` WHERE br_id=t.toProjectId LIMIT 1) AS toProjectId,
					t.receiverId,
					t.userFor,
					(SELECT first_name FROM rms_users AS u WHERE u.id = t.userId LIMIT 1) AS byUser
					,CASE
						WHEN  COALESCE((SELECT trsd.isCompleted FROM `st_transferstock_detail` AS trsd WHERE trsd.transferId =t.id   ORDER BY trsd.isCompleted ASC LIMIT 1 ),0) = 1 THEN '".$tr->translate("RECEIVED")."'
						ELSE   '".$tr->translate("PENDING")."'
					END AS isCompleted
					
				";
    	$sql.=$dbGb->caseStatusShowImage("t.status");
    	$sql.=" FROM `st_transferstock` t WHERE 1  ";
    	$from_date =(empty($search['start_date']))? '1': " t.transferDate >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " t.transferDate <= '".$search['end_date']." 23:59:59'";
    	
    	$where_date = " AND ".$from_date." AND ".$to_date;
    	$where='';
    	
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = (trim($search['adv_search']));
    		$s_where[] = " t.transferNo LIKE '%{$s_search}%'";
    		$s_where[] = " t.driver LIKE '%{$s_search}%'";
    		$s_where[] = " t.transferer LIKE '%{$s_search}%'";
    		$s_where[] = " t.receiverId LIKE '%{$s_search}%'";
    		$s_where[] = " t.userFor LIKE '%{$s_search}%'";
    		
    		//$s_where[] = " (SELECT p.id FROM `ln_properties` p WHERE p.id=so.houseId AND p.land_address LIKE '%{$s_search}%' LIMIT 1)";
    		
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if($search['branch_id']>-1){
    		$where.= " AND t.fromProjectId = ".$search['branch_id'];
    	}
//     	if($search['workType']>0){
//     		$where.= " AND so.workType = ".$search['workType'];
//     	}
//     	if(!empty($search['propertyType'])){
//     		$where.= " AND so.houseType = ".$search['propertyType'];
//     	}
//     	if($search['contractor']>0){
//     		$where.= " AND so.contractor = ".$search['contractor'];
//     	}
//     	if($search['staffWithdraw']>0){
//     		$where.= " AND so.staffId = ".$search['staffWithdraw'];
//     	}
    	if($search['status']>-1){
    		$where.= " AND so.status = ".$search['status'];
    	}
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	$where.= $dbg->getAccessPermission('t.fromProjectId');
    	
    	$order=' ORDER BY t.id DESC  ';
    	$db = $this->getAdapter();
    	return $db->fetchAll($sql.$where_date.$where.$order);
    }
   
    function addTransferStock($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try
    	{
    		$dbs = new Application_Model_DbTable_DbGlobalStock();
    		$requestStock = $dbs->generateTransferNo($data);
    		
    		$arr = array(
    			'fromProjectId'=>$data['branch_id'],
    			'transferNo'=>$requestStock,
    			'driver'=>$data['driver'],
    			'transferer'=>$data['transferer'],
    			'transferDate'=>$data['transferDate'],
    				
    			'toProjectId'=>$data['toProjectId'],
    			'receiverId'=>$data['receiver'],
    			'userFor'=>$data['useFor'],
    			'note'=>$data['note'],
    			
    			'createDate'=>date('Y-m-d H:i:s'),
    			'modifyDate'=>date('Y-m-d H:i:s'),
    			'status'=>1,
				'isApproved'=>$data['isApproved'],
    			'userId'=>$this->getUserId(),
    			);
    		$transferId = $this->insert($arr);
    		
    		$dbb = new Budget_Model_DbTable_DbInitilizeBudget();
    		
    		$param = array(
    			'branch_id'=>$data['branch_id'],
    			'type'=>3,
    			'transactionId'=>$transferId,
    		);
    		
    		$budgetExpenseId = $dbb->addBudgetExpense($param);
    		
    		$param = array(
    				'branch_id'=>$data['toProjectId'],
    				'type'=>4,
    				'transactionId'=>$transferId,
    		);
    		
    		$budgetExpenseId1 = $dbb->addBudgetExpense($param);
    		
    		
    		$ids = explode(',',$data['identity']);
    		if(!empty($ids)){
    			foreach($ids as $i){
    				$arr = array(
    					'transferId'=>$transferId,
    					'proId'=>$data['proId'.$i],
    					'qtyRequest'=>$data['qtyRequest'.$i],
    					'qtyApproved'=>$data['qtyRequest'.$i],
    					'qtyAppAfter'=>$data['qtyRequest'.$i],
    					'unitPrice'=>$data['costing'.$i],
    					'note'=>$data['note'.$i],
						'createDate'=>date('Y-m-d H:i:s'),
						'modifyDate'=>date('Y-m-d H:i:s'),
    				);
    				$this->_name='st_transferstock_detail';
    				$id = $this->insert($arr);
    				
    				$param = array(
    					'budgetExpenseId'=>$budgetExpenseId,
    					'subtransactionId'=>$id,
    					'productId'=>$data['proId'.$i],
    					'price'=>$data['costing'.$i],
    					'qty'=>$data['qtyRequest'.$i],
    					'totalDiscount'=>0
    				);
    				$dbb->addBudgetExpenseDetail($param);
    				
    				$param = array(
    					'budgetExpenseId'=>$budgetExpenseId1,
    					'subtransactionId'=>$id,
    					'productId'=>$data['proId'.$i],
    					'price'=>$data['costing'.$i],
    					'qty'=>$data['qtyRequest'.$i],
    					'totalDiscount'=>0
    				);
    				$dbb->addBudgetExpenseDetail($param);
    				
    				
    				$param = array(
    					'EntyQty'=> -$data['qtyRequest'.$i],
    					'branch_id'=> $data['branch_id'],
    					'productId'=> $data['proId'.$i],
    				);
    				$dbs->updateProductLocation($param);//Update Stock qty and new costing
    				$dbs->addProductHistoryQty($data['branch_id'],$data['proId'.$i],5,$data['qtyRequest'.$i],$id);//movement'
    				
    				/*
					$param = array(
    					'EntyQty'=> $data['qtyRequest'.$i],
    					'branch_id'=> $data['toProjectId'],
    					'productId'=> $data['proId'.$i],
    					'costing'=> $data['costing'.$i],
    				);
    				$dbs->updateProductLocation($param);//Update Stock qty and new costing
    				$dbs->addProductHistoryQty($data['toProjectId'],$data['proId'.$i],5,$data['qtyRequest'.$i],$id);//movement'
					*/
    			}
    		}
    		$db->commit();
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    		Application_Form_FrmMessage::Sucessfull("INSERT_FAIL", "/stockinout/transferout/add",2);
    	}
    }
    function upateUsageStock($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try
    	{
    		$dbs = new Application_Model_DbTable_DbGlobalStock();
    		$requestStock = $dbs->generateRequestUsageNo($data['branch_id']);
    
    		$arr = array(
    				'projectId'=>$data['branch_id'],
    				'requestNo'=>$requestStock,
    				'reqOutNo'=>$data['requestNoProject'],
    				'requestDate'=>$data['withdrawDate'],
    				'staffId'=>$data['staffWithdraw'],
    				'contractor'=>$data['contractor'],
    				'staffId'=>$data['staffWithdraw'],
    				'workerName'=>$data['ConstructionWorker'],
    				'houseType'=>$data['propertyType'],
    				'houseId'=>$data['houseId'],
    				'workType'=>$data['workType'],
    				'typeofWork'=>$data['typeofWork'],
    				'note'=>$data['note'],
    				'createDate'=>$data['withdrawDate'],
    				'status'=>1,
    				'userId'=>$this->getUserId(),
    				'tranType'=>1,
    		);
    		$stockId = $data['id'];
    		$where="id=".$stockId;
    		$this->update($arr, $where);
    		
    		$this->resetUsageStock($stockId, $data['branch_id']);
    
    		$ids = explode(',',$data['identity']);
    		if(!empty($ids)){
    			foreach($ids as $i){
    				$arr = array(
    						'stockoutId'=>$stockId,
    						'proId'=>$data['proId'.$i],
    						'qtyRequest'=>$data['qtyRequest'.$i],
    						'unitPrice'=>0,
    						'note'=>$data['note'.$i],
    				);
    				$this->_name='st_stockout_detail';
    				$id = $this->insert($arr);
    
    				$param = array(
    						'EntyQty'=> -$data['qtyRequest'.$i],
    						'branch_id'=> $data['branch_id'],
    						'productId'=> $data['proId'.$i],
    				);
    				$dbs->updateProductLocation($param);//Update Stock qty and new costing
    				$dbs->addProductHistoryQty($data['branch_id'],$data['proId'.$i],3,$data['qtyRequest'.$i],$id);//movement'
    			}
    		}
    		$db->commit();
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    		Application_Form_FrmMessage::Sucessfull("INSERT_FAIL", "/stockinout/usage/add",2);
    	}
    }
    function resetUsageStock($stockId,$branchId){
    	$dbs = new Application_Model_DbTable_DbGlobalStock();
    	$results = $this->getDataAllRow($stockId);
    	if(!empty($results)){
    		foreach($results as $row){
    			$param = array(
    				'EntyQty'=> $row['qtyRequest'],
    				'branch_id'=> $branchId,
    				'productId'=> $row['proId'],
    			);
    			$dbs->updateProductLocation($param);
    			
    			$dbs->DeleteProductHistoryQty($row['id'],5);
    		}
    		$where ='stockoutId='.$stockId;
    		$this->delete($where);	
    	}
    }
	
	function updateTransferStock($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try
    	{
    		$dbs = new Application_Model_DbTable_DbGlobalStock();
    		
    		$arr = array(
    			'fromProjectId'		=>$data['branch_id'],
    			'driver'			=>$data['driver'],
    			'transferer'		=>$data['transferer'],
    			'transferDate'		=>$data['transferDate'],
    				
    			'toProjectId'		=>$data['toProjectId'],
    			'receiverId'		=>$data['receiver'],
    			'userFor'			=>$data['useFor'],
    			'note'				=>$data['note'],
    			
    			'modifyDate'		=>date('Y-m-d H:i:s'),
    			'status'			=>$data['status'],
    			'userId'			=>$this->getUserId(),
    			);
			$transferId = $data['id'];
    		$where="id=".$transferId;
			$this->update($arr,$where);
			
    		//reverst 
			$dbb = new Budget_Model_DbTable_DbInitilizeBudget();
			
			$dbb->reverBudgetExpense($transferId);
			$rowDetail = $this->getDataRowDetail($transferId);
			if(!empty($rowDetail)) foreach($rowDetail AS $detail){
				$param = array(
    					'EntyQty'=> $detail['qtyRequest'],
    					'branch_id'=> $data['branch_id'],
    					'productId'=> $detail['proId'],
    				);
    				$dbs->updateProductLocation($param);//Update Stock qty and new costing
					$dbs->DeleteProductHistoryQty($detail['id'],5);
    				
			}
			$whereDetail= "transferId = ".$transferId;
	    	$this->_name='st_transferstock_detail';
	    	$this->delete($whereDetail);
			
			if($data['status']==0){
    			$db->commit();
    			return true;
    		}
    		
    		
    		$param = array(
    			'branch_id'=>$data['branch_id'],
    			'type'=>3,
    			'transactionId'=>$transferId,
    		);
    		
    		$budgetExpenseId = $dbb->addBudgetExpense($param);
    		
    		$param = array(
    				'branch_id'=>$data['toProjectId'],
    				'type'=>4,
    				'transactionId'=>$transferId,
    		);
    		
    		$budgetExpenseId1 = $dbb->addBudgetExpense($param);
    		
    		
    		$ids = explode(',',$data['identity']);
    		if(!empty($ids)){
    			foreach($ids as $i){
    				$arr = array(
    					'transferId'=>$transferId,
    					'proId'=>$data['proId'.$i],
    					'qtyRequest'=>$data['qtyRequest'.$i],
    					'qtyApproved'=>$data['qtyRequest'.$i],
    					'qtyAppAfter'=>$data['qtyRequest'.$i],
    					'unitPrice'=>$data['costing'.$i],
    					'note'=>$data['note'.$i],
						'createDate'=>date('Y-m-d H:i:s'),
						'modifyDate'=>date('Y-m-d H:i:s'),
    				);
    				$this->_name='st_transferstock_detail';
    				$id = $this->insert($arr);
    				
    				$param = array(
    					'budgetExpenseId'=>$budgetExpenseId,
    					'subtransactionId'=>$id,
    					'productId'=>$data['proId'.$i],
    					'price'=>$data['costing'.$i],
    					'qty'=>$data['qtyRequest'.$i],
    					'totalDiscount'=>0
    				);
    				$dbb->addBudgetExpenseDetail($param);
    				
    				$param = array(
    					'budgetExpenseId'=>$budgetExpenseId1,
    					'subtransactionId'=>$id,
    					'productId'=>$data['proId'.$i],
    					'price'=>$data['costing'.$i],
    					'qty'=>$data['qtyRequest'.$i],
    					'totalDiscount'=>0
    				);
    				$dbb->addBudgetExpenseDetail($param);
    				
    				
    				$param = array(
    					'EntyQty'=> -$data['qtyRequest'.$i],
    					'branch_id'=> $data['branch_id'],
    					'productId'=> $data['proId'.$i],
    				);
    				$dbs->updateProductLocation($param);//Update Stock qty and new costing
    				$dbs->addProductHistoryQty($data['branch_id'],$data['proId'.$i],5,$data['qtyRequest'.$i],$id);//movement'
    				
    				/*
					$param = array(
    					'EntyQty'=> $data['qtyRequest'.$i],
    					'branch_id'=> $data['toProjectId'],
    					'productId'=> $data['proId'.$i],
    					'costing'=> $data['costing'.$i],
    				);
    				$dbs->updateProductLocation($param);//Update Stock qty and new costing
    				$dbs->addProductHistoryQty($data['toProjectId'],$data['proId'.$i],5,$data['qtyRequest'.$i],$id);//movement'
					*/
    			}
    		}
    		$db->commit();
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    		Application_Form_FrmMessage::Sucessfull("INSERT_FAIL", "/stockinout/transferout/add",2);
    	}
    }
	
    function getDataRow($recordId){
    	$db = $this->getAdapter();
    	$sql=" SELECT trs.*,
			COALESCE((SELECT trsd.isCompleted FROM `st_transferstock_detail` AS trsd WHERE trsd.transferId =trs.id  ORDER BY trsd.isCompleted ASC LIMIT 1 ),0) AS isCompletedReceive
		FROM $this->_name AS trs WHERE trs.id=".$recordId;
    	
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	$sql.= $dbg->getAccessPermission('trs.fromProjectId');
    	
    	$sql.=" LIMIT 1";
    	return $db->fetchRow($sql);
    }
    function getDataAllRow($recordId){
    	$db = $this->getAdapter();
    	$this->_name='st_stockout_detail';
    	$sql=" SELECT 
    				 sd.*,
    				 (SELECT `proCode` FROM `st_product` where st_product.`proId`=sd.proId LIMIT 1) AS proCode,
					 (SELECT `proName` FROM `st_product` where st_product.`proId`=sd.proId LIMIT 1) AS proName
    		FROM $this->_name as sd WHERE sd.stockoutId=".$recordId." ";
    	return $db->fetchAll($sql);
    }
	
	
	function getDataRowDetail($recordId){
    	$db = $this->getAdapter();
    	$this->_name='st_transferstock_detail';
    	$sql=" SELECT 
    				 sd.*,
    				 (SELECT `proCode` FROM `st_product` where st_product.`proId`=sd.proId LIMIT 1) AS proCode
					 ,(SELECT `proName` FROM `st_product` where st_product.`proId`=sd.proId LIMIT 1) AS proName
					 ,(SELECT `measureLabel` FROM `st_product` where st_product.`proId`=sd.proId LIMIT 1) AS measureTitle 
					 ,(SELECT COALESCE(pl.qty,0) FROM st_product_location AS pl WHERE pl.proId=sd.proId AND pl.projectId= s.fromProjectId LIMIT 1) AS currentQty
					
    		FROM $this->_name as sd 
				JOIN st_transferstock AS s ON s.id = sd.transferId
			WHERE sd.transferId=".$recordId." ";
    	return $db->fetchAll($sql);
    }
	
	function checkTransferInReceived($recordId){
    	$db = $this->getAdapter();
    	$this->_name='st_transfer_receive';
    	$sql=" SELECT 
    				 sd.*
    		FROM $this->_name as sd 
			WHERE sd.transferId=".$recordId." AND sd.status=1 LIMIT 1 ";
    	return $db->fetchRow($sql);
    }
}