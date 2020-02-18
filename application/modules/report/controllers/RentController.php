<?php
class Report_RentController extends Zend_Controller_Action {
    public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
  function indexAction(){
  }
  function rptRentAction(){//release all loan
  	$db  = new Report_Model_DbTable_DbRent();
  	if($this->getRequest()->isPost()){
  		$search = $this->getRequest()->getPost();
  	}
  	else{
  		$search = array(
  				'adv_search'=>'',
  				'branch_id'=>-1,
  				'schedule_opt'=>-1,
  				'property_type'=>0,
  				'client_name'=>'',
  				'buy_type'=>-1,
  				'land_id'=>-1,
  				'co_id'=>-1,
  				'start_date'=> date('Y-m-d'),
  				'end_date'=>date('Y-m-d'));
  	}
  	$this->view->loanrelease_list=$db->getAllLoan($search);
  	$this->view->list_end_date=$search;
  	$this->view->search = $search;
  	$this->view->branch_id = $search['branch_id'];
  		
  	$frm = new Loan_Form_FrmSearchLoan();
  	$frm = $frm->AdvanceSearch();
  	Application_Model_Decorator::removeAllDecorator($frm);
  	$this->view->frm_search = $frm;
  		
  	$key = new Application_Model_DbTable_DbKeycode();
  	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
  
  	$frmpopup = new Application_Form_FrmPopupGlobal();
  	$this->view->footerReport = $frmpopup->getFooterReport();
  }
  function rptPaymentAction(){
  	$db  = new Report_Model_DbTable_DbRent();
  	$key = new Application_Model_DbTable_DbKeycode();
  	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
  	if($this->getRequest()->isPost()){
  		$search = $this->getRequest()->getPost();
  	}else {
  		$search = array(
  				'adv_search' => '',
  				'status_search' => -1,
  				'status' => -1,
  				'client_name' => "",
  				'branch_id' => -2,
  				'land_id'=>-1,
  				'user_id'=>'',
  				'start_date'=> date('Y-m-d'),
  				'end_date'=>date('Y-m-d'),
  				'payment_method'=>-1,
  		);
  	}
  	$search['is_closed']='';
  	$this->view->rssearch = $search;
  	$this->view->loantotalcollect_list = $db->getALLLoanPayment($search);
  	$this->view->list_end_date = $search;
  
  	$frm = new Loan_Form_FrmSearchLoan();
  	$frm = $frm->AdvanceSearch();
  	Application_Model_Decorator::removeAllDecorator($frm);
  	$this->view->frm_search = $frm;
  
  	$frmpopup = new Application_Form_FrmPopupGlobal();
  	$this->view->footerReport = $frmpopup->getFooterReport();
  }
  function paymenthistoryAction(){
  	$db  = new Report_Model_DbTable_DbRent();
  	$id = $this->getRequest()->getParam('id');
  	$id = empty($id)?0:$id;
  	$rs=$db->getPaymentSaleid($id);
  	$this->view->loantotalcollect_list =$rs;
  	if(empty($rs)){
  		Application_Form_FrmMessage::Sucessfull("RECORD_NOTFUND","/rent/deposit");
  		exit();
  	}
  }
  function rptPaymentschedulesAction(){
  	$db = new Report_Model_DbTable_DbRent();
  	$id =$this->getRequest()->getParam('id');
  	$id = empty($id)?0:$id;
  	$row = $db->getPaymentSchedule($id);
  	$this->view->tran_schedule=$row;
  	if(empty($row) or $row==''){
  		Application_Form_FrmMessage::Sucessfull("RECORD_NOT_EXIST",'/report/rent/rpt-rent');
  	}
  	$rs = $db->getClientByMemberId($id);
  	if(empty($rs)){
  		Application_Form_FrmMessage::Sucessfull("RECORD_NOTFUND",'/report/rent/rpt-rent');
  		exit();
  	}
  	$this->view->client =$rs;
  	$frm = new Application_Form_FrmSearchGlobal();
  	$form = $frm->FrmSearchLoadSchedule();
  	Application_Model_Decorator::removeAllDecorator($form);
  	$this->view->form_filter = $form;
  	
  	$dbp = new Application_Model_DbTable_DbGlobal();
  	$day_inkhmer = $dbp->getDayInkhmerBystr(null);
  	$this->view->day_inkhmer = $day_inkhmer;
  	
  	$key = new Application_Model_DbTable_DbKeycode();
  	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
  }
  function receiptAction(){
  	$key = new Application_Model_DbTable_DbKeycode();
  	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
  	$db  = new Report_Model_DbTable_DbRent();
  	$id = $this->getRequest()->getParam('id');
  	if(!empty($id)){
  		$receipt = $db->getRentReceiptByID($id);
  		$this->view->rs = $receipt;
  		if(empty($receipt['name_kh'])){
  			$this->_redirect("/report/paramater");
  		}
  	}else{
  		$this->_redirect("/report/paramater");
  	}
  	$frmpopup = new Application_Form_FrmPopupGlobal();
  	$this->view->officailreceipt = $frmpopup->getOfficailReceiptRent();
  }
  function rptAgreementAction(){
  	$db  = new Report_Model_DbTable_DbRent();
  	$id = $this->getRequest()->getParam("id");
  	$id = empty($id)?0:$id;
  
  	$rsagreement = $db->getAgreementByRentID($id);
  	if (empty($rsagreement)){
  		Application_Form_FrmMessage::Sucessfull("RECORD_NOTFUND","/rent/deposit");
  		exit();
  	}
  	$this->view->agreement = $rsagreement;
  	$this->view->deposit = $db->getDepositAgreement($id);
  	$db_keycode = new Application_Model_DbTable_DbKeycode();
  	$this->view->keyValue = $db_keycode->getKeyCodeMiniInv();
  	
  	$dbSet = new Rent_Model_DbTable_DbSetting();
  	$this->view->rowdetail = $dbSet->getSettingDetailById($rsagreement['setting_opt']);
  }
  
