<?php

class Application_Model_DbTable_DbStockSystemNotify extends Zend_Db_Table_Abstract
{
	
	function getNotifyRequest($_data=array()){
		$db=$this->getAdapter();
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$tr=Application_Form_FrmLanguages::getCurrentlanguage();
    	$sql="
			SELECT 
				rq.*,
				CASE
					WHEN  rq.checkingStatus= 0 THEN '".$tr->translate("PENDING")."'
					WHEN  rq.checkingStatus = 1 THEN '".$tr->translate("APPROVED")."'
					WHEN  rq.checkingStatus = 2 THEN '".$tr->translate("REJECTED")."'
				END AS checkingStatusTitle,
				CASE
					WHEN  rq.pCheckingStatus= 0 THEN '".$tr->translate("PENDING")."'
					WHEN  rq.pCheckingStatus = 1 THEN '".$tr->translate("APPROVED")."'
					WHEN  rq.pCheckingStatus = 2 THEN '".$tr->translate("REJECTED")."'
				END AS pCheckingStatusTitle,
				CASE
					WHEN  rq.approveStatus= 0 THEN '".$tr->translate("PENDING")."'
					WHEN  rq.approveStatus = 1 THEN '".$tr->translate("APPROVED")."'
					WHEN  rq.approveStatus = 2 THEN '".$tr->translate("REJECTED")."'
				END AS approveStatusTitle,
				(SELECT p.project_name FROM `ln_project` AS p WHERE p.br_id = rq.projectId LIMIT 1) AS branch_name,
				(SELECT  CONCAT(COALESCE(u.last_name,''),' ',COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.checkingBy LIMIT 1 ) AS checkingByName,
				(SELECT  CONCAT(COALESCE(u.last_name,''),' ',COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.pCheckingBy LIMIT 1 ) AS pCheckingByName,
				(SELECT  CONCAT(COALESCE(u.last_name,''),' ',COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.approveBy LIMIT 1 ) AS approveByName,
				(SELECT  CONCAT(COALESCE(u.last_name,''),' ',COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.userId LIMIT 1 ) AS user_name	
		";
		
		$dbGbSt = new Application_Model_DbTable_DbGlobalStock();
		$arrStep = array(
			'stepNum'=>"rq.processingStatus",
			'typeStep'=>3,
		);
		$sql.= $dbGbSt->requestingProccess($arrStep);
		$sql.=" ,(SELECT rqd.isCompletedPO FROM `st_request_po_detail` AS rqd WHERE rqd.requestId =rq.id AND rqd.approvedStatus=1 ORDER BY rqd.isCompletedPO ASC LIMIT 1 ) AS isCompletedPO  ";
		
		
		$sql.=" FROM `st_request_po` AS rq WHERE rq.status=1 ";
		
		$dbUser = new Application_Model_DbTable_DbUsers();
		$processingStatus=null;
		$rs = $dbUser->getAccessUrl("requesting","checkingrequest","add");
		if(!empty($rs)){
			if(is_null($processingStatus)){
				$processingStatus =1; //forWarehouse
			}
			
		}
		$rs = $dbUser->getAccessUrl("requesting","pcheckingrequest","add");
		if(!empty($rs)){
			//forPurchaseDept
			if(is_null($processingStatus)){
				$processingStatus =2; 
			}else{
				$processingStatus =$processingStatus.",2";
			}
			$sql.=" AND rq.checkingStatus!=2 ";
		}
		$rs = $dbUser->getAccessUrl("requesting","approvedrequest","add");
		if(!empty($rs)){
			//forApproved
			if(is_null($processingStatus)){
				$processingStatus =3; 
			}else{
				$processingStatus =$processingStatus.",3";
			}
			$sql.=" AND rq.pCheckingStatus!=2 ";
		}
		
		$rs = $dbUser->getAccessUrl("po","index","add");
		if(!empty($rs)){
			//forPurchasing
			if(is_null($processingStatus)){
				$processingStatus ="4,5"; 
			}else{
				$processingStatus =$processingStatus.",4,5";
			}
			$sql.=" AND rq.approveStatus!=2 "; 
			$sql.= " AND (SELECT rqd.isCompletedPO FROM `st_request_po_detail` AS rqd WHERE rqd.requestId =rq.id AND rqd.approvedStatus!=2 ORDER BY rqd.isCompletedPO  ASC LIMIT 1 )= 0 ";
    	
		}
		
		if(is_null($processingStatus)){
			return array();
		}else{
			$sql.=" AND rq.processingStatus IN ($processingStatus) ";
		}
	

		$sql.=$dbGb->getAccessPermission("rq.projectId");
    	$sql.=" ORDER BY rq.processingStatus ASC, rq.id DESC";
    	return $db->fetchAll($sql);
	}
	
	function getNotifyRequestHtml($_data=array()){
		$row = $this->getNotifyRequest($_data);
		$tr=Application_Form_FrmLanguages::getCurrentlanguage();
		$baseUrls = Zend_Controller_Front::getInstance()->getBaseUrl();
		
		$count = count($row);
		$string='
			<li class=" event title">
				<h4><i class="fa fa-file-text"></i> '.$tr->translate("REQUEST_PO").'</h4>
			</li>
		';
		if(!empty($row)) foreach($row as $key=> $result){
			$url="";
			$title="";
			if($result['processingStatus']==1){
				$url=$baseUrls."/requesting/checkingrequest/add/id/".$result['id'];
				$title=$tr->translate("MAKE_CHECKING_REQUEST_PO");
			}elseif($result['processingStatus']==2){
				$url=$baseUrls."/requesting/pcheckingrequest/add/id/".$result['id'];
				$title=$tr->translate("MAKE_PCHECKING_REQUEST_PO");
			}elseif($result['processingStatus']==3){
				$url=$baseUrls."/requesting/approvedrequest/add/id/".$result['id'];
				$title=$tr->translate("MAKE_APPROVED_REQUEST_PO");
			}elseif($result['processingStatus']==4){
				$url=$baseUrls."/po/index/add/id/".$result['id'];
				$title=$tr->translate("MAKING_PURCHASE_REQUEST_PO");
			}elseif($result['processingStatus']==5){
				if($result['isCompletedPO']==0){
					$url=$baseUrls."/po/index/add/id/".$result['id'];
					$title=$tr->translate("MAKING_PURCHASE_REQUEST_PO")." (".$tr->translate("Continue").")";
				}
			}
			
			$string.='
				<li class=" event">
					<div class="media-body">
						<small>'.$result['branch_name'].'</small><br />
						<span class="title" >'.$result['requestNo'].'</span>
						<p><strong><i class="fa fa-newspaper-o"></i> '.$result['purpose'].'</strong>  </p>
						<p><strong></strong> <i class="fa fa-calendar"></i> '.date("d/m/Y",strtotime($result['date'])).'</p>
						<p><strong></strong> <i class="fa fa-user"></i> '.$result['user_name'].'</p>
						<p class="proccessingStatus"><i class="fa fa-check-square"></i> '.$result['processingStatusTitle'].'</p>
						<a class="btn-go" href="'.$url.'"><i class="fa fa-location-arrow" aria-hidden="true"></i> '.$title.'</a>
								
					</div>
				</li>
			';
		}
		
		$arrNoti = array(
			'notification' => $string,
			'counting'  => $count
		);
		return $arrNoti;
	}
	
	
}
?>