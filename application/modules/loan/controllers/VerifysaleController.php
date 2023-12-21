<?php
class Loan_VerifysaleController extends Zend_Controller_Action {
	
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
			$db = new Loan_Model_DbTable_DdVerifySale();
			$rs_rows= $db->getAllIndividuleLoan($search);
			$glClass = new Application_Model_GlobalClass();
			$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
			$list = new Application_Form_Frmtable();
			$collumns = array(
			
					"BRANCH_NAME"
					,"CUSTOMER_NAME"
					,"TEL"
					,"PROPERTY_CODE"
					,"STREET"
					,"PAYMENT_TYPE"
			
					,"verifyDate"
					,"PRINCIPLE_PICE_VERIFY","SOLD_PRICE_VERIFY","PAID_VERIFY","BALANCE_VERIFY"
					,"verifyBy"
			
					,"PRINCIPLE_PICE"
					,"SOLD_PRICE"
					,"PAID"
					,"BALANCE"
					
					
					
					,"STATUS"
			);
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
			$_data = $this->getRequest()->getPost();
			try {
				// Check Session Expire
				$dbgb = new Application_Model_DbTable_DbGlobal();
				$checkses = $dbgb->checkSessionExpire();
				if (empty($checkses)){
					$dbgb->reloadPageExpireSession();
					exit();
				}
				if (empty($_data)){
					Application_Form_FrmMessage::Sucessfull("File Attachment to large can't upload and Save data !","/loan/verifysale",2);
					exit();
				}
				$_dbmodel = new Loan_Model_DbTable_DdVerifySale();
				$_dbmodel->addVerifySale($_data);
				if(!empty($_data['saveclose'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/loan/verifysale");
				}
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/loan/verifysale/add");
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$frm = new Loan_Form_FrmLoan();
		$frm_loan=$frm->FrmAddLoan();
		Application_Model_Decorator::removeAllDecorator($frm_loan);
		$this->view->frm_loan = $frm_loan;
	}
	function editAction(){
		Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/loan/verifysale/add");
	}
	
}