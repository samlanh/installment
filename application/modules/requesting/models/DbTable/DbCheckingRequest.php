<?php
class Requesting_Model_DbTable_DbCheckingRequest extends Zend_Db_Table_Abstract
{
	protected $_name = 'st_request_po';
	
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    public function getAllCheckingRequestPO($search){
    	$db= $this->getAdapter();
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$tr=Application_Form_FrmLanguages::getCurrentlanguage();
    	$sql="
			SELECT 
				rq.id,
				(SELECT p.project_name FROM `ln_project` AS p WHERE p.br_id = rq.projectId LIMIT 1) AS branch_name,
				rq.requestNo,
				rq.requestNoLetter,
				rq.purpose,
				rq.date,
				rq.checkingDate,
				CASE
					WHEN  rq.pCheckingStatus= 0 THEN '".$tr->translate("PENDING")."'
					WHEN  rq.pCheckingStatus = 1 THEN '".$tr->translate("APPROVED")."'
					WHEN  rq.pCheckingStatus = 2 THEN '".$tr->translate("REJECTED")."'
				END AS pCheckingStatus,
				(SELECT  CONCAT(COALESCE(u.last_name,''),' ',COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.pCheckingBy LIMIT 1 ) AS pCheckingByName,
				CASE
					WHEN  rq.checkingStatus= 0 THEN '".$tr->translate("PENDING")."'
					WHEN  rq.checkingStatus = 1 THEN '".$tr->translate("APPROVED")."'
					WHEN  rq.checkingStatus = 2 THEN '".$tr->translate("REJECTED")."'
				END AS checkingStatus,
				(SELECT  CONCAT(COALESCE(u.last_name,''),' ',COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.checkingBy LIMIT 1 ) AS checkingByName
		";
		$sql.=" FROM `st_request_po` AS rq WHERE rq.checkingBy>0 AND rq.checkingStatus>0 ";
		
    	$where = "";
		$from_date =(empty($search['start_date']))? '1': " rq.checkingDate >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " rq.checkingDate <= '".$search['end_date']." 23:59:59'";
    	$where.= " AND ".$from_date." AND ".$to_date;
    	if(!empty($search['search'])){
    		$s_where=array();
    		$s_search=addslashes(trim($search['search']));
    		$s_where[]= " rq.requestNo LIKE '%{$s_search}%'";
    		$s_where[]= " rq.requestNoLetter LIKE '%{$s_search}%'";
    		$s_where[]= " rq.purpose LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
		if(!empty($search['checkingStatus'])){
    		$where.= " AND rq.checkingStatus = ".$search['checkingStatus'];
    	}
		if(!empty($search['pCheckingStatus'])){
    		$where.= " AND rq.pCheckingStatus = ".$search['pCheckingStatus'];
    	}
    	if(($search['branch_id'])>0){
    		$where.= " AND rq.projectId = ".$search['branch_id'];
    	}
		$where.=$dbGb->getAccessPermission("rq.projectId");
    	$order=" ORDER BY rq.id DESC";
    	return $db->fetchAll($sql.$where.$order);
    }
	
	public function checkingRequestPO($data){
		$db= $this->getAdapter();
		try{
			$id =$data['id'];
			$dbReq = new Requesting_Model_DbTable_DbRequest();
			$thisRow = $dbReq->getRequestPOById($id);
			
			$dbGbSt = new Application_Model_DbTable_DbGlobalStock();
			$arrStep = array(
				'stepNum'=>$data['stepNum'],
				'typeStep'=>1,
			);
			$processingStatus = $dbGbSt->requestingProccess($arrStep);
			
			$arr = array(
    				'checkingNote'			=>$data['checkingNote'],
    				'checkingDate'			=>$data['checkingDate'],
    				'checkingStatus'		=>$data['checkingStatus'],
    				'checkingModifyDate'	=>date("Y-m-d H:i:s"),
    				'checkingBy'			=>$this->getUserId(),
    				'processingStatus'		=>$processingStatus,//Warehouse Step checking Approved/Rejected
    		);
			if(empty($thisRow['checkingBy'])){
				$arr['checkingCreateDate']=date("Y-m-d H:i:s");
			}
    		$this->_name='st_request_po';
			$where=" id = ".$data['id'];
			$this->update($arr, $where);
			
			
			if(!empty($data['identity'])){
				$ids = explode(',', $data['identity']);
				foreach ($ids as $i){
					if (!empty($data['detailId'.$i])){
						$arr = array(
							'requestId'			=>$id,
							'proId'				=>$data['proId'.$i],
							
							'qtyAdjust'			=>$data['qtyAdjust'.$i],
							'qtyApproved'		=>$data['qtyAdjust'.$i],
							
							'dateReqStockIn'	=>$data['dateReqStockIn'.$i],
							'note'				=>$data['note'.$i],
							'adjustStatus'		=>$data['adjustStatus'.$i],
						);
						
							
							
						$this->_name='st_request_po_detail';
						$where =" id =".$data['detailId'.$i];
						$this->update($arr, $where);
					}
				}
			}
    	}catch(Exception $e){
	    	Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
    	}
	}
	

	
	
}