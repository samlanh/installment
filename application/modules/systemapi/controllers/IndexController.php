<?php
class Systemapi_IndexController extends Zend_Controller_Action
{
	const REDIRECT_URL = '/api/index';
	
    public function init()
    {
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }
    public function indexAction()
    {
    	$this->_helper->layout()->disableLayout();
    	header('Content-type:application/json;charset=utf-8');
    	
//     	header('Content-Type: application/json');
    	$_dbAction = new Systemapi_Model_DbTable_DbActions();
    	$GetData = $this->getRequest()->getParams();
		
		$session_lang=new Zend_Session_Namespace('lang');
		$session_lang->lang_id=empty($GetData['currentLang'])?1:$GetData['currentLang'];
			
    	if ($_SERVER['REQUEST_METHOD'] == "GET"){
    		if($GetData['url']=="profile"){
    			$_dbAction->profileAction($GetData);
    		}else if ($GetData['url']=="slieshow"){
    			$_dbAction->slidshowAction($GetData);
			
			}else if ($GetData['url']=="allRequestList"){
				$_dbAction->allRequestNotifyAction($GetData);
			}else if ($GetData['url']=="checkingRequestNotification"){
    			$_dbAction->checkingRequestNotifyAction($GetData);
			}else if ($GetData['url']=="verifyRequestNotification"){
    			$_dbAction->verifyRequestNotifyAction($GetData);
			}else if ($GetData['url']=="approveRequestNotification"){
    			$_dbAction->approveRequestNotifyAction($GetData);
			}else if ($GetData['url']=="requestForPONotify"){
    			$_dbAction->requestForPONotifyAction($GetData);
			}else if ($GetData['url']=="requestDetail"){
    			$_dbAction->requestDetailAction($GetData);
			}else if ($GetData['url']=="poRequestList"){
				$_dbAction->getAllListPurchaseByRequestAction($GetData);
			}else if ($GetData['url']=="poRequestToReceiveNotify"){
    			$_dbAction->PORequestToReceiveNotifyAction($GetData);
			}else if ($GetData['url']=="detailPoRequest"){
    			$_dbAction->getDetailPurchaseByRequestAction($GetData);
			
			}else if ($GetData['url']=="dnToVerifyNotify"){
    			$_dbAction->getDNToVerifyNotifyAction($GetData);
			}else if ($GetData['url']=="dnToVerifyDetail"){
    			$_dbAction->dnToVerifyDetailAction($GetData);
				
			}else if ($GetData['url']=="transferProductNotify"){
    			$_dbAction->getTransferProductNotifyAction($GetData);
			}else if ($GetData['url']=="purchaseConcreteList"){
    			$_dbAction->getPurchaseConcreteListAction($GetData);
			}else if ($GetData['url']=="purchaseConcreteForReAdjustment"){
				$GetData['isForReAdjustment'] = "1";
    			$_dbAction->getPurchaseConcreteListAction($GetData);
			
			}else if ($GetData['url']=="requestNumber"){
				$_dbAction->getRequestNumberGenerateAction($GetData);	
			}else if ($GetData['url']=="branchList"){
				$_dbAction->getBranchListAction($GetData);				
			}else if ($GetData['url']=="productCategory"){
				$GetData['getControlType']="productCategory";
    			$_dbAction->getFormSearchOptionAction($GetData);
			}else if ($GetData['url']=="productList"){
				$_dbAction->getProductListAction($GetData);
			}else if ($GetData['url']=="supplierList"){
				$_dbAction->getSupplierListAction($GetData);
				
			}else if ($GetData['url']=="formRequestStatus"){
				$GetData['getControlType'] = "requestStatus";
    			$_dbAction->getFormSearchOptionAction($GetData);
			}else if ($GetData['url']=="formRequestStep"){
				$GetData['getControlType'] = "requestStep";
    			$_dbAction->getFormSearchOptionAction($GetData);
			}else if ($GetData['url']=="formWarehouseStaff"){
				$GetData['getControlType'] = "warehouseStaff";
    			$_dbAction->getFormSearchOptionAction($GetData);
			}else if ($GetData['url']=="formContractorStaff"){
				$GetData['getControlType'] = "contractorStaff";
    			$_dbAction->getFormSearchOptionAction($GetData);
			}else if ($GetData['url']=="formWorkType"){
				$GetData['getControlType'] = "workType";
    			$_dbAction->getFormSearchOptionAction($GetData);
			}else if ($GetData['url']=="formProperty"){
				$GetData['getControlType'] = "property";
    			$_dbAction->getFormSearchOptionAction($GetData);
			}else if ($GetData['url']=="formPropertyType"){
				$GetData['getControlType'] = "propertyType";
    			$_dbAction->getFormSearchOptionAction($GetData);
			}else if ($GetData['url']=="formProductMeasure"){
				$GetData['getControlType'] = "productMeasure";
    			$_dbAction->getFormSearchOptionAction($GetData);
			}else if ($GetData['url']=="formBudgetType"){
				$GetData['getControlType'] = "budgetType";
    			$_dbAction->getFormSearchOptionAction($GetData);
			}else if ($GetData['url']=="formBudgetItem"){
				$GetData['getControlType'] = "budgetItem";
    			$_dbAction->getFormSearchOptionAction($GetData);
			}else if ($GetData['url']=="formStatus"){
				$GetData['getControlType'] = "status";
    			$_dbAction->getFormSearchOptionAction($GetData);
			}else if ($GetData['url']=="formIsCountStock"){
				$GetData['getControlType'] = "isCountStock";
    			$_dbAction->getFormSearchOptionAction($GetData);
			}else if ($GetData['url']=="formIsService"){
				$GetData['getControlType'] = "isService";
    			$_dbAction->getFormSearchOptionAction($GetData);
				
			}else if ($GetData['url']=="usageNumber"){
				$_dbAction->getUsageNumberGenerateAction($GetData);
			}else if ($GetData['url']=="usageStockList"){
				$_dbAction->getUsageStockListAction($GetData);
			}else if ($GetData['url']=="usageStockDetail"){
				$_dbAction->getUsageStockDetailAction($GetData);
				
			}else if ($GetData['url']=="preCountStock"){
				$_dbAction->getPreCountStockListAction($GetData);
			}else if ($GetData['url']=="preCountStockDetail"){
				$_dbAction->getPreCountStockDetailAction($GetData);
			}else if ($GetData['url']=="productCodeGenerate"){
				$_dbAction->getProductCodeGenerateAction($GetData);	
    		}else{
    			echo Zend_Http_Response::responseCodeAsText(401,true);
    		}
    	}else if ($_SERVER['REQUEST_METHOD'] == "POST"){
    		if($this->getRequest()->isPost()){
    			$postData = $this->getRequest()->getPost();
    			if ($GetData['url']=="auth"){// login
    				$_dbAction->loginAction($postData);
    			}else if ($GetData['url']=="changePassword"){// change password
    				$_dbAction->changePasswordAction($postData);
    			}else if ($GetData['url']=="addtoken"){// change password
    				$_dbAction->addTokenAction($postData);
				}else if ($GetData['url']=="removeTokenApp"){
    				$_dbAction->removeTokenAction($postData);
					
				
				}else if ($GetData['url']=="submitCheckingRequest"){
    				$_dbAction->checkingRequestPOAction($postData);	
				}else if ($GetData['url']=="submitVerifyRequestPO"){
    				$_dbAction->submitVerifyRequestPOAction($postData);
				}else if ($GetData['url']=="submitApproveRequestPO"){
    				$_dbAction->submitApproveRequestPOAction($postData);		
				}else if ($GetData['url']=="submitVerifyDN"){
    				$_dbAction->submitVerifyDNAction($postData);
				}else if ($GetData['url']=="submitApproveToReAdjustPoConcrete"){
    				$_dbAction->submitApproveToReAdjustPoConcreteAction($postData);
				}else if ($GetData['url']=="submitNewRequest"){
					$_dbAction->submitNewRequestAction($postData);	
				}else if ($GetData['url']=="submitNewUsage"){
					$_dbAction->submitNewUsageAction($postData);
				}else if ($GetData['url']=="submitUpdateUsage"){
					$_dbAction->submitUpdateUsageAction($postData);
				}else if ($GetData['url']=="submitPreCountingStock"){
					$_dbAction->submitPreCountingStockAction($postData);
				}else if ($GetData['url']=="submitEditPreCountingStock"){
					$_dbAction->submitEditPreCountingStockAction($postData);
				}else if ($GetData['url']=="submitNewProduct"){
					$_dbAction->submitNewProductAction($postData);
				}else if ($GetData['url']=="submitEditProduct"){
					$_dbAction->submitEditProductAction($postData);
					
				}else if ($GetData['url']=="submitEditProfile"){
					$_dbAction->submitEditUserProfileAction($postData);
    			}
    			else{
    				echo Zend_Http_Response::responseCodeAsText(401,true);
    			}
				
			}else{
				echo Zend_Http_Response::responseCodeAsText(405,true);
			}
    	}else{
    		echo Zend_Http_Response::responseCodeAsText(405,true);
    	}
    	exit();
    }

}