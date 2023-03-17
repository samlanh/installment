<?php
class Requesting_Model_DbTable_DbRequest extends Zend_Db_Table_Abstract
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
				(SELECT  CONCAT(COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.checkingBy LIMIT 1 ) AS checkingByName,
				
				CASE
					WHEN  rq.pCheckingStatus= 0 THEN '".$tr->translate("PENDING")."'
					WHEN  rq.pCheckingStatus = 1 THEN '".$tr->translate("APPROVED")."'
					WHEN  rq.pCheckingStatus = 2 THEN '".$tr->translate("REJECTED")."'
				END AS pCheckingStatus,
				(SELECT  CONCAT(COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.pCheckingBy LIMIT 1 ) AS pCheckingByName,
				
				CASE
					WHEN  rq.approveStatus= 0 THEN '".$tr->translate("PENDING")."'
					WHEN  rq.approveStatus = 1 THEN '".$tr->translate("APPROVED")."'
					WHEN  rq.approveStatus = 2 THEN '".$tr->translate("REJECTED")."'
				END AS approveStatus,
				(SELECT  CONCAT(COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.approveBy LIMIT 1 ) AS approveByName,
				
				
				
				(SELECT  CONCAT(COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.userId LIMIT 1 ) AS user_name
				
		";
		$dbGbSt = new Application_Model_DbTable_DbGlobalStock();
		$arrStep = array(
			'stepNum'=>"rq.processingStatus",
			'typeStep'=>3,
		);
		$sql.= $dbGbSt->requestingProccess($arrStep);
		$sql.=" ,(SELECT CASE
					WHEN COALESCE((SELECT sp.id FROM `st_purchasing` AS sp WHERE  sp.status = 1 AND sp.requestId =rqd.requestId LIMIT 1),0) = 0 
					THEN '".$tr->translate("NOT_YET_PO")."'
					WHEN  rqd.isCompletedPO = 1 THEN '".$tr->translate("COMPLETED_PO")."'
					ELSE   '".$tr->translate("UPCOMPLETED_PO")."'
					END 
				FROM `st_request_po_detail` AS rqd WHERE rqd.requestId =rq.id AND rqd.approvedStatus=1 ORDER BY rqd.isCompletedPO ASC LIMIT 1 ) AS isCompletedPO  ";
		$sql.=$dbGb->caseStatusShowImage("rq.status");
		
		$sql.=" FROM `st_request_po` AS rq WHERE 1 ";
		
    	$where = "";
		$from_date =(empty($search['start_date']))? '1': " rq.date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " rq.date <= '".$search['end_date']." 23:59:59'";
    	$where.= " AND ".$from_date." AND ".$to_date;
    	if(!empty($search['adv_search'])){
    		$s_where=array();
    		$s_search=addslashes(trim($search['adv_search']));
    		$s_where[]= " rq.requestNo LIKE '%{$s_search}%'";
    		$s_where[]= " rq.requestNoLetter LIKE '%{$s_search}%'";
    		$s_where[]= " rq.purpose LIKE '%{$s_search}%'";
			$s_where[]= " rq.note LIKE '%{$s_search}%'";
    		$s_where[]= " rq.checkingNote LIKE '%{$s_search}%'";
    		$s_where[]= " rq.pCheckingNote LIKE '%{$s_search}%'";
    		$s_where[]= " rq.approveNote LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ', $s_where).')';
    	}
		if(!empty($search['checkingStatus'])){
    		$where.= " AND rq.checkingStatus = ".$search['checkingStatus'];
    	}
		if(!empty($search['pCheckingStatus'])){
    		$where.= " AND rq.pCheckingStatus = ".$search['pCheckingStatus'];
    	}
		if(!empty($search['approveStatus'])){
    		$where.= " AND rq.approveStatus = ".$search['approveStatus'];
    	}
		if($search['status']>-1 AND $search['status']!=''){
    		$where.= " AND rq.status = ".$search['status'];
    	}
    	if(($search['branch_id'])>0){
    		$where.= " AND rq.projectId = ".$search['branch_id'];
    	}
		if(!empty($search['processingStatus'])){
    		$where.= " AND rq.processingStatus = ".$search['processingStatus'];
    	}
		if(($search['reqPOStatus'])>-1 AND $search['reqPOStatus']!=''){
    		$where.= " AND (SELECT rqd.isCompletedPO FROM `st_request_po_detail` AS rqd WHERE rqd.requestId =rq.id AND rqd.approvedStatus=1 ORDER BY rqd.isCompletedPO ASC LIMIT 1 )= ".$search['reqPOStatus'];
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
							'qtyVerify'		=>$data['qtyRequest'.$i],
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
							'qtyVerify'			=>$data['qtyRequest'.$i],
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
							'qtyVerify'			=>$data['qtyRequest'.$i],
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
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$sql=" 
		SELECT rq.*,
			(SELECT p.project_name FROM `ln_project` AS p WHERE p.br_id = rq.projectId LIMIT 1) AS branch_name
		FROM st_request_po AS rq WHERE 1 ";
		if (!empty($id)){
			$sql.=" AND id = $id ";
		}
		$sql.=$dbGb->getAccessPermission("rq.projectId");
		$sql.=" LIMIT 1 ";
		return $db->fetchRow($sql);
	}
	function getRequestPODetailById($rsData=null){
		$db = $this->getAdapter();
		
		$id=empty($rsData['id'])?0:$rsData['id'];
		$sql=" 	SELECT 
					rqd.*,p.proCode,
					p.proName,
					
					(SELECT COALESCE(SUM(pl.qty),0) FROM st_product_location AS pl WHERE pl.proId=p.proId LIMIT 1) AS currentQtyAllBranch,
					(SELECT COALESCE(pl.qty,0) FROM st_product_location AS pl WHERE pl.proId=p.proId AND pl.projectId= rq.projectId LIMIT 1) AS currentQty,
					(SELECT COALESCE(pod.unitPrice,0) FROM `st_purchasing_detail` AS pod WHERE pod.proId=p.proId ORDER BY pod.purchaseId DESC LIMIT 1) AS latestUnitPrice,
					p.measureLabel AS measureTitle
				";
				
		$sql.="		FROM 
					`st_request_po_detail` as rqd
					JOIN `st_request_po` AS rq ON rq.id = rqd.requestId
					LEFT JOIN `st_product` AS p  ON p.proId = rqd.proId 
				
			";
		$sql.="WHERE 1 AND rqd.requestId = $id";
		if (!empty($rsData['pCheckingRequest']) OR !empty($rsData['approvedrequest'])){
			$sql.=" AND rqd.adjustStatus = 1 ";
		}
		return $db->fetchAll($sql);
	}
	function getRequestPOInfoById($data=null){
		$db = $this->getAdapter();
		$tr=Application_Form_FrmLanguages::getCurrentlanguage();
		$sql="
			SELECT 
				rq.*,
				DATE_FORMAT(rq.date,'".DATE_FORMAT_FOR_SQL."') AS requestDateDMY,
				(SELECT p.project_name FROM `ln_project` AS p WHERE p.br_id = rq.projectId LIMIT 1) AS branch_name,
				CASE
					WHEN  rq.checkingStatus= 0 THEN '".$tr->translate("PENDING")."'
					WHEN  rq.checkingStatus = 1 THEN '".$tr->translate("APPROVED")."'
					WHEN  rq.checkingStatus = 2 THEN '".$tr->translate("REJECTED")."'
				END AS checkingStatusTitle,
				(SELECT  CONCAT(COALESCE(u.last_name,''),' ',COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.checkingBy LIMIT 1 ) AS checkingByName,
				
				CASE
					WHEN  rq.pCheckingStatus= 0 THEN '".$tr->translate("PENDING")."'
					WHEN  rq.pCheckingStatus = 1 THEN '".$tr->translate("APPROVED")."'
					WHEN  rq.pCheckingStatus = 2 THEN '".$tr->translate("REJECTED")."'
				END AS pCheckingStatusTitle,
				(SELECT  CONCAT(COALESCE(u.last_name,''),' ',COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.pCheckingBy LIMIT 1 ) AS pCheckingByName,
				
				CASE
					WHEN  rq.approveStatus= 0 THEN '".$tr->translate("PENDING")."'
					WHEN  rq.approveStatus = 1 THEN '".$tr->translate("APPROVED")."'
					WHEN  rq.approveStatus = 2 THEN '".$tr->translate("REJECTED")."'
				END AS approveStatusTitle,
				(SELECT  CONCAT(COALESCE(u.last_name,''),' ',COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.approveBy LIMIT 1 ) AS approveByName,
				(SELECT  CONCAT(COALESCE(u.last_name,''),' ',COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.userId LIMIT 1 ) AS requestByname
		";
		$sql.=" FROM st_request_po AS rq WHERE 1 ";
		if (!empty($data['requestId'])){
			$sql.=" AND rq.id = ".$data['requestId'] ;
		}
		$sql.=" LIMIT 1";
		return $db->fetchRow($sql);
	}
	
	function getRequestInfoHTML($data){
		
		$_row =$this->getRequestPOInfoById($data);
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
	
		$string="";
		$checkingstring="";
		$verifystring="";
		$popose="";
		if(!empty($_row)){
			$urlInfo = $baseUrl."/report/stockmg/request-info/id/".$_row['id'];
			if($_row['checkingStatus']>0){
				$checkingstring.='
					<div class="col-md-12 col-sm-12 col-xs-12">
								<span class="noteInfo"><i class="fa fa-file-text-o" aria-hidden="true"></i> '.$tr->translate("CHECKING_INFO").'</span>
					</div>
					<ul>
						<li title="'.$tr->translate("CHECKING_STATUS").'"><span class="lbl-tt">'.$tr->translate("CHECKING_STATUS").'</span>: <span class="colorValue">'.$_row['checkingStatusTitle'].'</span></li>
						<li title="'.$tr->translate("CHECKING_DATE").'"><span class="lbl-tt">'.$tr->translate("CHECKING_DATE").'</span>: <span class="colorValue">'.$_row['checkingDate'].'</span></li>
						<li title="'.$tr->translate("CHECKING_BY").'"><span class="lbl-tt">'.$tr->translate("CHECKING_BY").'</span>: <span class="colorValue">'.$_row['checkingByName'].'</span></li>
					</ul>
				';
			}
			if($_row['pCheckingStatus']>0){
				$verifystring.='
					<div class="col-md-12 col-sm-12 col-xs-12">
						<span class="noteInfo"><i class="fa fa-file-text-o" aria-hidden="true"></i> '.$tr->translate("VIEW_INFO").'</span>
					</div>
					<ul>
						<li title="'.$tr->translate("PCHECKING_STATUS").'"><span class="lbl-tt">'.$tr->translate("PCHECKING_STATUS").'</span>: <span class="colorValue">'.$_row['pCheckingStatusTitle'].'</span></li>
						<li title="'.$tr->translate("PCHECKING_DATE").'"><span class="lbl-tt">'.$tr->translate("PCHECKING_DATE").'</span>: <span class="colorValue">'.$_row['pCheckingDate'].'</span></li>
						<li title="'.$tr->translate("PCHECKING_BY").'"><span class="lbl-tt">'.$tr->translate("PCHECKING_BY").'</span>: <span class="colorValue">'.$_row['pCheckingByName'].'</span></li>
					</ul>
				';
				$popose.='
						<li title="'.$tr->translate("PURPOSE").'"><span class="lbl-tt"><i class="fa fa-hand-o-right" aria-hidden="true"></i>  '.$tr->translate("PURPOSE").'</span>: <span class="colorValue">'.$_row['purpose'].'</span></li>
						<li title="'.$tr->translate("NOTE").'"><span class="lbl-tt"><i class="fa fa-sticky-note" aria-hidden="true"></i>   '.$tr->translate("NOTE").'</span>: <span class="colorValue">'.$_row['note'].'</span></li>
				';
			}
			$string.='
				<div class="form-group" style=" padding: 10px; ">
					
					<div class="col-md-6 col-sm-6 col-xs-12">
						<div class="col-md-12 col-sm-12 col-xs-12">
							<span class="noteInfo"><i class="fa fa-file-text-o" aria-hidden="true"></i> '.$tr->translate("REQUEST_INFO").'</span>
						</div>
						<ul>
							<li title="'.$tr->translate("BRANCH_NAME").'"><span class="lbl-tt"><i class="fa fa-map-marker" aria-hidden="true"></i> '.$tr->translate("BRANCH_NAME").'</span>: <span class="colorValue">'.$_row['branch_name'].'</span></li>
							<li title="'.$tr->translate("REQUEST_NO").'"><span class="lbl-tt"><i class="fa fa-list-alt" aria-hidden="true"></i> '.$tr->translate("REQUEST_NO").'</span>: <span class="colorValue"><a title="'.$tr->translate("REQUEST_INFO_DETAIL").' - '.$_row['requestNo'].'" target="_blank" href="'.$urlInfo.'">'.$_row['requestNo'].'<a></span></li>
							<li title="'.$tr->translate("REQUEST_NO_FROM").'"><span class="lbl-tt"><i class="fa fa-file-text-o" aria-hidden="true"></i> '.$tr->translate("REQUEST_NO_FROM").'</span>: <span class="colorValue">'.$_row['requestNoLetter'].'</span></li>
							<li title="'.$tr->translate("REQUEST_DATE").'"><span class="lbl-tt"><i class="fa fa-calendar" aria-hidden="true"></i></span>: <span class="colorValue">'.date(DATE_FORMAT_FOR_PHP,strtotime($_row['date'])).'</span></li>
							<li title="'.$tr->translate("REQUEST_BY").'"><span class="lbl-tt"><i class="fa fa-user" aria-hidden="true"></i></span>: <span class="colorValue">'.$_row['requestByname'].'</span></li>
							'.$popose.'
						</ul>
					</div>
					<div class="col-md-6 col-sm-6 col-xs-12">
						'.$checkingstring.'
						'.$verifystring.'
					</div>
					<div class="clearfix"></div>
				</div>
			';
		}
		$arrayReturn = array(
			'rowResult'=>$_row,
			'htmlBlog'=>$string,
		);
		return $arrayReturn;
	}
	
	
}