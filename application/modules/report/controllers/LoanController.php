<?php
class Report_LoanController extends Zend_Controller_Action {
	private $activelist = array('មិនប្រើ​ប្រាស់', 'ប្រើ​ប្រាស់');
    public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
  function indexAction(){
  }
  function rptLoanDisburseCoAction(){//realease by co
	  $db  = new Report_Model_DbTable_DbLandreport();
	  if($this->getRequest()->isPost()){
	  		$search = $this->getRequest()->getPost();
	  	}
	  	else{
	  		$search = array(
	  				'branch_id'=>-1,
	  				'pay_every'=>'',
	  			  	'member'=>'',
	  				'co_id'=>-1,
	  				'start_date'=> date('Y-m-d'),
	  				'end_date'=>date('Y-m-d'));
	  			
	  	}
  	$this->view->list_end_date=$search;
  	$this->view->loanrelease_list=$db->getAllLoanCo($search);
  	  	 
  	$frm = new Loan_Form_FrmSearchLoan();
  	$frm = $frm->AdvanceSearch();
  	Application_Model_Decorator::removeAllDecorator($frm);
  	$this->view->frm_search = $frm;
  	
  	$key = new Application_Model_DbTable_DbKeycode();
  	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
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
  				'status' => -1,);  		 
  	}
  	$db  = new Report_Model_DbTable_DbLandreport();
  	$this->view->date_show=$search['end_date'];
  	$this->view->search=$search;
  	$row = $dbs->getAllLnClient($search);
  	$this->view->tran_schedule=$row;
  	
//  $this->view->loanlate_list = $db->getALLLoanlate($search);
  	$key = new Application_Model_DbTable_DbKeycode();
  	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
  	 
  	$this->view->list_end_dates = $search["end_date"];
  	$frm = new Loan_Form_FrmSearchLoan();
  	$frm = $frm->AdvanceSearch();
  	Application_Model_Decorator::removeAllDecorator($frm);
  	$this->view->frm_search = $frm;  	
  }
  function rptGroupmemberAction(){
  	$db  = new Report_Model_DbTable_DbLandreport();
  	$id = $this->getRequest()->getParam("id");
  	$key = new Application_Model_DbTable_DbKeycode();
  	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
  	if (!empty($id)){
  		$this->view->loanmember_list =$db->getALLGroupDisburse($id);
  		//print_r($db->getALLGroupDisburse($id));
    }
  }
 
  function rptPaymentAction(){
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
				'start_date'=> date('Y-m-d'),
	  			'end_date'=>date('Y-m-d'),
				'payment_method'=>-1,
		);
	}
	$this->view->rssearch = $search;
	$this->view->loantotalcollect_list = $db->getALLLoanPayment($search);
	$this->view->list_end_date = $search;	
	
	$frm = new Loan_Form_FrmSearchLoan();
	$frm = $frm->AdvanceSearch();
	Application_Model_Decorator::removeAllDecorator($frm);
	$this->view->frm_search = $frm;
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
  				'adv_search'		=>	"",
  				'end_date' => date('Y-m-d'),
  				'status' => -1,
  				'branch_id'=>-1,
  				'client_name'=>0
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
  }
  function receiptOtherincomeAction(){
  	$id =$this->getRequest()->getParam('id');
  	$db  = new Report_Model_DbTable_DbParamater();
  	$this->view->rsincome =$db->getIncomeById($id);

  	
//   	$db = new Application_Model_DbTable_DbGlobal();
//   	$this->view->classified_loan = $db->ClassifiedLoan();
  	$key = new Application_Model_DbTable_DbKeycode();
  	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
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
	  	$rs= $db->getAllOutstadingLoan($search);
	  	$frm = new Loan_Form_FrmSearchLoan();
	  	$frms = $frm->AdvanceSearch();
	  	$key = new Application_Model_DbTable_DbKeycode();
	  	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
	  	Application_Model_Decorator::removeAllDecorator($frms);
	  	$this->view->frm_search = $frms;
	  	$this->view->outstandloan = $rs;
	  	
  }
  
  
  function rptUnpaidLoanByCoAction(){
  	$db  = new Report_Model_DbTable_DbLandreport();
  	
  	$key = new Application_Model_DbTable_DbKeycode();
  	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
  	if($this->getRequest()->isPost()){
  		$search = $this->getRequest()->getPost();
  		if(isset($search['btn_submit'])){
  			$this->view->LoanCollectionco_list =$db->getAllLoanByCo($search);
  		}else {
  			$collumn = array("id","branch","co_name","receipt_no","loan_number","team_group","total_principal_permonth"
  					,"total_interest","penalize_amount","amount_payment","service_charge","date_pay");
  			$this->exportFileToExcel('ln_client_receipt_money',$db->getAllLoanByCo(),$collumn);
  		}
  	}else{
  		$search = array(
  				'advance_search' => '',
  				'client_name' => "",
  				'start_date'=> date('Y-m-d'),
  				'end_date'=>date('Y-m-d'),
  				'branch_id'		=>	-1,
  				'co_id'		=> "",
  				'paymnet_type'	=> -1,
  				'status'=>"",);
  		$this->view->LoanCollectionco_list =$db->getAllLoanByCo($search);
  	}
  	$this->view->date_show=$search['end_date'];
	$this->view->start_date=$search['start_date'];
  	$frm = new Loan_Form_FrmSearchGroupPayment();
  	$fm = $frm->AdvanceSearch();
  	Application_Model_Decorator::removeAllDecorator($fm);
  	$this->view->frm_search = $fm;
  }
  function rptLoanCollectioncoAction(){
  	
  	if($this->getRequest()->isPost()){
  		$search = $this->getRequest()->getPost();
  	}else{
			$search = array(
				'adv_search' => '',
				'client_name' => -1,
				'start_date'=> date('Y-m-d'),
				'end_date'=>date('Y-m-d'),
				'branch_id'		=>	-1,
				'co_id'		=> -1,
				'paymnet_type'	=> -1,
				'status'=>"",);
			
	}
	$db  = new Report_Model_DbTable_DbLandreport();
	$key = new Application_Model_DbTable_DbKeycode();
	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
	
	$this->view->LoanCollectionco_list =$db->getALLLoanCollectionco($search);
	$this->view->date_show=$search['end_date'];
	$this->view->start_date=$search['start_date'];
	
  	$frm = new Loan_Form_FrmSearchGroupPayment();
  	$fm = $frm->AdvanceSearch();
  	Application_Model_Decorator::removeAllDecorator($fm);
  	$this->view->frm_search = $fm;
  }
