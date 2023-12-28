<?php
class Report_LoanController extends Zend_Controller_Action {
    public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
  function indexAction(){
  }
  
  function rptLoancollectAction(){//list payment that collect from client
  	$dbs = new Report_Model_DbTable_DbloanCollect();
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
  	$db  = new Report_Model_DbTable_DbLandreport();
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

  	$db = new Application_Model_DbTable_DbGlobal();
  	$this->view->stepoption = $db->getVewOptoinTypeByType(29);
  	
  	$frmpopup = new Application_Form_FrmPopupGlobal();
  	$this->view->footerReport = $frmpopup->getFooterReport();
	$this->view->headerReport = $frmpopup->getLetterHeadReport();
  }

  function rptPaymentAction(){
  	$db  = new Report_Model_DbTable_DbLandreport();	
	if($this->getRequest()->isPost()){
		$search = $this->getRequest()->getPost();
	}else {
		$search = array(
			'adv_search' => '',
			'status_search' => -1,
			'status' => -1,
			'client_name' => "",
			'branch_id' => -1,
			'land_id'=>-1,
			'option_pay'=>-1,
			'receipt_type'=>-1,
			'user_id'=>'',
			'start_date'=> date('Y-m-d'),
  			'end_date'=>date('Y-m-d'),
			'payment_method'=>-1,
		);
	}
	$search['is_closed']='';
	$this->view->rssearch = $search;
	$this->view->search = $search;
	$this->view->loantotalcollect_list = $db->getALLLoanPayment($search);
	$this->view->list_end_date = $search;	
	$this->view->bankList = $db->getPayemtTotalByBankList();
	
	$frm = new Loan_Form_FrmSearchLoan();
	$frm = $frm->AdvanceSearch();
	Application_Model_Decorator::removeAllDecorator($frm);
	$this->view->frm_search = $frm;
	
	
	$frmpopup = new Application_Form_FrmPopupGlobal();
	$this->view->footerReport = $frmpopup->getFooterReport();
	$this->view->headerReport = $frmpopup->getLetterHeadReport();
  }
  function rptPaymentdownloadAction(){
  	
  	$db  = new Report_Model_DbTable_DbLandreport();
  	if($this->getRequest()->isPost()){
  		$search = $this->getRequest()->getPost();
  	}else {
  		$search = array(
  				'adv_search' => '',
  				'status_search' => -1,
  				'status' => -1,
  				'client_name' => "",
  				'branch_id' => -1,
  				'land_id'=>-1,
  				'option_pay'=>-1,
  				'receipt_type'=>-1,
  				'user_id'=>'',
  				'start_date'=> date('Y-m-d'),
  				'end_date'=>date('Y-m-d'),
  				'payment_method'=>-1,
  		);
  	}
  	$search['is_closed']='';
  	$this->view->rssearch = $search;
  	$this->view->search = $search;
  	$this->view->loantotalcollect_list = $db->getALLLoanPayment($search);
  	$this->view->list_end_date = $search;
  
  	$frm = new Loan_Form_FrmSearchLoan();
  	$frm = $frm->AdvanceSearch();
  	Application_Model_Decorator::removeAllDecorator($frm);
  	$this->view->frm_search = $frm;
  
  	$frmpopup = new Application_Form_FrmPopupGlobal();
  	$this->view->footerReport = $frmpopup->getFooterReport();
  	$this->view->headerReport = $frmpopup->getLetterHeadReport();
  }
  function rptClosingentryAction(){
  	$db  = new Report_Model_DbTable_DbLandreport();
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
  				'option_pay'=>-1,
  				'receipt_type'=>-1,
  				'start_date'=> date('Y-m-d'),
  				'end_date'=>date('Y-m-d'),
  				'payment_method'=>-1,
				'is_closed' => "",
  		);
  	}
  	$this->view->rssearch = $search;
  	$this->view->search = $search;
  	$this->view->loantotalcollect_list =$rs=$db->getALLLoanPayment($search);
  	$this->view->list_end_date = $search;
  
  	$frm = new Loan_Form_FrmSearchLoan();
  	$frm = $frm->AdvanceSearch();
  	Application_Model_Decorator::removeAllDecorator($frm);
  	$this->view->frm_search = $frm;
  	
  	$frmpopup = new Application_Form_FrmPopupGlobal();
  	$this->view->footerReport = $frmpopup->getFooterReport();
	$this->view->headerReport = $frmpopup->getLetterHeadReport();
  }
  function submitentryAction(){
  	$db  = new Report_Model_DbTable_DbLandreport();
  	$key = new Application_Model_DbTable_DbKeycode();
  	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
  	if($this->getRequest()->isPost()){
  		$data = $this->getRequest()->getPost();
  		$db = new Report_Model_DbTable_DbLandreport();
  		$db->submitClosingEngry($data);
  		Application_Form_FrmMessage::Sucessfull("Closing Entry Success", "/report/loan/rpt-closingentry");
  	}
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
  	$db  = new Report_Model_DbTable_DbLandreport();
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
  function rptLoanOutstandingAction(){//loand out standing with /collection
	    $db  = new Report_Model_DbTable_DbLandreport();
	  	if($this->getRequest()->isPost()){
	  		$search = $this->getRequest()->getPost();
	  	}else {
	  		$search = array(
	  				'adv_search' => "",
	  				'end_date' => date('Y-m-d'),
	  				'status' => "",
	  				'land_id' => "",
	  				'branch_id'		=>"",
	  				'schedule_opt'=>-1,
	  				'client_name'=>-1
	  		);
	  	}
	  	$this->view->fordate = $search['end_date'];
	  	$this->view->search = $search;
	  
	  	$frm = new Loan_Form_FrmSearchLoan();
	  	$frms = $frm->AdvanceSearch();
	  	Application_Model_Decorator::removeAllDecorator($frms);
	  	$this->view->frm_search = $frms;
	  	
	  	$rs= $db->getAllOutstadingLoan($search);
	  	$this->view->outstandloan = $rs;
	  	
	  	$frmpopup = new Application_Form_FrmPopupGlobal();
	  	$this->view->footerReport = $frmpopup->getFooterReport();
		$this->view->headerReport = $frmpopup->getLetterHeadReport();
  } 
 function rptLoanPayoffAction(){
 	$db  = new Report_Model_DbTable_DbLandreport();
 	$key = new Application_Model_DbTable_DbKeycode();
 	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
 	if($this->getRequest()->isPost()){
 		$search = $this->getRequest()->getPost();
 	}else{
	 	$search = array(
	 	'advance_search'  => '',
	 	'client_name' => 0,
	 	'start_date'  => date('Y-m-d'),
	 	'end_date'    => date('Y-m-d'),
	 	'branch_id'	  =>-1,
		'land_id'	  => 0,
		'paymnet_type'=> 0,
	 	'status'      => "",);
 	}
 	$this->view->LoanCollectionco_list =$db->getALLLoanPayoff($search);
 	$this->view->list_end_date=$search;
 	$this->view->search=$search;
 	$frm = new Loan_Form_FrmSearchLoan();
 	$frm = $frm->AdvanceSearch();
 	Application_Model_Decorator::removeAllDecorator($frm);
 	$this->view->frm_search = $frm;
 	
 	$frmpopup = new Application_Form_FrmPopupGlobal();
 	$this->view->footerReport = $frmpopup->getFooterReport();
	$this->view->headerReport = $frmpopup->getLetterHeadReport();
 }
 function rptLoanExpectIncomeAction(){
 	if($this->getRequest()->isPost()){
 		$search = $this->getRequest()->getPost();
 	}else{
 		$search = array(
 			'adv_search' => '',
 			'client_name' => -1,
 			'start_date'=> date('Y-m-d'),
 			'end_date'=>date('Y-m-d'),
 			'branch_id'		=>	-1,
 			'co_id'			=>'',
 			'schedule_opt'	=> -1,
 			'stepoption'=>-1,
 			'status'=>-1,
 			'is_completed'=>-1
 		);
 	}
 	$this->view->search=$search;
 	$db  = new Report_Model_DbTable_DbLandreport();
 	$this->view->LoanCollectionco_list =$db->getALLLoanExpectIncome($search);
 	
 	$frm = new Loan_Form_FrmSearchLoan();
 	$frm = $frm->AdvanceSearch();
 	Application_Model_Decorator::removeAllDecorator($frm);
 	$this->view->frm_search = $frm;
 	
 	$db = new Application_Model_DbTable_DbGlobal();
 	$this->view->stepoption = $db->getVewOptoinTypeByType(29);
 	
 	$frmpopup = new Application_Form_FrmPopupGlobal();
 	$this->view->footerReport = $frmpopup->getFooterReport();
	$this->view->headerReport = $frmpopup->getLetterHeadReport();
  
 }
 
  function rptPaymentHistoryAction(){
 	$db  = new Report_Model_DbTable_DbLandreport();
 	if($this->getRequest()->isPost()){
 		$search = $this->getRequest()->getPost();
 	}else {
 		$search = array(
 				'adv_search' => '',
 				'status_search' => -1,
 				'status' => -1,
 				'branch_id' => "",
 				'client_name' => -1,
 				'option_pay'=>-1,
 				'receipt_type'=>-1,
 				'co_id' => "",
 				'streetlist'=> "",
 				'land_id'=>-1,
 				'start_date' =>date('Y-m-d'),
 				'end_date' => date('Y-m-d'),
 		);
 	}
 	$this->view->loantotalcollect_list =$db->getALLLoanPayment($search,1);
 	$this->view->search=$search;
 	$frm = new Loan_Form_FrmSearchLoan();
 	$frm = $frm->AdvanceSearch();
 	Application_Model_Decorator::removeAllDecorator($frm);
 	$this->view->frm_search = $frm;
 	
 	$db = new Application_Model_DbTable_DbGlobal();
 	$street = $db->getAllStreetForOpt();
 	$this->view->street = $street;
 	
 	$key = new Application_Model_DbTable_DbKeycode();
 	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
 	
 	$frmpopup = new Application_Form_FrmPopupGlobal();
 	$this->view->footerReport = $frmpopup->getFooterReport();
	$this->view->headerReport = $frmpopup->getLetterHeadReport();
 }
 
 function rptSoldAction(){//release all loan
 	$db  = new Report_Model_DbTable_DbLandreport();
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
 	$this->view->loanrelease_list=$db->getAllLoanReport($search);
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
 	$this->view->headerReport = $frmpopup->getLetterHeadReport();
 }
 
 function rptPaymentschedulesAction(){
 	$db = new Report_Model_DbTable_DbRptPaymentSchedule();
 	$id =$this->getRequest()->getParam('id');
 	$id = empty($id)?0:$id;
 	$row = $db->getPaymentSchedule($id);
 	$this->view->tran_schedule=$row;
 	$this->view->saleId=$id;
 	if(empty($row) or $row==''){
 		Application_Form_FrmMessage::Sucessfull("RECORD_NOT_EXIST",'/report/loan/rpt-sold',2);
 	}
	$arr = array(
		"saleId"=>$id,
	);
	$this->view->coutingCompetedRecord =$db->checkCoutingScheduleCompetedRecord($arr);
 	$db = new Application_Model_DbTable_DbGlobal();
 	$rs = $db->getClientByMemberIdGlobal($id);
 	if(empty($rs)){
 		Application_Form_FrmMessage::Sucessfull("RECORD_NOTFUND",'/report/loan/rpt-sold',2);
 		exit();
 	}
 	$this->view->client =$rs;
 	$frm = new Application_Form_FrmSearchGlobal();
 	$form = $frm->FrmSearchLoadSchedule();
 	Application_Model_Decorator::removeAllDecorator($form);
 	$this->view->form_filter = $form;
 	$day_inkhmer = $db->getDayInkhmerBystr(null);
 	$this->view->day_inkhmer = $day_inkhmer;
 	$key = new Application_Model_DbTable_DbKeycode();
 	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
 	
 	$dbp = new Project_Model_DbTable_DbProject();
 	$this->view->branchinfo = $dbp->getBranchById($rs['branch_id']);//for to get bank info
 }
 function rptPaymentschedulesclientAction(){//for bokor
 	$db = new Report_Model_DbTable_DbRptPaymentSchedule();
 	$id =$this->getRequest()->getParam('id');
 	$id = empty($id)?0:$id;
 	$row = $db->getPaymentSchedule($id);
 	$this->view->tran_schedule=$row;
 	if(empty($row) or $row==''){
 		Application_Form_FrmMessage::Sucessfull("RECORD_NOT_EXIST",'/report/loan/rpt-sold',2);
 	}
 	$db = new Application_Model_DbTable_DbGlobal();
 	$rs = $db->getClientByMemberIdGlobal($id);
 	if(empty($rs)){
 		Application_Form_FrmMessage::Sucessfull("RECORD_NOTFUND",'/report/loan/rpt-sold',2);
 		exit();
 	}
 	$this->view->client =$rs;
 	$frm = new Application_Form_FrmSearchGlobal();
 	$form = $frm->FrmSearchLoadSchedule();
 	Application_Model_Decorator::removeAllDecorator($form);
 	$this->view->form_filter = $form;
 	$day_inkhmer = $db->getDayInkhmerBystr(null);
 	$this->view->day_inkhmer = $day_inkhmer;
 	$key = new Application_Model_DbTable_DbKeycode();
 	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
 
 	$dbp = new Project_Model_DbTable_DbProject();
 	$this->view->branchinfo = $dbp->getBranchById($rs['branch_id']);//for to get bank info
 }


 
  function receiptAction(){
	 $key = new Application_Model_DbTable_DbKeycode();
	 $this->view->data=$key->getKeyCodeMiniInv(TRUE);
	 $db  = new Report_Model_DbTable_DbLandreport();
	 $id = $this->getRequest()->getParam('id');
	 if(!empty($id)){
		 $receipt = $db->getReceiptByID($id);
		 if(empty($receipt) or $receipt==''){
		 	Application_Form_FrmMessage::Sucessfull("NO_RECORD",'/report/loan/rpt-payment',2);
		 }
			$this->view->rs = $receipt;
			if(empty($receipt['name_kh'])){
				$this->_redirect("/report/paramater");
			}
	 }else{
  		$this->_redirect("/report/paramater");
  	}
  	
  	$dateLimit=MAX_DATE_OLD_RECEIPT;
  	$receiptType = null;
  	if ($receipt['field3']==1){
  		if (date("Y-m-d",strtotime($receipt['date_pay']))>=date("Y-m-d",strtotime($dateLimit))){
  			if ($receipt['payment_times']==1){
  				$receiptType=1;
  			}
  		}
  	}
  	$this->view->dateLimit = $dateLimit;
	
	$db  = new Report_Model_DbTable_DbIncomeexpense();
	$conditionArr = array(
		"id" => $id,
		"documentforType" => 3,
	);
	$this->view->document=$db->getExpenseDocumentbyid($conditionArr);
  		$frmpopup = new Application_Form_FrmPopupGlobal();
		$this->view->footer = $frmpopup->getFooterReceipt();
		$this->view->officailreceipt = $frmpopup->getOfficailReceipt($receiptType);
  }
  

  function rptUpdatestatusAction(){// keep to use for internal developer
  	$db = new Report_Model_DbTable_DbRptPaymentSchedule();
  	$id =$this->getRequest()->getParam('id');
  	$id = empty($id)?0:$id;
  	$row = $db->getPaymentScheduleById($id);
  	$this->view->tran_schedule=$row;
  	if(empty($row) or $row==''){
  		Application_Form_FrmMessage::Sucessfull("RECORD_NOT_EXIST",'/report/loan/rpt-sold',2);
  		exit();
  	}
  	$db = new Application_Model_DbTable_DbGlobal();
  	$rs = $db->getClientByMemberIdGlobal($id);
  	if(empty($rs)){
  		Application_Form_FrmMessage::Sucessfull("RECORD_NOTFUND",'/report/loan/rpt-sold',2);
  		exit();
  	}
  	if($this->getRequest()->isPost()){
  		$_data = $this->getRequest()->getPost();
  		try {
  			$_dbmodel = new Report_Model_DbTable_DbLandreport();
  			$_dbmodel->updateScheculeStatus($_data);
  			Application_Form_FrmMessage::Sucessfull("UPDATE_SUCESS","/report/loan/rpt-sold");
  		}catch (Exception $e){
  			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
  		}
  	}

  	$this->view->client =$rs;
  	$frm = new Application_Form_FrmSearchGlobal();
  	$form = $frm->FrmSearchLoadSchedule();
  
  	$day_inkhmer = $db->getDayInkhmerBystr(null);
  	$this->view->day_inkhmer = $day_inkhmer;
  	$key = new Application_Model_DbTable_DbKeycode();
  	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
  	$this->view->id=$id;
  	
  	$steppay = $db->getVewOptoinTypeByType(29);
  	$this->view->steppay =$steppay;
	$this->view->userlist =  $db->getAllUserGlobal();
  }
  
  function addschedultestAction(){
  	if($this->getRequest()->isPost()){
  		$_data = $this->getRequest()->getPost();
  		$_dbmodel = new Report_Model_DbTable_DbLandreport();
  		$rows_return=$_dbmodel->getPreviewSchedule($_data);
  		print_r(Zend_Json::encode($rows_return));
  		exit();
  	}
  }
  
  function bugAction(){
  		$db = new Report_Model_DbTable_Dbbug();
  		if($this->getRequest()->isPost()){
  			$formdata=$this->getRequest()->getPost();
  		}
  		else{
  			$formdata = array(
  					"adv_search"=>'',
  					"branch_id"=>0,
  					"property_type"=>0,
  					"currency_type"=>-1,
  					"status"=>-1,
  					'start_date'=> date('Y-m-d'),
  					'end_date'=>date('Y-m-d'),
  			);
  		}
  		$this->view->rs_sold= $db->getRealDatescheduleandSoldprice($formdata);//call frome model
  		$this->view->rs_paid = $db->getRealPaid($formdata);
  		$this->view->rs_schedule = $db->getScheduleCompletednotUpdate($formdata);
  		$this->view->rs_begining = $db->getBeginingBalance($formdata);
		
		$frm = new Loan_Form_FrmSearchLoan();
		$frm = $frm->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
  }
  function paymenthistoryAction(){
  	$db  = new Report_Model_DbTable_DbLandreport();
  	$id = $this->getRequest()->getParam('id');
  	$id = empty($id)?0:$id;
  	$rs=$db->getPaymentSaleid($id);
  	$this->view->loantotalcollect_list =$rs;
	
	$this->view->creaditHistory=$db->getCreditBySaleid($id);
	
  	if(empty($rs)){
  		Application_Form_FrmMessage::Sucessfull("RECORD_NOTFUND","/loan/index",2);
  		exit();
  	}
  }
  
  function rptChangeownerAction(){
  	$db  = new Report_Model_DbTable_DbLandreport();
  	$key = new Application_Model_DbTable_DbKeycode();
  	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
  	
  	if($this->getRequest()->isPost()){
 				$search = $this->getRequest()->getPost();
 	}else{
		$search = array(
			'txt_search'=>'',
			'client_name'=> -1,
			'branch_id' => -1,
			'land_id'=>0,
			'start_date'=> date('Y-m-d'),
			'end_date'=>date('Y-m-d'),
			 );
	}
	$rs_rows= $db->getAllTranferOwner($search,1);
	$this->view->row = $rs_rows;
  	$this->view->search = $search;
  	
  	$frm = new Loan_Form_FrmSearchLoan();
	$frm = $frm->AdvanceSearch();
	Application_Model_Decorator::removeAllDecorator($frm);
	$this->view->frm_search = $frm;
	
	$frmpopup = new Application_Form_FrmPopupGlobal();
	$this->view->footerReport = $frmpopup->getFooterReport();
	$this->view->headerReport = $frmpopup->getLetterHeadReport();
  }
  function rptChangepropertyAction(){
  	$db  = new Report_Model_DbTable_DbLandreport();
  	$key = new Application_Model_DbTable_DbKeycode();
  	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
  	 
  	if($this->getRequest()->isPost()){
  		$search = $this->getRequest()->getPost();
  	}else{
  		$search = array(
  				'txt_search'=>'',
  				'client_name'=> -1,
  				'branch_id' => -1,
  				'land_id'=>0,
  				'status'=>-1,
  				'start_date'=> date('Y-m-d'),
  				'end_date'=>date('Y-m-d'),
  		);
  	}
  	$rs_rows= $db->getAllChangeHouse($search,1);
  	$this->view->row = $rs_rows;
  	$this->view->search = $search;
  	 
  	$frm = new Loan_Form_FrmSearchLoan();
  	$frm = $frm->AdvanceSearch();
  	Application_Model_Decorator::removeAllDecorator($frm);
  	$this->view->frm_search = $frm;
  	
  	$frmpopup = new Application_Form_FrmPopupGlobal();
  	$this->view->footerReport = $frmpopup->getFooterReport();
	$this->view->headerReport = $frmpopup->getLetterHeadReport();
  }
  
  function rptValidateagreementAction(){//release all loan
  	$db  = new Report_Model_DbTable_DbLandreport();
  	if($this->getRequest()->isPost()){
  		$search = $this->getRequest()->getPost();
  	}
  	else{
		$dbgb = new Setting_Model_DbTable_DbGeneral();
    	$alert = $dbgb->geLabelByKeyName('agree_day_alert');
		$amt_day = 0;
    	if (!empty($alert['keyValue'])){
    		$amt_day = $alert['keyValue'];
    	}
		
  		$search = array(
  				'adv_search'=>'',
  				'branch_id'=>-1,
  				'property_type'=>0,
  				'client_name'=>'',
  				'land_id'=>-1,
  				'co_id'=>-1,
  				'start_date'=> date('Y-m-d'),
  				'end_date'=>date('Y-m-d',strtotime("+$amt_day day"))
				);
  	}
  	$this->view->loanrelease_list=$db->getValidationAgreement($search);
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
	$this->view->headerReport = $frmpopup->getLetterHeadReport();
  }
  function samplereceiptAction(){
  	$key = new Application_Model_DbTable_DbKeycode();
  	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
  	$db  = new Report_Model_DbTable_DbLandreport();
  	$id = $this->getRequest()->getParam('id');
  	if(!empty($id)){
  		$receipt = $db->getReceiptByID($id);
  		$this->view->rs = $receipt;
  		if(empty($receipt['name_kh'])){
  			$this->_redirect("/report/paramater");
  		}
  	}else{
  		$this->_redirect("/report/paramater");
  	}
  	$frmpopup = new Application_Form_FrmPopupGlobal();
  	$this->view->footer = $frmpopup->getFooterReceipt();
  }
  public function rptCrmAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'advance_search' => "",
						'branch_search' => "",
						'ask_for_search' => "",
						'know_by_search' => "",
						'prev_concern' => "",
						'status_search' => -1,
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
				);
			}
			
			$db = new Report_Model_DbTable_Dbcrm();
			$rs_rows = $db->getAllCRM($search);
			$this->view->row = $rs_rows;
			$this->view->search  = $search;
		
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$_dbgb = new Application_Model_DbTable_DbGlobal();
		$pevconcer = $_dbgb->getViewById(22);
		$this->view->prev_concern = $pevconcer;
		
