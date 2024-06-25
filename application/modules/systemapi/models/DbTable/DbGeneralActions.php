<?php

class Systemapi_Model_DbTable_DbGeneralActions extends Zend_Db_Table_Abstract
{
	
	public function loginAction($_data){
		try{
			$db = new Systemapi_Model_DbTable_DbGeneralApi();
			$row = $db->getUserLogin($_data);
			if ($row['status']){
				if(!empty($row['value'])){
					$row['value']['deviceType'] = empty($_data['deviceType'])?1:$_data['deviceType'];
					$row['value']['mobileToken'] = empty($_data['mobileToken'])?1:$_data['mobileToken'];
					$row['value']['currentUserId'] = empty($_data['currentUserId'])?0:$_data['currentUserId'];
					$token = $db->generateToken($row['value']);
					
					$arrResult = array(
						"result" => $row['value'],
						"code" => "SUCCESS",
					);
				}else{
					$arrResult = array(
						"result" => $row['value'],
						"code" => "FAIL",
					);
				}
			}else{
				$arrResult = array(
					"code" => "ERR_",
					"message" => $row['value'],
				);
			}
			print_r(Zend_Json::encode($arrResult));
			exit();
		}catch(Exception $e){
			$arrResult = array(
				"code" => "ERR_",
				"message" => $e->getMessage(),
			);
			print_r(Zend_Json::encode($arrResult));
			exit();
		}
	}
	public function profileAction($search){
		try{
			$userId = empty($search['userId'])?0:$search['userId'];
			$currentLang = empty($search['currentLang'])?1:$search['currentLang'];
			$db = new Systemapi_Model_DbTable_DbGeneralApi();
			$row = $db->getUserInfById($userId);

			if ($row['status']){
				$arrResult = array(
					"result" => $row['value'],
					"code" => "SUCCESS",
				);
			}else{
				$arrResult = array(
					"code" => "ERR_",
					"message" => $row['value'],
				);
			}
			header('Content-Type: application/json');
			print_r(Zend_Json::encode($arrResult));
			exit();
		}catch(Exception $e){
			$arrResult = array(
				"code" => "ERR_",
				"message" => $e->getMessage(),
			);
			print_r(Zend_Json::encode($arrResult));
			exit();
		}
	}
	public function changePasswordAction($_data){
		try{
			$db = new Systemapi_Model_DbTable_DbGeneralApi();
			$_data['oldPassword'] = empty($_data['oldPassword'])?0:$_data['oldPassword'];
			$_data['userId'] = empty($_data['userId'])?0:$_data['userId'];
			$row = $db->checkChangePassword($_data);
			if (!$row){
				$arrResult = array(
					"code" => "FAIL",
					"message" => "INVALID_OLD_PASSWORD",
				);
			}else{
				$row = $db->changePassword($_data);
				if (!$row){
					$arrResult = array(
						"code" => "FAIL",
						"message" => "UNABLE_TO_CHANGE_PASSWORD",
					);
				}else{
					$arrResult = array(
						"code" => "SUCCESS",
						"message" => "",
					);
				}
			}
			print_r(Zend_Json::encode($arrResult));
			exit();
		}catch(Exception $e){
			$arrResult = array(
					"code" => "ERR_",
					"message" => $e->getMessage(),
			);
			print_r(Zend_Json::encode($arrResult));
			exit();
		}
	}
	public function addTokenAction($_data){
		try{
			$db = new Systemapi_Model_DbTable_DbGeneralApi();
			$recordId = $db->addAppTokenId($_data);
			if(!empty($recordId)){
				$arrResult = array(
					"code" => "SUCCESS",
					"result" =>$recordId,
				);			
			}else{
				$arrResult = array(
					"code" => "FAIL",
					"message" => "INVALID_OLD_PASSWORD",
				);
			}
			print_r(Zend_Json::encode($arrResult));
			exit();
		}catch(Exception $e){
			$arrResult = array(
					"code" => "ERR_",
					"message" => $e->getMessage(),
			);
			print_r(Zend_Json::encode($arrResult));
			exit();
		}
	}
	public function removeTokenAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['mobileToken'] = empty($search['mobileToken'])?0:$search['mobileToken'];
			
