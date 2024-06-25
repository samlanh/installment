<?php

//API For General Installment System
class Systemapi_GeneralController extends Zend_Controller_Action
{
	const REDIRECT_URL = '/api/general';
	
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
    	$_dbAction = new Systemapi_Model_DbTable_DbGeneralActions();
    	$GetData = $this->getRequest()->getParams();
		
		$session_lang=new Zend_Session_Namespace('lang');
		$session_lang->lang_id=empty($GetData['currentLang'])?1:$GetData['currentLang'];
			
    	if ($_SERVER['REQUEST_METHOD'] == "GET"){
    		if($GetData['url']=="profile"){
    			$_dbAction->profileAction($GetData);
    		}else if ($GetData['url']=="slieshow"){
    			$_dbAction->slidshowAction($GetData);
			}else if ($GetData['url']=="news"){
    			$_dbAction->newsAction($GetData);
    		}else if ($GetData['url']=="contactus"){
    			$_dbAction->contactUsAction($GetData);
			}else if ($GetData['url']=="checkingStatus"){
    			$_dbAction->getSystemStatusAction($GetData);
			}else if ($GetData['url']=="summaryCountingType"){
    			$_dbAction->getAppCountingTypeAction($GetData);
				
			}else if ($GetData['url']=="optionSvBranch"){
				$GetData['getControlType'] = "serverBranch";
				$_dbAction->getFormOptionSelectAction($GetData);
			}else if ($GetData['url']=="optionStatus"){
				$GetData['getControlType'] = "status";
				$_dbAction->getFormOptionSelectAction($GetData);
			}else if ($GetData['url']=="optionBranch"){
				$GetData['getControlType'] = "systemBranch";
				$_dbAction->getFormOptionSelectAction($GetData);
			}else if ($GetData['url']=="optionPropertyType"){
				$GetData['getControlType'] = "propertyType";
				$_dbAction->getFormOptionSelectAction($GetData);
			}else if ($GetData['url']=="optionSchedulePmtType"){
				$GetData['getControlType'] = "schedulePmtType";
				$_dbAction->getFormOptionSelectAction($GetData);
			}else if ($GetData['url']=="optionCancelType"){
				$GetData['getControlType'] = "cancelType";
				$_dbAction->getFormOptionSelectAction($GetData);
			}else if ($GetData['url']=="optionLayoutType"){
				$GetData['getControlType'] = "layoutType";
				$_dbAction->getFormOptionSelectAction($GetData);
			}else if ($GetData['url']=="optionIssueHouseType"){
				$GetData['getControlType'] = "issueHouseType";
				$_dbAction->getFormOptionSelectAction($GetData);
				
			}else if ($GetData['url']=="serverBranchInfo"){
    			$_dbAction->serverBranchInfoAction($GetData);
			}else if ($GetData['url']=="countingSummary"){
    			$_dbAction->countingSummmaryAction($GetData);
			
			}else if ($GetData['url']=="saleInfoList"){
    			$_dbAction->saleInfoListAction($GetData);
			}else if ($GetData['url']=="depositSaleList"){
				$GetData['saleType'] = "1";
    			$_dbAction->saleInfoListAction($GetData);	
			}else if ($GetData['url']=="issuedAgreementList"){
				$GetData['saleType'] = "2";
    			$_dbAction->saleInfoListAction($GetData);					
			}else if ($GetData['url']=="saleCanceledList"){
    			$_dbAction->saleCanceledListAction($GetData);
			}else if ($GetData['url']=="issuedHouseList"){
    			$_dbAction->issuedHouseListAction($GetData);
			}else if ($GetData['url']=="issuedLayoutList"){
    			$_dbAction->issuedLayoutListAction($GetData);
				
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
				}else if ($GetData['url']=="deleteMyAccount"){
					$_dbAction->deleteMyAccountAction($postData);
				}else if ($GetData['url']=="forgetPassword"){
					$_dbAction->forgetPasswordAction($postData);
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