// 		$frm = new Home_Form_FrmCrm();
// 		$frm->FrmAddCRM(null);
// 		Application_Model_Decorator::removeAllDecorator($frm);
// 		$this->view->frm_crm = $frm;
	}
	public function rptCrmDetailAction(){
		$id=$this->getRequest()->getParam("id");
		if(empty($id)){
			$this->_redirect("/allreport/allstudent/rpt-crm");
		}
		$db = new Report_Model_DbTable_Dbcrm();
		$row = $db->getCRMById($id);
		$this->view->rs = $row;
		
		$rowdetail = $db->getCRMDetailById($id);
		$this->view->rowdetail = $rowdetail;
		$allContact = $db->AllHistoryContact($id);
		$this->view->history = $allContact;
		
		$pre = explode(",", $row['prev_concern']);
		$prevCon="";
		if (!empty($pre)) foreach ($pre as $a){
			$title = $db->getPrevTilteByKeyCode($a);
			if (empty($prevCon)){
				$prevCon = $title;
			}else {
				if (!empty($title)){
					$prevCon = $prevCon." , ".$title;
				}
			}
		}
		$this->view->prevconcern = $prevCon;
	}
	
	function rptCrmDailyContactAction(){
		try{
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'advance_search' => "",
						'branch_search' => "",
						'ask_for_search' => "",
						'crm_list'  => "",
						'status_search' => -1,
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
				);
			}
				
			$db = new Report_Model_DbTable_Dbcrm();
			$rs_rows = $db->getAllCRMDailyContact($search);
			$this->view->row = $rs_rows;
			$this->view->search  = $search;
		
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		
		$frm = new Application_Form_FrmAdvanceSearch();
		$frm = $frm->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
		$this->view->result=$search;
		
