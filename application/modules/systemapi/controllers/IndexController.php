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
			}else if ($GetData['url']=="poRequestToReceiveNotify"){
    			$_dbAction->PORequestToReceiveNotifyAction($GetData);
			
			}else if ($GetData['url']=="dnToVerifyNotify"){
    			$_dbAction->getDNToVerifyNotifyAction($GetData);
			}else if ($GetData['url']=="dnToVerifyDetail"){
    			$_dbAction->dnToVerifyDetailAction($GetData);
				
			}else if ($GetData['url']=="transferProductNotify"){
    			$_dbAction->getTransferProductNotifyAction($GetData);
			
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
					
				}else if ($GetData['url']=="setReadNotification"){
    				$_dbAction->setReadNotificationAction($postData);
				
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