<?php
class Report_InvestController extends Zend_Controller_Action {
	private $activelist = array('មិនប្រើ​ប្រាស់', 'ប្រើ​ប្រាស់');
    public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
  function indexAction(){
  }
  
  public function rptBrokerBalanceAction(){
  	try{
  		if($this->getRequest()->isPost()){
  			$search = $this->getRequest()->getPost();
  		}
  		else{
  			$search = array(
  					'adv_search'=>'',
  					'status' => -1,
  					'investor_id'=>0,
  					'broker_id'=>0,
  					'start_date'=> date('Y-m-d'),
  					'end_date'=>date('Y-m-d'),
  			);
  		}
  		$db = new Report_Model_DbTable_DbInvestment();
  		$rs_rows= $db->getAllBrokerBalance($search);
  		$this->view->row = $rs_rows;	
  			
  	}catch (Exception $e){
  		Application_Form_FrmMessage::message("Application Error");
  		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
  	}
  
  	$this->view->search = $search;
  	
  	$frm = new Application_Form_FrmAdvanceSearch();
  	$frm = $frm->AdvanceSearch();
  	Application_Model_Decorator::removeAllDecorator($frm);
  	$this->view->frm_search = $frm;
  
  	$frm = new Invest_Form_FrmInvestment();
  	$frm_loan=$frm->FrmAddInvestment();
  	Application_Model_Decorator::removeAllDecorator($frm_loan);
  	$this->view->frm = $frm_loan;
  	
  	$frmpopup = new Application_Form_FrmPopupGlobal();
  	$this->view->footerReport = $frmpopup->getFooterReport();
  }
  function rptWithdrawalhistoryAction(){
  	
  	try{
  		if($this->getRequest()->isPost()){
  			$search = $this->getRequest()->getPost();
  		}
  		else{
  			$search = array(
  					'adv_search'=>'',
  					'investor_id'=>0,
  					'start_date'=> date('Y-m-d'),
  					'end_date'=>date('Y-m-d'),
  			);
  		}
  		$db = new Report_Model_DbTable_DbInvestment();
  		$rs_rows= $db->getAllInvestorPaymentHistory($search);
  		$this->view->historypayment = $rs_rows;

  		$this->view->search = $search;
  		 
  		$frmpopup = new Application_Form_FrmPopupGlobal();
  		$this->view->footerReport = $frmpopup->getFooterReport();
  		
  		$frm = new Application_Form_FrmAdvanceSearch();
  		$frm = $frm->AdvanceSearch();
  		Application_Model_Decorator::removeAllDecorator($frm);
  		$this->view->frm_search = $frm;
  		
  		$frm = new Invest_Form_FrmInvestment();
  		$frm_loan=$frm->FrmAddInvestment();
  		Application_Model_Decorator::removeAllDecorator($frm_loan);
  		$this->view->frm = $frm_loan;
  	}catch (Exception $e){
  		Application_Form_FrmMessage::message("Application Error");
  		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
  	}
  }
  
  function rptWithdrawalbrokerhistoryAction(){
  	try{
  		if($this->getRequest()->isPost()){
  			$search = $this->getRequest()->getPost();
  		}
  		else{
  			$search = array(
  					'adv_search'=>'',
  					'broker_id'=>0,
  					'start_date'=> date('Y-m-d'),
  					'end_date'=>date('Y-m-d'),
  			);
  		}
  		$db = new Report_Model_DbTable_DbInvestment();
  		$rs_rows= $db->getAllBrokerPaymentHistory($search);
  		$this->view->historypayment = $rs_rows;

  		$this->view->search = $search;
  		
  		$frm = new Application_Form_FrmAdvanceSearch();
  		$frm = $frm->AdvanceSearch();
  		Application_Model_Decorator::removeAllDecorator($frm);
  		$this->view->frm_search = $frm;
  		 
  		$frm = new Invest_Form_FrmInvestment();
  		$frm_loan=$frm->FrmAddInvestment();
  		Application_Model_Decorator::removeAllDecorator($frm_loan);
  		$this->view->frm = $frm_loan;
  		
  		$frmpopup = new Application_Form_FrmPopupGlobal();
  		$this->view->footerReport = $frmpopup->getFooterReport();
  		
  	}catch (Exception $e){
  		Application_Form_FrmMessage::message("Application Error");
  		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
  	}
  	
  }
  