// 		$frm = new Home_Form_FrmCrm();
// 		$frm->FrmAddCRM(null);
// 		Application_Model_Decorator::removeAllDecorator($frm);
// 		$this->view->frm_crm = $frm;
	}
	
	function rptSoldsummaryAction(){//release all loan
		$db  = new Report_Model_DbTable_DbLandreport();
		if($this->getRequest()->isPost()){
			$search = $this->getRequest()->getPost();
		}
		else{
			$search = array(
				'adv_search'=>'',
				'branch_id'=>-1,
				'schedule_opt'=>-1,
				'property_type'=>0,
				'queryOrdering'=>0,
				'client_name'=>'',
				'buy_type'=>-1,
				'land_id'=>-1,
				'streetlist'=>'',
				'sale_status'=>'',
				'start_date'=> date('Y-m-d'),
				'end_date'=>date('Y-m-d')
			);
		}
		$this->view->loanrelease_list = $db->getSaleSummary($search);
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
		$this->view->headerReport = $frmpopup->getLetterHeadReport();
	}
	
	
	function rptMonthremainAction(){//release all loan
		$db  = new Report_Model_DbTable_DbLandreport();
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
					);
		}
		$this->view->loanrelease_list=$db->getAllRemainMonth($search);
		$this->view->list_end_date=$search;
		$this->view->search = $search;
		$this->view->branch_id = $search['branch_id'];
			
		$frm = new Loan_Form_FrmSearchLoan();
		$frm = $frm->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
			
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data=$key->getKeyCodeMiniInv(TRUE);
	}
	
	function issueAgreementAction(){
		$db  = new Report_Model_DbTable_DbParamater();
		$id = $this->getRequest()->getParam("id");
		$id = empty($id)?0:$id;
		$rsagreement = $db->getIssueHouseAgreement($id);
		if (empty($rsagreement)){
			Application_Form_FrmMessage::Sucessfull("RECORD_NOTFUND","/loan/index",2);
			exit();
		}
		$this->view->agreement = $rsagreement;
		$db_keycode = new Application_Model_DbTable_DbKeycode();
		$this->view->keyValue = $db_keycode->getKeyCodeMiniInv();
	}
	
	function rptTransferCashAction(){//release all loan
		$db  = new Report_Model_DbTable_DbLandreport();
		if($this->getRequest()->isPost()){
 				$search = $this->getRequest()->getPost();
 				
 			}else{
				$search = array(
						'adv_search'=>'',
						'client_name'=> -1,
						'branch_id' => -1,
						'status' => -1,
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
						 );
			}
		$this->view->row = $db->getAllTransferCash($search);
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
	
	function rptSaleCondictionAction(){
		$db  = new Report_Model_DbTable_DbLandreport();
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
					'streetlist'=>'',
					'sale_status'=>'',
					'start_date'=> date('Y-m-d'),
					'end_date'=>date('Y-m-d')
			);
		}
		$this->view->loanrelease_list = $db->getSaleCondiction($search);
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
	
	function updatenoteExpectIncomeAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Report_Model_DbTable_DbLandreport();
			$row = $db->updateNoteExpectIncome($data);
			print_r(Zend_Json::encode($row));
			exit();
		}
	}
	
	function rptMaterialinludeAction(){
		$db  = new Report_Model_DbTable_DbLandreport();	
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data=$key->getKeyCodeMiniInv(TRUE);
		if($this->getRequest()->isPost()){
			$search = $this->getRequest()->getPost();
		}else {
			$search = array(
				"adv_search"=>'',
				"branch_id"=>-1,
				'land_id'=>-1,
				'client_name'=>-1,
				'is_gived'=>-1,
				'start_date'=> date('Y-m-d'),
				'end_date'=>date('Y-m-d'),
				"items_id"=>'',
			);
		}
		$this->view->search = $search;
		$this->view->row = $db->getAllIncludeMaterial($search);
		
		$frm = new Loan_Form_FrmSearchLoan();
		$frm = $frm->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
		
		$frmMaterial = new Loan_Form_FrmMaterialInclude();
		$frmMaterial = $frmMaterial->FrmAddMaterialIncludeSearch();
		Application_Model_Decorator::removeAllDecorator($frmMaterial);
		$this->view->frmMaterial = $frmMaterial;
		
		$frmpopup = new Application_Form_FrmPopupGlobal();
		$this->view->footerReport = $frmpopup->getFooterReport();
  }
  public function rptMaterialinludeDetailAction(){
		$id=$this->getRequest()->getParam("id");
		$id = empty($id)?0:$id;
		$db= new Loan_Model_DbTable_DbMaterialInclude();
		$row = $db->getMaterialIncludebyid($id);
		if(empty($row)){
			Application_Form_FrmMessage::Sucessfull("RECORD_NOTFUND","/loan/materialinc",2);
			exit();
		}
		$this->view->rows = $db->getMaterialIncludeDetailbyid($id);
		$this->view->rs = $row;
		
		$frmpopup = new Application_Form_FrmPopupGlobal();
		$this->view->headerReport = $frmpopup->getLetterHeadReport();
    }

	
	function rptIncomeGraphicAction(){
		$this->_redirect("/home/index/rpt-income-graphic");
		exit();
	}

	function rptPropertypriceAction(){ // by Vandy
		if($this->getRequest()->isPost()){
			$search = $this->getRequest()->getPost();
		}else{
			$search = array(
					'adv_search'=>'',
					'property_type'=>'',
					"branch_id"=> -1,
					'type_property_sale'=>-1,
					'date_type'=>1,
					'start_date'=> date('Y-m-d'),
					'end_date'=>date('Y-m-d'),
					'streetlist'=>''
			);
		}
		$this->view->list_end_date = $search;
		$db  = new Report_Model_DbTable_DbLandreport();
		$this->view->row = $db->getAllPropertiesprice($search);
		 
		$frm=new Other_Form_FrmProperty();
		$row=$frm->FrmFrmProperty();
		Application_Model_Decorator::removeAllDecorator($row);
		$this->view->frm_property=$row;
	}

	function rptUnclosingentryAction(){
		$db  = new Report_Model_DbTable_DbLandreport();
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
					'option_pay'=>-1,
					'receipt_type'=>-1,
					'start_date'=> date('Y-m-d'),
					'end_date'=>date('Y-m-d'),
					'payment_method'=>-1,
					'is_closed' => "",
			);
		}
		$this->view->rssearch = $search;
		$this->view->loantotalcollect_list =$rs=$db->getALLLoanPayment($search);
		$this->view->list_end_date = $search;
	  
		$frm = new Loan_Form_FrmSearchLoan();
		$frm = $frm->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
		
		$frmpopup = new Application_Form_FrmPopupGlobal();
		$this->view->footerReport = $frmpopup->getFooterReport();
  }
   function submitentryunclosedAction(){
  	$db  = new Report_Model_DbTable_DbLandreport();
  	$key = new Application_Model_DbTable_DbKeycode();
  	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
  	if($this->getRequest()->isPost()){
  		$data = $this->getRequest()->getPost();
  		$db = new Report_Model_DbTable_DbLandreport();
  		$db->submitUnclosingEngry($data);
  		Application_Form_FrmMessage::Sucessfull("Unclosing Entry Success", "/report/loan/rpt-unclosingentry");
  	}
  }
   function rptSummaryDailyAction(){
		$db = new Report_Model_DbTable_DbLandreport();
		
		if($this->getRequest()->isPost()){
			$search = $this->getRequest()->getPost();
		}else {
			$search = array(
				'branch_id' => "",
				'start_date'=> date('Y-m-d'),
				'end_date'=> date('Y-m-d'),
			);
		}
		$frm = new Loan_Form_FrmSearchLoan();
		$frm = $frm->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
	
		$row = $db->getSummaryDailyOperation($search);
		$this->view->summaryData = $row;
		$this->view->search = $search;
		
		$frmpopup = new Application_Form_FrmPopupGlobal();
		$this->view->footerReport = $frmpopup->getFooterReport();
		$this->view->headerReport = $frmpopup->getLetterHeadReport();	
	}
	function rptSummaryAction(){
		$data = array();
		$db = new Report_Model_DbTable_DbSummary();
		
		if($this->getRequest()->isPost()){
			$search = $this->getRequest()->getPost();
		}else {
			$search = array(
					'projectId' => "",
			);
		}
		
		
		$this->view->resultData = $db->getAllSummaryData($search);
		$this->view->rsFilter = $search;
		
		$dbg = new Application_Model_DbTable_DbGlobal();
		
		$filter= array(
				'showAll'=>1
				);
		$this->view->rsBranch = $dbg->getAllBranchName(null,null,$filter);
	}
	
	function rptVerificationSaleAction(){
		$db  = new Report_Model_DbTable_DbLandreport();
		if($this->getRequest()->isPost()){
			$search = $this->getRequest()->getPost();
		}
		else{
			$search = array(
				'adv_search'=>'',
				'branch_id'=>-1,
				'schedule_opt'=>-1,
				'property_type'=>0,
				'queryOrdering'=>0,
				'client_name'=>'',
				'buy_type'=>-1,
				'land_id'=>-1,
				'streetlist'=>'',
				'sale_status'=>'',
				'start_date'=> date('Y-m-d'),
				'end_date'=>date('Y-m-d')
			);
		}
		$this->view->loanrelease_list = $db->getVerificationSale($search);
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
		$this->view->headerReport = $frmpopup->getLetterHeadReport();
	}
	
	function rptVerificationDetailAction(){
	 $key = new Application_Model_DbTable_DbKeycode();
	 $this->view->data=$key->getKeyCodeMiniInv(TRUE);
	 
	 $db  = new Report_Model_DbTable_DbLandreport();
	 $id = $this->getRequest()->getParam('id');
	 if(!empty($id)){
		 $rs = $db->getVerificationDetail($id);
		 if(empty($rs) or $rs==''){
		 	Application_Form_FrmMessage::Sucessfull("NO_RECORD",'/report/loan/rpt-verification-sale',2);
		 }
		$this->view->rs = $rs;
			
	 }else{
  		$this->_redirect("/report/loan");
  	}
  	
  	$saleId = empty($rs["saleId"]) ? 0 : $rs["saleId"];
	$db  = new Report_Model_DbTable_DbIncomeexpense();
	$conditionArr = array(
		"id" => $saleId,
		"documentforType" => 4,
	);
	$this->view->document=$db->getExpenseDocumentbyid($conditionArr);
	
	$frmpopup = new Application_Form_FrmPopupGlobal();
	$this->view->headerReport = $frmpopup->getLetterHeadReport();
  }

}