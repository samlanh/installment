<?php

class Stockinout_Model_DbTable_DbStockout extends Zend_Db_Table_Abstract
{
    protected $_name = 'st_stockout';
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
								
			FROM `st_stockout` as so where so.tranType=1 ";
    	
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
    	$order=' ORDER BY so.id DESC  ';
    	$db = $this->getAdapter();
    	return $db->fetchAll($sql.$where_date.$where.$order);
    }
   
    function addUsageStock($data){
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
    		Application_Form_FrmMessage::Sucessfull("INSERT_FAIL", "/stockinout/usage/add");
    	}
    }
//     function updateWorker($data){
//     	try
//     	{
//     		$arr = array(
//     				'projectId'=>$data['branch_id'],
//     				'staffName'=>$data['staffName'],
//     				'gender'=>$data['gender'],
//     				'position'=>$data['position'],
//     				'tel'=>$data['tel'],
//     				'pob'=>$data['pob'],
//     				'dob'=>$data['dob'],
//     				'address'=>$data['address'],
//     				'createDate'=>date("Y-m-d"),
//     				'status'=>$data['status'],
//     				'userId'=>$this->getUserId()
//     			);	;
    		
//     		$where = 'id = '.$data['id'];
// 			$this->update($arr, $where);
			
//     	}catch (Exception $e){
//     		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
//     		Application_Form_FrmMessage::Sucessfull("UPDATE_FAIL", "/stockinout/staff/index");
//     	}
//     }
    function getDataRow($recordId){
    	$db = $this->getAdapter();
    	$sql=" SELECT * FROM $this->_name WHERE id=".$recordId." LIMIT 1";
    	return $db->fetchRow($sql);
    }
}