  function withdrawalhistoryAction(){
  	$db  = new Report_Model_DbTable_DbInvestment();
  	$id = $this->getRequest()->getParam('id');
  	$id = empty($id)?0:$id;
  	$rs=$db->getInvestorPaymentHistory($id);
  	$this->view->historypayment =$rs;
  	if(empty($rs)){
  		Application_Form_FrmMessage::Sucessfull("RECORD_NOTFUND","/invest/investment");
  		exit();
  	}
  	$frmpopup = new Application_Form_FrmPopupGlobal();
		$this->view->footerReport = $frmpopup->getFooterReport();
  	 
  }
 function withdrawalbrokerhistoryAction(){
  	$db  = new Report_Model_DbTable_DbInvestment();
  	$id = $this->getRequest()->getParam('id');
  	$id = empty($id)?0:$id;
  	$rs=$db->getBrokerPaymentHistory($id);
  	$this->view->historypayment =$rs;
  	if(empty($rs)){
  		Application_Form_FrmMessage::Sucessfull("RECORD_NOTFUND","/invest/investment");
  		exit();
  	}
  	$frmpopup = new Application_Form_FrmPopupGlobal();
	$this->view->footerReport = $frmpopup->getFooterReport();
  }
  function rptInvestScheduleAction(){
	  $id =$this->getRequest()->getParam('id');
	  $id = empty($id)?0:$id;
	  $dbinv = new Report_Model_DbTable_DbInvestment();
	  $rs = $dbinv->getInvestmentById($id);
	  if(empty($rs)){
	  	Application_Form_FrmMessage::Sucessfull("NO_RECORD",'/report/invest/rpt-investment',2);
	  }
	  $this->view->rs = $rs;
	  
	  $row = $dbinv->getInvestorSchedule($id);
	  $this->view->tran_schedule=$row;
	  
	  $key = new Application_Model_DbTable_DbKeycode();
	  $this->view->data=$key->getKeyCodeMiniInv(TRUE);
  }
   function rptBrokerScheduleAction(){
	  $id =$this->getRequest()->getParam('id');
	  $id = empty($id)?0:$id;
	  $dbinv = new Report_Model_DbTable_DbInvestment();
	  $rs = $dbinv->getInvestmentBrokerById($id);
	  if(empty($rs)){
	  	Application_Form_FrmMessage::Sucessfull("NO_RECORD",'/report/invest/rpt-investment',2);
	  }
	  $this->view->rs = $rs;
	  
	  $row = $dbinv->getBrokerSchedule($id);
	  $this->view->tran_schedule=$row;
	  
	  $key = new Application_Model_DbTable_DbKeycode();
	  $this->view->data=$key->getKeyCodeMiniInv(TRUE);
  }
  function receiptAction(){
  	$key = new Application_Model_DbTable_DbKeycode();
  	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
  	$db  = new Report_Model_DbTable_DbInvestment();
  	$id = $this->getRequest()->getParam('id');
  	if(!empty($id)){
  		$receipt = $db->getInvestmentReceiptById($id);
  		if(empty($receipt)){
  			Application_Form_FrmMessage::Sucessfull("NO_RECORD",'/report/invest/rpt-withdrawalhistory',2);
  			exit();
  		}
  		$this->view->rs = $receipt;
  		if(empty($receipt['name'])){
  			$this->_redirect("/report/paramater");
  		}
  	}else{
  		$this->_redirect("/report/paramater");
  	}
  	//   	$rss = $db->UpdatePaytimeBooking();
  	$frmpopup = new Application_Form_FrmPopupGlobal();
  	$this->view->footer = $frmpopup->getFooterReceipt();
  	$this->view->officailreceipt = $frmpopup->getInvestmentReceipt();
  }
  