function rptLoanTotalCollectAction(){
	$db  = new Report_Model_DbTable_DbLandreport();	
	$key = new Application_Model_DbTable_DbKeycode();
	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
	if($this->getRequest()->isPost()){
		$search = $this->getRequest()->getPost();
		if(isset($search['btn_search'])){
			$this->view->loantotalcollect_list=$db->getALLLoanTotalcollect($search);
		}
	}else {
	$search = array(
			'adv_search' => '',
			'status_search' => -1,
			'status' => -1,
			'branch_id' => "",
			'client_name' => "",
			'co_id' => "",
			'start_date' =>date('Y-m-d'),
			'end_date' => date('Y-m-d'),
	);
	$this->view->loantotalcollect_list =$rs=$db->getALLLoanTotalcollect($search);
	}
	$this->view->list_end_date=$search;
	$frm = new Loan_Form_FrmSearchLoan();
	$frm = $frm->AdvanceSearch();
	Application_Model_Decorator::removeAllDecorator($frm);
	$this->view->frm_search = $frm;
}
function rptRescheduleLoanAction(){
	$db  = new Report_Model_DbTable_DbLandreport();
	$key = new Application_Model_DbTable_DbKeycode();
	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
	if($this->getRequest()->isPost()){
		$search = $this->getRequest()->getPost();
	}
	else{
		$search = array(
				'branch_id'=>'',
				'client_name'=>'',
				'pay_every'=>-1,
				'start_date'=> date('Y-m-d'),
				'end_date'=>date('Y-m-d'));
	}
	$this->view->loanrelease_list=$db->getRescheduleLoan($search);
	$this->view->list_end_date=$search;
	 
	$frm = new Loan_Form_FrmSearchLoan();
	$frm = $frm->AdvanceSearch();
	Application_Model_Decorator::removeAllDecorator($frm);
	$this->view->frm_search = $frm;
}
public function paymentscheduleListAction(){
	try{
		$db = new Report_Model_DbTable_DbRptPaymentSchedule();
		if($this->getRequest()->isPost()){
			$search = $this->getRequest()->getPost();
		}
		else{
			$search = array(
					'adv_search' => '',
					'status_search' => -1,
					'client_id' => -1,
					'status' => -1,
					'from_date' =>date('Y-m-d'),
					'to_date' => date('Y-m-d'),
			);
		}
		$rs_rows = $db->getAllClientPaymentListRpt($search);
		
		$glClass = new Application_Model_GlobalClass();
		$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
		
		$collumns = array("BRANCH_NAME","LOAN_NO","CLIENT_NO","CUSTOMER_NAME","LOAN_AMOUNT","AMIN_FEE","INTEREST RATE","TERM_BORROW","METHOD","TIME_COLLECT","ZONE","CO_NAME",
				"STATUS");
		$link=array(
				'module'=>'report','controller'=>'loan','action'=>'rpt-paymentschedules',
		);
		$list = new Application_Form_Frmtable();
		$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array(
				'total_capital'=>$link,'loan_number'=>$link,'client_number'=>$link));
				
		}catch (Exception $e){
		Application_Form_FrmMessage::message("Application Error");
		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	}
	
	$frm = new Loan_Form_FrmSearchLoan();
	$frm = $frm->AdvanceSearch();
	Application_Model_Decorator::removeAllDecorator($frm);
	$this->view->frm_search = $frm;
}
public function exportFileToExcel($table,$data,$thead){
	$this->_helper->layout->disableLayout();
	$db = new Report_Model_DbTable_DbExportfile();
	$finalData = $db->getFileby($table,$data,$thead);
	$filename = APPLICATION_PATH . "/tmp/$table-" . date( "m-d-Y" ) . ".xlsx";
	$realPath = realpath( $filename );
	if ( false === $realPath ){
		touch( $filename );
		chmod( $filename, 0777 );
	}
	$filename = realpath( $filename );
	$handle = fopen( $filename, "w" );
	fputcsv( $handle, $thead, "\t" );
	$this->getResponse()->setRawHeader( "Content-Type: application/vnd.ms-excel; charset=utf-8" )
	->setRawHeader( "Content-Disposition: attachment; filename=excel.xls" )
	->setRawHeader( "Content-Transfer-Encoding: binary" )
	->setRawHeader( "Expires: 0" )
	->setRawHeader( "Cache-Control: must-revalidate, post-check=0, pre-check=0" )
	->setRawHeader( "Pragma: public" )
	->setRawHeader( "Content-Length: " . filesize( $filename ) )
	->sendResponse();
	foreach ( $finalData AS $finalRow )
	{
		fputcsv( $handle,$finalRow, "\t" );
	}
	fclose( $handle );
	$this->_helper->viewRenderer->setNoRender();
	readfile( $filename );//exit();
}

