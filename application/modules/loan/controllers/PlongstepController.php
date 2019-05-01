<?php
class Loan_PlongstepController extends Zend_Controller_Action {
	private $activelist = array('មិនប្រើ​ប្រាស់', 'ប្រើ​ប្រាស់');
    public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	private $sex=array(1=>'M',2=>'F');
	public function indexAction(){
		try{
		    if($this->getRequest()->isPost()){
 				$search = $this->getRequest()->getPost();
 				
 			}else{
				$search = array(
						'txt_search'=>'',
						'client_name'=> -1,
						'branch_id' => -1,
						'status' => -1,
						'process_status'=>0,
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
						 );
			}
			$db = new Loan_Model_DbTable_DbPlongStep();
			$rs_rows= $db->getAllissueplong($search,1);
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH_NAME","CUSTOMER_NAME","PROPERTY_CODE","STREET","PHONE","DATE","NOTE","PROCCESSING","STATUS");
			$link_info=array('module'=>'loan','controller'=>'plongstep','action'=>'edit',);
			$this->view->list=$list->getCheckList(10, $collumns, $rs_rows,array('branch_name'=>$link_info,'name_kh'=>$link_info),0);
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}	
		$frm = new Loan_Form_FrmSearchLoan();
		$frm = $frm->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
		
		$frm = new Loan_Form_FrmPlongStep();
		$frm_loan=$frm->FrmPlongStep();
		Application_Model_Decorator::removeAllDecorator($frm_loan);
		$this->view->frm_searchplog = $frm_loan;
  }
  function addAction()
  {
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$_dbmodel = new Loan_Model_DbTable_DbPlongStep();
				$_dbmodel->addPlongStep($_data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/loan/plongstep");
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$frm = new Loan_Form_FrmPlongStep();
		$frm_loan=$frm->FrmPlongStep();
		Application_Model_Decorator::removeAllDecorator($frm_loan);
		$this->view->frm_loan = $frm_loan;
        $db = new Application_Model_DbTable_DbGlobal();
        
	}	
	
	public function editAction(){
		$_dbmodel = new Loan_Model_DbTable_DbPlongStep();
		
		$id = $this->getRequest()->getParam('id');
		$id = empty($id)?0:$id;
		$rs = $_dbmodel->getPlogStepById($id);
		$this->view->rs = $rs;
		if(empty($rs)){
			Application_Form_FrmMessage::Sucessfull("RECORD_NOTFUND","/loan/plongstep");
			exit();
		}
		$this->view->rsdetail = $_dbmodel->getPlogStepDetailById($id);
		
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$_dbmodel->addChangeProject($_data);
				if(!empty($_data['saveclose'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/loan/issueplong");
				}else{
					Application_Form_FrmMessage::message("INSERT_SUCCESS");
				}
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		
		$frm = new Loan_Form_FrmPlongStep();
		$frm_loan=$frm->FrmPlongStep($rs);
		Application_Model_Decorator::removeAllDecorator($frm_loan);
		$this->view->frm_loan = $frm_loan;
	}
	function stepupAction()
	{
		$_dbmodel = new Loan_Model_DbTable_DbPlongStep();
		$id = $this->getRequest()->getParam('id');
		$id = empty($id)?0:$id;
		$rs = $_dbmodel->getPlogStepById($id);
		$this->view->rs = $rs;
		if(empty($rs)){
			Application_Form_FrmMessage::Sucessfull("RECORD_NOTFUND","/loan/plongstep");
			exit();
		}
		if ($rs['process_status']==5){
			Application_Form_FrmMessage::Sucessfull("COMPLETED_STEP","/loan/plongstep");
			exit();
		}
		$this->view->rsdetail = $_dbmodel->getPlogStepDetailById($id);
		
		$record = $this->getRequest()->getParam('record');
		$record = empty($record)?0:$record;
		$this->view->record = $record;
		$detialinfo =  $_dbmodel->getPlogStepDetailRowById($record);
		$this->view->detialinfo = $detialinfo;
		
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$_dbmodel = new Loan_Model_DbTable_DbPlongStep();
				$_dbmodel->addPlongStepDetail($_data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/loan/plongstep");
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$frm = new Loan_Form_FrmPlongStep();
		$frm_loan=$frm->FrmPlongStep();
		Application_Model_Decorator::removeAllDecorator($frm_loan);
		$this->view->frm_loan = $frm_loan;
		$db = new Application_Model_DbTable_DbGlobal();
	
	}
	
}

