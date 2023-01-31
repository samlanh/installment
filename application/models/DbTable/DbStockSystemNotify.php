<?php

class Application_Model_DbTable_DbStockSystemNotify extends Zend_Db_Table_Abstract
{
	
	function getNotifyRequest($_data=array()){
		$db=$this->getAdapter();
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$tr=Application_Form_FrmLanguages::getCurrentlanguage();
    	$sql="
			SELECT 
				'requestingRecord' AS recordType
				,rq.date AS recordDate
				,rq.*
				,CASE
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
				(SELECT p.project_name FROM `ln_project` AS p WHERE p.br_id = rq.projectId LIMIT 1) AS projectName,
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
	function getUnverifyReceiveDn($_data=array()){
		$db=$this->getAdapter();
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$tr=Application_Form_FrmLanguages::getCurrentlanguage();
    	$sql="
			SELECT 
				'unverifyReceiveDn' AS recordType
				,rst.receiveDate AS recordDate
				,(SELECT p.project_name FROM `ln_project` AS p WHERE p.br_id = rst.projectId LIMIT 1) AS projectName			
				,rst.*
				,rq.requestNo
				,rq.requestNoLetter
				,rq.purpose
				,rq.date AS requestDate
				,rq.requestNo

				,po.purchaseNo
				,(SELECT spl.supplierName FROM `st_supplier` AS spl WHERE spl.id = po.supplierId LIMIT 1) AS supplierName
				,po.purpose
				,po.date AS purchaseDate
				,(SELECT  CONCAT(COALESCE(u.last_name,''),' ',COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rst.userId LIMIT 1 ) AS userName
		";
		
	
		
		$sql.=" FROM  `st_receive_stock` AS rst 
					LEFT JOIN `st_request_po` AS rq ON rq.id = rst.requestId
					LEFT JOIN `st_purchasing` AS po ON po.id = rst.poId
			WHERE rst.status =1
			AND rst.verified =0 
			AND rst.isIssueInvoice=0
			AND rst.requestId >0
			";
			
		$sql.=$dbGb->getAccessPermission("rst.projectId");
    	$sql.=" ORDER BY rst.id DESC";
    	return $db->fetchAll($sql);
	}
	function getAllCombineNotification($_data=array()){
		
		$dbUser=new Application_Model_DbTable_DbUsers();
		$userAccessVerifyDN = $dbUser->getAccessUrl("stockinout","index",'verify');
		$userAccessTrn = $dbUser->getAccessUrl("stockinout","transferin",'add');
		
		$row = $this->getNotifyRequest($_data);
		
		if(!empty($userAccessVerifyDN)){
			$rRow = $this->getUnverifyReceiveDn($_data);
			$row = (object) array_merge((array) $rRow, (array) $row);//merg two object array list
			
			$row = (array) $row;//sort by key Value DESC
			usort($row, function ($a, $b) {return $a['recordDate'] < $b['recordDate'];});
		}
		
		if(!empty($userAccessTrn)){
			$rowTransfer = $this->getAllTransferStock($_data);
			$row = (object) array_merge((array) $rowTransfer, (array) $row);//merg two object array list
			
			$row = (array) $row;//sort by key Value DESC
			usort($row, function ($a, $b) {return $a['recordDate'] < $b['recordDate'];});
		}
		
		return $row;
	}
	function getNotifyRequestHtml($_data=array()){
		$row = $this->getAllCombineNotification($_data);
		$tr=Application_Form_FrmLanguages::getCurrentlanguage();
		$baseUrls = Zend_Controller_Front::getInstance()->getBaseUrl();
		
		$count = count($row);
		$string='
			<li class=" event title">
				<h4><i class="fa fa-bell-o "></i> '.$tr->translate("NOTIFICATION").'</h4>
			</li>
		';
		if(!empty($row)) foreach($row as $key=> $result){
			if($result['recordType']=="requestingRecord"){
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
				$requestLetter = $baseUrls."/report/stockmg/request-letter/id/";
				$string.='
					<li class=" event" title="'.$tr->translate("REQUESTING").'">
						<div class="media-body">
							<div class="media-body-left '.$result['recordType'].' col-md-3 col-sm-3 col-xs-12">
								<div class="noti-left-blg ">
									<div class="title-flex">
										<small>'.$result['projectName'].'</small>
										<i class="fa fa-file-text"></i>
									</div>
								</div>
							</div>
							<div class="media-body-right col-md-9 col-sm-9 col-xs-12">
								
								<span class="title" ><a class="letter-link" title="'.$tr->translate("REQUEST_LETTER").' - '.$result['requestNo'].'" href="'.$requestLetter.'">'.$result['requestNo'].'</a></span>
								<p><strong></strong> <i class="fa fa-calendar"></i> '.date("d/m/Y",strtotime($result['date'])).'</p>
								<p><strong><i class="fa fa-bullseye "></i> '.$result['requestNoLetter'].'</strong>  </p>
								<p><strong><i class="fa fa-newspaper-o"></i> '.$result['purpose'].'</strong>  </p>
								<p><strong></strong> <i class="fa fa-user"></i> '.$result['user_name'].'</p>
								<p class="proccessingStatus"><i class="fa fa-check-square"></i> '.$result['processingStatusTitle'].'</p>
								<a class="btn-go" href="'.$url.'"><i class="fa fa-location-arrow" aria-hidden="true"></i> '.$title.'</a>
									
							</div>
						</div>
					</li>
				';
			}else if($result['recordType']=="unverifyReceiveDn"){
				$dnLetter = $url=$baseUrls."/report/stockreport/rpt-receivestockdetail/id/".$result['id'];
				$urlVerify = $url=$baseUrls."/stockinout/index/verify/id/".$result['id'];
				$title=$tr->translate("VERIFY_DN_NOW");
				$string.='
					<li class=" event" title="'.$tr->translate("RECEIVE_DN").'">
						<div class="media-body">
							<div class="media-body-left '.$result['recordType'].' col-md-3 col-sm-3 col-xs-12">
								<div class="noti-left-blg ">
									<div class="title-flex">
										<small>'.$result['projectName'].'</small>
										<i class="fa fa-truck"></i> <i class="fa fa-check-circle-o " aria-hidden="true"></i>
									</div>
									
								</div>
							</div>
							<div class="media-body-right col-md-9 col-sm-9 col-xs-12">
								
								<span class="title" ><a class="letter-link" title="'.$tr->translate("DELIVERY_NOTE").' - '.$result['dnNumber'].'" href="'.$dnLetter.'">'.$result['dnNumber'].'</a></span>
								<p><strong></strong> <i class="fa fa-calendar"></i> '.date("d/m/Y",strtotime($result['recordDate'])).'</p>
								
								<p><strong><i class="fa fa-bullseye "></i> '.$result['requestNo'].'</strong>  </p>
								<p><strong><i class="fa fa-newspaper-o"></i> '.$result['purchaseNo'].'</strong>  </p>
								<p><strong></strong> <i class="fa fa-user"></i> '.$result['userName'].'</p>
								<a class="btn-go" href="'.$urlVerify.'"><i class="fa fa-check-square-o" aria-hidden="true"></i> '.$title.'</a>
									
							</div>
							
						</div>
					</li>
					';
			}else if($result['recordType']=="receiveTransfer"){
				
				$url="";
				$url=$baseUrls."/stockinout/transferin/add/id/".$result['id'];
				$title=$tr->translate("RECEIVE_NOW");
			
				
				$string.='
					<li class=" event" title="'.$tr->translate("RECEIVE_TRANSFER").'">
						<div class="media-body">
							<div class="media-body-left '.$result['recordType'].' col-md-3 col-sm-3 col-xs-12">
								<div class="noti-left-blg ">
									<div class="title-flex">
										<small>'.$result['projectName'].'</small>
										<i class="fa fa-cubes"></i> <i class="fa fa-compress " aria-hidden="true"></i>
									</div>
									
								</div>
							</div>
							<div class="media-body-right col-md-9 col-sm-9 col-xs-12">
								
								<span class="title" >'.$result['transferNo'].'</span>
								<p><strong></strong> <i class="fa fa-calendar"></i> '.$result['transferDateFormat'].'</p>
								<p><strong></strong> <i class="fa fa-truck"></i> <i class="fa fa-user"></i> '.$result['driver'].'</p>
								<p><strong></strong> <i class="fa fa-user"></i> '.$result['transferer'].'</p>
								
								<a class="btn-go" href="'.$url.'"><i class="glyphicon glyphicon-save-file" ></i> '.$title.'</a>
					
							</div>
						</div>
					</li>
				';
			}
			
		}
		
		$arrNoti = array(
			'notification' => $string,
			'counting'  => $count
		);
		return $arrNoti;
	}
	
	function getCountDNConcrete($data=array()){
		$db=$this->getAdapter();
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$tr=Application_Form_FrmLanguages::getCurrentlanguage();
		$sql="SELECT r.id,
				(SELECT p.project_name FROM `ln_project` AS p WHERE p.br_id = r.projectId LIMIT 1) AS projectName,
				(SELECT s.supplierName FROM `st_supplier` s WHERE s.id=r.supplierId LIMIT 1) AS supplierName,
				DATE_FORMAT(receiveDate,'%d-%m-%Y') as receiveDate,
				dnType,
				dnNUmber,
				staffCounter,
				driverName,
			   (SELECT first_name FROM rms_users WHERE id=r.userId LIMIT 1 ) AS user_name,
				workType
			FROM `st_receive_stock` r
				WHERE r.status=1 ";
		if(!empty($data['transactionType'])){
			$sql.=" AND r.transactionType=".$data['transactionType'];
		}
		if(isset($data['verified'])){
			$sql.=" AND r.verified=".$data['verified'];
		}
		if(isset($data['isclosed'])){
			$sql.=" AND (SELECT id FROM st_receive_stock_detail rd WHERE r.id = rd.receiveId AND `isclosed`=".$data['isclosed'].")";
		}
	
		$sql.=$dbGb->getAccessPermission("r.projectId");
		$sql.=" ORDER BY r.id DESC ";
		return $db->fetchAll($sql);
	}
	function getNotifyDNConcreteHtml($_data=array()){
		$row = $this->getCountDNConcrete($_data);
		$tr=Application_Form_FrmLanguages::getCurrentlanguage();
		$baseUrls = Zend_Controller_Front::getInstance()->getBaseUrl();
	
		$count = count($row);
		$string='
		<li class=" event title">
		<h4><i class="fa fa-file-text"></i> '.$tr->translate("DN_CONCRETE").'</h4>
		</li>
		';
		if(!empty($row)) foreach($row as $key=> $result){
			$url="";
			$url=$baseUrls."/po/directpo/check/id/".$result['id'];
			$titleStatus=$tr->translate("UNVERIFY");
			$title=$tr->translate("CLICK_TO_VERIFY");
				
			$string.='
			<li class=" event">
				<div class="media-body">
					<small>'.$result['projectName'].'</small><br />
					<span class="title" >'.$result['dnNUmber'].'</span>
					<p><strong></strong> <i class="fa fa-calendar"></i> '.$result['receiveDate'].'</p>
					<p><strong></strong> <i class="fa fa-user"></i> '.$result['user_name'].'</p>
					<p class="proccessingStatus"><i class="fa fa-check-square"></i> '.$titleStatus.'</p>
					<a class="btn-go" href="'.$url.'"><i class="fa fa-location-arrow" aria-hidden="true"></i> '.$title.'</a>
				</div>
			</li>';
		}
		$arrNoti = array(
				'notification' => $string,
				'counting'  => $count
		);
		return $arrNoti;
	}
	
	
	function getAllTransferStock($_data=array()){
		$db=$this->getAdapter();
		$dbGb = new Application_Model_DbTable_DbGlobal();
		$tr=Application_Form_FrmLanguages::getCurrentlanguage();
		$sql="SELECT 
				'receiveTransfer' AS recordType
				,trs.transferDate AS recordDate
				,trs.id
				,(SELECT p.project_name FROM `ln_project` AS p WHERE p.br_id = trs.fromProjectId LIMIT 1) AS projectName
				,(SELECT p.project_name FROM `ln_project` AS p WHERE p.br_id = trs.toProjectId LIMIT 1) AS toProjectName
				,trs.transferNo
				,trs.driver
				,trs.transferer
				,trs.transferDate
				,DATE_FORMAT(trs.transferDate,'".DATE_FORMAT_FOR_SQL."') As transferDateFormat
			FROM `st_transferstock` AS trs  WHERE trs.status=1 AND trs.isApproved=1 ";
		$sql.=" AND (SELECT trsd.isCompleted FROM `st_transferstock_detail` AS trsd WHERE trsd.transferId =trs.id   ORDER BY trsd.isCompleted ASC LIMIT 1 )=0 ";
	
	
		$sql.=$dbGb->getAccessPermission("trs.toProjectId");
		$sql.=" ORDER BY trs.id DESC ";
		return $db->fetchAll($sql);
	}
	
	/*
	function getNotifyTransferStockHtml($_data=array()){
		$row = $this->getAllTransferStock($_data);
		$tr=Application_Form_FrmLanguages::getCurrentlanguage();
		$baseUrls = Zend_Controller_Front::getInstance()->getBaseUrl();
	
		$count = count($row);
		$string='
		<li class=" event title">
		<h4><i class="fa fa-random"></i> '.$tr->translate("TRANSFER_STOCK").'</h4>
		</li>
		';
		if(!empty($row)) foreach($row as $key=> $result){
			$url="";
			$url=$baseUrls."/stockinout/transferin/add/id/".$result['id'];
		
			$title=$tr->translate("RECEIVE_NOW");
				
			$string.='
			<li class=" event" title="'.$result['projectName'].' => '.$result['toProjectName'].' '.$result['transferNo'].'">
				<div class="media-body">
					<small>'.$result['projectName'].' <i class="fa fa-long-arrow-right" aria-hidden="true"></i> <span class="text-info bold">'.$result['toProjectName'].'<span></small><br />
					<span class="title" >'.$result['transferNo'].'</span>
					<p><strong></strong> <i class="fa fa-calendar"></i> '.$result['transferDateFormat'].'</p>
					<p><strong></strong> <i class="fa fa-truck"></i> <i class="fa fa-user"></i> '.$result['driver'].'</p>
					<p><strong></strong> <i class="fa fa-user"></i> '.$result['transferer'].'</p>
					
					<a class="btn-go" href="'.$url.'"><i class="glyphicon glyphicon-save-file" ></i> '.$title.'</a>
				</div>
			</li>';
		}
		$arrNoti = array(
				'notification' => $string,
				'counting'  => $count
		);
		return $arrNoti;
	}
	*/
}
?>