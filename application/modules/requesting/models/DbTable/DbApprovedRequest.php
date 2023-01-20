<?php
class Requesting_Model_DbTable_DbApprovedRequest extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_interestsetting';
	
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    public function getAllApprovedRequestPO($search){
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
				rq.approveDate,
				CASE
					WHEN  rq.approveStatus= 0 THEN '".$tr->translate("PENDING")."'
					WHEN  rq.approveStatus = 1 THEN '".$tr->translate("APPROVED")."'
					WHEN  rq.approveStatus = 2 THEN '".$tr->translate("REJECTED")."'
				END AS approveStatus,
				(SELECT  CONCAT(COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.approveBy LIMIT 1 ) AS approveByName
		";
		$sql.=" FROM `st_request_po` AS rq WHERE rq.approveBy>0 AND rq.approveStatus>0 ";
		
    	$where = "";
		$from_date =(empty($search['start_date']))? '1': " rq.approveDate >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " rq.approveDate <= '".$search['end_date']." 23:59:59'";
    	$where.= " AND ".$from_date." AND ".$to_date;
    	if(!empty($search['adv_search'])){
    		$s_where=array();
    		$s_search=addslashes(trim($search['adv_search']));
    		$s_where[]= " rq.requestNo LIKE '%{$s_search}%'";
    		$s_where[]= " rq.requestNoLetter LIKE '%{$s_search}%'";
    		$s_where[]= " rq.purpose LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
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
	
	
	public function approvedRequestPO($data){
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
    				'approveNote'			=>$data['approveNote'],
    				'approveDate'			=>$data['approveDate'],
    				'approveStatus'			=>$data['approveStatus'],
    				'approveModifyDate'		=>date("Y-m-d H:i:s"),
    				'approveBy'				=>$this->getUserId(),
					'processingStatus'		=>$processingStatus,//Admin/GM Step checking Approved/Rejected
    		);
			if(empty($thisRow['approveBy'])){
				$arr['approveCreateDate']=date("Y-m-d H:i:s");
			}
    		$this->_name='st_request_po';
			$where=" id = ".$data['id'];
			$this->update($arr, $where);
			
			
			if(!empty($data['identity'])){
				$ids = explode(',', $data['identity']);
				foreach ($ids as $i){
					if (!empty($data['detailId'.$i])){
						
						$approveStatus = $data['approvedStatus'.$i];
						if($data['approveStatus']==2){ //REJECTED
							$approveStatus = $data['approvedStatus'];
						}
						
						$arr = array(
							'requestId'			=>$id,
							'proId'				=>$data['proId'.$i],
								
							'qtyApproved'		=>$data['qtyApproved'.$i],
							'qtyApprovedAfter'	=>$data['qtyApproved'.$i],
							
							//'note'				=>$data['note'.$i],
							'approvedStatus'	=>$approveStatus,
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