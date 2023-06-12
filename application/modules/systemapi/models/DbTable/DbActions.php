<?php

class Systemapi_Model_DbTable_DbActions extends Zend_Db_Table_Abstract
{
	
	public function loginAction($_data){
		try{
			$db = new Systemapi_Model_DbTable_DbApi();
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
			$db = new Systemapi_Model_DbTable_DbApi();
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
			$db = new Systemapi_Model_DbTable_DbApi();
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
			$db = new Systemapi_Model_DbTable_DbApi();
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
			
			$db = new Systemapi_Model_DbTable_DbApi();
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
	
	public function slidshowAction($search){
		$db = new Systemapi_Model_DbTable_DbApi();
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
	
	public function requestDetailAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['mobileToken'] = empty($search['mobileToken'])?0:$search['mobileToken'];
			
			$db = new Systemapi_Model_DbTable_DbApi();
			$row = $db->getRequestDetail($search);
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
	
	
	public function checkingRequestPOAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['mobileToken'] = empty($search['mobileToken'])?0:$search['mobileToken'];
			
			$db = new Systemapi_Model_DbTable_DbApi();
			$submitRequest = $db->submitCheckingRequestPO($search);
			
			if($submitRequest){
				$arrResult = array(
					"code" => "SUCCESS",
					"result" =>$submitRequest,
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
	
	public function submitVerifyRequestPOAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['mobileToken'] = empty($search['mobileToken'])?0:$search['mobileToken'];
			
			$db = new Systemapi_Model_DbTable_DbApi();
			$submitRequest = $db->submitVerifyRequestPO($search);
			
			if($submitRequest){
				$arrResult = array(
					"code" => "SUCCESS",
					"result" =>$submitRequest,
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
	public function submitApproveRequestPOAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['mobileToken'] = empty($search['mobileToken'])?0:$search['mobileToken'];
			
			$db = new Systemapi_Model_DbTable_DbApi();
			$submitRequest = $db->submitApproveRequestPO($search);
			
			if($submitRequest){
				$arrResult = array(
					"code" => "SUCCESS",
					"result" =>$submitRequest,
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
	public function allRequestNotifyAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['mobileToken'] = empty($search['mobileToken'])?0:$search['mobileToken'];
			$search['isAllRequest']="1";
			
			$db = new Systemapi_Model_DbTable_DbApi();
			$row = $db->getAllRequestNotify($search);
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
	public function checkingRequestNotifyAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['mobileToken'] = empty($search['mobileToken'])?0:$search['mobileToken'];
			$search['checkingStatus']="1";
			
			$db = new Systemapi_Model_DbTable_DbApi();
			$row = $db->getAllRequestNotify($search);
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
	
	public function verifyRequestNotifyAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['mobileToken'] = empty($search['mobileToken'])?0:$search['mobileToken'];
			$search['pCheckingStatus']="1";
			
			$db = new Systemapi_Model_DbTable_DbApi();
			$row = $db->getAllRequestNotify($search);
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
	
	public function approveRequestNotifyAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['mobileToken'] = empty($search['mobileToken'])?0:$search['mobileToken'];
			$search['approveStatus']="1";
			
			$db = new Systemapi_Model_DbTable_DbApi();
			$row = $db->getAllRequestNotify($search);
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
	
	public function requestForPONotifyAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['mobileToken'] = empty($search['mobileToken'])?0:$search['mobileToken'];
			$search['forPurchasing']="1";
			
			$db = new Systemapi_Model_DbTable_DbApi();
			$row = $db->getAllRequestNotify($search);
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
	
	public function getAllListPurchaseByRequestAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['userType'] = empty($search['userType'])?0:$search['userType'];
			$search['mobileToken'] = empty($search['mobileToken'])?0:$search['mobileToken'];
		
			$db = new Systemapi_Model_DbTable_DbApi();
			$row = $db->getAllPurchasingByRequest($search);
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
	
	public function PORequestToReceiveNotifyAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['userType'] = empty($search['userType'])?0:$search['userType'];
			$search['mobileToken'] = empty($search['mobileToken'])?0:$search['mobileToken'];
			$search['forReceivedDn'] = 1;
		
			$db = new Systemapi_Model_DbTable_DbApi();
			$row = $db->getAllPurchasingByRequest($search);
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
	
	public function getDetailPurchaseByRequestAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['mobileToken'] = empty($search['mobileToken'])?0:$search['mobileToken'];
			
			$db = new Systemapi_Model_DbTable_DbApi();
			$row = $db->getDetailPurchaseByRequest($search);
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
	
	public function getDNToVerifyNotifyAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['mobileToken'] = empty($search['mobileToken'])?0:$search['mobileToken'];
			
			$db = new Systemapi_Model_DbTable_DbApi();
			$row = $db->getAllDNToVerifyNotify($search);
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
	public function submitVerifyDNAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['mobileToken'] = empty($search['mobileToken'])?0:$search['mobileToken'];
			
			$db = new Systemapi_Model_DbTable_DbApi();
			$submitVerifyDN = $db->submitVerifyDN($search);
			
			if($submitVerifyDN){
				$arrResult = array(
					"code" => "SUCCESS",
					"result" =>$submitVerifyDN,
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
	
	public function dnToVerifyDetailAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['mobileToken'] = empty($search['mobileToken'])?0:$search['mobileToken'];
			
			$db = new Systemapi_Model_DbTable_DbApi();
			$row = $db->getDNDetail($search);
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
	
	public function getTransferProductNotifyAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['mobileToken'] = empty($search['mobileToken'])?0:$search['mobileToken'];
			
			$db = new Systemapi_Model_DbTable_DbApi();
			$row = $db->getTransferNotify($search);
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
	
	public function getPurchaseConcreteListAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['mobileToken'] = empty($search['mobileToken'])?0:$search['mobileToken'];
			
			$db = new Systemapi_Model_DbTable_DbApi();
			$row = $db->getPurchaseConcreteList($search);
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
	public function submitApproveToReAdjustPoConcreteAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['mobileToken'] = empty($search['mobileToken'])?0:$search['mobileToken'];
			
			$db = new Systemapi_Model_DbTable_DbApi();
			$submitVerify = $db->submitApproveToReAdjustPoConcrete($search);
			
			if($submitVerify){
				$arrResult = array(
					"code" => "SUCCESS",
					"result" =>$submitVerify,
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
	
	public function getFormSearchOptionAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['mobileToken'] = empty($search['mobileToken'])?0:$search['mobileToken'];
			
			$db = new Systemapi_Model_DbTable_DbApi();
			$row = $db->getFormSearchOption($search);
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
	public function getBranchListAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['mobileToken'] = empty($search['mobileToken'])?0:$search['mobileToken'];
			
			$db = new Systemapi_Model_DbTable_DbApi();
			$row = $db->getAllBranchList($search);
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
	
	public function getProductListAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['mobileToken'] = empty($search['mobileToken'])?0:$search['mobileToken'];
			
			$db = new Systemapi_Model_DbTable_DbApi();
			$row = $db->getAllProductList($search);
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
	public function getSupplierListAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['mobileToken'] = empty($search['mobileToken'])?0:$search['mobileToken'];
			
			$db = new Systemapi_Model_DbTable_DbApi();
			$row = $db->getAllSupplierList($search);
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
	public function getRequestNumberGenerateAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['mobileToken'] = empty($search['mobileToken'])?0:$search['mobileToken'];
			
			$db = new Systemapi_Model_DbTable_DbApi();
			$row = $db->getRequestNumberGenerate($search);
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
	public function submitNewRequestAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['mobileToken'] = empty($search['mobileToken'])?0:$search['mobileToken'];
			
			$db = new Systemapi_Model_DbTable_DbApi();
			$submitRequest = $db->submitNewRequest($search);
			
			if($submitRequest){
				$arrResult = array(
					"code" => "SUCCESS",
					"result" =>$submitRequest,
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
	
	public function getUsageNumberGenerateAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['mobileToken'] = empty($search['mobileToken'])?0:$search['mobileToken'];
			
			$db = new Systemapi_Model_DbTable_DbApi();
			$row = $db->getUsageNumberGenerate($search);
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
	
	public function getUsageStockListAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['mobileToken'] = empty($search['mobileToken'])?0:$search['mobileToken'];
			
			$db = new Systemapi_Model_DbTable_DbApi();
			$row = $db->getAllUsageStockList($search);
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
	
	public function getUsageStockDetailAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['mobileToken'] = empty($search['mobileToken'])?0:$search['mobileToken'];
			
			$db = new Systemapi_Model_DbTable_DbApi();
			$row = $db->getUsageStockDetail($search);
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
	
	public function submitNewUsageAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['mobileToken'] = empty($search['mobileToken'])?0:$search['mobileToken'];
			
			$db = new Systemapi_Model_DbTable_DbApi();
			$submitRequest = $db->submitNewUsage($search);
			if($submitRequest){
				$arrResult = array(
					"code" => "SUCCESS",
					"result" =>$submitRequest,
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
	public function submitUpdateUsageAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['mobileToken'] = empty($search['mobileToken'])?0:$search['mobileToken'];
			
			$db = new Systemapi_Model_DbTable_DbApi();
			$submitRequest = $db->submitUpdateUsageStock($search);
			if($submitRequest){
				$arrResult = array(
					"code" => "SUCCESS",
					"result" =>$submitRequest,
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
	
	
	public function getPreCountStockListAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['mobileToken'] = empty($search['mobileToken'])?0:$search['mobileToken'];
			
			$db = new Systemapi_Model_DbTable_DbApi();
			$row = $db->getAllPreCountStockList($search);
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
	public function getPreCountStockDetailAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['mobileToken'] = empty($search['mobileToken'])?0:$search['mobileToken'];
			
			$db = new Systemapi_Model_DbTable_DbApi();
			$row = $db->getPreCountStockDetail($search);
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
	public function submitPreCountingStockAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['mobileToken'] = empty($search['mobileToken'])?0:$search['mobileToken'];
			
			$db = new Systemapi_Model_DbTable_DbApi();
			$submitRequest = $db->submitPreCountingStock($search);
			if($submitRequest){
				$arrResult = array(
					"code" => "SUCCESS",
					"result" =>$submitRequest,
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
	
	public function submitEditPreCountingStockAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['mobileToken'] = empty($search['mobileToken'])?0:$search['mobileToken'];
			
			$db = new Systemapi_Model_DbTable_DbApi();
			$submitRequest = $db->submitEditPreCountingStock($search);
			if($submitRequest){
				$arrResult = array(
					"code" => "SUCCESS",
					"result" =>$submitRequest,
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
	
	public function submitNewProductAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['mobileToken'] = empty($search['mobileToken'])?0:$search['mobileToken'];
			
			$db = new Systemapi_Model_DbTable_DbApi();
			$submitPro = $db->submitNewProduct($search);
			if($submitPro){
				$arrResult = array(
					"code" => "SUCCESS",
					"result" =>$submitPro,
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
	public function submitEditProductAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['mobileToken'] = empty($search['mobileToken'])?0:$search['mobileToken'];
			
			$db = new Systemapi_Model_DbTable_DbApi();
			$submitPro = $db->submitEditProduct($search);
			if($submitPro){
				$arrResult = array(
					"code" => "SUCCESS",
					"result" =>$submitPro,
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
	
	public function getProductCodeGenerateAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['mobileToken'] = empty($search['mobileToken'])?0:$search['mobileToken'];
			
			$db = new Systemapi_Model_DbTable_DbApi();
			$row = $db->getProductCodeGenerate($search);
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
	
	public function submitEditUserProfileAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['mobileToken'] = empty($search['mobileToken'])?0:$search['mobileToken'];
			
			$db = new Systemapi_Model_DbTable_DbApi();
			$submitPro = $db->submitEditUserProfile($search);
			if($submitPro){
				$arrResult = array(
					"code" => "SUCCESS",
					"result" =>$submitPro,
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
	
	
	public function contactUsAction($search){
		$db = new Systemapi_Model_DbTable_DbApi();
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
		$db = new Systemapi_Model_DbTable_DbApi();
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
	
	
	public function checkExistingUserAction($_data){
		try{
			$db = new Systemapi_Model_DbTable_DbApi();
			$_data['phoneNumber'] = empty($_data['phoneNumber'])?"":$_data['phoneNumber'];
			$_data['countryCode'] = empty($_data['countryCode'])?"":$_data['countryCode'];
			$_data['countryISOCode'] = empty($_data['countryISOCode'])?"":$_data['countryISOCode'];
			$_data['userName'] = empty($_data['userName'])?"":$_data['userName'];
			$_data['emailAddress'] = empty($_data['emailAddress'])?"":$_data['emailAddress'];
			
			$row = $db->getCheckExistingUser($_data);
			if ($row['status']){
				$arrResult = array(
						"result" =>  $row['value'],
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
	
	public function submitNewUserAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['mobileToken'] = empty($search['mobileToken'])?0:$search['mobileToken'];
			
			$db = new Systemapi_Model_DbTable_DbApi();
			$resultSubmit = $db->submitNewUser($search);
			
			if ($resultSubmit['status']){
				if(!empty($resultSubmit['value'])){
					$row = $db->getUserInfById($resultSubmit['value']);
					if ($row['status']){
						if(!empty($row['value'])){
							$row['value']['deviceType'] = empty($search['deviceType'])?1:$search['deviceType'];
							$row['value']['mobileToken'] = empty($search['mobileToken'])?1:$search['mobileToken'];
							$row['value']['currentUserId'] = empty($search['currentUserId'])?0:$search['currentUserId'];
							$token = $db->generateToken($row['value']);
							$this->sentVerificationCode($row['value']);
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
				
				}else{
					$arrResult = array(
						"code" => "ERR_",
						"message" => $row['value'],
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
	
	public function submitResentVerifyCode($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['mobileToken'] = empty($search['mobileToken'])?0:$search['mobileToken'];
			
			$db = new Systemapi_Model_DbTable_DbApi();
			$resultSubmit = $db->updateVerifcationCode($search);
			
			if ($resultSubmit['status']){
				if(!empty($resultSubmit['value'])){
					$row = $db->getUserInfById($resultSubmit['value']);
					if(!empty($row['value'])){
						$mailStatus = $this->sentVerificationCode($row['value']);
						$mailStatus = json_decode($mailStatus, true);
						if($mailStatus['code']=="SUCCESS"){
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
	
	function sentVerificationCode($userInfo){
		$dbGbSt = new Application_Model_DbTable_DbGlobalStock();
		$userName = empty($userInfo["userName"])?"":$userInfo["userName"];
		$verifyCode = empty($userInfo["verifyCode"])?"":$userInfo["verifyCode"];
		$expireDateVerifyCode = empty($userInfo["expireDateVerifyCode"])?"":$userInfo["expireDateVerifyCode"];
		
		$subjectEmail = "Verification code for BPPT Mobile";
		if(!empty($userInfo["email"])){
			$_dataEmail = array(
				"email" => $userInfo["email"],
				"subjectEmail" => $subjectEmail,
				"verifyCode" => $verifyCode,
				"expireDateVerifyCode" => $expireDateVerifyCode,
				"userName" => $userName,
				"emailFor" => "verification",
			);
			return $dbGbSt->sentEmailFunction($_dataEmail);
		}	
	}
	
	
	public function submitVerifyAccountAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['mobileToken'] = empty($search['mobileToken'])?0:$search['mobileToken'];
			
			$db = new Systemapi_Model_DbTable_DbApi();
			$verifying = $db->verifyCodeAndUpdateAccount($search);
			if($verifying){
				$arrResult = array(
					"code" => "SUCCESS",
					"result" =>$verifying,
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
	
	public function submitRequestRoleAccountAction($search){
		try{
			$search['userId'] = empty($search['userId'])?0:$search['userId'];
			$search['mobileToken'] = empty($search['mobileToken'])?0:$search['mobileToken'];
			
			$db = new Systemapi_Model_DbTable_DbApi();
			$rs = $db->requestRoleForAccount($search);
			if($rs){
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
	
}