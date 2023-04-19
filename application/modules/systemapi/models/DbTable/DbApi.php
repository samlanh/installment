<?php

class Systemapi_Model_DbTable_DbApi extends Zend_Db_Table_Abstract
{
	function getUserLogin($_data){
		$db = $this->getAdapter();
		$_data['userName']=trim($_data['userName']);
		$_data['password']=trim($_data['password']);
		try{
			$sql =" SELECT
				s.*
				,s.user_type AS userType
				,s.userAction AS userAction
				,s.photo AS photo
				,s.branch_list AS branchList
				,CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.first_name,'')) as userName
			FROM
				rms_users AS s
			WHERE s.active = 1 ";
			$sql.= " AND ".$db->quoteInto('s.user_name=?', $_data['userName']);
			$sql.= " AND ".$db->quoteInto('s.password=?', md5($_data['password']));
			$row = $db->fetchRow($sql);
			
			$result = array(
					'status' =>true,
					'value' =>$row,
			);
			return $result;
	
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$result = array(
					'status' =>false,
					'value' =>$e->getMessage(),
			);
			return $result;
		}
	}
	function getUserInfById($userId){
		$db = $this->getAdapter();
		try{
			$sql =" SELECT
				s.*
				,s.user_type AS userType
				,s.userAction AS userAction
				,CASE
					WHEN  s.userAction = 1 THEN 'Boss'
					WHEN  s.userAction = 2 THEN 'Warehouse MG'
					WHEN  s.userAction = 3 THEN 'PO Department'
					WHEN  s.userAction = 4 THEN 'Warehouse Staff'
				END AS userActionTitle
				,s.photo AS photo
				,s.branch_list AS branchList
				,CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.first_name,'')) as userName
			FROM
				rms_users AS s
			WHERE s.active = 1 AND s.id = $userId ";
			$row = $db->fetchRow($sql);
			
			$result = array(
					'status' =>true,
					'value' =>$row,
			);
			return $result;
	
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$result = array(
					'status' =>false,
					'value' =>$e->getMessage(),
			);
			return $result;
		}
	}
	function generateToken($row){
    	$db = $this->getAdapter();
    	try{
    		$this->_name = "mobile_mobile_token";
    		
    		$token = $row['mobileToken'];
    		$sql="SELECT id FROM mobile_mobile_token WHERE token='".$token."' AND userId=0 LIMIT 1";
    		$rsid = $db->fetchOne($sql);
    		if(!empty($rsid)){
    			$_arr =array(
    					'userId' 		=> $row['id'],
    					'deviceType' 	=> $row['deviceType'],
    					'userAction' 	=> $row['userAction'],
    					'deviceModel' 	=> "",
    			);
    			$where ='id= '.$rsid;
    			$this->update($_arr, $where);
    		}else{
	    		$sql="SELECT id FROM mobile_mobile_token WHERE userId=".$row['id']." AND token='".$token."' LIMIT 1";
	    		$rs = $db->fetchOne($sql);
	    		if(empty($rs)){
					$currentUserCheck = 0;
					if($row['currentUserId']>0){
						$sql="SELECT id FROM mobile_mobile_token WHERE userId=".$row['currentUserId']." AND token='".$token."' LIMIT 1";
						$currentUserCheck = $db->fetchOne($sql);
					}
					$_arr =array(
	    				'userId' 	  => $row['id'],
	    				'token' 	  => $token,
	    				'deviceType'  => $row['deviceType'],
	    				'userAction'  => $row['userAction'],
	    				'deviceModel' => "",
	    			);
					if($currentUserCheck >0){
						$this->_name = "mobile_mobile_token";
						$where=" id = $currentUserCheck ";
						$this->update($_arr,$where);
					}else{
						$_arr['date'] = date("Y-m-d H:i:s");
						$this->_name = "mobile_mobile_token";
						$this->insert($_arr);
					}
					
	    		}
    		}
    		
    		return $token;
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		return null;
    	}
    }
	
	function checkChangePassword($_data){
		$db = $this->getAdapter();
		try{
	
			$sql ="
			SELECT
				s.id AS id
				,s.user_type AS userType
				,s.branch_list AS branchList
				,CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.first_name,'')) as userName
			FROM rms_users AS s
			WHERE s.active = 1 ";
			$sql.= " AND ".$db->quoteInto('s.id=?', $_data['userId']);
			$sql.= " AND ".$db->quoteInto('s.password=?', md5($_data['oldPassword']));
			$row = $db->fetchRow($sql);
			if (empty($row)){
				return false;
			}
			return true;
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			return false;
		}
	}
	function changePassword($_data){
		$db = $this->getAdapter();
		try{
			$_arr=array(
					'password'	  	=> md5($_data['newPassword']),
			);
			$this->_name = "rms_users";
			$where = $this->getAdapter()->quoteInto("id=?",$_data['userId']);
			$this->update($_arr, $where);
			return true;
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			return false;
		}
	}
	
	function checkTokenDevice($token="0"){
		$db = $this->getAdapter();
		$sql=" SELECT t.* FROM mobile_mobile_token AS t ";
		$sql.=" WHERE t.token ='$token' LIMIT 1 ";
		return $db->fetchRow($sql);
	}
	function addAppTokenId($_data){
		$db = $this->getAdapter();
		try{
			$_data['userAction'] = empty($_data['userAction']) ? "0" : $_data['userAction'];
			$_data['deviceType'] = empty($_data['deviceType']) ? "0" : $_data['deviceType'];
			$check = $this->checkTokenDevice($_data['token']);
			$this->_name='mobile_mobile_token';
			if(empty($check)){
				$array = array(
					'token'			=> $_data['token'],
					'userAction'	=> $_data['userAction'],
					'deviceType'	=> $_data['deviceType'],
					'date'			=> date('Y-m-d H:i:s'),
				);
				return $this->insert($array);
			}
			
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			return false;
		}
	}
	function removeAppTokenId($_data){
		$db = $this->getAdapter();
		try{
			$mobileToken 	= empty($_data['mobileToken'])?0:$_data['mobileToken'];
			$userId 		= empty($_data['userId'])?0:$_data['userId'];
			$currentUserId 	= empty($_data['currentUserId'])?0:$_data['currentUserId'];
			$typeRemove 	= empty($_data['typeRemove'])?0:$_data['typeRemove'];
			$deviceType 	= empty($_data['deviceType'])?1:$_data['deviceType'];
			
			if($typeRemove==1){ //Swtiching
				$sql="SELECT id FROM mobile_mobile_token WHERE userId=".$userId." AND token='".$mobileToken."' LIMIT 1";
	    		$rs = $db->fetchOne($sql);
	    		if(empty($rs)){
					$currentStudentCheck = 0;
					if($currentUserId>0){
						$sql="SELECT id FROM mobile_mobile_token WHERE userId=".$currentUserId." AND token='".$mobileToken."' LIMIT 1";
						$currentStudentCheck = $db->fetchOne($sql);
					}
					$_arr =array(
	    				'userId' 	=> $userId,
	    				'token' 	=> $mobileToken,		
	    				'deviceType' => $deviceType,
	    				'deviceModel' => "",
	    			);
					if($currentStudentCheck >0){
						$this->_name = "mobile_mobile_token";
						$where=" id = $currentStudentCheck ";
						$this->update($_arr,$where);
					}else{
						$_arr['date'] = date("Y-m-d H:i:s");
						$this->_name = "mobile_mobile_token";
						$this->insert($_arr);
					}
				}
			}else{ 
				if($userId>0){
					$where ="userId=".$userId." AND token='$mobileToken' ";
					$this->_name="mobile_mobile_token";
					$this->delete($where);
				}
			}
			
			
			$result = array(
					'status' =>true,
					'value' =>$userId,
			);
			return $result;
			
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$result = array(
				'status' =>false,
				'value' =>$e->getMessage(),
			);
			return $result;
		}
	}
	
	public function getAccessPermission($branchStr='branch_id',$_data = array()){
		
		$branchList= "0";
		$userType 	= "0";
		$userId 		= empty($_data['userId'])?0:$_data['userId'];
		$userInfo = $this->getUserInfById($userId);
		if(!empty($userInfo["value"])){
			$row = $userInfo["value"];
			$branchList = empty($row['branch_list'])?"0":$row['branch_list'];
			$userType = empty($row['userType'])?"0":$row['userType'];
		}
		
		$result="";
		if(!empty($branchList)){
			if($userType==1){
				$result.= "";
			}
			else{
				$result.= " AND $branchStr IN ($branchList)";
			}
		}
		return $result;
	}
	
	function getNotifyRequest($_data=array()){
		$db=$this->getAdapter();
		
		
    	$sql="
			SELECT 
				'requestingRecord' AS recordType
				
				,rq.date AS recordDate
				,rq.requestNo AS recordNo
				,rq.*
				,CASE
					WHEN  rq.checkingStatus= 0 THEN  'PENDING'
					WHEN  rq.checkingStatus = 1 THEN 'APPROVED'
					WHEN  rq.checkingStatus = 2 THEN 'REJECTED'
				END AS checkingStatusTitle,
				CASE
					WHEN  rq.pCheckingStatus= 0 THEN   'PENDING'
					WHEN  rq.pCheckingStatus = 1 THEN  'APPROVED'
					WHEN  rq.pCheckingStatus = 2 THEN  'REJECTED'
				END AS pCheckingStatusTitle,
				CASE
					WHEN  rq.approveStatus= 0 THEN  'PENDING'
					WHEN  rq.approveStatus = 1 THEN 'APPROVED'
					WHEN  rq.approveStatus = 2 THEN 'REJECTED'
				END AS approveStatusTitle,
				(SELECT p.project_name FROM `ln_project` AS p WHERE p.br_id = rq.projectId LIMIT 1) AS projectName,
				(SELECT  CONCAT(COALESCE(u.last_name,''),' ',COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.checkingBy LIMIT 1 ) AS checkingByName,
				(SELECT  CONCAT(COALESCE(u.last_name,''),' ',COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.pCheckingBy LIMIT 1 ) AS pCheckingByName,
				(SELECT  CONCAT(COALESCE(u.last_name,''),' ',COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.approveBy LIMIT 1 ) AS approveByName,
				(SELECT  CONCAT(COALESCE(u.last_name,''),' ',COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.userId LIMIT 1 ) AS userName,
				rq.processingStatus AS currentStep,
				'' AS itemsRequest
		";
		
		
		$dbGbSt = new Application_Model_DbTable_DbGlobalStock();
		
		$arrStep = array(
			'stepNum'=>"rq.processingStatus",
			'typeStep'=>3,
		);
		$sql.= $dbGbSt->requestingProccess($arrStep);
		$sql.=" ,(SELECT rqd.isCompletedPO FROM `st_request_po_detail` AS rqd WHERE rqd.requestId =rq.id AND rqd.approvedStatus=1 ORDER BY rqd.isCompletedPO ASC LIMIT 1 ) AS isCompletedPO  ";
		
		
		$sql.=" FROM `st_request_po` AS rq WHERE rq.status=1 ";
		
		$processingStatus=null;
		
		$_data["userAction"] = empty($_data["userAction"]) ? "0" : $_data["userAction"];
		if($_data["userAction"]=="2"){
			if(is_null($processingStatus)){
				$processingStatus =1; //forWarehouse
			}
		}
		
		if($_data["userAction"]=="1"){
			//forApproved
			if(is_null($processingStatus)){
				$processingStatus =3; 
			}else{
				$processingStatus =$processingStatus.",3";
			}
			$sql.=" AND rq.pCheckingStatus!=2 ";
		}
		
		if($_data["userAction"]=="3"){
			//forPurchasing
			if( !empty($_data['forPurchasing']) ){ //get Only Request Approved and go Make PO
				if(is_null($processingStatus)){
					$processingStatus ="4,5"; 
				}else{
					$processingStatus =$processingStatus.",4,5";
				}
				$sql.=" AND rq.approveStatus!=2 "; 
				$sql.= " AND (SELECT rqd.isCompletedPO FROM `st_request_po_detail` AS rqd WHERE rqd.requestId =rq.id AND rqd.approvedStatus!=2 ORDER BY rqd.isCompletedPO  ASC LIMIT 1 )= 0 ";
				$sql.=" AND rq.checkingStatus = 1 AND rq.pCheckingStatus = 1 "; 
			}else{
				//forPurchaseDept
				if(is_null($processingStatus)){
					$processingStatus =2; 
				}else{
					$processingStatus =$processingStatus.",2";
				}
				$sql.=" AND rq.checkingStatus!=2 ";
			}
		}
		
		if(is_null($processingStatus)){
			return array();
		}else{
			$sql.=" AND rq.processingStatus IN ($processingStatus) ";
		}
		
		if( !empty($_data['checkingStatus']) ){ //get Only First Step Request
			$sql.=" AND rq.checkingStatus = 0 "; 
		}
		if( !empty($_data['pCheckingStatus']) ){ //get Only Request already checking
			$sql.=" AND rq.checkingStatus = 1 AND rq.pCheckingStatus = 0 "; 
		}
		if( !empty($_data['approveStatus']) ){ //get Only Request already checking & verify
			$sql.=" AND rq.checkingStatus = 1 AND rq.pCheckingStatus = 1 AND rq.approveStatus = 0 "; 
		}
		

		$sql.=$this->getAccessPermission("rq.projectId",$_data);
    	$sql.=" ORDER BY rq.processingStatus ASC, rq.id DESC";
		
		$limit=" ";
		if(!empty($_data['LimitStart'])){
			$limit.=" LIMIT ".$_data['LimitStart'].",".$_data['limitRecord'];
		}else if(!empty($_data['limitRecord'])){
			$limit.=" LIMIT ".$_data['limitRecord'];
		}
		
    	return $db->fetchAll($sql.$limit);
	}
	
	function getUnverifyReceiveDn($_data=array()){
		$db=$this->getAdapter();		
    	$sql="
			SELECT 
				'unverifyReceiveDn' AS recordType
				,rst.receiveDate AS recordDate
				,rst.dnNumber AS recordNo
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
				,(SELECT  CONCAT(COALESCE(u.last_name,''),' ',COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.userId LIMIT 1 ) AS requestByName
				,(SELECT  CONCAT(COALESCE(u.last_name,''),' ',COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=po.userId LIMIT 1 ) AS purchaseByName
		";
		
	
		
		$sql.=" FROM  `st_receive_stock` AS rst 
					LEFT JOIN `st_request_po` AS rq ON rq.id = rst.requestId
					LEFT JOIN `st_purchasing` AS po ON po.id = rst.poId
			WHERE rst.status =1
			AND rst.verified =0 
			AND rst.isIssueInvoice=0
			AND rst.requestId >0
			";
			
		$sql.=$this->getAccessPermission("rst.projectId",$_data);
    	$sql.=" ORDER BY rst.id DESC";
		
		$limit=" ";
		if(!empty($_data['LimitStart'])){
			$limit.=" LIMIT ".$_data['LimitStart'].",".$_data['limitRecord'];
		}else if(!empty($_data['limitRecord'])){
			$limit.=" LIMIT ".$_data['limitRecord'];
		}
    	return $db->fetchAll($sql.$limit);
	}
	
	function getAllTransferStock($_data=array()){
		$db=$this->getAdapter();
		$sql="SELECT 
				'receiveTransfer' AS recordType
				,trs.transferDate AS recordDate
				,trs.transferNo AS recordNo
				,trs.id
				,(SELECT p.project_name FROM `ln_project` AS p WHERE p.br_id = trs.fromProjectId LIMIT 1) AS projectName
				,(SELECT p.project_name FROM `ln_project` AS p WHERE p.br_id = trs.toProjectId LIMIT 1) AS toProjectName
				,trs.transferNo
				,trs.driver
				,trs.transferer
				,trs.transferDate
				,DATE_FORMAT(trs.transferDate,'".DATE_FORMAT_FOR_SQL."') As transferDateFormat
				,(SELECT  CONCAT(COALESCE(u.last_name,''),' ',COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=trs.userId LIMIT 1 ) AS userName
			FROM `st_transferstock` AS trs  WHERE trs.status=1 AND trs.isApproved=1 ";
		$sql.=" AND (SELECT trsd.isCompleted FROM `st_transferstock_detail` AS trsd WHERE trsd.transferId =trs.id   ORDER BY trsd.isCompleted ASC LIMIT 1 )=0 ";
	
	
		$sql.=$this->getAccessPermission("trs.toProjectId",$_data);
		$sql.=" ORDER BY trs.id DESC ";
		
		$limit=" ";
		if(!empty($_data['LimitStart'])){
			$limit.=" LIMIT ".$_data['LimitStart'].",".$_data['limitRecord'];
		}else if(!empty($_data['limitRecord'])){
			$limit.=" LIMIT ".$_data['limitRecord'];
		}
		return $db->fetchAll($sql.$limit);
	}
	
	public function getAllRequestNotify($_data){
		$db = $this->getAdapter();
		try{
			
			$_data["userAction"] = empty($_data["userAction"]) ? "0" : $_data["userAction"];
			$_data['userId'] 	 = empty($_data['userId'])?0:$_data['userId'];			
			$row = array();
		
			$row = $this->getNotifyRequest($_data);
				
			$counting = count($row);
			$allResult = array('rowData'=>$row,'countingRecord'=>$counting);
			
			$result = array(
						'status' =>true,
						'value' =>$allResult,
					);
			return $result;
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$result = array(
				'status' =>false,
				'value' =>$e->getMessage(),
			);
			return $result;
		}
    }
	
	function getRequestDetail($_data){
		$db = $this->getAdapter();
		try{
			
			
			$_data['userId'] 	 = empty($_data['userId'])?0:$_data['userId'];
			$_data['pCheckingRequest'] 	 = empty($_data['pCheckingRequest'])?0:$_data['pCheckingRequest'];
			$_data['approvedrequest'] 	 = empty($_data['approvedrequest'])?0:$_data['approvedrequest'];
			$recordId 	 = empty($_data['recordId'])?0:$_data['recordId'];
			
			$sql=" 	SELECT 
					rqd.*,p.proCode,
					p.proName,
					p.image AS productImage,
					(SELECT COALESCE(SUM(pl.qty),0) FROM st_product_location AS pl WHERE pl.proId=p.proId LIMIT 1) AS currentQtyAllBranch,
					(SELECT COALESCE(pl.qty,0) FROM st_product_location AS pl WHERE pl.proId=p.proId AND pl.projectId= rq.projectId LIMIT 1) AS currentQty,
					(SELECT COALESCE(pod.unitPrice,0) FROM `st_purchasing_detail` AS pod WHERE pod.proId=p.proId ORDER BY pod.purchaseId DESC LIMIT 1) AS latestUnitPrice,
					p.measureLabel AS measureTitle
				";
			$sql.="	FROM 
						`st_request_po_detail` as rqd
						JOIN `st_request_po` AS rq ON rq.id = rqd.requestId
						LEFT JOIN `st_product` AS p  ON p.proId = rqd.proId 
			";
			$sql.=" WHERE 1 AND rqd.requestId IN ($recordId) ";	
			if (!empty($_data['pCheckingRequest']) OR !empty($_data['approvedrequest'])){
				$sql.=" AND rqd.adjustStatus = 1 ";
			}
			$sql.=$this->getAccessPermission("rq.projectId",$_data);
			$sql.=" ORDER BY rqd.requestId DESC ";
			
			$rs = $db->fetchAll($sql);
			
			$result = array(
						'status' =>true,
						'value' =>$rs,
					);
			return $result;
			
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$result = array(
				'status' =>false,
				'value' =>$e->getMessage(),
			);
			return $result;
		}
	}
	
	function submitCheckingRequestPO($_data){
    	$db = $this->getAdapter();
    	try{
			
			$_data['userId'] 	 = empty($_data['userId'])?0:$_data['userId'];
			$checkingStatus 	 = empty($_data['checkingStatus'])?1:$_data['checkingStatus'];
			
			$listFromPost 	 = empty($_data['checkListRequestSubmit'])?null:$_data['checkListRequestSubmit'];
			$requestList 	 = Zend_Json::decode($listFromPost);
    		
    		$dbGbSt = new Application_Model_DbTable_DbGlobalStock();
			$arrStep = array(
				'stepNum'=>2,
				'typeStep'=>1,
			);
			$processingStatus = $dbGbSt->requestingProccess($arrStep);
			if(!empty($requestList)) foreach($requestList AS $request){
				$requestId = $request['id'];
				
				$arr = array(
						'checkingNote'			=>$request['note'],
						'checkingDate'			=>date("Y-m-d"),
						'checkingStatus'		=>$checkingStatus,
						'checkingCreateDate'	=>date("Y-m-d H:i:s"),
						'checkingModifyDate'	=>date("Y-m-d H:i:s"),
						'checkingBy'			=>$_data['userId'],
						'processingStatus'		=>$processingStatus,//Warehouse Step checking Approved/Rejected
				);
				$this->_name = "st_request_po";
				$where=" id = ".$requestId;
				$this->update($arr, $where);
				
				
				
				if($checkingStatus==1){
					$notify = array(
						"userAction" => 3,
						"typeNotify" => "checkingRequest",
						"deviceType" => "1",
						"notificationTitle" => "Requesting PO",
					);
					$notify["notificationId"]  = $requestId;
					$notify["notificationSubTitle"]  = $request["projectName"]." ".$request["requestNo"];
					$notify["branchId"]  = $request["projectId"];
					$dbGbSt->pushNotificationForAndroid($notify);
				}
				
				$itemsRequest 	 = empty($request['itemsRequest'])?null:$request['itemsRequest'];
				if(!empty($itemsRequest)){
					foreach ($itemsRequest as $row){
						if (!empty($row['id'])){
							$adjustStatus = empty($row['adjustStatus']) ?1:$row['adjustStatus'];
							if($checkingStatus==2){ //REJECTED
								$adjustStatus = $checkingStatus;
							}
							$arr = array(
								'requestId'			=>$requestId,
								'proId'				=>$row['proId'],
								
								'qtyAdjust'			=>$row['qtyAdjust'],
								'qtyVerify'			=>$row['qtyAdjust'],
								'qtyApproved'		=>$row['qtyAdjust'],
								
								'note'				=>$row['note'],
								'adjustStatus'		=>$adjustStatus,
							);
							$this->_name='st_request_po_detail';
							$where =" id =".$row['id'];
							$this->update($arr, $where);						
						
						}
					}
				}else{
					$adjustStatus = $checkingStatus;
					$arr = array(
						'adjustStatus'		=>$adjustStatus,
					);
					$this->_name='st_request_po_detail';
					$where =" requestId =".$requestId;
					$this->update($arr, $where);
				}
			}
			
	
    		return true;
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		return false;
    	}
    }
	
	function submitVerifyRequestPO($_data){
    	$db = $this->getAdapter();
    	try{
			
			$_data['userId'] 	 = empty($_data['userId'])?0:$_data['userId'];
			$checkingStatus 	 = empty($_data['checkingStatus'])?1:$_data['checkingStatus'];
			
			$listFromPost 	 = empty($_data['listRequestSubmit'])?null:$_data['listRequestSubmit'];
			$requestList 	 = Zend_Json::decode($listFromPost);
    		
    		$dbGbSt = new Application_Model_DbTable_DbGlobalStock();
			$arrStep = array(
				'stepNum'=>3,
				'typeStep'=>1,
			);
			$processingStatus = $dbGbSt->requestingProccess($arrStep);
			if(!empty($requestList)) foreach($requestList AS $request){
				$requestId = $request['id'];
						
				$arr = array(
						'pCheckingNote'			=>$request['pCheckingNote'],
						'pCheckingDate'			=>date("Y-m-d"),
						'pCheckingStatus'		=>$checkingStatus,
						'pCheckingModifyDate'	=>date("Y-m-d H:i:s"),
						'checkingCreateDate'	=>date("Y-m-d H:i:s"),
						'pCheckingBy'			=>$_data['userId'],
						'processingStatus'		=>$processingStatus,//Purchasing Step checking Approved/Rejected
				);
				$this->_name = "st_request_po";
				$where=" id = ".$requestId;
				$this->update($arr, $where);
				
				if($checkingStatus==1){
					$notify = array(
						"userAction" => 1,// push to Boss Approve
						"typeNotify" => "poVerifyRequest",
						"deviceType" => "1",
						"notificationTitle" => "Requesting PO",
					);
					$notify["notificationId"]  = $requestId;
					$notify["notificationSubTitle"]  = $request["projectName"]." ".$request["requestNo"];
					$notify["branchId"]  = $request["projectId"];
					$dbGbSt->pushNotificationForAndroid($notify);
				}
				
				$itemsRequest 	 = empty($request['itemsRequest'])?null:$request['itemsRequest'];
				if(!empty($itemsRequest)){
					foreach ($itemsRequest as $row){
						if (!empty($row['id'])){
							$verifyStatus = empty($row['verifyStatus']) ?1:$row['verifyStatus'];
							if($checkingStatus==2){ //REJECTED
								$verifyStatus = $checkingStatus;
							}
							$arr = array(
								'requestId'			=>$requestId,
								'proId'				=>$row['proId'],
								
	
								'qtyVerify'			=>$row['qtyVerify'],
								'qtyApproved'		=>$row['qtyVerify'],
								
								'verifyNote'		=>$row['verifyNote'],
								'verifyStatus'		=>$verifyStatus,
							);
							$this->_name='st_request_po_detail';
							$where =" id =".$row['id'];
							$this->update($arr, $where);						
						
						}
					}
				}else{
					$verifyStatus = $checkingStatus;
					$arr = array(
						'verifyStatus'		=>$verifyStatus,
					);
					$this->_name='st_request_po_detail';
					$where =" requestId =".$requestId;
					$this->update($arr, $where);
				}
			}
			
	
    		return true;
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		return false;
    	}
    }
	
	function submitApproveRequestPO($_data){
    	$db = $this->getAdapter();
    	try{
			
			$_data['userId'] 	 = empty($_data['userId'])?0:$_data['userId'];
			$checkingStatus 	 = empty($_data['checkingStatus'])?1:$_data['checkingStatus'];
			
			$listFromPost 	 = empty($_data['listRequestSubmit'])?null:$_data['listRequestSubmit'];
			$requestList 	 = Zend_Json::decode($listFromPost);
    		
    		$dbGbSt = new Application_Model_DbTable_DbGlobalStock();
			$arrStep = array(
				'stepNum'=>4,
				'typeStep'=>1,
			);
			$processingStatus = $dbGbSt->requestingProccess($arrStep);
			if(!empty($requestList)) foreach($requestList AS $request){
				$requestId = $request['id'];
					
				$arr = array(
						'approveNote'		=>$request['approveNote'],
						'approveDate'		=>date("Y-m-d"),
						'approveStatus'		=>$checkingStatus,
						'approveModifyDate'	=>date("Y-m-d H:i:s"),
						'approveCreateDate'	=>date("Y-m-d H:i:s"),
						'approveBy'			=>$_data['userId'],
						'processingStatus'	=>$processingStatus,//Purchasing Step checking Approved/Rejected
				);
				$this->_name = "st_request_po";
				$where=" id = ".$requestId;
				$this->update($arr, $where);
				
				if($checkingStatus==1){
					$notify = array(
						"userAction" => 3,// push to PO Dept to Make PO
						"typeNotify" => "approvedRequest",
						"deviceType" => "1",
						"notificationTitle" => "Approved Request",
					);
					$notify["notificationId"]  = $requestId;
					$notify["notificationSubTitle"]  = $request["projectName"]." ".$request["requestNo"];
					$notify["branchId"]  = $request["projectId"];
					$dbGbSt->pushNotificationForAndroid($notify);
				}
				
				$itemsRequest 	 = empty($request['itemsRequest'])?null:$request['itemsRequest'];
				if(!empty($itemsRequest)){
					foreach ($itemsRequest as $row){
						if (!empty($row['id'])){
							$approvedStatus = empty($row['approvedStatus']) ?1:$row['approvedStatus'];
							if($checkingStatus==2){ //REJECTED
								$approvedStatus = $checkingStatus;
							}
							if($row['verifyStatus']==2){
								$approvedStatus =2;
							}
							$arr = array(
								'requestId'			=>$requestId,
								'proId'				=>$row['proId'],
								
	
								'qtyApproved'		=>$row['qtyApproved'],
								'qtyApprovedAfter'	=>$row['qtyApproved'],
								
								'approveNote'		=>$row['approveNote'],
								'approvedStatus'	=>$approvedStatus,
							);
							$this->_name='st_request_po_detail';
							$where =" id =".$row['id'];
							$this->update($arr, $where);						
						
						}
					}
				}else{
					
					$_data["recordId"] = $requestId;
					$_data["approvedrequest"] = 1;
					$requseDetail = $this->getRequestDetail($_data);
					if(!empty($requseDetail)){
						foreach ($requseDetail as $row){
							if (!empty($row['id'])){
								$approvedStatus = $checkingStatus;
								if($row['verifyStatus']==2){
									$approvedStatus =2;
								}
								$arr = array(
									'approvedStatus'	=>$approvedStatus,
									'qtyApprovedAfter'	=>$row['qtyApproved'],
								);
								$this->_name='st_request_po_detail';
								$where =" id =".$row['id'];
								$this->update($arr, $where);						
							
							}
						}
					}
					
				}
			}
			
	
    		return true;
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		return false;
    	}
    }
	
	
	function getPORequestToReceive($_data=array()){
		$db=$this->getAdapter();
		try{
			
			$_data['userId'] 	 = empty($_data['userId'])?0:$_data['userId'];
			$dbGBstock = new Application_Model_DbTable_DbGlobalStock();
			$arrStep = array(
					'keyIndex'=>1,
					'typeKeyIndex'=>1,
				);
			$purchaseType = $dbGBstock->purchasingTypeKey($arrStep);
		
			$sql="
				SELECT 
					po.*
					,po.date AS poDate
					,spp.supplierName
					,spp.address AS supplierAddress
					,spp.supplierTel
					,spp.contactName AS supplierContactName
					,spp.contactNumber AS supplierContactNumber
					,spp.email AS supplierEmail
					,rq.requestNo
					,rq.date AS requestDate
					,rq.requestNoLetter
					,rq.purpose
					,(SELECT p.project_name FROM `ln_project` AS p WHERE p.br_id = po.projectId LIMIT 1) AS projectName
					,(SELECT  CONCAT(COALESCE(u.last_name,''),' ',COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.checkingBy LIMIT 1 ) AS checkingByName
					,(SELECT  CONCAT(COALESCE(u.last_name,''),' ',COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.pCheckingBy LIMIT 1 ) AS pCheckingByName
					,(SELECT  CONCAT(COALESCE(u.last_name,''),' ',COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.approveBy LIMIT 1 ) AS approveByName
					,(SELECT  CONCAT(COALESCE(u.last_name,''),' ',COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=rq.userId LIMIT 1 ) AS requestByName
					,(SELECT  CONCAT(COALESCE(u.last_name,''),' ',COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=po.userId LIMIT 1 ) AS purchaseByName
				
			";
			
			$sql.=" 
				FROM `st_purchasing` AS po 
					JOIN `st_request_po` AS rq ON rq.id = po.requestId AND rq.projectId = po.projectId 
					LEFT JOIN `st_supplier` AS spp ON spp.id = po.supplierId  ";
			$sql.=" WHERE po.purchaseType=".$purchaseType."
					AND po.status = 1 ";
			
			$sql.=$this->getAccessPermission("po.projectId",$_data);
			$sql.=" ORDER BY po.id DESC";
			
			$limit=" ";
			if(!empty($_data['LimitStart'])){
				$limit.=" LIMIT ".$_data['LimitStart'].",".$_data['limitRecord'];
			}else if(!empty($_data['limitRecord'])){
				$limit.=" LIMIT ".$_data['limitRecord'];
			}

			$row = $db->fetchAll($sql.$limit);	
			$counting = count($row);
			$allResult = array('rowData'=>$row,'countingRecord'=>$counting);
			
			$result = array(
						'status' =>true,
						'value' =>$allResult,
					);
			return $result;
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$result = array(
				'status' =>false,
				'value' =>$e->getMessage(),
			);
			return $result;
		}
				
	}
	
	public function getAllDNToVerifyNotify($_data){
		$db = $this->getAdapter();
		try{
			
			$_data["userAction"] = empty($_data["userAction"]) ? "0" : $_data["userAction"];
			$_data['userId'] 	 = empty($_data['userId'])?0:$_data['userId'];			
			$row = array();
		
			$row = $this->getUnverifyReceiveDn($_data);
				
			$counting = count($row);
			$allResult = array('rowData'=>$row,'countingRecord'=>$counting);
			
			$result = array(
						'status' =>true,
						'value' =>$allResult,
					);
			return $result;
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$result = array(
				'status' =>false,
				'value' =>$e->getMessage(),
			);
			return $result;
		}
    }
	
}