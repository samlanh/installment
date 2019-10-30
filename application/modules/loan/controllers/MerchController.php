<?php
class Loan_MerchController extends Zend_Controller_Action {
    public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	private $sex=array(1=>'M',2=>'F');
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
			$db = new Loan_Model_DbTable_DbLandpayment();
			$rs_rows= $db->getAllIndividuleLoan($search);
			$glClass = new Application_Model_GlobalClass();
			$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH_NAME","CUSTOMER_NAME","TEL","PROPERTY_CODE","STREET","PAYMENT_TYPE","PRINCIPLE_PICE","DISCOUNT_PERCENT","DISCOUNT","SOLD_PRICE",
					"PAID","BALANCE","DATE_BUY","BY_USER","STATUS","IS_CANCEL");
			$link_info=array('module'=>'loan','controller'=>'index','action'=>'edit',);

			$link_editsale=array('module'=>'loan','controller'=>'index','action'=>'editsale');
			$agreement=array('module'=>'report','controller'=>'paramater','action'=>'rpt-agreement',);
			$reschedule=array('module'=>'loan','controller'=>'repaymentschedule','action'=>'add',);
			$payment=array('module'=>'loan','controller'=>'ilpayment','action'=>'add',);
			$this->view->list=$list->getCheckList(11, $collumns, $rs_rows,array(),0);
			
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
			// Check Session Expire
			$dbgb = new Application_Model_DbTable_DbGlobal();
			$checkses = $dbgb->checkSessionExpire();
			if (empty($checkses)){
				$dbgb->reloadPageExpireSession();
				exit();
			}
			$_data = $this->getRequest()->getPost();
			try {
				$_dbmodel = new Loan_Model_DbTable_DbLandpayment();
				$_dbmodel->addSchedulePayment($_data);
				if(!empty($_data['saveclose'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/loan");
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/loan/index/add");
				}
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$frm = new Loan_Form_FrmLoan();
		$frm_loan=$frm->FrmAddLoan();
		Application_Model_Decorator::removeAllDecorator($frm_loan);
		$this->view->frm_loan = $frm_loan;
		
		$frmpopup = new Application_Form_FrmPopupGlobal();
		$this->view->footer = $frmpopup->getFooterReceipt();
		$this->view->officailreceipt = $frmpopup->getOfficailReceipt();
		
		$db = new Application_Model_DbTable_DbGlobal();
		$co_name = $db->getAllCoNameOnly();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		array_unshift($co_name,array(
		        'id' => -1,
		        'name' =>$tr->translate("ADD_NEW"),
		) );
	    $this->view->co_name=$co_name;
	    
	    $this->view->stepoption = $db->getOptionStepPayment();
	    
	    $interest = $db->getAllInterestratestore();
	    array_unshift($interest,array(
		    'id' => -1,
		    'name' =>$tr->translate("ADD_NEW"),
	    ) );
	    $this->view->rs_interest = $interest;
	    
	    $key = new Application_Model_DbTable_DbKeycode();
		$this->view->data=$key->getKeyCodeMiniInv(TRUE);
	}
}