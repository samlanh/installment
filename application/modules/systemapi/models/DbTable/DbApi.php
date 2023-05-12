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
					WHEN  s.userAction = 1 THEN 'Warehouse Staff'
					WHEN  s.userAction = 2 THEN 'Warehouse MG'
					WHEN  s.userAction = 3 THEN 'PO Department'
					WHEN  s.userAction = 4 THEN 'Boss'
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
	
	public function getAllSlider($search){
    	$db = $this->getAdapter();
    	try{
    		
    		$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
    		$sql=" SELECT * FROM `mobile_slideshow` ";
    		$rows = $db->fetchAll($sql);
    		
    
    		$result = array(
    				'status' =>true,
    				'value' =>$rows,
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
		
		if(!empty($_data['endDate'])){
			$from_date =(empty($_data['startDate']))? '1': " rq.date >= '".date("Y-m-d",strtotime($_data['startDate']))." 00:00:00'";
			$to_date = (empty($_data['endDate']))? '1': " rq.date <= '".date("Y-m-d",strtotime($_data['endDate']))." 23:59:59'";
			$sql.= " AND ".$from_date." AND ".$to_date;
		}
		
		if( !empty($_data['requestStep']) ){
			if($_data['requestStep']>0){
				$sql.=" AND rq.processingStatus = ".$_data['requestStep'];
			}
		}
		if( !empty($_data['requestStatus']) ){
			if ($_data['requestStatus'] == 1) {
				$sql .= "
				AND ( 
					   (rq.checkingStatus=1 AND rq.pCheckingStatus=1 AND rq.approveStatus=1) 
					
				)
				";
				/*
				OR (rq.checkingStatus=1 AND rq.pCheckingStatus=1 AND rq.approveStatus=0) 
					OR (rq.checkingStatus=1 AND rq.pCheckingStatus=0 AND rq.approveStatus=0) 
				OR (rq.checkingStatus=1 AND rq.pCheckingStatus=0 AND rq.approveStatus=1) 
					OR (rq.checkingStatus=0 AND rq.pCheckingStatus=0 AND rq.approveStatus=1)
					OR (rq.checkingStatus=0 AND rq.pCheckingStatus=1 AND rq.approveStatus=1) 
				*/
			} else if ($_data['requestStatus'] == 2) {
				$sql .= "
				AND ( 
					   (rq.checkingStatus=2 AND rq.pCheckingStatus=2 AND rq.approveStatus=2) 
					OR (rq.checkingStatus=2 AND rq.pCheckingStatus=2 AND rq.approveStatus=0) 
					OR (rq.checkingStatus=2 AND rq.pCheckingStatus=0 AND rq.approveStatus=0)
					OR (rq.checkingStatus=1 AND rq.pCheckingStatus=1 AND rq.approveStatus=2)
					OR (rq.checkingStatus=1 AND rq.pCheckingStatus=2 AND rq.approveStatus=0)
					
				)";
			} else if ($_data['requestStatus'] == 3) {
				$sql .= "
				AND ( 
					   (rq.checkingStatus=1 AND rq.pCheckingStatus=0 AND rq.approveStatus=0) 
					OR (rq.checkingStatus=1 AND rq.pCheckingStatus=1 AND rq.approveStatus=0) 
					OR (rq.checkingStatus=0 AND rq.pCheckingStatus=0 AND rq.approveStatus=0)
				)
				";
			}
		}
		if( empty($_data['isAllRequest']) ){
			$processingStatus=null;
			$_data["userAction"] = empty($_data["userAction"]) ? "0" : $_data["userAction"];
			if($_data["userAction"]=="2"){
				if(is_null($processingStatus)){
					$processingStatus =1; //forWarehouse
				}
			}
			
			if($_data["userAction"]=="4"){
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
				,rq.createDate AS requestCreateDate
				,rq.requestNo

				,po.purchaseNo
				,(SELECT spl.supplierName FROM `st_supplier` AS spl WHERE spl.id = po.supplierId LIMIT 1) AS supplierName
				,po.purpose
				,po.date AS purchaseDate
				,po.createDate AS purchaseCreateDate
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
					,(SELECT SUM(pod1.qty) FROM st_purchasing AS po,st_purchasing_detail AS pod1 WHERE pod1.purchaseId=po.id AND pod1.proId = rqd.proId AND rqd.requestId=po.requestId  AND  po.status =1 ) AS purchaseQty
					,(SELECT GROUP_CONCAT(po.purchaseNo) FROM st_purchasing AS po,st_purchasing_detail AS pod1 WHERE pod1.purchaseId=po.id AND pod1.proId = rqd.proId AND rqd.requestId=po.requestId  AND  po.status =1 ) AS purchaseNoList
					,(SELECT GROUP_CONCAT(po.id) FROM st_purchasing AS po,st_purchasing_detail AS pod1 WHERE pod1.purchaseId=po.id AND pod1.proId = rqd.proId AND rqd.requestId=po.requestId  AND  po.status =1 ) AS purchaseIdList
					,(SELECT GROUP_CONCAT(po.date) FROM st_purchasing AS po,st_purchasing_detail AS pod1 WHERE pod1.purchaseId=po.id AND pod1.proId = rqd.proId AND rqd.requestId=po.requestId  AND  po.status =1 ) AS purchaseDateList
					,(SELECT GROUP_CONCAT(po.createDate) FROM st_purchasing AS po,st_purchasing_detail AS pod1 WHERE pod1.purchaseId=po.id AND pod1.proId = rqd.proId AND rqd.requestId=po.requestId  AND  po.status =1 ) AS purchaseCreateDateList
					,(SELECT GROUP_CONCAT((SELECT spp.supplierName FROM st_supplier AS spp WHERE spp.id = po.supplierId LIMIT 1 )) FROM st_purchasing AS po,st_purchasing_detail AS pod1 WHERE pod1.purchaseId=po.id AND pod1.proId = rqd.proId AND rqd.requestId=po.requestId  AND  po.status =1 ) AS supplierNameList
					
					,(SELECT GROUP_CONCAT(rsd1.qtyReceive) FROM st_receive_stock AS rs,st_receive_stock_detail AS rsd1 WHERE rsd1.receiveId=rs.id AND rsd1.proId = rqd.proId AND rqd.requestId=rs.requestId AND rs.status =1 ) AS totalReceiveQty
					,(SELECT GROUP_CONCAT(rs.dnNumber) FROM st_receive_stock AS rs,st_receive_stock_detail AS rsd1 WHERE rsd1.receiveId=rs.id AND rsd1.proId = rqd.proId AND rqd.requestId=rs.requestId AND rs.status =1 ) AS dnNumberList
					,(SELECT GROUP_CONCAT(rs.id) FROM st_receive_stock AS rs,st_receive_stock_detail AS rsd1 WHERE rsd1.receiveId=rs.id AND rsd1.proId = rqd.proId AND rqd.requestId=rs.requestId AND rs.status =1 ) AS dnIdList
					,(SELECT GROUP_CONCAT(rs.receiveDate) FROM st_receive_stock AS rs,st_receive_stock_detail AS rsd1 WHERE rsd1.receiveId=rs.id AND rsd1.proId = rqd.proId AND rqd.requestId=rs.requestId AND rs.status =1 ) AS dnReceiveDateList
					
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
						"typeNotify" => "toPoVerifyRequest",
					);
					$notify["notificationId"]  = $requestId;
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
						'pCheckingCreateDate'	=>date("Y-m-d H:i:s"),
						'pCheckingBy'			=>$_data['userId'],
						'processingStatus'		=>$processingStatus,//Purchasing Step checking Approved/Rejected
				);
				$this->_name = "st_request_po";
				$where=" id = ".$requestId;
				$this->update($arr, $where);
				
				if($checkingStatus==1){
					$notify = array(
						"userAction" => 4,// push to Boss Approve
						"typeNotify" => "toApproveRequest",
					);
					$notify["notificationId"]  = $requestId;
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
						"typeNotify" => "toPoPurchase",
					);
					$notify["notificationId"]  = $requestId;
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
					,po.createDate AS purchaseCreateDate
					,spp.supplierName
					,spp.address AS supplierAddress
					,spp.supplierTel
					,spp.contactName AS supplierContactName
					,spp.contactNumber AS supplierContactNumber
					,spp.email AS supplierEmail
					,rq.requestNo
					,rq.date AS requestDate
					,rq.createDate AS requestCreateDate
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
	function getDNDetail($_data){
		$db = $this->getAdapter();
		try{
			
			$_data['userId'] 	 = empty($_data['userId'])?0:$_data['userId'];
			$recordId 	 = empty($_data['recordId'])?0:$_data['recordId'];
			
			$sql="SELECT 
					sd.*
					,p.proCode
					,p.proName
					,p.image AS productImage
					,p.measureLabel AS measureTitle
				";
			$sql.="	FROM 
						`st_receive_stock_detail` AS sd 
						JOIN st_receive_stock AS rst ON rst.id = sd.receiveId
						LEFT JOIN `st_product` AS p  ON p.proId = sd.proId 
			";
			$sql.=" WHERE 1 AND sd.receiveId IN ($recordId) ";	
			
			$sql.=$this->getAccessPermission("rst.projectId",$_data);
			$sql.=" ORDER BY sd.receiveId DESC, sd.id ASC ";
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
	function submitVerifyDN($_data){
    	$db = $this->getAdapter();
    	try{
			
			$_data['userId'] 	 = empty($_data['userId'])?0:$_data['userId'];
			$checkingStatus 	 = empty($_data['checkingStatus'])?1:$_data['checkingStatus'];
			
			$listFromPost 	 = empty($_data['listForVerifyDn'])?null:$_data['listForVerifyDn'];
			$listForVerifyDn 	 = Zend_Json::decode($listFromPost);
    		
			if(!empty($listForVerifyDn)) foreach($listForVerifyDn AS $row){
				$verifiedId = $row['id'];
				$arr = array(
					'verified' => 1,
					'verifiedBy' => $_data['userId'],
					'verifiedDate' => date('Y-m-d H:i:s')
				);
				$where = "id=" . $verifiedId;
				$this->_name = "st_receive_stock";
				$this->update($arr, $where);
				
			}
			
    		return true;
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		return false;
    	}
    }
	
	public function getTransferNotify($_data){
		$db = $this->getAdapter();
		try{
			
			$_data["userAction"] = empty($_data["userAction"]) ? "0" : $_data["userAction"];
			$_data['userId'] 	 = empty($_data['userId'])?0:$_data['userId'];			
			$row = array();
		
			$row = $this->getAllTransferStock($_data);
				
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
	
	public function getPurchaseConcreteList($_data){
		$db = $this->getAdapter();
		try{
			
			$_data['userId'] = empty($_data['userId'])?0:$_data['userId'];
			$sql="SELECT 
					r.*
					,(SELECT pro.project_name FROM `ln_project` AS pro WHERE pro.br_id=r.projectId LIMIT 1) AS projectName
					,(SELECT s.supplierName FROM st_supplier s WHERE s.id=r.supplierId LIMIT 1) AS supplierName
					,(SELECT s.supplierTel FROM st_supplier s WHERE s.id=r.supplierId LIMIT 1) AS supplierTel
					,(SELECT s.address FROM st_supplier s WHERE s.id=r.supplierId LIMIT 1) AS supplierAddress
					,p.proName
					,p.proCode
					,p.measureLabel
					,(SELECT CONCAT(COALESCE(s.last_name,''),' ',COALESCE(s.first_name,'')) FROM rms_users AS s WHERE s.id=r.userId LIMIT 1 ) AS byUserName
					,rd.qtyReceive
					,rd.price
					,rd.discountPercent
					,rd.discountAmount
					,rd.totalDiscount
					,rd.subTotal
					,rd.strength
					,rd.note AS descriptionConcreteInfo 
					,rd.workType AS workTypeId
					,(SELECT wt.workTitle FROM `st_work_type` AS wt WHERE wt.id =rd.workType LIMIT 1) AS workTypeTitle
				";
			$sql.="	FROM `st_receive_stock` AS r 
						JOIN st_receive_stock_detail AS rd  ON r.id=rd.receiveId 
						JOIN `st_purchasing` AS po ON po.id=r.poId and po.purchasetype = 3 
						Left Join `st_product` AS p On p.proId=rd.proId 
			";
			$sql.=" WHERE r.status = 1 ";	
			
			if(!empty($_data['endDate'])){
				$from_date =(empty($_data['startDate']))? '1': " r.receiveDate >= '".date("Y-m-d",strtotime($_data['startDate']))." 00:00:00'";
				$to_date = (empty($_data['endDate']))? '1': " r.receiveDate <= '".date("Y-m-d",strtotime($_data['endDate']))." 23:59:59'";
				$sql.= " AND ".$from_date." AND ".$to_date;
			}
			
			$sql.=$this->getAccessPermission("r.projectId",$_data);
			if(!empty($_data['isForReAdjustment'])){
				$sql.=" AND r.verified = 2 ";	
			}
			$sql.=" ORDER BY r.id DESC ";
			
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
	
	function submitApproveToReAdjustPoConcrete($_data){
    	$db = $this->getAdapter();
    	try{
			
			$_data['userId'] 	 = empty($_data['userId'])?0:$_data['userId'];
			
			
			$listFromPost 	 = empty($_data['listForVerify'])?null:$_data['listForVerify'];
			$listForVerify 	 = Zend_Json::decode($listFromPost);
    		
			if(!empty($listForVerify)) foreach($listForVerify AS $row){
				$verifiedId = $row['id'];
				$arr = array(
					'verified' 		=> 0,
					'verifiedBy' 	=> $_data['userId'],
					'modifyDate' 	=> date('Y-m-d H:i:s')
				);
				$where = "id=" . $verifiedId;
				$this->_name = "st_receive_stock";
				$this->update($arr, $where);
				
			}
			
    		return true;
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		return false;
    	}
    }
	
	function getAllStaffbyBranch($_data)
	{
		$db = $this->getAdapter();
		$sql = "SELECT  
					w.*
					,w.id
					,w.staffName AS name
				FROM st_worker AS w
					WHERE w.status=1 ";
		if (!empty($_data['branchId'])) {
			$sql .= " AND w.projectId=" . $_data['branchId'];
		}
		$rows = $db->fetchAll($sql);
		
		return $rows;
	}
	function getAllContractorbyBranch($_data)
	{
		$db = $this->getAdapter();
		$sql = "
		SELECT
			ct.*
			,ct.id AS id
			,ct.staffName AS name
		FROM st_contractor AS ct
		WHERE ct.status=1 ";
		if (!empty($_data['branchId'])) {
			$sql .= " AND ct.projectId=" . $_data['branchId'];
		}
		$rows = $db->fetchAll($sql);
		
		return $rows;
	}
	public function getAllWorkType($parent = 0, $spacing = '', $cate_tree_array = '')
	{

		$db = $this->getAdapter();
		if (!is_array($cate_tree_array))
			$cate_tree_array = array();

		$sql = "SELECT
				wt.id AS id,
				wt.workTitle AS `name` ";
		$sql .= " FROM `st_work_type` AS wt  ";
		$sql .= " WHERE wt.status=1 AND wt.parentId = $parent ";
		$query = $db->fetchAll($sql);
		$rowCount = count($query);
		$id = '';
		if ($rowCount > 0) {
			foreach ($query as $row) {
				$cate_tree_array[] = array("id" => $row['id'], "name" => $spacing . $row['name']);
				$cate_tree_array = $this->getAllWorkType($row['id'], $spacing . ' - ', $cate_tree_array);
			}
		}
		return $cate_tree_array;
	}
	public function getPropertyType(){
		$db= $this->getAdapter();
		$sql="SELECT t.`id`,t.`type_nameen` AS `name` FROM `ln_properties_type` AS t WHERE t.`status`=1";
		$rows =  $db->fetchAll($sql);
		return $rows;
	  }
	public function getAllLand($_data){
  	   $db = $this->getAdapter();
  	   
	   $sql="
			SELECT 
				`id`,
				CONCAT(COALESCE(`land_address`,''),',',COALESCE(street,'')) AS name 
			FROM `ln_properties` 
			WHERE `land_address`!='' ";
	   $sql.=" AND  status Not IN (-1,0) ";
	   
		if (!empty($_data['branchId'])) {
			$sql .= " AND `branch_id`=" . $_data['branchId'];
		}
		if (!empty($_data['propertyType'])) {
			$sql .= " AND `property_type`=" . $_data['propertyType'];
		}
 
		$sql.=" ORDER BY id DESC";
  	    $rows = $db->fetchAll($sql);
  	    
  		return $rows;
  }
	
	public function getFormSearchOption($_data){
		$db = $this->getAdapter();
		try{
			
			
			$currentLang = empty($_data['currentLang'])?1:$_data['currentLang'];
			$getControlType = empty($_data['getControlType'])?"requestStatus":$_data['getControlType'];
			$_data['userId'] = empty($_data['userId'])?0:$_data['userId'];
			$_data['branchId'] = empty($_data['branchId'])?0:$_data['branchId'];
			$row=array();
			if($getControlType=="requestStatus"){
				$row = array(
					array("id"=>3,"name"=>$currentLang==1 ? "កំពុងរង់ចាំ" : "Pending"),
					array("id"=>1,"name"=>$currentLang==1 ? "បានយល់ព្រម" : "Approved"),
					array("id"=>2,"name"=>$currentLang==1 ? "បានបដិសេធ" : "Rejected"),
					
				);
			}else if($getControlType=="requestStep"){
				$row = array(
					array("id"=>1,"name"=>$currentLang==1 ? "ផ្នែកសំណើ" : "Request Dept"),
					array("id"=>2,"name"=>$currentLang==1 ? "ប្រធានឃ្លាំង ត្រួតពិនិត្យ" : "Manager Review"),
					array("id"=>3,"name"=>$currentLang==1 ? "ផ្នែកបញ្ជាទិញ ត្រួតពិនិត្យ" : "PO Review"),
					array("id"=>4,"name"=>$currentLang==1 ? "អគ្គនាយក ត្រួតពិនិត្យ" : "Boss Review"),
				);
			}else if($getControlType=="warehouseStaff"){
				$row = $this->getAllStaffbyBranch($_data);
			}else if($getControlType=="contractorStaff"){	
				$row = $this->getAllContractorbyBranch($_data);
			}else if($getControlType=="workType"){	
				$row = $this->getAllWorkType(0,'','');
			}else if($getControlType=="property"){	
				$row = $this->getAllLand($_data);
			}else if($getControlType=="propertyType"){	
				$row = $this->getPropertyType();
			}
			
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
	
	public function getAllBranchList($_data){
		$db = $this->getAdapter();
		try{
			
			$userId = empty($_data['userId'])?0:$_data['userId'];
			$userInfo = $this->getUserInfById($userId);
			$branchList="";
			$userType=0;
			if(!empty($userInfo["value"])){
				$row = $userInfo["value"];
				$branchList = empty($row['branch_list'])?"0":$row['branch_list'];
				$userType = empty($row['userType'])?"0":$row['userType'];
			}
			
			$sql="SELECT 
					p.*
					,p.br_id AS id
					,p.project_name AS `name`
				";
			$sql.="	FROM `ln_project` AS p 
			";
			$sql.="  WHERE p.status=1 ";	
			
			if (!empty($branchList)) {
				$sql.=" AND p.br_id IN (".$branchList.") ";
			}
			$sql.=" ORDER BY p.br_id DESC ";
			$limit=" ";
			if(!empty($_data['LimitStart'])){
				$limit.=" LIMIT ".$_data['LimitStart'].",".$_data['limitRecord'];
			}else if(!empty($_data['limitRecord'])){
				$limit.=" LIMIT ".$_data['limitRecord'];
			}
			
			
			$row = $db->fetchAll($sql.$limit);
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
	public function getAllProductList($_data){
		$db = $this->getAdapter();
		try{
			
			$_data['userId'] = empty($_data['userId'])?0:$_data['userId'];
			$userLoaction=$this->getAccessPermission("prl.projectId",$_data);
			
			$sql="SELECT 
					p.*
					,p.proId AS id
					,p.proName AS `name`
					,p.image AS `productImage`
					,p.measureLabel AS measureTitle
					,proCate.categoryName
					,pl.qty AS currentQtyAll
					,pl.qtyAlert AS qtyWarningAlert
					,(SELECT GROUP_CONCAT(prl.qty) FROM st_product_location AS prl JOIN ln_project AS pj ON pj.br_id = prl.projectId  WHERE p.proId = prl.proId ".$userLoaction." ORDER BY prl.projectId ASC LIMIT 1) AS qtyByLocationList
					,(SELECT GROUP_CONCAT(pj.project_name) FROM st_product_location AS prl JOIN ln_project AS pj ON pj.br_id = prl.projectId WHERE p.proId = prl.proId ".$userLoaction." ORDER BY prl.projectId ASC LIMIT 1) AS branchNameList
					
				";
			$sql.="	FROM `st_product` AS p 
					LEFT JOIN st_category AS proCate ON proCate.id = p.categoryId
					LEFT JOIN st_product_location AS pl ON p.proId = pl.proId
			";
			$sql.="  WHERE p.status=1 ";	
			
			if (!empty($_data['isService'])) {
				$sql .= " AND p.isService=1 "; //Case Service Items
			} else {
				$sql .= " AND p.isService=0 ";
			}
			if (!empty($_data['isCountStock'])) {
				$sql .= " AND p.isCountStock= " . $_data['isCountStock'];
			}

			if (!empty($_data['branch_id'])) {
				$sql .= " AND p.proId IN (SELECT l.proId FROM `st_product_location` AS l  WHERE l.projectId=" . $_data['branch_id'] . " )";
			}
			if (!empty($_data['categoryId'])) {
				$sql .= " AND p.categoryId= " . $_data['categoryId'];
			}
			if (!empty($_data['requestId'])) {
				$sql .= " AND p.proId IN (SELECT rqd.proId FROM `st_request_po_detail` AS rqd  WHERE rqd.requestId=" . $_data['requestId'] . " AND rqd.approvedStatus=1 AND rqd.isCompletedPO=0 GROUP BY rqd.proId )";
				if (!empty($_data['purchaseId'])) { //Case Purchase Edit
					$sql .= " OR p.proId IN (SELECT pod.proId FROM `st_purchasing_detail` AS pod  WHERE pod.purchaseId=" . $_data['purchaseId'] . " GROUP BY pod.proId ) ";
				}
			}
			if (!empty($_data['isMaterial'])) {
				$sql .= " AND (SELECT id FROM `st_category` AS ct  WHERE ct.id= p.categoryId AND ct.isMaterial=" . $_data['isMaterial'] . ")";
			}
			if (!empty($_data['searchValue'])) {
				$s_where = array();
				$s_search = addslashes(trim($_data['searchValue']));
				$s_search = str_replace(' ', '', addslashes(trim($_data['searchValue'])));
				$s_where[]=" REPLACE(p.proCode,' ','')   	LIKE '%{$s_search}%'";
				$s_where[]=" REPLACE(p.proName,' ','')   	LIKE '%{$s_search}%'";
				$s_where[]=" REPLACE(p.measureLabel,' ','')  	LIKE '%{$s_search}%'";
				$s_where[]=" REPLACE(proCate.categoryName,' ','')  	LIKE '%{$s_search}%'";
				
				$sql .=' AND ( '.implode(' OR ',$s_where).')';
			}
			if (!empty($_data['productIdList'])) {
				$sql.=" AND p.proId IN (".$_data['productIdList'].") ";
			}
			$sql.=" Group BY p.proId ";
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
	
	public function getAllSupplierList($_data){
		$db = $this->getAdapter();
		try{
			
			$_data['userId'] = empty($_data['userId'])?0:$_data['userId'];
			$currentLang = empty($_data['currentLang'])?1:$_data['currentLang'];
			$localTitle1 = $currentLang == 1 ? "ក្នុងស្រក" : "Local";
			$localTitle2 = $currentLang == 1 ? "ក្រៅប្រទេស" : "OverSea";
			$sql="SELECT 
					spp.*
					,spp.id AS id
					,spp.supplierName AS `name`
					,CASE
					WHEN  spp.supplierType = 1 THEN '".$localTitle1."'
					WHEN  spp.supplierType= 2 THEN '".$localTitle2."'
					END AS supplierTypeTitle 
					
				";
			$sql.="	FROM `st_supplier` AS spp
			";
			$sql.="  WHERE spp.status=1 ";	
			
			if(!empty($_data['supplierType'])){
				$sql.= " AND spp.supplierType = ".$search['supplierType'];
			}
			
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
	
	function getGetProductCategory($_data){
		$db = $this->getAdapter();
		try{
			
			$dbGbSt = new Application_Model_DbTable_DbGlobalStock();
			$row = $dbGbSt->getAllCategoryProduct(0,'','',null);
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
	
	function getRequestNumberGenerate($_data){
		$db = $this->getAdapter();
		try{
			
			$dbGBstock = new Application_Model_DbTable_DbGlobalStock();
			$_data['dateRequest']=date("Y-m-d");
			$_data['userId'] = empty($_data['userId'])?0:$_data['userId'];
			$_data['branch_id'] = empty($_data['branchId'])?0:$_data['branchId'];
			$requestNo =$dbGBstock->generateRequestNo($_data);
			
			$result = array(
					'status' =>true,
					'value' =>$requestNo,
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
	
	function submitNewRequest($_data){
    	$db = $this->getAdapter();
		$db->beginTransaction();
    	try{
			
			$_data['userId']	= empty($_data['userId'])?0:$_data['userId'];
			$_data['branch_id']	= empty($_data['projectId'])?0:$_data['projectId'];
			$_data['requestNoLetter'] 	= empty($_data['requestNoLetter'])?"":$_data['requestNoLetter'];
			$_data['purpose']	= empty($_data['purpose'])?"":$_data['purpose'];
			$_data['note']	= empty($_data['note'])?"":$_data['note'];
			
			$listFromPost 	 = empty($_data['listRequestSubmit'])?null:$_data['listRequestSubmit'];
			$listItems 	 = Zend_Json::decode($listFromPost);
			
			$dbGBstock = new Application_Model_DbTable_DbGlobalStock();
			$_data['dateRequest']=date("Y-m-d");
			$requestNo =$dbGBstock->generateRequestNo($_data);
			
    		$arr = array(
    				'projectId'			=>$_data['branch_id'],
    				'requestNo'			=>$requestNo,
    				'requestNoLetter'	=>$_data['requestNoLetter'],
    				'purpose'			=>$_data['purpose'],
    				'date'				=>$_data['dateRequest'],
    				'note'				=>$_data['note'],
										
    				'status'			=>1,
    				'createDate'		=>date("Y-m-d H:i:s"),
    				'modifyDate'		=>date("Y-m-d H:i:s"),
    				'userId'			=>$_data['userId'],
    		);
    		$this->_name='st_request_po';
    		$requestId = $this->insert($arr);
    		
			if(!empty($listItems)) foreach($listItems AS $row){
				
				$arrDetail = array(
				
						'requestId'		=>$requestId,
						'proId'			=>$row['proId'],
						
						'qtyRequest'	=>$row['qtyRequest'],
						'qtyAdjust'		=>$row['qtyRequest'],
						'qtyVerify'		=>$row['qtyRequest'],
						'qtyApproved'	=>$row['qtyRequest'],
						
						'dateReqStockIn' =>$row['dateReqStockIn'],
						'note'			=>$row['note'],
						
						'createDate'	=>date("Y-m-d H:i:s"),
						'modifyDate'	=>date("Y-m-d H:i:s"),
						'userId'		=>$_data['userId'],
							
				);
				$this->_name='st_request_po_detail';	
				$this->insert($arrDetail);
				
			}
			
			$notify = array(
				"userAction" => 2,
				"typeNotify" => "toCheckingRequest",
				"notificationId" => $requestId,
				"branchId" => $_data['branch_id'],
			);
			$dbGBstock->pushNotificationForAndroid($notify);
			
			$db->commit();
    		return true;
    	}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
    		return false;
    	}
    }
	
	function getUsageNumberGenerate($_data){
		$db = $this->getAdapter();
		try{
			
			$dbGBstock = new Application_Model_DbTable_DbGlobalStock();
			$_data['createDate']=date("Y-m-d");
			$_data['userId'] = empty($_data['userId'])?0:$_data['userId'];
			$_data['branch_id'] = empty($_data['branchId'])?0:$_data['branchId'];
			$_data['tranType'] = 1;
			$usageNo =$dbGBstock->generateRequestUsageNo($_data);
			
			$result = array(
					'status' =>true,
					'value' =>$usageNo,
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
	
	public function getAllUsageStockList($_data){
		$db = $this->getAdapter();
		try{
			
			$_data['userId'] = empty($_data['userId'])?0:$_data['userId'];
			$currentLang = empty($_data['currentLang'])?1:$_data['currentLang'];
			
			$titleColumn = 'name_en';
			if ($currentLang == 1) {
				$titleColumn = 'name_kh';
			}
			$sql="SELECT 
				so.*
				,(SELECT project_name FROM `ln_project` WHERE br_id=so.projectId LIMIT 1) AS projectName
				
				,(SELECT w.staffName FROM `st_worker` w where w.id=so.staffId LIMIT 1) AS staffName
				,(SELECT c.staffName FROM `st_contractor` c where c.id=so.contractor LIMIT 1) AS contractorName
			
				,(SELECT pt.type_nameen FROM `ln_properties_type` pt where pt.id=so.houseType LIMIT 1) AS houseTypeTitle
				,(SELECT p.land_address FROM `ln_properties` p where p.id=so.houseId LIMIT 1) AS houseNo
				,(SELECT w.workTitle FROM `st_work_type` w where w.id=so.workType LIMIT 1) workTypeTitle
				
				,(SELECT CONCAT(COALESCE(u.last_name,''),' ',COALESCE(u.first_name,'')) FROM rms_users AS u WHERE u.id=so.userId LIMIT 1 ) AS userName
				,(SELECT ".$titleColumn." FROM ln_view WHERE type=3 and key_code = so.status LIMIT 1) AS statusTitle
								
			 ";
			
			$sql.="	
				FROM `st_stockout` AS so WHERE so.tranType=1 
			";
			if(!empty($_data['endDate'])){
				$from_date =(empty($_data['startDate']))? '1': " so.createDate >= '".date("Y-m-d",strtotime($_data['startDate']))." 00:00:00'";
				$to_date = (empty($_data['endDate']))? '1': " so.createDate <= '".date("Y-m-d",strtotime($_data['endDate']))." 23:59:59'";
				$sql.= " AND ".$from_date." AND ".$to_date;
			}
			if(!empty($_data['houseType'])){
				$sql.=" AND so.houseType = ".$_data['houseType'];
			}
			if(!empty($_data['workType'])){
				$sql.=" AND so.workType = ".$_data['workType'];
			}
			
			$sql.=" AND so.userId = ".$_data['userId'];
			$sql.=$this->getAccessPermission("so.projectId",$_data);
			$sql.=" ORDER BY so.id DESC";
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
	
	public function getUsageStockDetail($_data){
		$db = $this->getAdapter();
		try{
			
			$_data['userId'] = empty($_data['userId'])?0:$_data['userId'];
			$_data['recordId'] = empty($_data['recordId'])?0:$_data['recordId'];
			$currentLang = empty($_data['currentLang'])?1:$_data['currentLang'];
			
			$sql="select 
					stkd.* 
					,p.proName AS `proName`
					,p.proCode AS `proCode`
					,p.image AS `productImage` 
					,p.measureLabel AS measureTitle
					,(SELECT proCate.categoryName FROM  st_category AS proCate WHERE proCate.id = p.categoryId LIMIT 1) as categoryName			
			 ";
			
			$sql.="	
				FROM st_stockout_detail AS stkd 
					LEFT JOIN `st_product` AS p ON p.proId = stkd.proId 
				WHERE 1 
			";
			
			$sql.=" AND stkd.stockoutId = ".$_data['recordId'];
			$row = $db->fetchAll($sql);
		
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
	
	function submitNewUsage($_data){
    	$db = $this->getAdapter();
		$db->beginTransaction();
    	try{
			
			
			$_data['userId']	= empty($_data['userId'])?0:$_data['userId'];
			$_data['branch_id']	= empty($_data['branchId'])?0:$_data['branchId'];
			$_data['branchId']	= empty($_data['branchId'])?0:$_data['branchId'];
			$_data['reqOutNo'] 	= empty($_data['reqOutNo'])?"":$_data['reqOutNo'];
			$_data['note']	= empty($_data['note'])?"":$_data['note'];
			$_data['tranType'] = 1;
			
			$listFromPost 	 = empty($_data['listRequestSubmit'])?null:$_data['listRequestSubmit'];
			$listItems 	 = Zend_Json::decode($listFromPost);
			
			$dbGBstock = new Application_Model_DbTable_DbGlobalStock();
			$_data['dateRequest']=date("Y-m-d");
			
			$requestStock =$dbGBstock->generateRequestUsageNo($_data);
			$arr = array(
    				'projectId'		=>$_data['branch_id'],
    				'requestNo'		=>$requestStock,
    				'reqOutNo'		=>$_data['reqOutNo'],
    				'requestDate'	=>$_data['dateRequest'],
    				'staffId'		=>$_data['staffId'],
    				'contractor'	=>$_data['contractor'],
    				'workerName'	=>$_data['workerName'],
    				'houseType'		=>$_data['houseType'],
    				'houseId'		=>$_data['houseId'],
    				'workType'		=>$_data['workType'],
    				'typeofWork'	=>$_data['typeofWork'],
    				'note'			=>$_data['note'],
    				'createDate'	=>date("Y-m-d H:i:s"),
    				'status'		=>1,
    				'userId'		=>$_data['userId'],
    				'tranType'		=>1,
    			);
			
    		$this->_name='st_stockout';
    		$requestId = $this->insert($arr);
    		
			if(!empty($listItems)) foreach($listItems AS $row){
				
				$arrDetail = array(
    					'stockoutId'	=>$requestId,
    					'proId'			=>$row['proId'],
    					'qtyRequest'	=>$row['qtyRequest'],
    					'unitPrice'		=>0,
    					'totalPrice'	=>0,
    					'note'			=>$row['note'],
    				);
    				$this->_name='st_stockout_detail';
    				$id = $this->insert($arrDetail);
    				
    				$param = array(
    					'EntyQty'=> -$row['qtyRequest'],
    					'branch_id'=> $_data['branch_id'],
    					'productId'=> $row['proId'],
    				);
    				$dbGBstock->updateProductLocation($param);//Update Stock qty and new costing
    				$dbGBstock->addProductHistoryQty($_data['branch_id'],$row['proId'],3,$row['qtyRequest'],$id);//movement'	
				
			}
			
			$db->commit();
    		return true;
    	}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
    		return false;
    	}
    }
	
	function submitUpdateUsageStock($_data){
    	$db = $this->getAdapter();
		$db->beginTransaction();
    	try{
			
			
			$_data['userId']	= empty($_data['userId'])?0:$_data['userId'];
			$stockId	= empty($_data['recordId'])?0:$_data['recordId'];
			$_data['branch_id']	= empty($_data['branchId'])?0:$_data['branchId'];
			$_data['branchId']	= empty($_data['branchId'])?0:$_data['branchId'];
			$_data['reqOutNo'] 	= empty($_data['reqOutNo'])?"":$_data['reqOutNo'];
			$_data['note']	= empty($_data['note'])?"":$_data['note'];
			$_data['tranType'] = 1;
			
			$listFromPost 	 = empty($_data['listRequestSubmit'])?null:$_data['listRequestSubmit'];
			$listItems 	 = Zend_Json::decode($listFromPost);
			
			$dbGBstock = new Application_Model_DbTable_DbGlobalStock();
			$_data['dateRequest']=date("Y-m-d");
			
			$requestStock =$dbGBstock->generateRequestUsageNo($_data);
			$arr = array(
    				'reqOutNo'		=>$_data['reqOutNo'],
    				'requestDate'	=>$_data['dateRequest'],
    				'staffId'		=>$_data['staffId'],
    				'contractor'	=>$_data['contractor'],
    				'workerName'	=>$_data['workerName'],
    				'houseType'		=>$_data['houseType'],
    				'houseId'		=>$_data['houseId'],
    				'workType'		=>$_data['workType'],
    				'typeofWork'	=>$_data['typeofWork'],
    				'note'			=>$_data['note'],
    				'createDate'	=>date("Y-m-d H:i:s"),
    				'status'		=>$_data['status'],
    				'userId'		=>$_data['userId'],
    				
    			);
			
    		$this->_name='st_stockout';
			$where="id=".$stockId;
    		$this->update($arr, $where);
			
			
			$this->resetUsageStock($_data);
    		
			if($_data['status']=="1"){
				if(!empty($listItems)) foreach($listItems AS $row){
					$arrDetail = array(
						'stockoutId'	=>$stockId,
						'proId'			=>$row['proId'],
						'qtyRequest'	=>$row['qtyRequest'],
						'unitPrice'		=>0,
						'totalPrice'	=>0,
						'note'			=>$row['note'],
					);
					$this->_name='st_stockout_detail';
					$id = $this->insert($arrDetail);
					
					$param = array(
						'EntyQty'=> -$row['qtyRequest'],
						'branch_id'=> $_data['branch_id'],
						'productId'=> $row['proId'],
					);
					$dbGBstock->updateProductLocation($param);//Update Stock qty and new costing
					$dbGBstock->addProductHistoryQty($_data['branch_id'],$row['proId'],3,$row['qtyRequest'],$id);//movement'	
					
				}
			}
			
			
			$db->commit();
    		return true;
    	}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
    		return false;
    	}
    }
	
	function resetUsageStock($_data){
    	$dbs = new Application_Model_DbTable_DbGlobalStock();
		$stockId	= empty($_data['recordId'])?0:$_data['recordId'];
		$branchId	= empty($_data['branchId'])?0:$_data['branchId'];
		
		$dbStockOut = new Stockinout_Model_DbTable_DbStockout();
    	$results = $dbStockOut->getDataAllRow($stockId);
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
			
			if($_data['status']=="1"){
				$this->_name='st_stockout_detail';
				$where ='stockoutId='.$stockId;
				$this->delete($where);	
			}
    	}
    }
}