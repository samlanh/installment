<?php

class Stockinout_Model_DbTable_DbStockout extends Zend_Db_Table_Abstract
{
    protected $_name = 'st_stockout';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getAllUsageStock($search){
    	$sql="SELECT id,
				(SELECT project_name FROM `ln_project` WHERE br_id=so.projectId LIMIT 1) AS projectName,
				so.requestNo,
				so.reqOutNo,
				so.requestDate,
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
   
    function addUsageStock($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try
    	{
    		$param = array(
    				'branch_id'=>$data['branch_id'],
    				'tranType'=>1
    				);
    		$dbs = new Application_Model_DbTable_DbGlobalStock();
    		$requestStock = $dbs->generateRequestUsageNo($param);
    		
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
    		$stockId = $this->insert($arr);
    		
    		$ids = explode(',',$data['identity']);
    		if(!empty($ids)){
    			foreach($ids as $i){
    				$arr = array(
    					'stockoutId'=>$stockId,
    					'proId'=>$data['proId'.$i],
    					'qtyRequest'=>$data['qtyRequest'.$i],
    					'unitPrice'=>0,
    					'totalPrice'=>0,
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
    function upateUsageStock($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try
    	{
    		$dbs = new Application_Model_DbTable_DbGlobalStock();
    
    		$arr = array(
    				'projectId'=>$data['branch_id'],
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
    						'totalPrice'=>0,
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
    			
    			$dbs->DeleteProductHistoryQty($row['id'],3);
    		}
    		$where ='stockoutId='.$stockId;
    		$this->delete($where);	
    	}
    }
    function getDataRow($recordId){
    	$db = $this->getAdapter();
    	$this->_name='st_stockout';
    	$sql=" SELECT * FROM $this->_name WHERE tranType=1 AND id=".$recordId;
    	
    	$dbg = new Application_Model_DbTable_DbGlobal();
    	$sql.= $dbg->getAccessPermission('projectId');
    	
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
}