  function brokerreceiptAction(){
  	$key = new Application_Model_DbTable_DbKeycode();
  	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
  	$db  = new Report_Model_DbTable_DbInvestment();
  	$id = $this->getRequest()->getParam('id');
  	if(!empty($id)){
  		$receipt = $db->getBrokerReceiptById($id);
  		if(empty($receipt)){
  			Application_Form_FrmMessage::Sucessfull("NO_RECORD",'/report/invest/rpt-withdrawalbrokerhistory',2);
  			exit();
  		}
  		$this->view->rs = $receipt;
  		if(empty($receipt['name'])){
  			$this->_redirect("/report/paramater");
  		}
  	}else{
  		$this->_redirect("/report/paramater");
  	}
  	//   	$rss = $db->UpdatePaytimeBooking();
  	$frmpopup = new Application_Form_FrmPopupGlobal();
  	$this->view->footer = $frmpopup->getFooterReceipt();
  	$this->view->officailreceipt = $frmpopup->getBrokerReceipt();
  }
  
  
  function rptUpdatebrokerScheduleAction(){
  	if($this->getRequest()->isPost()){
  		$_data = $this->getRequest()->getPost();
  			
  		try {
  			$_dbmodel = new Invest_Model_DbTable_DbInvestment();
  			$_dbmodel->updateBrokerSchedule($_data);
  			Application_Form_FrmMessage::Sucessfull("UPDATE_SUCCESS","/invest/investment");
  			exit();
  		}catch (Exception $e) {
  			Application_Form_FrmMessage::message("UPDATE_FAIL");
  			exit();
  			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
  		}
  	}
  	$id =$this->getRequest()->getParam('id');
  	$id = empty($id)?0:$id;
  	$dbinv = new Report_Model_DbTable_DbInvestment();
  	$rs = $dbinv->getInvestmentBrokerById($id);
  	if(empty($rs)){
  		Application_Form_FrmMessage::Sucessfull("NO_RECORD",'/report/invest/investment',2);
  		exit();
  	}
  	if ($rs['is_broker_completed']==1){
  		Application_Form_FrmMessage::Sucessfull("COMPLETED_WITHDRAW_CAN_NOT_EDIT","/invest/investment",2);
  		exit();
  	}
  	$this->view->rs = $rs;
  	 
  	$row = $dbinv->getBrokerSchedule($id);
  	$this->view->tran_schedule=$row;
  	 
  	$key = new Application_Model_DbTable_DbKeycode();
  	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
  }
  function rptUpdateinvestScheduleAction(){
  	
  	if($this->getRequest()->isPost()){
  		$_data = $this->getRequest()->getPost();
  			
  		try {
  			$_dbmodel = new Invest_Model_DbTable_DbInvestment();
  			$_dbmodel->updateInvestorSchedule($_data);
  			Application_Form_FrmMessage::Sucessfull("UPDATE_SUCCESS","/invest/investment");
  			exit();
  		}catch (Exception $e) {
  			Application_Form_FrmMessage::message("UPDATE_FAIL");
  			exit();
  			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
  		}
  	}
  	
  	$id =$this->getRequest()->getParam('id');
  	$id = empty($id)?0:$id;
  	$dbinv = new Report_Model_DbTable_DbInvestment();
  	$rs = $dbinv->getInvestmentById($id);
  	if(empty($rs)){
  		Application_Form_FrmMessage::Sucessfull("NO_RECORD",'/report/invest/investment',2);
  		exit();
  	}
  	if ($rs['is_completed']==1){
  		Application_Form_FrmMessage::Sucessfull("COMPLETED_WITHDRAW_CAN_NOT_EDIT","/invest/investment",2);
  		exit();
  	}
  	$this->view->rs = $rs;
  	 
  	$row = $dbinv->getInvestorSchedule($id);
  	$this->view->tran_schedule=$row;
  	 
  	$key = new Application_Model_DbTable_DbKeycode();
  	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
  }
  
  
  function rptClosingwithdrawalAction(){
  	 
  	try{
  		if($this->getRequest()->isPost()){
  			$search = $this->getRequest()->getPost();
  		}
  		else{
  			$search = array(
  					'adv_search'=>'',
  					'investor_id'=>0,
  					'start_date'=> date('Y-m-d'),
  					'end_date'=>date('Y-m-d'),
  			);
  		}
  		$db = new Report_Model_DbTable_DbInvestment();
  		$rs_rows= $db->getAllInvestorPaymentHistory($search);
  		$this->view->historypayment = $rs_rows;
  
  		$this->view->search = $search;
  			
  		$frmpopup = new Application_Form_FrmPopupGlobal();
  		$this->view->footerReport = $frmpopup->getFooterReport();
  
  		$frm = new Application_Form_FrmAdvanceSearch();
  		$frm = $frm->AdvanceSearch();
  		Application_Model_Decorator::removeAllDecorator($frm);
  		$this->view->frm_search = $frm;
  
  		$frm = new Invest_Form_FrmInvestment();
  		$frm_loan=$frm->FrmAddInvestment();
  		Application_Model_Decorator::removeAllDecorator($frm_loan);
  		$this->view->frm = $frm_loan;
  	}catch (Exception $e){
  		Application_Form_FrmMessage::message("Application Error");
  		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
  	}
  }
  