			$db = new Systemapi_Model_DbTable_DbGeneralApi();
			$row = $db->removeAppTokenId($search);
			if ($row['status']){
				$arrResult = array(
						"result" => $row['value'],
						"code" => "SUCCESS",
					);
			}else{
				$arrResult = array(
					"code" => "ERR_",
					"message" => $row['value'],
				);
			}
			
			print_r(Zend_Json::encode($arrResult));
			exit();
		}catch(Exception $e){
			$arrResult = array(
				"code" => "ERR_",
				"message" => $e->getMessage(),
			);
			print_r(Zend_Json::encode($arrResult));
			exit();
		}
	}
	public function getSystemStatusAction($_data){
		try{
			$_data['currentLang'] = empty($_data['currentLang'])?1:$_data['currentLang'];
			
			$title = "ប្រព័ន្ធកំពុងធ្វើការជួសជុល និងថែទាំ";
			$desc = "យើងខ្ញុំកំពុងរៀបចំ និងកែសម្រួលលើប្រព័ន្ធ កម្មវិធីរបស់យើងនឹងត្រឡប់មកវិញឆាប់ៗនេះ។ សូមអរគុណសម្រាប់ការអត់ធ្មត់រង់ចាំ។";
			if($_data['currentLang']==2){
				$title = "System Maintenance";
				$desc = "We are currently updating and improving our application. We expect to be back shortly. Thank you for your patience.";
			}
			$row = array();
			$row['value'] = array("status"=>1,"title"=>$title,"description"=>$desc);
			$arrResult = array(
				"result" => $row['value'],
				"code" => "SUCCESS",
			);
			print_r(Zend_Json::encode($arrResult));
			exit();
		}catch(Exception $e){
			$arrResult = array(
				"code" => "ERR_",
				"message" => $e->getMessage(),
			);
			print_r(Zend_Json::encode($arrResult));
			exit();
		}
	}
	
	public function getAppCountingTypeAction($_data){
		try{
			$_data['currentLang'] = empty($_data['currentLang'])?1:$_data['currentLang'];
			
			$type="1";
			$title = "ទិន្នន័យសង្ខេបសម្រាប់ខែនេះ";
			$desc = "ទិន្នន័យសង្ខេប ប្រតិ្តបត្តិការចំណូលចំណាយសម្រាប់ខែចុងក្រោយនេះ";
			if($_data['currentLang']==2){
				$title = "Summary data for this month";
				$desc = "Operating Income and Expenditure Summary for the this Month";
			}
			$row = array();
			$row['value'] = array("status"=>1,"type"=>$type,"title"=>$title,"description"=>$desc);
			$arrResult = array(
				"result" => $row['value'],
				"code" => "SUCCESS",
			);
			print_r(Zend_Json::encode($arrResult));
			exit();
		}catch(Exception $e){
			$arrResult = array(
				"code" => "ERR_",
				"message" => $e->getMessage(),
			);
			print_r(Zend_Json::encode($arrResult));
			exit();
		}
	}
	
	public function slidshowAction($search){
		$db = new Systemapi_Model_DbTable_DbGeneralApi();
		$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
		$row = $db->getAllSlider($search);
		if ($row['status']){
			$arrResult = array(
					"result" => $row['value'],
					"code" => "SUCCESS",
			);
		}else{
			$arrResult = array(
					"code" => "ERR_",
					"message" => $row['value'],
			);
		}
		print_r(Zend_Json::encode($arrResult));
		exit();
	}
	
	public function contactUsAction($search){
		$db = new Systemapi_Model_DbTable_DbGeneralApi();
		$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
		$row = $db->getAllContact($search);
		if ($row['status']){
			$arrResult = array(
					"result" => $row['value'],
					"code" => "SUCCESS",
			);
		}else{
			$arrResult = array(
					"code" => "ERR_",
					"message" => $row['value'],
			);
		}
		print_r(Zend_Json::encode($arrResult));
		exit();
	}
	
	public function newsAction($search){
		$db = new Systemapi_Model_DbTable_DbGeneralApi();
		$search['userId'] = empty($search['userId'])?0:$search['userId'];
		$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
		$row = $db->getAllNews($search);
		if ($row['status']){
			$arrResult = array(
					"result" => $row['value'],
					"code" => "SUCCESS",
			);
		}else{
			$arrResult = array(
					"code" => "ERR_",
					"message" => $row['value'],
			);
		}
		print_r(Zend_Json::encode($arrResult));
		exit();
	}
	
	
	
	public function deleteMyAccountAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['mobileToken'] = empty($search['mobileToken'])?0:$search['mobileToken'];
			
			$db = new Systemapi_Model_DbTable_DbGeneralApi();
			$row = $db->getUserInfById($search['userId']);
			
			$rs = $db->deleteMyAccount($search);
			if($rs){
				if ($row['status']){
					if(!empty($row['value'])){
						$userInfo = $row['value'];
						$dbGbSt = new Application_Model_DbTable_DbGlobalStock();
						$userName = empty($userInfo["userName"])?"":$userInfo["userName"];
						$subjectEmail = "Your account has been delete from BPPT Mobile";
						if(!empty($userInfo["email"])){
							$_dataEmail = array(
								"email" => $userInfo["email"],
								"subjectEmail" => $subjectEmail,
								"userName" => $userName,
								"emailFor" => "deleteAccount",
							);
							$dbGbSt->sentEmailFunction($_dataEmail);
						}
					}
				}
					
					
				
				$arrResult = array(
					"code" => "SUCCESS",
					"result" =>$rs,
				);		
				
			}else{
				$arrResult = array(
					"code" => "FAIL",
					"message" => "FAIL_TO_SUBMIT",
				);
			}
			
			print_r(Zend_Json::encode($arrResult));
			exit();
		}catch(Exception $e){
			$arrResult = array(
				"code" => "ERR_",
				"message" => $e->getMessage(),
			);
			print_r(Zend_Json::encode($arrResult));
			exit();
		}
	}
	
	public function forgetPasswordAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['mobileToken'] = empty($search['mobileToken'])?0:$search['mobileToken'];
			
			$db = new Systemapi_Model_DbTable_DbGeneralApi();
			$passwordGenerate = $db->generateNewPasswordNumber();
			$search['passwordGenerate'] = $passwordGenerate;
			
			$rs = $db->forgetMyPassword($search);
			$row = $db->getUserInfById($search['userId']);
			if($rs){
				if ($row['status']){
					if(!empty($row['value'])){
						$userInfo = $row['value'];
						$dbGbSt = new Application_Model_DbTable_DbGlobalStock();
						$userName = empty($userInfo["userName"])?"":$userInfo["userName"];
						$subjectEmail = "Forgeting password for BPPT Mobile";
						if(!empty($userInfo["email"])){
							$_dataEmail = array(
								"email" => $userInfo["email"],
								"subjectEmail" => $subjectEmail,
								"userName" => $userName,
								"accountName" => $userInfo["user_name"],
								"passwordGenerate" => $passwordGenerate,
								"emailFor" => "forgetPassword",
							);
							$dbGbSt->sentEmailFunction($_dataEmail);
						}
					}
				}
				
				$arrResult = array(
					"code" => "SUCCESS",
					"result" =>$rs,
				);		
				
			}else{
				$arrResult = array(
					"code" => "FAIL",
					"message" => "FAIL_TO_SUBMIT",
				);
			}
			
			print_r(Zend_Json::encode($arrResult));
			exit();
		}catch(Exception $e){
			$arrResult = array(
				"code" => "ERR_",
				"message" => $e->getMessage(),
			);
			print_r(Zend_Json::encode($arrResult));
			exit();
		}
	}
	
	public function getFormOptionSelectAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
			
			$db = new Systemapi_Model_DbTable_DbGeneralApi();
			$row = $db->getFormOptionSelect($search);
			if ($row['status']){
				$arrResult = array(
						"result" => $row['value'],
						"code" => "SUCCESS",
					);
			}else{
				$arrResult = array(
					"code" => "ERR_",
					"message" => $row['value'],
				);
			}
			
			print_r(Zend_Json::encode($arrResult));
			exit();
		}catch(Exception $e){
			$arrResult = array(
				"code" => "ERR_",
				"message" => $e->getMessage(),
			);
			print_r(Zend_Json::encode($arrResult));
			exit();
		}
	}
	
	public function serverBranchInfoAction($search){
		$db = new Systemapi_Model_DbTable_DbGeneralApi();
		$search['userId'] = empty($search['userId'])?0:$search['userId'];
		$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
		$row = $db->getServerBranchInfo($search);
		if ($row['status']){
			$arrResult = array(
					"result" => $row['value'],
					"code" => "SUCCESS",
			);
		}else{
			$arrResult = array(
					"code" => "ERR_",
					"message" => $row['value'],
			);
		}
		print_r(Zend_Json::encode($arrResult));
		exit();
	}
	
	public function countingSummmaryAction($search){
		$db = new Systemapi_Model_DbTable_DbGeneralApi();
		$search['userId'] = empty($search['userId'])?0:$search['userId'];
		$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
		$row = $db->getCountingSaleSummary($search);
		if ($row['status']){
			$arrResult = array(
					"result" => $row['value'],
					"code" => "SUCCESS",
			);
		}else{
			$arrResult = array(
					"code" => "ERR_",
					"message" => $row['value'],
			);
		}
		print_r(Zend_Json::encode($arrResult));
		exit();
	}
	public function saleInfoListAction($search){
		$db = new Systemapi_Model_DbTable_DbGeneralApi();
		$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
		$row = $db->getSaleInfoList($search);
		if ($row['status']){
			$arrResult = array(
				"result" => $row['value'],
				"code" => "SUCCESS",
			);
		}else{
			$arrResult = array(
				"code" => "ERR_",
				"message" => $row['value'],
			);
		}
		print_r(Zend_Json::encode($arrResult));
		exit();
	}
	public function saleCanceledListAction($search){
		$db = new Systemapi_Model_DbTable_DbGeneralApi();
		$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
		$row = $db->getSaleCanceledList($search);
		if ($row['status']){
			$arrResult = array(
				"result" => $row['value'],
				"code" => "SUCCESS",
			);
		}else{
			$arrResult = array(
				"code" => "ERR_",
				"message" => $row['value'],
			);
		}
		print_r(Zend_Json::encode($arrResult));
		exit();
	}
	public function issuedHouseListAction($search){
		$db = new Systemapi_Model_DbTable_DbGeneralApi();
		$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
		$row = $db->getIssuedHouseList($search);
		if ($row['status']){
			$arrResult = array(
				"result" => $row['value'],
				"code" => "SUCCESS",
			);
		}else{
			$arrResult = array(
				"code" => "ERR_",
				"message" => $row['value'],
			);
		}
		print_r(Zend_Json::encode($arrResult));
		exit();
	}
	public function issuedLayoutListAction($search){
		$db = new Systemapi_Model_DbTable_DbGeneralApi();
		$search['currentLang'] = empty($search['currentLang'])?1:$search['currentLang'];
		$row = $db->getIssuedLayoutList($search);
		if ($row['status']){
			$arrResult = array(
				"result" => $row['value'],
				"code" => "SUCCESS",
			);
		}else{
			$arrResult = array(
				"code" => "ERR_",
				"message" => $row['value'],
			);
		}
		print_r(Zend_Json::encode($arrResult));
		exit();
	}
	
	
}