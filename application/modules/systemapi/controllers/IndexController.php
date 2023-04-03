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
    		
			}else if ($GetData['url']=="allActionNotification"){
    			$_dbAction->allActionNotifyAction($GetData);
			}else if ($GetData['url']=="checkingRequestNotification"){
    			$_dbAction->checkingRequestNotifyAction($GetData);
			}else if ($GetData['url']=="verifyRequestNotification"){
    			$_dbAction->verifyRequestNotifyAction($GetData);
			}else if ($GetData['url']=="approveRequestNotification"){
    			$_dbAction->approveRequestNotifyAction($GetData);
			}else if ($GetData['url']=="requestDetail"){
    			$_dbAction->requestDetailAction($GetData);
				
			}else if ($GetData['url']=="systemLanguage"){
    			$_dbAction->systemLanguageAction($GetData);
			}else if ($GetData['url']=="systemViewType"){
    			$_dbAction->systemViewTypeAction($GetData);
			}else if ($GetData['url']=="systemSettingKeycode"){
    			$_dbAction->systemSettingKeycodeAction($GetData);
			}else if ($GetData['url']=="systemAcademicYear"){
    			$_dbAction->systemAcademicYearAction($GetData);
			}else if ($GetData['url']=="systemStudyDegree"){
    			$_dbAction->systemStudyDegreeAction($GetData);					
			}else if ($GetData['url']=="gradingSystem"){
    			$_dbAction->gradingSystemAction($GetData);
			}else if ($GetData['url']=="disciplinePolicy"){
    			$_dbAction->disciplinePolicyAction($GetData);
			}else if ($GetData['url']=="attendancePolicy"){
    			$_dbAction->attendancePolicyAction($GetData);
			}else if ($GetData['url']=="branchList"){
    			$_dbAction->schoolBranchListAction($GetData);
			}else if ($GetData['url']=="studentEvaluation"){
    			$_dbAction->studentEvaluationAction($GetData);
			}else if ($GetData['url']=="studentEvaluationDetail"){
    			$_dbAction->studentEvaluationDetailAction($GetData);
			}else if ($GetData['url']=="studentAttendance"){
    			$_dbAction->studentAttendanceAction($GetData);
			}else if ($GetData['url']=="studentAttendanceDetail"){
    			$_dbAction->studentAttendanceDetailAction($GetData);
			}else if ($GetData['url']=="studentSchedule"){
    			$_dbAction->studentScheduleAction($GetData);
			}else if ($GetData['url']=="studentScore"){
    			$_dbAction->studentScoreAction($GetData);
			}else if ($GetData['url']=="scoreInformation"){
    			$_dbAction->scoreInformationAction($GetData);
			}else if ($GetData['url']=="subjectByGroup"){
    			$_dbAction->subjectByGroupAction($GetData);
			}else if ($GetData['url']=="studentScoreBySubject"){
    			$_dbAction->studentScoreBySubjectAction($GetData);
			}else if ($GetData['url']=="studentPayment"){
    			$_dbAction->studentPaymentAction($GetData);
			}else if ($GetData['url']=="studentPaymentInfo"){
    			$_dbAction->studentPaymentInfoAction($GetData);
			}else if ($GetData['url']=="studentPaymentDetail"){
    			$_dbAction->studentPaymentDetailAction($GetData);
			}else if ($GetData['url']=="newsDetail"){
    			$_dbAction->newsDetailAction($GetData);
			}else if ($GetData['url']=="unread"){
    			$_dbAction->unreadAction($GetData);
				
			}else if ($GetData['url']=="mobileNotify"){ //2023-03-18
    			$_dbAction->mobileNotifyAction($GetData);
			}else if ($GetData['url']=="mobileNotifyDetail"){
    			$_dbAction->mobileNotificationDetailAction($GetData);
    		}
    		else{
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
					
				}else if ($GetData['url']=="notificationRead"){
    				$_dbAction->notificationReadAction($postData);
					
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