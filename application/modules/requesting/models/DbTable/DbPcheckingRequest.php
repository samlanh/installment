<?php
class Requesting_Model_DbTable_DbPcheckingRequest extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_interestsetting';
	
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    public function getAllPcheckingRequestPO($search){
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
				rq.pCheckingDate,
				CASE
					WHEN  rq.approveStatus= 0 THEN '".$tr->translate("PENDING")."'
					WHEN  rq.approveStatus = 1 THEN '".$tr->translate("APPROVED")."'
					WHEN  rq.approveStatus = 2 THEN '".$tr->translate("REJECTED")."'
				END AS approveStatus,
				(SELECT  CONCAT(COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.approveBy LIMIT 1 ) AS approveByName,
				CASE
					WHEN  rq.pCheckingStatus= 0 THEN '".$tr->translate("PENDING")."'
					WHEN  rq.pCheckingStatus = 1 THEN '".$tr->translate("APPROVED")."'
					WHEN  rq.pCheckingStatus = 2 THEN '".$tr->translate("REJECTED")."'
				END AS pCheckingStatus,
				(SELECT  CONCAT(COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.pCheckingBy LIMIT 1 ) AS pCheckingByName
		";
		$sql.=" FROM `st_request_po` AS rq WHERE rq.pCheckingBy>0 AND rq.pCheckingStatus>0 ";
		
    	$where = "";
		$from_date =(empty($search['start_date']))? '1': " rq.pCheckingDate >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " rq.pCheckingDate <= '".$search['end_date']." 23:59:59'";
    	$where.= " AND ".$from_date." AND ".$to_date;
    	if(!empty($search['adv_search'])){
    		$s_where=array();
    		$s_search=addslashes(trim($search['adv_search']));
    		$s_where[]= " rq.requestNo LIKE '%{$s_search}%'";
    		$s_where[]= " rq.requestNoLetter LIKE '%{$s_search}%'";
    		$s_where[]= " rq.purpose LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
		if(!empty($search['pCheckingStatus'])){
    		$where.= " AND rq.pCheckingStatus = ".$search['pCheckingStatus'];
    	}
		if(!empty($search['approveStatus'])){
    		$where.= " AND rq.approveStatus = ".$search['approveStatus'];
    	}
    	if(($search['branch_id'])>0){
    		$where.= " AND rq.projectId = ".$search['branch_id'];
    	}
		$where.=$dbGb->getAccessPermission("rq.projectId");
    	$order=" ORDER BY rq.id DESC";
    	return $db->fetchAll($sql.$where.$order);
    }
	
	
	public function pCheckingRequestPO($data){
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
    				'pCheckingNote'			=>$data['pCheckingNote'],
    				'pCheckingDate'			=>$data['pCheckingDate'],
    				'pCheckingStatus'		=>$data['pCheckingStatus'],
    				'pCheckingModifyDate'	=>date("Y-m-d H:i:s"),
    				'pCheckingBy'			=>$this->getUserId(),
					'processingStatus'		=>$processingStatus,//Purchase Dept Step checking Approved/Rejected
    		);
			if(empty($thisRow['pCheckingBy'])){
				$arr['pCheckingCreateDate']=date("Y-m-d H:i:s");
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
							
							'qtyVerify'			=>$data['qtyVerify'.$i],
							'qtyApproved'		=>$data['qtyVerify'.$i],
							
							'verifyStatus'		=>$data['verifyStatus'.$i],
							
							'verifyNote'				=>$data['verifyNote'.$i],
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