<?php
class Rent_DepositController extends Zend_Controller_Action {
    public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
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
						'status' => -1,
						'co_id' => -1,
						'land_id'=>-1,
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
						 );
			}
			$db = new Rent_Model_DbTable_DbLanddeposit();
			$rs_rows= $db->getAlldepositLoan($search);
			$glClass = new Application_Model_GlobalClass();
			$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH_NAME","CUSTOMER_NAME","TEL","PROPERTY_CODE","STREET","PAYMENT_TYPE","PRINCIPLE_PICE","DISCOUNT_PERCENT","DISCOUNT","SOLD_PRICE","PAID","BALANCE","DATE_BUY",
				"BY_USER","STATUS","IS_CANCEL");
			$link_info=array('module'=>'rent','controller'=>'deposit','action'=>'edit',);

			$agreement=array('module'=>'report','controller'=>'rent','action'=>'rpt-agreement',);
			$reschedule=array('module'=>'rent','controller'=>'repaymentschedule','action'=>'add',);
			$payment=array('module'=>'rent','controller'=>'ilpayment','action'=>'add',);
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
			// Check Session Expire
			$dbgb = new Application_Model_DbTable_DbGlobal();
			$checkses = $dbgb->checkSessionExpire();
			if (empty($checkses)){
				$dbgb->reloadPageExpireSession();
				exit();
			}
		   $_data = $this->getRequest()->getPost();
		   try {
				$_dbmodel = new Rent_Model_DbTable_DbLanddeposit();
				$sale_id = $_dbmodel->addDepositPayment($_data);
				$_dbmodel->recordhistory($_data,$sale_id);
				if(!empty($_data['saveclose'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/rent/deposit");
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/rent/deposit/add");
				}
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$frm = new Rent_Form_FrmLoan();
		$frm_loan=$frm->FrmAddLoan();
		Application_Model_Decorator::removeAllDecorator($frm_loan);
		$this->view->frm_loan = $frm_loan;
		
		$frmpopup = new Application_Form_FrmPopupGlobal();
		$this->view->officailreceipt = $frmpopup->getOfficailReceiptRent();
		
		$db = new Application_Model_DbTable_DbGlobal();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$co_name = $db->getAllCoNameOnly();
		array_unshift($co_name,array(
		        'id' => -1,
		        'name' => $tr->translate("ADD_NEW"),
		) );
	    $this->view->co_name=$co_name;
	    
	    $key = new Application_Model_DbTable_DbKeycode();
		$this->view->data=$key->getKeyCodeMiniInv(TRUE);
	}	
	public function editAction(){
		if($this->getRequest()->isPost()){
			// Check Session Expire
			$dbgb = new Application_Model_DbTable_DbGlobal();
			$checkses = $dbgb->checkSessionExpire();
			if (empty($checkses)){
				$dbgb->reloadPageExpireSession();
				exit();
			}
			$_data = $this->getRequest()->getPost();
			try{
				$_dbmodel = new Rent_Model_DbTable_DbLanddeposit();
				$_dbmodel->updateLoanById($_data);
				Application_Form_FrmMessage::Sucessfull("UPDATE_SUCCESS","/rent/deposit/index");
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($err =$e->getMessage());
			}
		}
		$id = $this->getRequest()->getParam('id');
		$db = new Loan_Model_DbTable_DbLandpayment();
		$row = $db->getTranLoanByIdWithBranch($id,null);
		if(empty($row)){Application_Form_FrmMessage::Sucessfull("RECORD_NOTFUND","/rent/deposit");}
		
		 if($row['payment_id']!=1 AND $row['payment_id']!=2){
		 }
		$frm = new Rent_Form_FrmLoan();
		$frm_loan=$frm->FrmAddLoan($row);
		Application_Model_Decorator::removeAllDecorator($frm_loan);
		$this->view->frm_loan = $frm_loan;
		$this->view->datarow = $row;
		$this->view->amount_price = $row['balance']+$row['paid_amount']-$row['other_fee'];
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->client_code=array();//$dataclient;
		$this->view->client_name=array();
		
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$co_name = $db->getAllCoNameOnly();
		array_unshift($co_name,array(
				'id' => -1,
				'name' => $tr->translate("ADD_NEW"),
		) );
		$this->view->co_name=$co_name;
		
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data=$key->getKeyCodeMiniInv(TRUE);
		
		$frmpopup = new Application_Form_FrmPopupGlobal();
		$this->view->footer = $frmpopup->getFooterReceipt();
		$this->view->officailreceipt = $frmpopup->getOfficailReceipt();
	}
	function getReceiptNumberAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Rent_Model_DbTable_DbLanddeposit();
			$loan_number = $db->getRentReceiptByBranch($data);
			print_r(Zend_Json::encode($loan_number));
			exit();
		}
	}
	function addschedultestAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			$_dbmodel = new Rent_Model_DbTable_DbLanddeposit();
			$rows_return = $_dbmodel->addScheduleTestPayment($_data);
			print_r(Zend_Json::encode($rows_return));
			exit();
		}
	}
}