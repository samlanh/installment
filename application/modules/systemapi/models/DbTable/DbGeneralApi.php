<?php

class Systemapi_Model_DbTable_DbGeneralApi extends Zend_Db_Table_Abstract
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
				,(SELECT ut.`user_type` FROM `rms_acl_user_type` AS ut WHERE ut.user_type_id = s.user_type LIMIT 1) AS userTypeTitle
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
			
			$row = empty($row) ? null:$row;
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
	
	
	function submitEditUserProfile($_data){
    	$db = $this->getAdapter();
		$db->beginTransaction();
    	try{
			
			$_data['userId'] = empty($_data['userId'])?0:$_data['userId'];
			$_data['dob'] 	 = empty($_data['dob']) ? "" :$_data['dob'];
			
			$arr = array(
				'dob' 		=> $_data['dob'],
				'current_address' 		=> $_data['current_address'],
				'nationality' 	=> $_data['nationality'],
			);
			$part = PUBLIC_PATH . '/images/photo/profile/';
			if (!file_exists($part)) {
				mkdir($part, 0777, true);
			}
			if(!empty($_data['photo'])){
				$fileExtension="jpg";
				if(!empty($_data['imageName'])){
					$tem = explode(".", $_data['imageName']);
					$fileExtension=end($tem);
					if( end($tem) !="jpg" || end($tem) !="png"){
						$fileExtension="jpg";
					}
				}
				$image_name = "user_profile_" . date("Y") . date("m") . date("d") . time() . ".".$fileExtension;
				$outputFile = $part.$image_name;
				$fileHandle = fopen($outputFile,"wb");
				fwrite($fileHandle,base64_decode($_data["photo"]));
				fclose($fileHandle);
				$arr['photo'] = $image_name;
				
				if(!empty($_data['oldPhotoName'])){
					unlink($part . $_data['oldPhotoName']);
				}
			}
    		$where = 'id = ' . $_data['userId'];
			$this->_name='rms_users';
			$this->update($arr, $where);
    		
			
			$db->commit();
    		return true;
    	}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
    		return false;
    	}
    }
	
	
	public function getAllContact($search){
    	$db = $this->getAdapter();
    	try{
    		$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
    		
    		$sql=" SELECT 
						ad.title,ad.description
					FROM `mobile_about` AS a,
						`mobile_about_detail` AS ad
					WHERE a.id=ad.abouts_id
						AND ad.lang= $currentLang AND a.status=1 ";
    		if (!empty($search['isForHome'])){
				$sql.=" AND a.isForHome = 1 ";
			}
    		$rowabout = $db->fetchAll($sql);
    		
    		$sql=" SELECT
    		l.*,
    		ld.title,ld.description
    		FROM `mobile_location` AS l,
    		`mobile_location_detail` AS ld
    		WHERE l.id=ld.location_id
    		AND ld.lang= $currentLang ";
    		
    		$rowcontact = $db->fetchRow($sql);
    		
    		$all_result = array('about'=>$rowabout,'contact'=>$rowcontact);
    		
    		$result = array(
    				'status' =>true,
    				'value' =>$all_result,
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
	public function getAllNews($search){
    	$db = $this->getAdapter();
    	try{
	    		$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
	    		$base_url = Zend_Controller_Front::getInstance()->getBaseUrl()."/images/";
		    	
		    	$sql="SELECT
			    	act.*,
			    	(SELECT ad.description FROM `mobile_news_event_detail` AS ad WHERE ad.news_id = act.`id` AND ad.lang=$currentLang LIMIT 1) AS description,
			    	(SELECT ad.title FROM `mobile_news_event_detail` AS ad WHERE ad.news_id = act.`id` AND ad.lang=$currentLang LIMIT 1) AS title,
			    	DATE_FORMAT(act.`publish_date`, '%d-%m-%Y') AS publishDateFormat,
			    	act.image_feature,
			    	(SELECT u.first_name FROM `rms_users` AS u WHERE u.id = act.`user_id` LIMIT 1) AS user_name,
			    	CASE
					   	WHEN  act.`status` = 1 THEN '$base_url'
					  END AS imageUrl
					,CASE
						WHEN  re.`is_read` IS NULL THEN 0
						ELSE  re.`is_read`
						END AS isRead
		    	";
		    	$sql.=" FROM `mobile_news_event` AS act  ";
				$userId = empty($search['userId'])?0:$search['userId'];
				$sql.=" LEFT JOIN `mobile_news_event_read` AS re ON act.`id` = re.newsId  AND re.`userId` =$userId ";
				
		    	$sql.=" WHERE act.`status` =1 ";
		    	$sql_order= "  ORDER BY act.publish_date DESC,act.`id` DESC";
		    	
		    	if (!empty($search['limit'])){
		    		$sql_order.= "  LIMIT ".$search['limit'];
		    	}
				
				//New Added
				if(!empty($search['LimitStart'])){
					$sql_order.=" LIMIT ".$search['LimitStart'].",".$search['limitRecord'];
				}else if(!empty($search['limitRecord'])){
					$sql_order.=" LIMIT ".$search['limitRecord'];
				}
				if(!empty($search['unreadRecord'])){
					$sql.=" AND  0 = CASE
						WHEN  re.`is_read` IS NULL THEN 0
						ELSE  re.`is_read`
						END  ";
					return $row = $db->fetchAll($sql.$sql_order);
				}
			
		    $row = $db->fetchAll($sql.$sql_order);
		    
		    $sql.=" AND is_feature=2 ";
		    
		    $row_feature = $db->fetchAll($sql.$sql_order);
		     
		    $merch_result = array('feature_news'=>$row_feature,'normal_news'=>$row);
		   
		    $result = array(
		    		'status' =>true,
		    		'value' =>$merch_result,
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
	
	
	
	function deleteMyAccount($_data){
		$db = $this->getAdapter();
		$userId = empty($_data['userId'])?"0":$_data['userId'];
		$_data['branchId'] = empty($_data['branchId'])?"0":$_data['branchId'];
		$_data['emailAddress'] = empty($_data['emailAddress'])?"0":$_data['emailAddress'];
		
		
		try{
			$arr = array(
				'active'  => -1,
				'modifyDate' 		 => date("Y-m-d H:i:s"),
			);
			$where = 'id = ' . $userId." AND email ='".$_data['emailAddress']."'";
			$this->_name='rms_users';
			$this->update($arr, $where);
			return true;
			
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			return false;
		}
	}
	
	
	function generateNewPasswordNumber(){
		$digits = 8;
		return str_pad(rand(0, pow(10, $digits)-1), $digits, '0', STR_PAD_LEFT);
	}
	function forgetMyPassword($_data){
		$db = $this->getAdapter();
		$userId = empty($_data['userId'])?"0":$_data['userId'];
		$_data['branchId'] = empty($_data['branchId'])?"0":$_data['branchId'];
		$_data['emailAddress'] = empty($_data['emailAddress'])?"0":$_data['emailAddress'];
		$_data['passwordGenerate'] = empty($_data['passwordGenerate'])?"0":$_data['passwordGenerate'];
		
		try{
			$arr = array(
				'password' 			=> md5($_data['passwordGenerate']),
				'modifyDate' 		 => date("Y-m-d H:i:s"),
			);
			$where = "email ='".$_data['emailAddress']."'";
			$this->_name='rms_users';
			$this->update($arr, $where);
			return true;
			
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			return false;
		}
	}
	
	function getAllServerBranch($_data){
		$db = $this->getAdapter();
		$currentLang = empty($_data['currentLang'])?1:$_data['currentLang'];
		$title="titleKh";
		if($currentLang==2){
			$title="title";
		}
		$sql="SELECT 
				sb.`id` AS id
				,sb.$title AS `name`
			FROM 
				`mobile_server_branch` AS sb 
			WHERE sb.status=1
			";
		$sql .=" ORDER BY sb.id ASC,sb.$title ASC ";
		return $db->fetchAll($sql);
	}
	
	function getAllSystemProject($_data){
		$db = $this->getAdapter();
		$currentLang = empty($_data['currentLang'])?1:$_data['currentLang'];
		$title="project_name";
		if($currentLang==2){
			$title="project_name";
		}
		$sql="SELECT 
				p.`br_id` AS id
				,p.$title AS `name`
			FROM 
				`ln_project` AS p 
			WHERE p.status=1
			";
		$sql .=" ORDER BY p.br_id ASC,p.$title ASC ";
		return $db->fetchAll($sql);
	}
	function getAllPropertyType($_data){
		$db = $this->getAdapter();
		$currentLang = empty($_data['currentLang'])?1:$_data['currentLang'];
		$title="type_namekh";
		if($currentLang==2){
			$title="type_nameen";
		}
		$sql="SELECT 
				pt.`id` AS id
				,pt.$title AS `name`
			FROM 
				`ln_properties_type` AS pt 
			WHERE pt.status=1
			";
		$sql .=" ORDER BY pt.id ASC,pt.$title ASC ";
		return $db->fetchAll($sql);
	}
	public function getVewOptoinTypeByType($_data){
		$db = $this->getAdapter();
		
		$currentLang = empty($_data['currentLang'])?1:$_data['currentLang'];
		$type = empty($_data['viewType'])?0:$_data['viewType'];
		$title="name_kh";
		if($currentLang==2){
			$title="name_en";
		}
		$sql="SELECT 
				pt.`key_code` AS id
				,pt.$title AS `name`
			FROM 
				`ln_view` AS pt 
			WHERE pt.status=1 AND pt.name_en!=''
			";
		$sql .=" AND type = $type ";
		$sql .=" ORDER BY pt.id ASC,pt.$title ASC ";
		return $db->fetchAll($sql);
	}
	function getAllLayoutType($_data){
		$db = $this->getAdapter();
		$currentLang = empty($_data['currentLang'])?1:$_data['currentLang'];
		$title="type_namekh";
		if($currentLang==2){
			$title="type_nameen";
		}
		$sql="SELECT 
				DISTINCT rp.`layout_type` AS id
				,rp.`layout_type` AS `name`
			FROM 
				`ln_receiveplong` AS rp 
			WHERE 1
			";
		$sql .=" ORDER BY rp.`layout_type` ASC ";
		return $db->fetchAll($sql);
	}
	
	public function getFormOptionSelect($_data){
		$db = $this->getAdapter();
		try{
			$currentLang = empty($_data['currentLang'])?1:$_data['currentLang'];
			$getControlType = empty($_data['getControlType'])?"status":$_data['getControlType'];
			$_data['userId'] = empty($_data['userId'])?0:$_data['userId'];
			$_data['branchId'] = empty($_data['branchId'])?0:$_data['branchId'];
			$row=array();
			
			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
			if($getControlType=="status"){
				$row = array(
					array("id"=>1,"name"=>$currentLang==1 ? "ប្រើប្រាស់" : "Active"),
					array("id"=>2,"name"=>$currentLang==1 ? "មិនប្រើប្រាស់" : "Deactive"),
				);
			}else if($getControlType=="serverBranch"){
				$row = $this->getAllServerBranch($_data);
			}else if($getControlType=="systemBranch"){
				$row = $this->getAllSystemProject($_data);
			}else if($getControlType=="propertyType"){
				$row = $this->getAllPropertyType($_data);
			}else if($getControlType=="schedulePmtType"){
				$_data["viewType"] = 25;
				$row = $this->getVewOptoinTypeByType($_data);
			}else if($getControlType=="cancelType"){
				$row = array(
					array("id"=>1,"name"=>$tr->translate('CANCEL_WITHOUT_RETURN')),
					array("id"=>2,"name"=>$tr->translate('CANCEL_WITH_RETURN_AMOUNT')),
				);
			}else if($getControlType=="layoutType"){
				$row = $this->getAllLayoutType($_data);
			}else if($getControlType=="issueHouseType"){
				$row = array(
					array("id"=>1,"name"=>$tr->translate('IS_PAYOFF')),
					array("id"=>2,"name"=>$tr->translate('PAY_INSTALLMENT')),
				);
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
	
	public function getServerBranchInfo($search){
		$db = $this->getAdapter();
		try{
			$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
			$title="titleKh";
			if($currentLang==2){
				$title="title";
			}
			$id = empty($search['id'])?0:$search['id'];
			$sql=" SELECT 
						sb.*,
						sb.$title AS titleFlex
					FROM `mobile_server_branch` AS sb
					WHERE  sb.status=1 
				";
			$sql.=" AND sb.id=".$id;
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
	
	public function getCountingSale($search){
		$db = $this->getAdapter();
		try{
			$countingType = empty($search['countingType'])?1:$search['countingType'];
			$saleType = empty($search['saleType'])?0:$search['saleType'];
			$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
			$title="titleKh";
			if($currentLang==2){
				$title="title";
			}
			$id = empty($search['id'])?0:$search['id'];
			$sql=" SELECT 
						COUNT(s.id) AS countingAmt
					FROM `ln_sale` s 
					WHERE s.`status` =1 AND s.`is_cancel` = 0
				";
			$sql.="";
			if($saleType==1){ // Deposit Sale
				$sql.=" AND s.payment_id = 1 ";
			}else if($saleType==2){ // Issued Agreement
				$sql.=" AND s.payment_id != 1 ";
			}
			
			$forDate = empty($search['forDate']) ? date("Y-m-d") : $search['forDate'];
			if($countingType==1){// counting 1 Month
			
				$thisMonth = date("Y-m",strtotime($forDate));
				$thisMonth = empty($search['isPreviousTime']) ? $thisMonth : date("Y-m",strtotime("$forDate -1 month"));
				if($saleType==1){ // Deposit Sale
					$sql.=" AND DATE_FORMAT(s.`buy_date`, '%Y-%m')='$thisMonth' ";
				}else if($saleType==2){ // Issued Agreement
					$sql.=" AND DATE_FORMAT(s.`agreement_date`, '%Y-%m')='$thisMonth' ";
				}
			}else if($countingType==2){// Counting 1 Week
				$thisDay = date("Y-m-d",strtotime($forDate));
				$thisDay = empty($search['isPreviousTime']) ? $thisDay : date("Y-m-d",strtotime("$forDate -7 day"));
				
				if(date('N', strtotime($thisDay))==7){ // = sunday
					$startDayOfTheWeek = date('Y-m-d', strtotime("$thisDay-6 day"));
					$endDayOfTheWeek = $thisDay;
				}else{
					$startDayOfTheWeek = date('Y-m-d',strtotime("$thisDay last Sunday +1 day"));
					$endDayOfTheWeek = date('Y-m-d',strtotime("$thisDay next Sunday"));
				}
				if($saleType==1){ // Deposit Sale
					$sql.=" AND DATE_FORMAT(s.`buy_date`, '%Y-%m-%d')>='$startDayOfTheWeek'  AND DATE_FORMAT(s.`buy_date`, '%Y-%m-%d')<='$endDayOfTheWeek'";
				}else if($saleType==2){ // Issued Agreement
					$sql.=" AND DATE_FORMAT(s.`agreement_date`, '%Y-%m-%d')>='$startDayOfTheWeek' AND DATE_FORMAT(s.`agreement_date`, '%Y-%m-%d')<='$endDayOfTheWeek' ";
				}
			}else if($countingType==3){// Counting 1 Day
				$thisDay = date("Y-m-d",strtotime($forDate));
				$thisDay = empty($search['isPreviousTime']) ? $thisDay : date("Y-m-d",strtotime("$forDate -1 day"));
				
				if($saleType==1){ // Deposit Sale
					$sql.=" AND DATE_FORMAT(s.`buy_date`, '%Y-%m-%d')='$thisDay' ";
				}else if($saleType==2){ // Issued Agreement
					$sql.=" AND DATE_FORMAT(s.`agreement_date`, '%Y-%m-%d')='$thisDay' ";
				}
			}
    		$row = $db->fetchOne($sql);
			return $row;
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$result = array();
			return $result;
		}
	}
	public function getCountingSaleCanceled($search){
		$db = $this->getAdapter();
		try{
			$countingType = empty($search['countingType'])?1:$search['countingType'];
			$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
			$title="titleKh";
			if($currentLang==2){
				$title="title";
			}
			$id = empty($search['id'])?0:$search['id'];
			$sql=" SELECT 
						COUNT(sc.`id`) AS countingAmt
					FROM `ln_sale_cancel` AS sc 
						, `ln_sale` AS s 
					WHERE s.id = sc.`sale_id` 
						AND sc.`status` = 1 
						AND s.`is_cancel` =1 
						AND s.`status` = 1 
				";
			$sql.="";
			
			$forDate = empty($search['forDate']) ? date("Y-m-d") : $search['forDate'];
			if($countingType==1){// counting 1 Month
			
				$thisMonth = date("Y-m",strtotime($forDate));
				$thisMonth = empty($search['isPreviousTime']) ? $thisMonth : date("Y-m-d",strtotime("$forDate -1 month"));
				$sql.=" AND DATE_FORMAT(sc.`create_date`, '%Y-%m')='$thisMonth' ";
			}else if($countingType==2){// Counting 1 Week
				$thisDay = date("Y-m-d",strtotime($forDate));
				$thisDay = empty($search['isPreviousTime']) ? $thisDay : date("Y-m-d",strtotime("$forDate -7 day"));
				
				if(date('N', strtotime($thisDay))==7){ // = sunday
					$startDayOfTheWeek = date('Y-m-d', strtotime("$thisDay-6 day"));
					$endDayOfTheWeek = $thisDay;
				}else{
					$startDayOfTheWeek = date('Y-m-d',strtotime("$thisDay last Sunday +1 day"));
					$endDayOfTheWeek = date('Y-m-d',strtotime("$thisDay next Sunday"));
				}
				$sql.=" AND DATE_FORMAT(sc.`create_date`, '%Y-%m-%d')>='$startDayOfTheWeek'  AND DATE_FORMAT(sc.`create_date`, '%Y-%m-%d')<='$endDayOfTheWeek'";
			}else if($countingType==3){// Counting 1 Day
				$thisDay = date("Y-m-d",strtotime($forDate));
				$thisDay = empty($search['isPreviousTime']) ? $thisDay : date("Y-m-d",strtotime("$forDate -1 day"));
				$sql.=" AND DATE_FORMAT(sc.`create_date`, '%Y-%m-%d')='$thisDay' ";
			}
    		$row = $db->fetchOne($sql);
			return $row;
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$result = array();
			return $result;
		}
	}
	public function getCountingIssuedPlong($search){
		$db = $this->getAdapter();
		try{
			$countingType = empty($search['countingType'])?1:$search['countingType'];
			$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
			$title="titleKh";
			if($currentLang==2){
				$title="title";
			}
			$id = empty($search['id'])?0:$search['id'];
			$sql=" SELECT 
						COUNT(iss.`id`) AS countingAmt
					FROM `ln_issueplong` AS iss 
							, `ln_sale` AS s 
					WHERE iss.`sale_id` = s.`id`
						AND s.`is_cancel` =0
						AND s.`status` = 1 
				";
			$sql.="";
			
			$forDate = empty($search['forDate']) ? date("Y-m-d") : $search['forDate'];
			if($countingType==1){// counting 1 Month
			
				$thisMonth = date("Y-m",strtotime($forDate));
				$thisMonth = empty($search['isPreviousTime']) ? $thisMonth : date("Y-m-d",strtotime("$forDate -1 month"));
				$sql.=" AND DATE_FORMAT(iss.`issue_date`, '%Y-%m')='$thisMonth' ";
			}else if($countingType==2){// Counting 1 Week
				$thisDay = date("Y-m-d",strtotime($forDate));
				$thisDay = empty($search['isPreviousTime']) ? $thisDay : date("Y-m-d",strtotime("$forDate -7 day"));
				
				if(date('N', strtotime($thisDay))==7){ // = sunday
					$startDayOfTheWeek = date('Y-m-d', strtotime("$thisDay-6 day"));
					$endDayOfTheWeek = $thisDay;
				}else{
					$startDayOfTheWeek = date('Y-m-d',strtotime("$thisDay last Sunday +1 day"));
					$endDayOfTheWeek = date('Y-m-d',strtotime("$thisDay next Sunday"));
				}
				$sql.=" AND DATE_FORMAT(iss.`issue_date`, '%Y-%m-%d')>='$startDayOfTheWeek'  AND DATE_FORMAT(iss.`issue_date`, '%Y-%m-%d')<='$endDayOfTheWeek'";
			}else if($countingType==3){// Counting 1 Day
				$thisDay = date("Y-m-d",strtotime($forDate));
				$thisDay = empty($search['isPreviousTime']) ? $thisDay : date("Y-m-d",strtotime("$forDate -1 day"));
				$sql.=" AND DATE_FORMAT(iss.`issue_date`, '%Y-%m-%d')='$thisDay' ";
			}
    		$row = $db->fetchOne($sql);
			return $row;
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$result = array();
			return $result;
		}
	}
	
	public function getCountingIssuedHouse($search){
		$db = $this->getAdapter();
		try{
			$countingType = empty($search['countingType'])?1:$search['countingType'];
			$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
			$title="titleKh";
			if($currentLang==2){
				$title="title";
			}
			$id = empty($search['id'])?0:$search['id'];
			$sql=" SELECT 
						COUNT(iss.`id`) AS countingAmt
					FROM `ln_issue_house` AS iss 
						, `ln_sale` AS s 
					WHERE iss.`sale_id` = s.`id`
						AND iss.`status` = 1
						AND s.`is_cancel` =0
						AND s.`status` = 1 
				";
			$sql.="";
			
			$forDate = empty($search['forDate']) ? date("Y-m-d") : $search['forDate'];
			if($countingType==1){// counting 1 Month
			
				$thisMonth = date("Y-m",strtotime($forDate));
				$thisMonth = empty($search['isPreviousTime']) ? $thisMonth : date("Y-m-d",strtotime("$forDate -1 month"));
				$sql.=" AND DATE_FORMAT(iss.`issue_date`, '%Y-%m')='$thisMonth' ";
			}else if($countingType==2){// Counting 1 Week
				$thisDay = date("Y-m-d",strtotime($forDate));
				$thisDay = empty($search['isPreviousTime']) ? $thisDay : date("Y-m-d",strtotime("$forDate -7 day"));
				
				if(date('N', strtotime($thisDay))==7){ // = sunday
					$startDayOfTheWeek = date('Y-m-d', strtotime("$thisDay-6 day"));
					$endDayOfTheWeek = $thisDay;
				}else{
					$startDayOfTheWeek = date('Y-m-d',strtotime("$thisDay last Sunday +1 day"));
					$endDayOfTheWeek = date('Y-m-d',strtotime("$thisDay next Sunday"));
				}
				$sql.=" AND DATE_FORMAT(iss.`issue_date`, '%Y-%m-%d')>='$startDayOfTheWeek'  AND DATE_FORMAT(iss.`issue_date`, '%Y-%m-%d')<='$endDayOfTheWeek'";
			}else if($countingType==3){// Counting 1 Day
				$thisDay = date("Y-m-d",strtotime($forDate));
				$thisDay = empty($search['isPreviousTime']) ? $thisDay : date("Y-m-d",strtotime("$forDate -1 day"));
				$sql.=" AND DATE_FORMAT(iss.`issue_date`, '%Y-%m-%d')='$thisDay' ";
			}
    		$row = $db->fetchOne($sql);
			return $row;
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$result = array();
			return $result;
		}
	}
	
	public function getCountingSaleSummary($search){
		$db = $this->getAdapter();
		try{
			$search['countingType'] = empty($search['countingType'])?1:$search['countingType'];
			$search['saleType'] = 1;
			$search['forDate'] = date("Y-m-d");
			$depositThisTime = $this->getCountingSale($search);
			$depositThisTime = empty($depositThisTime) ? 0 : $depositThisTime;
			
			$search['isPreviousTime'] = true;
			$depositPreviousTime = $this->getCountingSale($search);
			$depositPreviousTime = empty($depositPreviousTime) ? 0 : $depositPreviousTime;
			
			$different = $depositThisTime - $depositPreviousTime;
			
			$percentage = 100;
			if($depositThisTime<=0){
				$percentage = 0;
			}
			if($depositPreviousTime>0){
				$percentage = (abs(($depositPreviousTime - $depositThisTime)) / $depositPreviousTime) * 100;
				if($percentage>0){
					$percentage = rtrim(rtrim(number_format($percentage, 2, ".", ""), '0'), '.');
				}
			}else{
				$percentage = "--";
			}
		
			$arrDeposit = array(
				"previousCount"=>sprintf('%02d',$depositPreviousTime),
				"countingAmt"=>sprintf('%02d',$depositThisTime),
				"isIncrease"=>($different)>=0 ? "1" : "0",
				"percentage"=>$percentage,
			);
			
			$search['saleType'] = 2;
			$search['isPreviousTime'] = false;
			$agreementThisTime = $this->getCountingSale($search);
			$agreementThisTime = empty($agreementThisTime) ? 0 : $agreementThisTime;
			
			$search['isPreviousTime'] = true;
			$agreementPreviousTime = $this->getCountingSale($search);
			$agreementPreviousTime = empty($agreementPreviousTime) ? 0 : $agreementPreviousTime;
			
			$differentAgree = $agreementThisTime - $agreementPreviousTime;
			$percentageAgree = 100;
			if($agreementThisTime<=0){
				$percentageAgree = 0;
			}
			if($agreementPreviousTime>0){
				$percentageAgree = (abs(($agreementPreviousTime - $agreementThisTime)) / $agreementPreviousTime) * 100;
				if($percentageAgree>0){
					$percentageAgree = rtrim(rtrim(number_format($percentageAgree, 2, ".", ""), '0'), '.');
				}
			}else{
				$percentageAgree = "--";
			}
		
			$arrAgreementIssueed = array(
				"previousCount"=>sprintf('%02d',$agreementPreviousTime),
				"countingAmt"=>sprintf('%02d',$agreementThisTime),
				"isIncrease"=>($differentAgree)>=0 ? "1" : "0",
				"percentage"=>$percentageAgree,
			);
			
			$search['isPreviousTime'] = false;
			$canceledThisTime = $this->getCountingSaleCanceled($search);
			$canceledThisTime = empty($canceledThisTime) ? 0 : $canceledThisTime;
			
			$search['isPreviousTime'] = true;
			$canceledPreviousTime = $this->getCountingSale($search);
			$canceledPreviousTime = empty($canceledPreviousTime) ? 0 : $canceledPreviousTime;
			
			$differentCanceled = $canceledThisTime - $canceledPreviousTime;
			$percentageCancel = 100;
			if($canceledThisTime<=0){
				$percentageCancel = 0;
			}
			if($canceledPreviousTime>0){
				$percentageCancel = (abs(($canceledPreviousTime - $canceledThisTime)) / $canceledPreviousTime) * 100;
				if($percentageCancel>0){
					$percentageCancel = rtrim(rtrim(number_format($percentageCancel, 2, ".", ""), '0'), '.');
				}
			}else{
				$percentageCancel = "--";
			}
			
			$arrCanceled = array(
				"previousCount"=>sprintf('%02d',$canceledPreviousTime),
				"countingAmt"=>sprintf('%02d',$canceledThisTime),
				"isIncrease"=>($differentCanceled)>=0 ? "1" : "0",
				"percentage"=> $percentageCancel,
			);
			
			
			$search['isPreviousTime'] = false;
			$issuedPlongThisTime = $this->getCountingIssuedPlong($search);
			$issuedPlongThisTime = empty($issuedPlongThisTime) ? 0 : $issuedPlongThisTime;
			
			$search['isPreviousTime'] = true;
			$issuedPlongPreviousTime = $this->getCountingIssuedPlong($search);
			$issuedPlongPreviousTime = empty($issuedPlongPreviousTime) ? 0 : $issuedPlongPreviousTime;
			
			$differentPlong = $issuedPlongThisTime - $issuedPlongPreviousTime;
			$percentagePlong = 100;
			if($issuedPlongThisTime<=0){
				$percentagePlong = 0;
			}
			if($issuedPlongPreviousTime>0){
				$percentagePlong = (abs(($issuedPlongPreviousTime - $issuedPlongThisTime)) / $issuedPlongPreviousTime) * 100;
				if($percentagePlong>0){
					$percentagePlong = rtrim(rtrim(number_format($percentagePlong, 2, ".", ""), '0'), '.');
				}
			}else{
				$percentagePlong = "--";
			}
			$arrPlong = array(
				"previousCount"=>sprintf('%02d',$issuedPlongPreviousTime),
				"countingAmt"=>sprintf('%02d',$issuedPlongThisTime),
				"isIncrease"=>($differentPlong)>=0 ? "1" : "0",
				"percentage"=>$percentagePlong,
			);
			
			
			$search['isPreviousTime'] = false;
			$issuedHouseThisTime = $this->getCountingIssuedHouse($search);
			$issuedHouseThisTime = empty($issuedHouseThisTime) ? 0 : $issuedHouseThisTime;
			
			$search['isPreviousTime'] = true;
			$issuedHousePreviousTime = $this->getCountingIssuedHouse($search);
			$issuedHousePreviousTime = empty($issuedHousePreviousTime) ? 0 : $issuedHousePreviousTime;
			
			$differentHouse = $issuedHouseThisTime - $issuedHousePreviousTime;
			$percentageHouse = 100;
			if($issuedHouseThisTime<=0){
				$percentageHouse = 0;
			}
			if($issuedHousePreviousTime>0){
				$percentageHouse = (abs(($issuedHousePreviousTime - $issuedHouseThisTime)) / $issuedHousePreviousTime) * 100;
				if($percentageHouse>0){
					$percentageHouse = rtrim(rtrim(number_format($percentageHouse, 2, ".", ""), '0'), '.');
				}
			}else{
				$percentageHouse = "--";
			}
			$arrHouse = array(
				"previousCount"=>sprintf('%02d',$issuedHousePreviousTime),
				"countingAmt"=>sprintf('%02d',$issuedHouseThisTime),
				"isIncrease"=>($differentHouse)>=0 ? "1" : "0",
				"percentage"=>$percentageHouse,
			);
		
			$row = array();
			$row["depositSale"] = $arrDeposit;
			$row["issuedAgreement"] = $arrAgreementIssueed;
			$row["saleCanceled"] = $arrCanceled;
			$row["saleIssuedLayout"] = $arrPlong;
			$row["saleIssuedHouse"] = $arrHouse;
			
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
	
	public function getSaleInfoList($search){
		$db = $this->getAdapter();
		try{
			$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
			$userId = empty($search['userId'])?0:$search['userId'];
			
			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
			$projectName = "project_name";
			$propertyType = "type_nameen";
			$title = "name_en";
			if($currentLang==1){
				$projectName = "project_name";
				$propertyType = "type_namekh";
				$title = "name_kh";
			}
			
			$sql=" SELECT 
						s.`id`
						,(SELECT pr.$projectName FROM `ln_project` AS pr WHERE pr.br_id = s.`branch_id` LIMIT 1) AS projectName
						,(SELECT pr.logo FROM `ln_project` AS pr WHERE pr.br_id = s.`branch_id` LIMIT 1) AS projectLogo
						,s.payment_id AS scheduleType
						,(SELECT v.$title FROM `ln_view` AS v WHERE v.key_code =s.payment_id AND v.type = 25 LIMIT 1) AS schedulePaymentType
						,s.`buy_date` AS buyDate
						,s.`agreement_date` AS agreementDate
						,s.`amount_build` AS amtBuild
						,s.`build_start` AS buildStart

						,s.`price_before` AS priceBefore
						,s.`discount_amount` AS discountAmt
						,s.`discount_percent` AS dicountPercent
						,s.`other_discount` AS otherDiscount
						,s.`price_sold` AS priceSold
						,CASE 
							WHEN s.interest_policy > 0 
								THEN (SELECT st.title FROM rms_interestsetting AS st WHERE st.id=s.interest_policy AND st.type=1 LIMIT 1 )
							ELSE CONCAT(COALESCE(s.`interest_rate`,0),' %') 
						END AS interestRate
						,s.`for_installamount` AS amountInstallment
						,FORMAT(s.total_duration,0) AS totalDuration
						,s.startcal_date AS startDatePayment
						,s.end_line AS endDatePayment
						,(SELECT (vp.totalPrincipalPaid-vp.totalCredit) FROM `v_getsaleprincipalpaid` vp WHERE vp.saleId=s.`id` LIMIT 1 ) totalPaidAmount
						,(`s`.`price_sold`-(SELECT vp.totalPrincipalPaid-vp.totalCredit FROM `v_getsaleprincipalpaid` vp WHERE vp.saleId=s.`id` LIMIT 1 )) balanceRemain
						,p.`land_address` AS propertyCode
						,p.`street`
						,p.`hardtitle` AS hardTitle
						,(SELECT pt.$propertyType FROM `ln_properties_type` AS pt WHERE pt.id = p.`property_type` LIMIT 1) AS propertyTypeTitle
						,cl.`name_kh` AS clientName
						,(SELECT v.$title FROM `ln_view` AS v WHERE v.key_code = cl.`sex` AND v.type =11 LIMIT 1) AS clientGender
						,cl.`phone` AS clientTel
						,cl.`hname_kh` AS withClientName
						,(SELECT v.$title FROM `ln_view` AS v WHERE v.key_code = cl.`ksex` AND v.type =11 LIMIT 1) AS withClientGender
						,cl.`lphone` AS withClientTel
						,(SELECT CONCAT(u.last_name,' ',u.first_name) FROM rms_users AS u WHERE u.id = s.user_id LIMIT 1) AS userName
					";
			$sql.="
				FROM 
					`ln_sale` AS s JOIN `ln_client` AS cl ON cl.`client_id` = s.`client_id`
					 LEFT JOIN `ln_properties` AS p ON p.id = s.`house_id` 
				WHERE  1
			";
    	
	    	$from_date =(empty($search['startDate']))? '1': " s.`buy_date` >= '".date("Y-m-d",strtotime($search['startDate']))." 00:00:00'";
	    	$to_date = (empty($search['endDate']))? '1': " s.`buy_date` <= '".date("Y-m-d",strtotime($search['endDate']))." 23:59:59'";
	    	$ordering=" ORDER BY s.`buy_date` DESC,s.id DESC";
			$saleType = empty($search['saleType'])?0:$search['saleType'];
			if($saleType==1){ // Deposit Sale
				$sql.=" AND s.payment_id = 1 ";
			}else if($saleType==2){ // Issued Agreement
				$sql.=" AND s.payment_id != 1 ";
				$from_date =(empty($search['startDate']))? '1': " s.`agreement_date` >= '".date("Y-m-d",strtotime($search['startDate']))." 00:00:00'";
				$to_date = (empty($search['endDate']))? '1': " s.`agreement_date` <= '".date("Y-m-d",strtotime($search['endDate']))." 23:59:59'";
				$ordering=" ORDER BY s.`agreement_date` DESC,s.id DESC";
			}
			
			$where = " AND ".$from_date." AND ".$to_date;
			if(!empty($search['searchBox'])){
	    		$s_where=array();
	    		$s_search=addslashes(trim($search['searchBox']));
	    		$s_search = str_replace(' ', '', addslashes(trim($search['searchBox'])));
	    		$s_where[]= " REPLACE(p.`land_address`,' ','') LIKE '%{$s_search}%'";
	    		$s_where[]= " REPLACE(p.`street`,' ','') LIKE '%{$s_search}%'";
	    		$s_where[]= " REPLACE(p.hardtitle,' ','') LIKE '%{$s_search}%'";
	    		$s_where[]= " REPLACE(cl.`name_kh`,' ','') LIKE '%{$s_search}%'";
	    		$s_where[]= " REPLACE(cl.`hname_kh`,' ','') LIKE '%{$s_search}%'";
	    		$s_where[]= " REPLACE(s.`price_sold`,' ','') LIKE '%{$s_search}%'";
	    		$s_where[]= " REPLACE(s.`total_duration`,' ','') LIKE '%{$s_search}%'";
	    		
	    		$where.=' AND ('.implode(' OR ', $s_where).')';
	    	}
			if(!empty($search['projectId'])){
	    		$where.=" AND s.`branch_id`=".$search['projectId'];
	    	}
	    	if(!empty($search['propertyType'])){
	    		$where.=" AND p.`property_type`=".$search['propertyType'];
	    	}
			if(!empty($search['schedulePaymentType'])){
	    		$where.=" AND s.payment_id=".$search['schedulePaymentType'];
	    	}
	    	
			$limit=" ";
			if(!empty($search['LimitStart'])){
				$limit.=" LIMIT ".$search['LimitStart'].",".$search['limitRecord'];
			}else if(!empty($search['limitRecord'])){
	    		$limit.=" LIMIT ".$search['limitRecord'];
	    	}
			 
			$row = $db->fetchAll($sql.$where.$ordering.$limit);
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
	
	public function getSaleCanceledList($search){
		$db = $this->getAdapter();
		try{
			$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
			$userId = empty($search['userId'])?0:$search['userId'];
			
			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
			$projectName = "project_name";
			$propertyType = "type_nameen";
			$title = "name_en";
			if($currentLang==1){
				$projectName = "project_name";
				$propertyType = "type_namekh";
				$title = "name_kh";
			}
			$sql=" SELECT 
						sc.*
						,(SELECT pr.$projectName FROM `ln_project` AS pr WHERE pr.br_id = sc.`branch_id` LIMIT 1) AS projectName
						,(SELECT pr.logo FROM `ln_project` AS pr WHERE pr.br_id = sc.`branch_id` LIMIT 1) AS projectLogo
						,p.`land_address` AS propertyCode
						,p.`street`
						,p.`hardtitle` AS hardTitle
						,(SELECT pt.$propertyType FROM `ln_properties_type` AS pt WHERE pt.id = p.`property_type` LIMIT 1) AS propertyTypeTitle
						
						,CASE
							WHEN  sc.cancel_type =1 THEN '".$tr->translate('CANCEL_WITHOUT_RETURN')."'
							WHEN  sc.cancel_type =2 THEN '".$tr->translate('CANCEL_WITH_RETURN_AMOUNT')."'
							ELSE  ''
						END AS cancelTypeTitle
						,(SELECT v.$title FROM `ln_view` AS v WHERE v.`type` = 32 AND v.key_code = sc.condition_return LIMIT 1) AS conditionReturnTitle
						,(SELECT SUM(e.total_amount) FROM ln_expense AS e WHERE e.cancelSale_id = sc.id AND e.status=1 LIMIT 1) AS totalReturnPaid
						,s.buy_date AS buyDate
						,s.`price_sold` AS priceSold
						,cl.`name_kh` AS clientName
						,(SELECT v.$title FROM `ln_view` AS v WHERE v.key_code = cl.`sex` AND v.type =11 LIMIT 1) AS clientGender
						,cl.`phone` AS clientTel
						,cl.`hname_kh` AS withClientName
						,(SELECT v.$title FROM `ln_view` AS v WHERE v.key_code = cl.`ksex` AND v.type =11 LIMIT 1) AS withClientGender
						,cl.`lphone` AS withClientTel
						,(SELECT CONCAT(u.last_name,' ',u.first_name) FROM rms_users AS u WHERE u.id = sc.user_id LIMIT 1) AS userName
					";
			$sql.="
				FROM `ln_sale_cancel` AS sc  JOIN `ln_sale` AS s ON s.id = sc.`sale_id` AND s.`is_cancel` =1  AND s.`status` = 1
					LEFT JOIN `ln_properties` AS p ON p.id = s.`house_id` 
					LEFT JOIN `ln_client` AS cl ON cl.`client_id` = s.`client_id`
				WHERE  sc.`status` = 1 
			";
    	
	    	$from_date =(empty($search['startDate']))? '1': " sc.create_date >= '".date("Y-m-d",strtotime($search['startDate']))." 00:00:00'";
	    	$to_date = (empty($search['endDate']))? '1': " sc.create_date <= '".date("Y-m-d",strtotime($search['endDate']))." 23:59:59'";
	    	$where = " AND ".$from_date." AND ".$to_date;
			if(!empty($search['searchBox'])){
	    		$s_where=array();
	    		$s_search=addslashes(trim($search['searchBox']));
	    		$s_search = str_replace(' ', '', addslashes(trim($search['searchBox'])));
	    		$s_where[]= " REPLACE(p.`land_address`,' ','') LIKE '%{$s_search}%'";
	    		$s_where[]= " REPLACE(p.`street`,' ','') LIKE '%{$s_search}%'";
	    		$s_where[]= " REPLACE(p.hardtitle,' ','') LIKE '%{$s_search}%'";
	    		$s_where[]= " REPLACE(cl.`name_kh`,' ','') LIKE '%{$s_search}%'";
	    		$s_where[]= " REPLACE(cl.`hname_kh`,' ','') LIKE '%{$s_search}%'";
	    		
	    		$where.=' AND ('.implode(' OR ', $s_where).')';
	    	}
			if(!empty($search['projectId'])){
	    		$where.=" AND sc.`branch_id`=".$search['projectId'];
	    	}
	    	if(!empty($search['propertyType'])){
	    		$where.=" AND p.`property_type`=".$search['propertyType'];
	    	}
			if(!empty($search['cancelType'])){
	    		$where.=" AND sc.cancel_type=".$search['cancelType'];
	    	}
			
			//$where.=" AND iss.user_id = ".$userId;
	    	$ordering=" ORDER BY sc.create_date DESC,sc.id DESC";
			$limit=" ";
			if(!empty($search['LimitStart'])){
				$limit.=" LIMIT ".$search['LimitStart'].",".$search['limitRecord'];
			}else if(!empty($search['limitRecord'])){
	    		$limit.=" LIMIT ".$search['limitRecord'];
	    	}
			 
			$row = $db->fetchAll($sql.$where.$ordering.$limit);
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
	public function getIssuedHouseList($search){
		$db = $this->getAdapter();
		try{
			$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
			$userId = empty($search['userId'])?0:$search['userId'];
			
			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
			$projectName = "project_name";
			$propertyType = "type_nameen";
			$title = "name_en";
			if($currentLang==1){
				$projectName = "project_name";
				$propertyType = "type_namekh";
				$title = "name_kh";
			}
			
			$sql=" SELECT 
						iss.*
						,(SELECT pr.$projectName FROM `ln_project` AS pr WHERE pr.br_id = iss.`branch_id` LIMIT 1) AS projectName
						,(SELECT pr.logo FROM `ln_project` AS pr WHERE pr.br_id = iss.`branch_id` LIMIT 1) AS projectLogo
						,p.`land_address` AS propertyCode
						,p.`street`
						,p.`hardtitle` AS hardTitle
						,(SELECT pt.$propertyType FROM `ln_properties_type` AS pt WHERE pt.id = p.`property_type` LIMIT 1) AS propertyTypeTitle
						,iss.`issue_date` AS  issuedDate
						,CASE    
							WHEN  iss.`payment_id` = 1 THEN '".$tr->translate("IS_PAYOFF")."'
							WHEN  iss.`payment_id` = 2 THEN '".$tr->translate("PAY_INSTALLMENT")."' 
						END AS paymentTypeTitle
						,s.`price_sold` AS priceSold
						,s.buy_date AS buyDate
						,cl.`name_kh` AS clientName
						,(SELECT v.$title FROM `ln_view` AS v WHERE v.key_code = cl.`sex` AND v.type =11 LIMIT 1) AS clientGender
						,cl.`phone` AS clientTel
						,cl.`hname_kh` AS withClientName
						,(SELECT v.$title FROM `ln_view` AS v WHERE v.key_code = cl.`ksex` AND v.type =11 LIMIT 1) AS withClientGender
						,cl.`lphone` AS withClientTel
						,(SELECT CONCAT(u.last_name,' ',u.first_name) FROM rms_users AS u WHERE u.id = iss.user_id LIMIT 1) AS userName
					";
			$sql.="
				FROM 
					`ln_issue_house` AS iss JOIN  `ln_sale` AS s ON iss.`sale_id` = s.`id` AND s.`is_cancel` =0 AND s.`status` = 1 
					LEFT JOIN `ln_properties` AS p ON p.id = s.`house_id` 
					LEFT JOIN `ln_client` AS cl ON cl.`client_id` = s.`client_id`
				WHERE  iss.`status` = 1
			";
    	
	    	$from_date =(empty($search['startDate']))? '1': " iss.issue_date >= '".date("Y-m-d",strtotime($search['startDate']))." 00:00:00'";
	    	$to_date = (empty($search['endDate']))? '1': " iss.issue_date <= '".date("Y-m-d",strtotime($search['endDate']))." 23:59:59'";
	    	$where = " AND ".$from_date." AND ".$to_date;
			if(!empty($search['searchBox'])){
	    		$s_where=array();
	    		$s_search=addslashes(trim($search['searchBox']));
	    		$s_search = str_replace(' ', '', addslashes(trim($search['searchBox'])));
	    		$s_where[]= " REPLACE(p.`land_address`,' ','') LIKE '%{$s_search}%'";
	    		$s_where[]= " REPLACE(p.`street`,' ','') LIKE '%{$s_search}%'";
	    		$s_where[]= " REPLACE(p.hardtitle,' ','') LIKE '%{$s_search}%'";
	    		$s_where[]= " REPLACE(cl.`name_kh`,' ','') LIKE '%{$s_search}%'";
	    		$s_where[]= " REPLACE(cl.`hname_kh`,' ','') LIKE '%{$s_search}%'";
	    		
	    		$where.=' AND ('.implode(' OR ', $s_where).')';
	    	}
			if(!empty($search['projectId'])){
	    		$where.=" AND iss.`branch_id`=".$search['projectId'];
	    	}
	    	if(!empty($search['propertyType'])){
	    		$where.=" AND p.`property_type`=".$search['propertyType'];
	    	}
			if(!empty($search['issueHouseType'])){
	    		$where.=" AND iss.`payment_id`=".$search['issueHouseType'];
	    	}
			
			//$where.=" AND iss.user_id = ".$userId;
	    	$ordering=" ORDER BY iss.issue_date DESC,iss.id DESC";
			$limit=" ";
			if(!empty($search['LimitStart'])){
				$limit.=" LIMIT ".$search['LimitStart'].",".$search['limitRecord'];
			}else if(!empty($search['limitRecord'])){
	    		$limit.=" LIMIT ".$search['limitRecord'];
	    	}
			 
			$row = $db->fetchAll($sql.$where.$ordering.$limit);
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
	
	public function getIssuedLayoutList($search){
		$db = $this->getAdapter();
		try{
			$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
			$userId = empty($search['userId'])?0:$search['userId'];
			
			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
			$projectName = "project_name";
			$propertyType = "type_nameen";
			$title = "name_en";
			if($currentLang==1){
				$projectName = "project_name";
				$propertyType = "type_namekh";
				$title = "name_kh";
			}
			
			$sql=" SELECT 
						iss.*
						,(SELECT pr.$projectName FROM `ln_project` AS pr WHERE pr.br_id = s.`branch_id` LIMIT 1) AS projectName
						,(SELECT pr.logo FROM `ln_project` AS pr WHERE pr.br_id = s.`branch_id` LIMIT 1) AS projectLogo
						,p.`land_address` AS propertyCode
						,p.`street`
						,p.`hardtitle` AS hardTitle
						,(SELECT pt.$propertyType FROM `ln_properties_type` AS pt WHERE pt.id = p.`property_type` LIMIT 1) AS propertyTypeTitle
						,iss.`date` AS  issuedDate
						,iss.`layout_type` AS  layoutTypeTitle
						,s.buy_date AS buyDate
						,s.`price_sold` AS priceSold
						,cl.`name_kh` AS clientName
						,(SELECT v.$title FROM `ln_view` AS v WHERE v.key_code = cl.`sex` AND v.type =11 LIMIT 1) AS clientGender
						,cl.`phone` AS clientTel
						,cl.`hname_kh` AS withClientName
						,(SELECT v.$title FROM `ln_view` AS v WHERE v.key_code = cl.`ksex` AND v.type =11 LIMIT 1) AS withClientGender
						,cl.`lphone` AS withClientTel
						,(SELECT CONCAT(u.last_name,' ',u.first_name) FROM rms_users AS u WHERE u.id = iss.user_id LIMIT 1) AS userName
					";
			$sql.="
				FROM 
					`ln_receiveplong` AS iss JOIN  `ln_sale` AS s ON iss.`sale_id` = s.`id` AND s.`is_cancel` =0 AND s.`status` = 1 
					LEFT JOIN `ln_properties` AS p ON p.id = s.`house_id` 
					LEFT JOIN `ln_client` AS cl ON cl.`client_id` = s.`client_id`
				WHERE  1
			";
    	
	    	$from_date =(empty($search['startDate']))? '1': " iss.date >= '".date("Y-m-d",strtotime($search['startDate']))." 00:00:00'";
	    	$to_date = (empty($search['endDate']))? '1': " iss.date <= '".date("Y-m-d",strtotime($search['endDate']))." 23:59:59'";
	    	$where = " AND ".$from_date." AND ".$to_date;
			if(!empty($search['searchBox'])){
	    		$s_where=array();
	    		$s_search=addslashes(trim($search['searchBox']));
	    		$s_search = str_replace(' ', '', addslashes(trim($search['searchBox'])));
	    		$s_where[]= " REPLACE(p.`land_address`,' ','') LIKE '%{$s_search}%'";
	    		$s_where[]= " REPLACE(p.`street`,' ','') LIKE '%{$s_search}%'";
	    		$s_where[]= " REPLACE(p.hardtitle,' ','') LIKE '%{$s_search}%'";
	    		$s_where[]= " REPLACE(cl.`name_kh`,' ','') LIKE '%{$s_search}%'";
	    		$s_where[]= " REPLACE(cl.`hname_kh`,' ','') LIKE '%{$s_search}%'";
	    		
	    		$where.=' AND ('.implode(' OR ', $s_where).')';
	    	}
			if(!empty($search['projectId'])){
	    		$where.=" AND iss.`branch_id`=".$search['projectId'];
	    	}
	    	if(!empty($search['propertyType'])){
	    		$where.=" AND p.`property_type`=".$search['propertyType'];
	    	}
			if(!empty($search['layoutType'])){
	    		$where.=" AND iss.`layout_type`='".$search['layoutType']."'";
	    	}
			
			//$where.=" AND iss.user_id = ".$userId;
	    	$ordering=" ORDER BY iss.date DESC,iss.id DESC";
			$limit=" ";
			if(!empty($search['LimitStart'])){
				$limit.=" LIMIT ".$search['LimitStart'].",".$search['limitRecord'];
			}else if(!empty($search['limitRecord'])){
	    		$limit.=" LIMIT ".$search['limitRecord'];
	    	}
			 
			$row = $db->fetchAll($sql.$where.$ordering.$limit);
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
	
	
	public function getCustomerPaymentList($search){
		$db = $this->getAdapter();
		try{
			$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
			$projectName = "project_name";
			$propertyType = "type_nameen";
			$title = "name_en";
			if($currentLang==1){
				$projectName = "project_name";
				$propertyType = "type_namekh";
				$title = "name_kh";
			}
			$sql=" SELECT 
						crm.id AS recordId
						,'payment' AS recordType
						,crm.`receipt_no` AS recordNum
						,crm.`date_input` AS dateInput
						
						,(SELECT pr.$projectName FROM `ln_project` AS pr WHERE pr.br_id = crm.`branch_id` LIMIT 1) AS projectName
						,(SELECT pr.logo FROM `ln_project` AS pr WHERE pr.br_id = crm.`branch_id` LIMIT 1) AS projectLogo
						,s.`price_sold` AS priceSold
						,crm.`date_payment` AS datePayment
						
						,crm.`total_interest_permonthpaid` AS interestPaid
						,crm.`total_principal_permonthpaid` AS principalPaid
						,crm.`penalize_amount` AS penalizeAmount
						,crm.`extra_payment` AS extraPayment
						,crm.`total_payment` AS totalPaymet
						,crm.`recieve_amount` AS recieveAmount
						,CASE 
							WHEN crm.recieve_amount >0 THEN 0
							ELSE 1 
						END AS isVoid
						,crm.void_reason AS voidReason
						,crm.void_date AS voidDate
						,crm.void_by AS voidBy
						,(SELECT CONCAT(u.last_name,' ',u.first_name) FROM `rms_users` AS u WHERE u.id = `crm`.`void_by` LIMIT 1) AS voidByUserName
						,`crm`.`payment_option` AS paymentOption
						,(SELECT `v`.$title FROM `ln_view` AS v  WHERE `v`.`key_code` = `crm`.`payment_option` AND `v`.`type` = 7 LIMIT 1) AS paymentOptionTitle
						,`crm`.`payment_method` AS paymentMethod
						,(SELECT `v`.$title FROM `ln_view` AS v  WHERE `v`.`key_code` = `crm`.`payment_method` AND `v`.`type` = 2 LIMIT 1) AS paymentMethodTitle
						,(SELECT b.bank_name FROM `st_bank` AS b WHERE b.id=`crm`.`bank_id` LIMIT 1) AS bankName
						
						,crm.`cheque` AS paymentMethodNumber
						,crm.is_closed AS `isClosed`
						
						,p.`land_address` AS propertyCode
						,p.`street`
						,p.`hardtitle` AS hardTitle
						,(SELECT pt.$propertyType FROM `ln_properties_type` AS pt WHERE pt.id = p.`property_type` LIMIT 1) AS propertyTypeTitle
						,cl.`name_kh` AS clientName
						,(SELECT v.$title FROM `ln_view` AS v WHERE v.key_code = cl.`sex` AND v.type =11 LIMIT 1) AS clientGender
						,cl.`phone` AS clientTel
						,cl.`hname_kh` AS withClientName
						,(SELECT v.$title FROM `ln_view` AS v WHERE v.key_code = cl.`ksex` AND v.type =11 LIMIT 1) AS withClientGender
						,cl.`lphone` AS withClientTel
						,(SELECT CONCAT(u.last_name,' ',u.first_name) FROM rms_users AS u WHERE u.id = s.user_id LIMIT 1) AS userName
				";
			$sql.="
				FROM 
					`ln_client_receipt_money` AS crm JOIN  `ln_sale` AS s ON crm.`sale_id` = s.`id`
					LEFT JOIN `ln_properties` AS p ON p.id = s.`house_id` 
					LEFT JOIN `ln_client` AS cl ON cl.`client_id` = s.`client_id`
			";
			$sql.=" WHERE crm.`status` = 1 ";
			
			$from_date =(empty($search['startDate']))? '1': " crm.date_input >= '".date("Y-m-d",strtotime($search['startDate']))." 00:00:00'";
	    	$to_date = (empty($search['endDate']))? '1': " crm.date_input <= '".date("Y-m-d",strtotime($search['endDate']))." 23:59:59'";
	    	$sql.= " AND ".$from_date." AND ".$to_date;
			if(!empty($search['searchBox'])){
	    		$s_where=array();
	    		$s_search=addslashes(trim($search['searchBox']));
	    		$s_search = str_replace(' ', '', addslashes(trim($search['searchBox'])));
	    		$s_where[]= " REPLACE(p.`land_address`,' ','') LIKE '%{$s_search}%'";
	    		$s_where[]= " REPLACE(p.`street`,' ','') LIKE '%{$s_search}%'";
	    		$s_where[]= " REPLACE(p.hardtitle,' ','') LIKE '%{$s_search}%'";
	    		$s_where[]= " REPLACE(cl.`name_kh`,' ','') LIKE '%{$s_search}%'";
	    		$s_where[]= " REPLACE(cl.`phone`,' ','') LIKE '%{$s_search}%'";
	    		$s_where[]= " REPLACE(crm.`receipt_no`,' ','') LIKE '%{$s_search}%'";
	    		
	    		$sql.=' AND ('.implode(' OR ', $s_where).')';
	    	}
			if(!empty($search['projectId'])){
	    		$sql.=" AND crm.`branch_id`=".$search['projectId'];
	    	}
			if(!empty($search['paymentMethod'])){
	    		$sql.=" AND crm.`payment_method`=".$search['paymentMethod'];
	    	}
	    	if(!empty($search['propertyType'])){
	    		$sql.=" AND p.`property_type`=".$search['propertyType'];
	    	}
			$sql.=" ORDER BY crm.`date_input` DESC, crm.id DESC ";
    		$row = $db->fetchAll($sql);
			return $row;
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$result = array();
			return $result;
		}
	}
	
	public function getOtherIncomeList($search){
		$db = $this->getAdapter();
		try{
			$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
			$projectName = "project_name";
			$propertyType = "type_nameen";
			$title = "name_en";
			if($currentLang==1){
				$projectName = "project_name";
				$propertyType = "type_namekh";
				$title = "name_kh";
			}
			$sql=" SELECT 
						inc.id AS recordId
						,CASE 
							WHEN inc.incomeType =2 THEN 'boreyFee' 
							ELSE 'otherIncome'  
						END AS recordType
						,inc.`invoice` AS recordNum
						,inc.`date` AS dateInput
						,(SELECT pr.$projectName FROM `ln_project` AS pr WHERE pr.br_id = inc.`branch_id` LIMIT 1) AS projectName
						,(SELECT pr.logo FROM `ln_project` AS pr WHERE pr.br_id = inc.`branch_id` LIMIT 1) AS projectLogo
						,inc.`title` AS title
						,inc.`description` AS description
						
						,`inc`.`payment_id` AS paymentMethod
						,(SELECT `v`.$title FROM `ln_view` AS v  WHERE `v`.`key_code` = `inc`.`payment_id` AND `v`.`type` = 2 LIMIT 1) AS paymentMethodTitle
						,(SELECT b.bank_name FROM `st_bank` AS b WHERE b.id=`inc`.`bank_id` LIMIT 1) AS bankName
						,inc.cheque AS paymentMethodNumber
						,inc.total_amount AS totalAmount
						,inc.from_date AS boreyFeeStartDate
						,inc.next_date AS boreyFeeExpireDate
						,inc.is_closed AS `isClosed`
						
						,p.`land_address` AS propertyCode
						,p.`street`
						,p.`hardtitle` AS hardTitle
						,(SELECT pt.$propertyType FROM `ln_properties_type` AS pt WHERE pt.id = p.`property_type` LIMIT 1) AS propertyTypeTitle
						,cl.`name_kh` AS clientName
						,(SELECT v.$title FROM `ln_view` AS v WHERE v.key_code = cl.`sex` AND v.type =11 LIMIT 1) AS clientGender
						,cl.`phone` AS clientTel
						,cl.`hname_kh` AS withClientName
						,(SELECT v.$title FROM `ln_view` AS v WHERE v.key_code = cl.`ksex` AND v.type =11 LIMIT 1) AS withClientGender
						,cl.`lphone` AS withClientTel
						,(SELECT CONCAT(u.last_name,' ',u.first_name) FROM rms_users AS u WHERE u.id = inc.user_id LIMIT 1) AS userName
				";
			$sql.="
				FROM 
					`ln_income` AS inc
						LEFT JOIN `ln_sale` AS s ON inc.`sale_id` = s.`id`
						LEFT JOIN `ln_properties` AS p ON p.id = s.`house_id` 
						LEFT JOIN `ln_client` AS cl ON cl.`client_id` = s.`client_id`
			";
			$sql.=" WHERE inc.`status` = 1 ";
			
			$from_date =(empty($search['startDate']))? '1': " inc.`date` >= '".date("Y-m-d",strtotime($search['startDate']))." 00:00:00'";
	    	$to_date = (empty($search['endDate']))? '1': " inc.`date` <= '".date("Y-m-d",strtotime($search['endDate']))." 23:59:59'";
	    	$sql.= " AND ".$from_date." AND ".$to_date;
			if(!empty($search['searchBox'])){
	    		$s_where=array();
	    		$s_search=addslashes(trim($search['searchBox']));
	    		$s_search = str_replace(' ', '', addslashes(trim($search['searchBox'])));
	    		$s_where[]= " REPLACE(p.`land_address`,' ','') LIKE '%{$s_search}%'";
	    		$s_where[]= " REPLACE(p.`street`,' ','') LIKE '%{$s_search}%'";
	    		$s_where[]= " REPLACE(p.hardtitle,' ','') LIKE '%{$s_search}%'";
	    		$s_where[]= " REPLACE(cl.`name_kh`,' ','') LIKE '%{$s_search}%'";
	    		$s_where[]= " REPLACE(cl.`phone`,' ','') LIKE '%{$s_search}%'";
	    		$s_where[]= " REPLACE(inc.`invoice`,' ','') LIKE '%{$s_search}%'";
	    		
	    		$sql.=' AND ('.implode(' OR ', $s_where).')';
	    	}
			if(!empty($search['projectId'])){
	    		$sql.=" AND inc.`branch_id`=".$search['projectId'];
	    	}
			if(!empty($search['paymentMethod'])){
	    		$sql.=" AND `inc`.`payment_id`=".$search['paymentMethod'];
	    	}
	    	if(!empty($search['propertyType'])){
	    		$sql.=" AND p.`property_type`=".$search['propertyType'];
	    	}
			$sql.=" ORDER BY inc.`date` DESC, inc.id DESC ";
    		$row = $db->fetchAll($sql);
			return $row;
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$result = array();
			return $result;
		}
	}
	
}