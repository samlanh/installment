<?php
class Requesting_ApprovedrequestController extends Zend_Controller_Action {
	const REDIRECT_URL = '/requesting/approvedrequest';
	const STEP_REQUEST = 4;
	public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
			if($this->getRequest()->isPost()){
    			$search = $this->getRequest()->getPost();
    		}
    		else{
    			$search=array(
					'adv_search'=>"",
					'branch_id' => -1,
					'start_date'=> date('Y-m-d'),
					'end_date'=>date('Y-m-d'),
					'status'=>-1,
				);
    		}
    		$this->view->search = $search;
			$db = new Requesting_Model_DbTable_DbApprovedRequest();
			$rs_rows = $db->getAllApprovedRequestPO($search);
			
			$list = new Application_Form_Frmtable();
    		$collumns = array("PROJECT_NAME","REQUEST_NO","REQUEST_NO_FROM","PURPOSE","REQUEST_DATE","DATE","APPROVED_STATUS","APPROVED_BY");
    		$link=array(
    				'module'=>'requesting','controller'=>'approvedrequest','action'=>'edit',
    		);
    		$this->view->list=$list->getCheckList(10, $collumns, $rs_rows , array());
    		
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
		}
		
		$frm_search = new Application_Form_FrmAdvanceSearchStock();
		$frm = $frm_search->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
		
	}
    public function addAction()
    {	
		$tr=Application_Form_FrmLanguages::getCurrentlanguage();
	
		$dbGbSt = new Application_Model_DbTable_DbGlobalStock();
		$notify = array(
			"userAction" => 3,// push to PO Dept to Make PO
			"typeNotify" => "toPoPurchase",
		);
		
		$id=$this->getRequest()->getParam('id');
		$id = empty($id)?0:$id;
    	$dbReq = new Requesting_Model_DbTable_DbRequest();
    	$row = $dbReq->getRequestPOById($id);
		
    	$db = new Requesting_Model_DbTable_DbApprovedRequest();
    	if($this->getRequest()->isPost()){
	    	try{
	    		$data = $this->getRequest()->getPost();
				
				$data['stepNum']=self::STEP_REQUEST;
	    		$db->approvedRequestPO($data);
				
				if(!empty($row)){
					$data['checkingStatus'] = empty($data['checkingStatus'])?0:$data['checkingStatus'];
					if($data['checkingStatus']==1){
						$notify["notificationId"]  = $id;
						$notify["branchId"]  = $row["projectId"];
						$dbGbSt->pushNotificationForAndroid($notify);
					}
				}
				
	    		Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL."/index");
				
	    	}catch(Exception $e){
	    		Application_Form_FrmMessage::message("APPLICATION_ERROR");
	    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	    	}
    	}
		
		
    	if (empty($row)){
    		Application_Form_FrmMessage::Sucessfull("NO_RECORD", self::REDIRECT_URL."/index",2);
    		exit();
    	}
		if ($row['status']==0){
    		Application_Form_FrmMessage::Sucessfull("NO_RECORD", self::REDIRECT_URL."/index",2);
    		exit();
    	}
		$dbGbSt = new Application_Model_DbTable_DbGlobalStock();
		$arrStep = array(
			'stepNum'=>$row['processingStatus'],
			'typeStep'=>2,
		);
		$processingStatusTitle = $dbGbSt->requestingProccess($arrStep);
		if ($row['processingStatus']>4){
    		Application_Form_FrmMessage::Sucessfull($tr->translate('REQUEST_IS_ON_PROCCESING')." ".$processingStatusTitle, self::REDIRECT_URL."/index",2);
    		exit();
    	}
		
		if ($row['pCheckingStatus']!=1){
    		Application_Form_FrmMessage::Sucessfull("RECORD_NEED_TO_COMPLETED_STEP_3", self::REDIRECT_URL."/index",2);
    		exit();
    	}
		
    	$this->view->row = $row;
		$row['approvedrequest']=1;
    	$this->view->rowdetail = $dbReq->getRequestPODetailById($row);
    	
		
    	$frm = new Requesting_Form_FrmRequest();
    	$frm->FrmRequestPO($row);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm = $frm;
    }
	
	
	
}