/* function rptMemberschedulesAction(){//for schedule member
 	$db = new Report_Model_DbTable_DbRptPaymentSchedule();
 	$id =$this->getRequest()->getParam('id');
 
 	$this->view->tran_schedule=$row;
 	if(empty($row)){
 		Application_Form_FrmMessage::Sucessfull("RECORD_NOT_EXIST",'/report/loan/paymentschedule-list');
 	}
 	$db = new Application_Model_DbTable_DbGlobal();
 	$rs = $db->getClientByMemberId(@$row[0]['member_id']);
 	$this->view->client =$rs;
 	$frm = new Application_Form_FrmSearchGlobal();
 	$form = $frm->FrmSearchLoadSchedule();
 	Application_Model_Decorator::removeAllDecorator($form);
 	$this->view->form_filter = $form;
 	$db= new Application_Model_DbTable_DbGlobal();
 	$day_inkhmer = $db->getDayInkhmerBystr(null);
 	$this->view->day_inkhmer = $day_inkhmer;
 
 	$key = new Application_Model_DbTable_DbKeycode();
 	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
 
 }*/
//  function rptGroupschedulesAction(){//for schedule member
//  	$db = new Report_Model_DbTable_DbRptPaymentSchedule();
//  	$id =$this->getRequest()->getParam('id');
//  	$row = $db->getPaymentScheduleGroupById($id);
//  	$this->view->tran_schedule=$row;
//  	if(empty($row)){
//  		Application_Form_FrmMessage::Sucessfull("RECORD_NOT_EXIST",'/report/loan/paymentschedule-list');
//  	}
//  	$db = new Application_Model_DbTable_DbGlobal();
//  	$rs = $db->getClientGroupByMemberId(@$row[0]['member_id']);
//  	$this->view->client =$rs;
//  	$frm = new Application_Form_FrmSearchGlobal();
//  	$form = $frm->FrmSearchLoadSchedule();
//  	Application_Model_Decorator::removeAllDecorator($form);
//  	$this->view->form_filter = $form;
//  	$db= new Application_Model_DbTable_DbGlobal();
//  	$day_inkhmer = $db->getDayInkhmerBystr(null);
//  	$this->view->day_inkhmer = $day_inkhmer;
 