  function rptLoanLateAction(){
  
  	if($this->getRequest()->isPost()){
  		$search = $this->getRequest()->getPost();
  	}else {
  		$search = array(
  				'adv_search' =>	"",
  				'start_date' => '',
  				'end_date'   => date('Y-m-d'),
  				'branch_id'  => -1,
  				'co_id'  => '',
  				'client_name'=> 0
  		);
  	}
  	$db  = new Report_Model_DbTable_DbRent();
  	$this->view->loanlate_list =$db->getALLLoanlate($search);
  	$key = new Application_Model_DbTable_DbKeycode();
  	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
  	$this->view->list_end_date = $search["end_date"];
  	 
  	$this->view->search = $search;
  	 
  	$frm = new Loan_Form_FrmSearchLoan();
  	$frm = $frm->AdvanceSearch();
  	Application_Model_Decorator::removeAllDecorator($frm);
  	$this->view->frm_search = $frm;
  	 
  	$frmpopup = new Application_Form_FrmPopupGlobal();
  	$this->view->footerReport = $frmpopup->getFooterReport();
  }
  function rptLoancollectAction(){//list payment that collect from client
  	$dbs = new Report_Model_DbTable_DbRent();
  	$frm = new Application_Form_FrmSearchGlobal();
  	if($this->getRequest()->isPost()){
  		$search = $this->getRequest()->getPost();
  	}
  	else{
  		$search = array(
  				'branch_id'=>-1,
  				'client_name'=>'',
  				'last_optiontype'=>-1,
  				'start_date'=> date('Y-m-d'),
  				'end_date'=>date('Y-m-d'),
  				'stepoption'=>0,
  				'status' => -1,);
  	}
  	$this->view->date_show=$search['end_date'];
  	$this->view->search=$search;
  	$row = $dbs->getAllLnClient($search);
  	$this->view->tran_schedule=$row;
  	 
  	$key = new Application_Model_DbTable_DbKeycode();
  	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
  
  	$this->view->list_end_dates = $search["end_date"];
  	$frm = new Loan_Form_FrmSearchLoan();
  	$frm = $frm->AdvanceSearch();
  	Application_Model_Decorator::removeAllDecorator($frm);
  	$this->view->frm_search = $frm;
  
  	 
  	$frmpopup = new Application_Form_FrmPopupGlobal();
  	$this->view->footerReport = $frmpopup->getFooterReport();
  }
}