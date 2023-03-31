<?php

class Systemapi_Model_DbTable_DbApi extends Zend_Db_Table_Abstract
{
	function getUserLogin($_data){
		$db = $this->getAdapter();
		$_data['userName']=trim($_data['userName']);
		$_data['password']=trim($_data['password']);
		try{
			$sql =" SELECT
				s.id AS id
				,s.user_type AS userType
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
	function generateToken($row){
    	$db = $this->getAdapter();
    	try{
    		$this->_name = "mobile_mobile_token";
    		
    		$token = $row['mobileToken'];
    		$sql="SELECT id FROM mobile_mobile_token WHERE token='".$token."' AND userId=0 LIMIT 1";
    		$rsid = $db->fetchOne($sql);
    		if(!empty($rsid)){
    			$_arr =array(
    					'userId' 	=> $row['id'],
    					'deviceType' => $row['deviceType'],
    					'deviceModel' 		=> "",
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
	    				'userId' 	=> $row['id'],
	    				'token' 	=> $token,
	    				'deviceType' => $row['deviceType'],
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
			WHERE s.status = 1 ";
			$sql.= " AND ".$db->quoteInto('s.userId=?', $_data['userId']);
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
			
			$check = $this->checkTokenDevice($_data['token']);
			$this->_name='mobile_mobile_token';
			if(empty($check)){
				$array = array(
					'token'	=>$_data['token'],
					'deviceType'=>1,
					'date'	=>date('Y-m-d H:i:s'),
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
	    				'device_type' => $deviceType,
	    				'device_model' => "",
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
		
		$branchList 	= empty($_data['branchList'])?"0":$_data['branchList'];
		$userType 	= empty($_data['userType'])?0:$_data['userType'];
		$userId 		= empty($_data['userId'])?0:$_data['userId'];
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
	
	function getAccessUrl($module,$controller,$action,$_data = array()){
		
		$userType 	= empty($_data['userType'])?0:$_data['userType'];
		if($userType==1){return 1;}
		$db = $this->getAdapter();
			$sql = "SELECT aa.module, aa.controller, aa.action FROM rms_acl_user_access AS ua  INNER JOIN rms_acl_acl AS aa 
					ON (ua.acl_id=aa.acl_id) WHERE ua.user_type_id='".$userType."' AND aa.module='".$module."' AND aa.controller='".$controller."' AND aa.action='".$action."' limit 1";
					$rows = $db->fetchAll($sql);
	    return $rows;
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
				rq.processingStatus AS currentStep
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
		$rs = $this->getAccessUrl("requesting","checkingrequest","add",$_data);
		if(!empty($rs)){
			if(is_null($processingStatus)){
				$processingStatus =1; //forWarehouse
			}
			
		}
		$rs = $this->getAccessUrl("requesting","pcheckingrequest","add",$_data);
		if(!empty($rs)){
			//forPurchaseDept
			if(is_null($processingStatus)){
				$processingStatus =2; 
			}else{
				$processingStatus =$processingStatus.",2";
			}
			$sql.=" AND rq.checkingStatus!=2 ";
		}
		$rs = $this->getAccessUrl("requesting","approvedrequest","add",$_data);
		if(!empty($rs)){
			//forApproved
			if(is_null($processingStatus)){
				$processingStatus =3; 
			}else{
				$processingStatus =$processingStatus.",3";
			}
			$sql.=" AND rq.pCheckingStatus!=2 ";
		}
		
		$rs = $this->getAccessUrl("po","index","add",$_data);
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
	
	public function getAllActionNotification($_data){
		$db = $this->getAdapter();
		try{
			$_data['branchList'] = empty($_data['branchList'])?"0":$_data['branchList'];
			$_data['userType']	 = empty($_data['userType'])?0:$_data['userType'];
			$_data['userId'] 	 = empty($_data['userId'])?0:$_data['userId'];
	
			$userAccessVerifyDN = $this->getAccessUrl("stockinout","index",'verify',$_data);
			$userAccessTrn = $this->getAccessUrl("stockinout","transferin",'add',$_data);
			
			$row = array();
			
			$countRequest = 0;
			$countUnverify = 0;
			$countTransfer = 0;
			if(!empty($_data['limitRecord'])){
				$limitRecordRequest=empty($_data['limitRecord'])?null:$_data['limitRecord'];
				$limitStartRequest=empty($_data['LimitStart'])?null:$_data['LimitStart'];
				
				$limitRecordUnverify=empty($_data['limitRecord'])?null:$_data['limitRecord'];
				$limitStartUnverify=empty($_data['LimitStart'])?null:$_data['LimitStart'];
				
				$limitRecordTransfer=empty($_data['limitRecord'])?null:$_data['limitRecord'];
				$limitStartTransfer=empty($_data['LimitStart'])?null:$_data['LimitStart'];
				
				$_data['limitRecord'] =null;
				$_data['LimitStart'] =null;
				$countRequest = count($this->getNotifyRequest($_data));
				if(!empty($userAccessVerifyDN)){
					$countUnverify = count($this->getUnverifyReceiveDn($_data));
				}
				if(!empty($userAccessTrn)){
					$countTransfer = count($this->getAllTransferStock($_data));
				}
				
				
				$_data['limitRecord'] = $limitRecordRequest;
				$_data['LimitStart'] = $limitStartRequest;
				$row = $this->getNotifyRequest($_data);
				
				if(!empty($userAccessVerifyDN)){
					
					$_data['limitRecord'] = $limitRecordUnverify;
					$_data['LimitStart'] = $limitStartUnverify;
					$rRow = $this->getUnverifyReceiveDn($_data);
					$row = (object) array_merge((array) $rRow, (array) $row);//merg two object array list
					
					$row = (array) $row;//sort by key Value DESC
					usort($row, function ($a, $b) {return $a['recordDate'] < $b['recordDate'];});
				}
				
				if(!empty($userAccessTrn)){
					$_data['limitRecord'] = $limitRecordTransfer;
					$_data['LimitStart'] = $limitStartTransfer;
					
					$rowTransfer = $this->getAllTransferStock($_data);
					$row = (object) array_merge((array) $rowTransfer, (array) $row);//merg two object array list
					
					$row = (array) $row;//sort by key Value DESC
					usort($row, function ($a, $b) {return $a['recordDate'] < $b['recordDate'];});
				}
					
			}else{
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
			
			}
			
			
	
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
			
			$_data['branchList'] = empty($_data['branchList'])?"0":$_data['branchList'];
			$_data['userType']	 = empty($_data['userType'])?0:$_data['userType'];
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
	
	
}