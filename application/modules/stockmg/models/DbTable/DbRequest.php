<?php
class Stockmg_Model_DbTable_DbRequest extends Zend_Db_Table_Abstract
{
	protected $_name = 'rms_interestsetting';
	
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    public function getAllRequestPO($search){
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
				CASE
					WHEN  rq.checkingStatus= 0 THEN '".$tr->translate("PENDING")."'
					WHEN  rq.checkingStatus = 1 THEN '".$tr->translate("APPROVED")."'
					WHEN  rq.checkingStatus = 2 THEN '".$tr->translate("REJECTED")."'
				END AS checkingStatus,
				(SELECT  CONCAT(COALESCE(u.last_name,''),' ',COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.checkingBy LIMIT 1 ) AS checkingByName,
				(SELECT  CONCAT(COALESCE(u.last_name,''),' ',COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.userId LIMIT 1 ) AS user_name
		";
		$sql.=$dbGb->caseStatusShowImage("rq.status");
		$sql.=" FROM `st_request_po` AS rq WHERE 1 ";
		
    	$where = "";
		$from_date =(empty($search['start_date']))? '1': " rq.date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " rq.date <= '".$search['end_date']." 23:59:59'";
    	$where.= " AND ".$from_date." AND ".$to_date;
    	if(!empty($search['search'])){
    		$s_where=array();
    		$s_search=addslashes(trim($search['search']));
    		$s_where[]= " rq.requestNo LIKE '%{$s_search}%'";
    		$s_where[]= " rq.requestNoLetter LIKE '%{$s_search}%'";
    		$s_where[]= " rq.purpose LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
		if($search['status']>-1){
    		$where.= " AND rq.status = ".$search['status'];
    	}
    	if(($search['branch_id'])>0){
    		$where.= " AND rq.projectId = ".$search['branch_id'];
    	}
		$where.=$dbGb->getAccessPermission("rq.projectId");
    	$order=" ORDER BY rq.id DESC";
    	return $db->fetchAll($sql.$where.$order);
    }
	public function addRequestPO($data){
    	$db= $this->getAdapter();
    	try{
			$dbGBstock = new Application_Model_DbTable_DbGlobalStock();
			$data['dateRequest']=$data['date'];
			$requestNo =$dbGBstock->generateRequestNo($data);
			
    		$arr = array(
    				'projectId'			=>$data['branch_id'],
    				'requestNo'			=>$requestNo,
    				'requestNoLetter'	=>$data['requestNoLetter'],
    				'purpose'			=>$data['purpose'],
    				'date'				=>$data['date'],
    				'note'				=>$data['note'],
										
    				'status'			=>1,
    				'createDate'		=>date("Y-m-d H:i:s"),
    				'modifyDate'		=>date("Y-m-d H:i:s"),
    				'userId'			=>$this->getUserId(),
    		);
    		$this->_name='st_request_po';
    		$id = $this->insert($arr);
    		
    		if(!empty($data['identity'])){
				$ids = explode(',', $data['identity']);
				foreach ($ids as $i){
					$arr = array(
							'requestId'		=>$id,
							'proId'			=>$data['proId'.$i],
							
							'qtyRequest'	=>$data['qtyRequest'.$i],
							'qtyAdjust'		=>$data['qtyRequest'.$i],
							'qtyApproved'	=>$data['qtyRequest'.$i],
							
							'dateReqStockIn'		=>$data['dateReqStockIn'.$i],
							'note'			=>$data['note'.$i],
							
							'createDate'	=>date("Y-m-d H:i:s"),
							'modifyDate'	=>date("Y-m-d H:i:s"),
							'userId'		=>$this->getUserId(),
						);
					$this->_name='st_request_po_detail';	
					$this->insert($arr);
				}
    		}
    	}catch(Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
    	}
	}
	
	public function editRequestPO($data){
		$db= $this->getAdapter();
		try{
			$arr = array(
    				'projectId'			=>$data['branch_id'],
    				'requestNoLetter'	=>$data['requestNoLetter'],
    				'purpose'			=>$data['purpose'],
    				'date'				=>$data['date'],
    				'note'				=>$data['note'],
										
    				'status'			=>$data['status'],
    				'modifyDate'		=>date("Y-m-d H:i:s"),
    				'userId'			=>$this->getUserId(),
    		);
    		$this->_name='st_request_po';
			$where=" id = ".$data['id'];
			$this->update($arr, $where);
			
			$id = $data['id'];
			
			$identitys = explode(',',$data['identity']);
			$detailId="";
			if (!empty($identitys)){
				foreach ($identitys as $i){
					if (empty($detailId)){
						if (!empty($data['detailId'.$i])){
							$detailId = $data['detailId'.$i];
						}
					}else{
						if (!empty($data['detailId'.$i])){
							$detailId= $detailId.",".$data['detailId'.$i];
						}
					}
				}
			}
			$this->_name='st_request_po_detail';
			$where = 'requestId = '.$id;
			if (!empty($detailId)){
				$where.=" AND id NOT IN ($detailId) ";
			}
			$this->delete($where);
			
			if(!empty($data['identity'])){
				$ids = explode(',', $data['identity']);
				foreach ($ids as $i){
					if (!empty($data['detailId'.$i])){
						$arr = array(
							'requestId'			=>$id,
							'proId'				=>$data['proId'.$i],
								
							'qtyRequest'		=>$data['qtyRequest'.$i],
							'qtyAdjust'			=>$data['qtyRequest'.$i],
							'qtyApproved'		=>$data['qtyRequest'.$i],
							
							'dateReqStockIn'	=>$data['dateReqStockIn'.$i],
							'note'				=>$data['note'.$i],
							
							'modifyDate'		=>date("Y-m-d H:i:s"),
							'userId'			=>$this->getUserId(),
						);
						
							
							
						$this->_name='st_request_po_detail';
						$where =" id =".$data['detailId'.$i];
						$this->update($arr, $where);
					}else{
						$arr = array(
							'requestId'		=>$id,
							'proId'			=>$data['proId'.$i],
							
							'qtyRequest'	=>$data['qtyRequest'.$i],
							'qtyAdjust'		=>$data['qtyRequest'.$i],
							'qtyApproved'	=>$data['qtyRequest'.$i],
							
							'dateReqStockIn'		=>$data['dateReqStockIn'.$i],
							'note'			=>$data['note'.$i],
							
							'createDate'	=>date("Y-m-d H:i:s"),
							'modifyDate'	=>date("Y-m-d H:i:s"),
							'userId'		=>$this->getUserId(),
						);
						$this->_name='st_request_po_detail';
						$this->insert($arr);
					}
				}
			}
    	}catch(Exception $e){
	    	Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
    	}
	}
	function getRequestPOById($id=null){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM st_request_po WHERE 1 ";
		if (!empty($id)){
			$sql.=" AND id = $id LIMIT 1";
		}
		return $db->fetchRow($sql);
	}
	function getRequestPODetailById($rsData=null){
		$db = $this->getAdapter();
		
		$id=empty($rsData['id'])?0:$rsData['id'];
		$sql=" 	SELECT 
					rqd.*,p.proCode,
					p.proName,
					0 AS currentQty,
					'Kg' AS measureTitle 
				FROM `st_request_po_detail` as rqd, `st_product` AS p WHERE p.proId = rqd.proId ";
		if (!empty($id)){
			$sql.=" AND rqd.requestId = $id";
		}
		if (!empty($rsData['pCheckingRequest']) OR !empty($rsData['approvedrequest'])){
			$sql.=" AND rqd.adjustStatus = 1 ";
		}
		return $db->fetchAll($sql);
	}
	
	
	public function checkingRequestPO($data){
		$db= $this->getAdapter();
		try{
			$id =$data['id'];
			$thisRow = $this->getRequestPOById($id);
			$arr = array(
    				'checkingNote'			=>$data['checkingNote'],
    				'checkingDate'			=>$data['checkingDate'],
    				'checkingStatus'		=>$data['checkingStatus'],
    				'checkingModifyDate'	=>date("Y-m-d H:i:s"),
    				'checkingBy'			=>$this->getUserId(),
    				'processingStatus'		=>1,//Warehouse Step checking Approved/Rejected
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
	
	public function pCheckingRequestPO($data){
		$db= $this->getAdapter();
		try{
			$id =$data['id'];
			$thisRow = $this->getRequestPOById($id);
			$arr = array(
    				'pCheckingNote'			=>$data['pCheckingNote'],
    				'pCheckingDate'			=>$data['pCheckingDate'],
    				'pCheckingStatus'		=>$data['pCheckingStatus'],
    				'pCheckingModifyDate'	=>date("Y-m-d H:i:s"),
    				'pCheckingBy'			=>$this->getUserId(),
					'processingStatus'		=>2,//Purchase Dept Step checking Approved/Rejected
    		);
			if(empty($thisRow['pCheckingBy'])){
				$arr['pCheckingCreateDate']=date("Y-m-d H:i:s");
			}
    		$this->_name='st_request_po';
			$where=" id = ".$data['id'];
			$this->update($arr, $where);

    	}catch(Exception $e){
	    	Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
    	}
	}
	
	public function approvedRequestPO($data){
		$db= $this->getAdapter();
		try{
			$id =$data['id'];
			$thisRow = $this->getRequestPOById($id);
			$arr = array(
    				'approveNote'			=>$data['approveNote'],
    				'approveDate'			=>$data['approveDate'],
    				'approveStatus'			=>$data['approveStatus'],
    				'approveModifyDate'		=>date("Y-m-d H:i:s"),
    				'approveBy'				=>$this->getUserId(),
					'processingStatus'		=>3,//Admin/GM Step checking Approved/Rejected
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
						$arr = array(
							'requestId'			=>$id,
							'proId'				=>$data['proId'.$i],
								
							'qtyApproved'		=>$data['qtyApproved'.$i],
							'qtyApprovedAfter'	=>$data['qtyApproved'.$i],
							
							'note'				=>$data['note'.$i],
							'approvedStatus'	=>$data['approvedStatus'.$i],
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