//  	$key = new Application_Model_DbTable_DbKeycode();
//  	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
 
//  }
//  function rptGroupchedulesAction(){//for schedule member
//  	$db = new Report_Model_DbTable_DbRptPaymentSchedule();
//  	$id =$this->getRequest()->getParam('id');
//  	$row = $db->getPaymentSchedule($id);
//  	$this->view->tran_schedule=$row;
//  	if(empty($row)){
//  		Application_Form_FrmMessage::Sucessfull("RECORD_NOT_EXIST",'/report/loan/paymentschedule-list');
//  	}
//  	$db = new Application_Model_DbTable_DbGlobal();
//  	$rs = $db->getClientByMemberId(@$row[0]['member_id']);
//  	$this->view->client =$rs;
//  	$frm = new Application_Form_FrmSearchGlobal();
//  	$form = $frm->FrmSearchLoadSchedule();
//  	Application_Model_Decorator::removeAllDecorator($form);
//  	$this->view->form_filter = $form;
//  	$db= new Application_Model_DbTable_DbGlobal();
//  	$day_inkhmer = $db->getDayInkhmerBystr(null);
//  	$this->view->day_inkhmer = $day_inkhmer;
 
//  	$key = new Application_Model_DbTable_DbKeycode();
//  	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
//  }
 function rptLoanIncomeAction(){
 	$db  = new Report_Model_DbTable_DbLandreport();
 	 
 	$key = new Application_Model_DbTable_DbKeycode();
 	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
 	if($this->getRequest()->isPost()){
 		$search = $this->getRequest()->getPost();
 	}else{
 	$search = array(
	 	'adv_search' => '',
	 	'client_name' => -1,
	 	'start_date'=> date('Y-m-d'),
	 	'end_date'=>date('Y-m-d'),
		'co_id'		=> -1,
		'paymnet_type'	=> -1,
 		'status'=>"",);
			
 	}
 	
 	$this->view->LoanFee_list =$db->getALLLFee($search);//sale
 	$this->view->LoanCollectionco_list =$db->getALLLoanIcome($search);//collect money
 	$this->view->list_end_date=$search;
 	$frm = new Loan_Form_FrmSearchGroupPayment();
 	$fm = $frm->AdvanceSearch();
 	Application_Model_Decorator::removeAllDecorator($fm);
 	$this->view->frm_search = $fm;
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
 	$frm = new Loan_Form_FrmSearchLoan();
 	$frm = $frm->AdvanceSearch();
 	Application_Model_Decorator::removeAllDecorator($frm);
 	$this->view->frm_search = $frm;
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
 				'schedule_opt'	=> -1,
 				'status'=>-1,);
 	}
 	$this->view->search=$search;
 	$db  = new Report_Model_DbTable_DbLandreport();
 	$this->view->LoanCollectionco_list =$db->getALLLoanExpectIncome($search);
 	
 	$frm = new Loan_Form_FrmSearchLoan();
 	$frm = $frm->AdvanceSearch();
 	Application_Model_Decorator::removeAllDecorator($frm);
 	$this->view->frm_search = $frm;
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
 	
 	$key = new Application_Model_DbTable_DbKeycode();
 	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
 }
 function rptLoanTrasferAction(){//release all loan
 	$db  = new Report_Model_DbTable_DbLandreport();
 	$key = new Application_Model_DbTable_DbKeycode();
 	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
 	if($this->getRequest()->isPost()){
 		$search = $this->getRequest()->getPost();
 	}
 	else{
 		$search = array(
 				'branch_id'=>'',
 				'client_name'=>'',
 				'pay_every'=>-1,
 				'co_id'=>'',
 				'start_date'=> date('Y-m-d'),
 				'end_date'=>date('Y-m-d'));
 	}
 	$this->view->loantrasfer=$db->getAllTransferoan($search);
 	$this->view->list_end_date=$search;
 	 
 	$frm = new Loan_Form_FrmSearchLoan();
 	$frm = $frm->AdvanceSearch();
 	Application_Model_Decorator::removeAllDecorator($frm);
 	$this->view->frm_search = $frm;
 }
 function rptLoanClientcoAction()
 {
 	$db  = new Report_Model_DbTable_DbLandreport();
 	if($this->getRequest()->isPost()){
 		$search = $this->getRequest()->getPost();
 	}
 	else{
 		$search = array(
 				'branch_id'=>-1,
 				'pay_every'=>'',
 				'member'=>'',
 				'co_id'=>-1,
 				'start_date'=> date('Y-m-d'),
 				'end_date'=>date('Y-m-d'));
 	
 	}
 	$this->view->list_end_date=$search;
 	$this->view->loanrelease_list=$db->getClientLoanCo($search);
 	 
 	$frm = new Loan_Form_FrmSearchLoan();
 	$frm = $frm->AdvanceSearch();
 	Application_Model_Decorator::removeAllDecorator($frm);
 	$this->view->frm_search = $frm;
 	 
 	$key = new Application_Model_DbTable_DbKeycode();
 	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
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
 }
 function rptDepositalertAction(){
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
 				'start_date'=> date('Y-m-d'),
 				'end_date'=>date('Y-m-d'));
 	}
 	$this->view->loanrelease_list=$db->getAlertDeposit($search);
 	$this->view->list_end_date=$search;
 	$this->view->branch_id = $search['branch_id'];
 		
 	$frm = new Loan_Form_FrmSearchLoan();
 	$frm = $frm->AdvanceSearch();
 	Application_Model_Decorator::removeAllDecorator($frm);
 	$this->view->frm_search = $frm;
 		
 	$key = new Application_Model_DbTable_DbKeycode();
 	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
 }
 function rptPaymentschedulesAction(){
 	$db = new Report_Model_DbTable_DbRptPaymentSchedule();
 	$id =$this->getRequest()->getParam('id');
 	$row = $db->getPaymentSchedule($id);
 	$this->view->tran_schedule=$row;
 	if(empty($row) or $row==''){
 		Application_Form_FrmMessage::Sucessfull("RECORD_NOT_EXIST",'/report/loan/rpt-sold');
 	}
 	$db = new Application_Model_DbTable_DbGlobal();
 	$rs = $db->getClientByMemberId($id);
 
 	$this->view->client =$rs;
 	$frm = new Application_Form_FrmSearchGlobal();
 	$form = $frm->FrmSearchLoadSchedule();
 	Application_Model_Decorator::removeAllDecorator($form);
 	$this->view->form_filter = $form;
 	$day_inkhmer = $db->getDayInkhmerBystr(null);
 	$this->view->day_inkhmer = $day_inkhmer;
 	$key = new Application_Model_DbTable_DbKeycode();
 	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
 }
 function rptCombineschedulesAction(){
 	$db = new Report_Model_DbTable_DbRptPaymentSchedule();
 	$id =$this->getRequest()->getParam('id');
//  	echo $id;exit();
 	$row = $db->getScheduleCombine($id);
 	$this->view->tran_schedule=$row;
 	if(empty($row) or $row==''){
 		Application_Form_FrmMessage::Sucessfull("RECORD_NOT_EXIST",'/report/loan/rpt-sold');
 	}
 	
 	$rs = $db->getClientCombineId($id);
 
 	$this->view->client =$rs;
 	$db = new Application_Model_DbTable_DbGlobal();
 	$frm = new Application_Form_FrmSearchGlobal();
 	$form = $frm->FrmSearchLoadSchedule();
 	Application_Model_Decorator::removeAllDecorator($form);
 	$this->view->form_filter = $form;
 	$day_inkhmer = $db->getDayInkhmerBystr(null);
 	$this->view->day_inkhmer = $day_inkhmer;
 	$key = new Application_Model_DbTable_DbKeycode();
 	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
 }
