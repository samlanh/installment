<?php
class Loan_AgreementController extends Zend_Controller_Action {
	
	protected $tr;
    public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function indexAction(){
		try{
		    if($this->getRequest()->isPost()){
 				$search = $this->getRequest()->getPost();
 			}
			else{
				$search = array(
					'txt_search'=>'',
					'client_name'=> -1,
					'schedule_opt' => -1,
					'branch_id' => -1,
					'streetlist'=>'',
					'status' => -1,
					'co_id' => -1,
					'land_id'=>-1,
					'start_date'=> date('Y-m-d'),
					'end_date'=>date('Y-m-d'),
				);
			}
			$db = new Loan_Model_DbTable_DbAgreement();
			$rs_rows= $db->getSaleAgreement($search);
			$glClass = new Application_Model_GlobalClass();
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH_NAME","CUSTOMER_NAME","TEL","PROPERTY_CODE","STREET","PAYMENT_TYPE","TITLE","CREATE_DATE","BY_USER");
			$link_info=array('module'=>'loan','controller'=>'agreement','action'=>'edit',);

			$this->view->list=$list->getCheckList(10, $collumns, $rs_rows,array(),0);
			
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}	
		$frm_search = new Loan_Form_FrmSearchLoan();
		$frm = $frm_search->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
		$this->view->rssearch = $search;
  }
  function addAction()
  {
		if($this->getRequest()->isPost()){
			$dbgb = new Application_Model_DbTable_DbGlobal();
			$checkses = $dbgb->checkSessionExpire();
			if (empty($checkses)){
				$dbgb->reloadPageExpireSession();
				exit();
			}
			$_data = $this->getRequest()->getPost();
			try {		
				$_dbmodel = new Loan_Model_DbTable_DbAgreement();
				$_dbmodel->addSaleAgreement($_data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/loan/agreement");
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$fm = new Loan_Form_FrmCancel();
		$frm = $fm->FrmAddFrmCancel();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_loan = $frm;
	}
		
	public function editAction(){
		$_dbmodel = new Loan_Model_DbTable_DbAgreement();
		if($this->getRequest()->isPost()){
			$dbgb = new Application_Model_DbTable_DbGlobal();
			$checkses = $dbgb->checkSessionExpire();
			if (empty($checkses)){
				$dbgb->reloadPageExpireSession();
				exit();
			}
			$_data = $this->getRequest()->getPost();
			try {
				$_dbmodel->updateSaleAgreement($_data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/loan/agreement");
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		
		$id = $this->getRequest()->getParam('id');
		$rs = $_dbmodel->getSaleAgreementById($id);
		if(empty($rs)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD","/loan/agreement",2);
		}
		$this->view->rs = $rs;
		$fm = new Loan_Form_FrmCancel();
		$frm = $fm->FrmAddFrmCancel();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_loan = $frm;
	}
	
}