  function rptClosingwithdrawalbrokerAction(){
  	try{
  		if($this->getRequest()->isPost()){
  			$search = $this->getRequest()->getPost();
  		}
  		else{
  			$search = array(
  					'adv_search'=>'',
  					'broker_id'=>0,
  					'start_date'=> date('Y-m-d'),
  					'end_date'=>date('Y-m-d'),
  			);
  		}
  		$db = new Report_Model_DbTable_DbInvestment();
  		$rs_rows= $db->getAllBrokerPaymentHistory($search);
  		$this->view->historypayment = $rs_rows;
  
  		$this->view->search = $search;
  
  		$frm = new Application_Form_FrmAdvanceSearch();
  		$frm = $frm->AdvanceSearch();
  		Application_Model_Decorator::removeAllDecorator($frm);
  		$this->view->frm_search = $frm;
  			
  		$frm = new Invest_Form_FrmInvestment();
  		$frm_loan=$frm->FrmAddInvestment();
  		Application_Model_Decorator::removeAllDecorator($frm_loan);
  		$this->view->frm = $frm_loan;
  
  		$frmpopup = new Application_Form_FrmPopupGlobal();
  		$this->view->footerReport = $frmpopup->getFooterReport();
  
  	}catch (Exception $e){
  		Application_Form_FrmMessage::message("Application Error");
  		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
  	}
  	 
  }
  function submitentryAction(){
  	$db  = new Report_Model_DbTable_DbLandreport();
  	$key = new Application_Model_DbTable_DbKeycode();
  	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
  	if($this->getRequest()->isPost()){
  		$data = $this->getRequest()->getPost();
  		$db = new Report_Model_DbTable_DbInvestment();
  		$db->submitClosingEngry($data);
  		Application_Form_FrmMessage::Sucessfull("Closing Entry Success", "/report/invest/rpt-closingwithdrawal");
  	}
  }
  function submitentrybrokerAction(){
  	$db  = new Report_Model_DbTable_DbLandreport();
  	$key = new Application_Model_DbTable_DbKeycode();
  	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
  	if($this->getRequest()->isPost()){
  		$data = $this->getRequest()->getPost();
  		$db = new Report_Model_DbTable_DbInvestment();
  		$db->submitClosingEngryBroker($data);
  		Application_Form_FrmMessage::Sucessfull("Closing Entry Success", "/report/invest/rpt-closingwithdrawalbroker");
  	}
  }
  
  public function rptInvestmentAction(){
  	try{
  		if($this->getRequest()->isPost()){
  			$search = $this->getRequest()->getPost();
  		}
  		else{
  			$search = array(
  					'adv_search'=>'',
  					'status' => -1,
  					'investor_id'=>0,
  					'start_date'=> date('Y-m-d'),
  					'end_date'=>date('Y-m-d'),
  			);
  		}
  		$db = new Report_Model_DbTable_DbInvestment();
  		$rs_rows= $db->getAllInvestment($search);
  		$this->view->row = $rs_rows;
  			
  	}catch (Exception $e){
  		Application_Form_FrmMessage::message("Application Error");
  		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
  	}
  
  	$this->view->search = $search;
  	 
  	$frm = new Application_Form_FrmAdvanceSearch();
  	$frm = $frm->AdvanceSearch();
  	Application_Model_Decorator::removeAllDecorator($frm);
  	$this->view->frm_search = $frm;
  
  	$frm = new Invest_Form_FrmInvestment();
  	$frm_loan=$frm->FrmAddInvestment();
  	Application_Model_Decorator::removeAllDecorator($frm_loan);
  	$this->view->frm = $frm_loan;
  	 
  	$frmpopup = new Application_Form_FrmPopupGlobal();
  	$this->view->footerReport = $frmpopup->getFooterReport();
  }
}