//  function rptExpenseAction(){
//  	$db = new Accounting_Model_DbTable_DbExpense();
//  	if($this->getRequest()->isPost()){
//  		$formdata=$this->getRequest()->getPost();
//  	}
//  	else{
//  		$formdata = array(
//  				"adv_search"=>'',
//  				"currency_type"=>-1,
//  				"status"=>-1,
//  				'start_date'=> date('Y-m-d'),
//  				'end_date'=>date('Y-m-d'),
//  		);
//  	}
//  	$this->view->rs= $db->getAllExpenseReport($formdata);//call frome model
//  	$this->view->list_end_date=$formdata;
 	 
//  	$frm = new Loan_Form_FrmSearchLoan();
//  	$frm = $frm->AdvanceSearch();
//  	Application_Model_Decorator::removeAllDecorator($frm);
//  	$this->view->frm_search = $frm;
//  }
 function rptIncomestatementAction(){
 	$db  = new Report_Model_DbTable_DbLandreport();
 		
 	$key = new Application_Model_DbTable_DbKeycode();
 	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
 	if($this->getRequest()->isPost()){
 		$search = $this->getRequest()->getPost();
 	}else{
 		$search = array(
 				'adv_search' => '',
 				'start_date'=> date('Y-m-d'),
 				'end_date'=>date('Y-m-d'),
 				'branch_id'		=>	-1,
 				'land_id'=>-1,
 				'status'=>"",
 				"currency_type"=>-1,
 		);
 
 	}
 
 	$this->view->LoanFee_list =$db->getAllLoan($search);
 	$this->view->LoanCollectionco_list =$db->getALLLoanPayment($search);
 	
 	$db = new Accounting_Model_DbTable_DbExpense();
 	$this->view->rs = $db->getAllExpenseReport($search);
 
 	$this->view->list_end_date=$search;
 	$frm = new Loan_Form_FrmSearchGroupPayment();
 	$fm = $frm->AdvanceSearch();
 	Application_Model_Decorator::removeAllDecorator($fm);
 	$this->view->frm_search = $fm;
 }
  function receiptAction(){
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
  
//   function rptUpdatepaymentAction(){
//   	if($this->getRequest()->isPost()){
//   		$_data = $this->getRequest()->getPost();
//   		try {
//   			$_dbmodel = new Report_Model_DbTable_DbLandreport();
//   			$_dbmodel->updatePaymentStatus($_data);
//   			Application_Form_FrmMessage::Sucessfull("UPDATE_SUCESS","/report/loan/rpt-sold");
//   		}catch (Exception $e) {
//   			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
//   		}
//   	}
//   	$id =$this->getRequest()->getParam('id');
//   	$db = new Application_Model_DbTable_DbGlobal();
//   	$rs = $db->getClientByMemberId($id);
//   	$this->view->client =$rs;
//   	$db = new Report_Model_DbTable_DbRptPaymentSchedule();
//   	$row = $db->getPaymentupdateSchedule($id,$rs['payment_id']);
//   	$this->view->tran_schedule=$row;
//   	if(empty($row)){
//   		//Application_Form_FrmMessage::Sucessfull("RECORD_NOT_EXIST",'/report/loan/rpt-sold');
//   	}
// //   	$day_inkhmer = $db->getDayInkhmerBystr(null);
//   	//$this->view->day_inkhmer = $day_inkhmer;
//   	$db = new Application_Model_DbTable_DbGlobal();
//   	$key = new Application_Model_DbTable_DbKeycode();
//   	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
//   	$this->view->id = $id;
//   	$this->view->payment_option = $db->getVewOptoinTypeByType(25,null,null,1);
//   	$db = new Application_Model_DbTable_DbGlobal();
//   	$this->view->customer =  $db->getAllClient();
//   }
  function rptUpdatestatusAction(){
  	if($this->getRequest()->isPost()){
  		$_data = $this->getRequest()->getPost();
  		try {
  			$_dbmodel = new Report_Model_DbTable_DbLandreport();
  			$_dbmodel->updateScheculeStatus($_data);
  			Application_Form_FrmMessage::Sucessfull("UPDATE_SUCESS","/report/loan/rpt-sold");
  		}catch (Exception $e) {
  			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
  		}
  	}
  	$db = new Report_Model_DbTable_DbRptPaymentSchedule();
  	$id =$this->getRequest()->getParam('id');
  	$row = $db->getPaymentScheduleById($id);
  	$this->view->tran_schedule=$row;
  	if(empty($row) or $row==''){
  		Application_Form_FrmMessage::Sucessfull("RECORD_NOT_EXIST",'/report/loan/rpt-sold');
  	}
  	$db = new Application_Model_DbTable_DbGlobal();
  	$rs = $db->getClientByMemberId($id);
  
  	$this->view->client =$rs;
  	$frm = new Application_Form_FrmSearchGlobal();
  	$form = $frm->FrmSearchLoadSchedule();
  
  	$day_inkhmer = $db->getDayInkhmerBystr(null);
  	$this->view->day_inkhmer = $day_inkhmer;
  	$key = new Application_Model_DbTable_DbKeycode();
  	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
  	$this->view->id=$id;
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
  function rptReceiveplongAction(){
  	if($this->getRequest()->isPost()){
  		$search=$this->getRequest()->getPost();
  	}else{
  		$search = array(
			    'adv_search'=>'',
				'branch_id' => -1,
				'land_id'=> -1,
				'client_name'=> -1,
  				'plong_type'=>'', 				
				'from_date_search'=> date('Y-m-d'),
				'to_date_search'=>date('Y-m-d'));
  	}
  	$this->view->rssearch = $search;
  	$db  = new Loan_Model_DbTable_DdReceived();
  	$this->view->row = $db->getCustomerReceivedPlong($search);
  	
  	$fm = new Loan_Form_FrmCancel();
  	$frm = $fm->FrmAddFrmCancel();
  	Application_Model_Decorator::removeAllDecorator($frm);
  	$this->view->frm_cancel = $frm;
  }
  function bugAction(){
  		$db = new Report_Model_DbTable_Dbbug();
  		if($this->getRequest()->isPost()){
  			$formdata=$this->getRequest()->getPost();
  		}
  		else{
  			$formdata = array(
  					"adv_search"=>'',
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
  }
  function paymenthistoryAction(){
  	$db  = new Report_Model_DbTable_DbLandreport();
  	$key = new Application_Model_DbTable_DbKeycode();
  	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
  	$id = $this->getRequest()->getParam('id');
  	
  	$this->view->loantotalcollect_list =$rs=$db->getPaymentSaleid($id);
  	$frm = new Loan_Form_FrmSearchLoan();
  	$frm = $frm->AdvanceSearch();
  	Application_Model_Decorator::removeAllDecorator($frm);
  	$this->view->frm_search = $frm;
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
  	$this->view->rssearch = $search;
  	$this->view->list_end_date = $search;
  	
  	$frm = new Loan_Form_FrmSearchLoan();
	$frm = $frm->AdvanceSearch();
	Application_Model_Decorator::removeAllDecorator($frm);
	$this->view->frm_search = $frm;
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
  	$this->view->rssearch = $search;
  	$this->view->list_end_date = $search;
  	 
  	$frm = new Loan_Form_FrmSearchLoan();
  	$frm = $frm->AdvanceSearch();
  	Application_Model_Decorator::removeAllDecorator($frm);
  	$this->view->frm_search = $frm;
  }
  public function rptExpenseBycateAction(){
  	try{
  		
  		if($this->getRequest()->isPost()){
  			$search=$this->getRequest()->getPost();
  		}else{
  			$search=array(
  					'txtsearch' =>'',
  					'branch_id'	=>'',
  					'user'	=>'',
  					'start_date'=>date('Y-m-d'),
  					'end_date'=>date('Y-m-d'),
  			);
  		}
  		$this->view->search = $search;
  		$db  = new Report_Model_DbTable_DbParamater();
  		$this->view->row = $db->getAllExpensebyCate($search);
  		
  		
  		$key = new Application_Model_DbTable_DbKeycode();
  		$this->view->data=$key->getKeyCodeMiniInv(TRUE);
  		if($this->getRequest()->isPost()){
  			$search = $this->getRequest()->getPost();
  		}else{
  			$search = array(
  					'land_id'=>0,
  					'start_date'  => date('Y-m-d'),
  					'end_date'    => date('Y-m-d'),
  					'txtsearch' => '',
  					'branch_id'=>-1,
  					'co_khname'=>-1,
  					'search_status'=>-1);
  		}
  		
  		$this->view->rscomisison = $db->getAllCommission($search);
  		
  		$this->view->expense_comission = $db->getAllComissionExpense($search);
  		
  		
  		
  	}catch(Exception $e){
  		Application_Form_FrmMessage::message("APPLICATION_ERROR");
  		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
  		echo $e->getMessage();
  	}
  	$frm = new Loan_Form_FrmSearchLoan();
  	$frm = $frm->AdvanceSearch();
  	Application_Model_Decorator::removeAllDecorator($frm);
  	$this->view->frm_search = $frm;
  }
  function rptValidateagreementAction(){//release all loan
  	$db  = new Report_Model_DbTable_DbLandreport();
  	if($this->getRequest()->isPost()){
  		$search = $this->getRequest()->getPost();
  	}
  	else{
  		$search = array(
  				'adv_search'=>'',
  				'branch_id'=>-1,
  				'property_type'=>0,
  				'client_name'=>'',
  				'land_id'=>-1,
  				'co_id'=>-1,
  				'start_date'=> date('Y-m-d'),
  				'end_date'=>date('Y-m-d'));
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
// 		$_dbgb = new Application_Model_DbTable_DbGlobal();
// 		$pevconcer = $_dbgb->getViewByType(22);
// 		$this->view->prev_concern = $pevconcer;
		
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
	function rptPrintplongAction(){
		$id = $this->getRequest()->getParam('id');
		$_dbmodel = new Loan_Model_DbTable_DdReceived();
		$row  = $_dbmodel->getRecivePlongInfo($id);
		$this->view->row = $row;
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
					'client_name'=>'',
					'buy_type'=>-1,
					'land_id'=>-1,
					'streetlist'=>'',
					'start_date'=> date('Y-m-d'),
					'end_date'=>date('Y-m-d'));
		}
		$this->view->loanrelease_list=$db->getSaleSummary($search);
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
	
	function rptOtherincomedetailAction(){
		$db  = new Report_Model_DbTable_DbLandreport();
			if($this->getRequest()->isPost()){
    			$search=$this->getRequest()->getPost();
    		}
    		else{
    			$search = array(
    					"adv_search"=>'',
    					"branch_id"=>-1,
    					"category_id"=>'',
    					'start_date'=> date('Y-m-d'),
    					'end_date'=>date('Y-m-d'),
    					'client_name'=>-1,
//     					'payment_method'=>'',
    			);
    		}
		$this->view->loantotalcollect_list =$db->getAllIncomeOtherDetail($search,1);
		$this->view->search=$search;
		
		$frm = new Loan_Form_FrmSearchLoan();
    	$frm = $frm->AdvanceSearch();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_search = $frm;
	
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data=$key->getKeyCodeMiniInv(TRUE);
	}
}