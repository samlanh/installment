<?php
class Report_IncomeexpenseController extends Zend_Controller_Action {
    public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
  function indexAction(){
  	
  }
  function rptClosingexpenseAction(){ //
  	if($this->getRequest()->isPost()){
  		$search=$this->getRequest()->getPost();
  	}else{
  		$search = array(
  				"adv_search"=>'',
  				"supplier_id"=>"",
  				"branch_id"=>-1,
  				"ordering"=>1,
  				"category_id_expense"=>-1,
  				'payment_type'=>-1,
  				'start_date'=> date('Y-m-d'),
  				'end_date'=>date('Y-m-d'),
  		);
  	}
  	$this->view->search=$search;
  	$db  = new Report_Model_DbTable_DbIncomeexpense();
  	$this->view->row = $db->getAllExpenseReport($search);
  	$this->view->rowCommissionPayment = $db->getAllCommissionPayment($search);
  	$this->view->rowExpensePayment = $db->getAllPurchasePayment($search);
  
  	$frm = new Loan_Form_FrmSearchLoan();
  	$frm = $frm->AdvanceSearch();
  	Application_Model_Decorator::removeAllDecorator($frm);
  	$this->view->frm_search = $frm;
  
  	$key = new Application_Model_DbTable_DbKeycode();
  	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
  	if($this->getRequest()->isPost()){
  		$search = $this->getRequest()->getPost();
  		$search['client_name'] = empty($search['client_name'])?0:$search['client_name'];
  		$search['land_id'] = empty($search['land_id'])?0:$search['land_id'];
  	}else{
  		$search = array(
  				'client_name'=>0,
  				'land_id'=>0,
  				'start_date'  => date('Y-m-d'),
  				'end_date'    => date('Y-m-d'),
  				'txtsearch' => '',
  				'branch_id'=>-1,
  				'co_khname'=>-1,
  				'search_status'=>-1);
  	}
  
  	$this->view->rscomisison = $db->getAllCommission($search);
  
  	$db  = new Report_Model_DbTable_DbLandreport();
  	$this->view->houserepair =$db->getAllIncomeOtherPayment($search,13);
  
  
  	$frmpopup = new Application_Form_FrmPopupGlobal();
  	$this->view->footerReport = $frmpopup->getFooterReport();
  }
  function rptClosingincomeAction(){ //
  	if($this->getRequest()->isPost()){
  		$search=$this->getRequest()->getPost();
  	}
  	else{
  		$search = array(
  				"adv_search"=>'',
  				"branch_id"=>-1,
  				"status"=>-1,
  				"category_id"=>-1,
  				"ordering"=>1,
  				'land_id'=>-1,
  				'user_id'=>-1,
  				'client_name'=>'',
  				'start_date'=> date('Y-m-d'),
  				'end_date'=>date('Y-m-d'),
  		);
  	}
  	$this->view->search=$search;
  	$db  = new Report_Model_DbTable_DbIncomeexpense();
  	$this->view->row = $db->getAllIncome($search);
  
  	$db  = new Report_Model_DbTable_DbLandreport();
  	$this->view->houserepair =$db->getAllIncomeOtherPayment($search,12);
  
  	$frm = new Loan_Form_FrmSearchLoan();
  	$frm = $frm->AdvanceSearch();
  	Application_Model_Decorator::removeAllDecorator($frm);
  	$this->view->frm_search = $frm;
  	$this->view->rssearch = $search;
  
  	$frmpopup = new Application_Form_FrmPopupGlobal();
  	$this->view->footerReport = $frmpopup->getFooterReport();
  }
  function rptDailyCashAction(){
  	if($this->getRequest()->isPost()){
  		$search=$this->getRequest()->getPost();
  	}
  	else{
  		$search = array(
  				"adv_search"=>'',
  				"branch_id"=>-1,
  				"status"=>-1,
  				'land_id'=>-1,
  				"ordering"=>1,
  				'client_name' => -1,
  				'streetlist'=>'',
  				'start_date'=> date('Y-m-d'),
  				'end_date'=>date('Y-m-d'),
  				'payment_type'=>-1,
  				'payment_method'=>-1,
  				'user_id'=>-1,
  				"cheque_issuer_search"=>"",
  		);
  	}
  	$search["co_khname"]="";
  	$this->view->search=$search;
  	$db  = new Report_Model_DbTable_DbIncomeexpense();
  	$this->view->row = $db->getAllIncome($search);
  	$this->view->rowExpense = $db->getAllExpenseReport($search);
  	$this->view->collectMoney = $db->getCollectPayment($search);
  	 
  	$this->view->rscomisison = $db->getAllCommission($search);
  	$this->view->rsCommissinPaymentDetail = $db->getCommissionPaymentDetailList($search);
  
  
  	$this->view->rowExpensePayment = $db->getAllPurchasePayment($search);
  
  	$db  = new Report_Model_DbTable_DbLandreport();
  	$this->view->houserepair =$db->getAllIncomeOtherPayment($search,12);
  	$this->view->houserepairExpense =$db->getAllIncomeOtherPayment($search,13);
  	$this->view->bankList = $db->getPayemtTotalByBankList();
  
  	$frm = new Loan_Form_FrmSearchLoan();
  	$frm = $frm->AdvanceSearch();
  	Application_Model_Decorator::removeAllDecorator($frm);
  	$this->view->frm_search = $frm;
  	 
  	$frmpopup = new Application_Form_FrmPopupGlobal();
  	$this->view->footerReport = $frmpopup->getFooterReport();
  	$this->view->headerReport = $frmpopup->getLetterHeadReport();
  }
  function rptExpenseBymonthAction(){ // by Vandy
  	if($this->getRequest()->isPost()){
  		$search=$this->getRequest()->getPost();
  	}else{
  		$search = array(
  				"adv_search"=>'',
  				"supplier_id"=>"",
  				"branch_id"=>-1,
  				"ordering"=>1,
  				"category_id_expense"=>-1,
  				'payment_type'=>-1,
  				'start_date'=> date('Y-m-d'),
  				'end_date'=>date('Y-m-d'),
  				'monthlytype'=>1,
  		);
  	}
  	$this->view->search=$search;
  	$db  = new Report_Model_DbTable_DbParamater();
  	$this->view->project = $db->getProject($search);
  	$frm = new Loan_Form_FrmSearchLoan();
  	$frm = $frm->AdvanceSearch();
  	Application_Model_Decorator::removeAllDecorator($frm);
  	$this->view->frm_search = $frm;
  }
  public function rptExpenseDetailAction(){
  	try{
  		$id=$this->getRequest()->getParam('id');
  			
  		$db = new Report_Model_DbTable_DbIncomeexpense();
  		$row = $db->getexpensebyid($id);
  		if (empty($row)){
  			Application_Form_FrmMessage::Sucessfull("No Record","/report/incomeexpense/rpt-expense",2);
  			exit();
  		}
  		$this->view->row = $row;
  		$this->view->row_pur_detai = $db->getExpenseDetail($id);
  			
  			
  	}catch(Exception $e){
  		Application_Form_FrmMessage::message("Application Error");
  		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
  	}
  }
  public function rptExpenseReceiptAction(){
  	try{
  		$id=$this->getRequest()->getParam('id');
  			
  		$db = new Report_Model_DbTable_DbIncomeexpense();
  		$row = $db->getPurchasePaymentById($id);
  		if (empty($row)){
  			Application_Form_FrmMessage::Sucessfull("No Record","/report/incomeexpense/rpt-expense-payment",2);
  			exit();
  		}
  		$this->view->row = $row;
  		$this->view->rowDetail = $db->getPurchasePaymentDetail($id);;
  	}catch(Exception $e){
  		Application_Form_FrmMessage::message("Application Error");
  		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
  	}
  }
  
  public function rptExpensePaymentAction(){
  	try{
  		if($this->getRequest()->isPost()){
  			$search = $this->getRequest()->getPost();
  		}
  		else{
  			$search=array(
  					'branch_id' => '',
  					'adv_search' => '',
  					'supplier_search'=>'',
  					'paid_by_search'=>'',
  					'start_date'=> date('Y-m-d'),
  					'end_date'=>date('Y-m-d'),
  			);
  		}
  		$this->view->search = $search;
  		$db = new Report_Model_DbTable_DbIncomeexpense();
  		$this->view->row = $db->getAllPurchasePayment($search);
  
  	}catch(Exception $e){
  		Application_Form_FrmMessage::message("Application Error");
  		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
  	}
  	$frm = new Incexp_Form_FrmExpensePayment();
  	$frm->FrmAddPurchasePayment(null);
  	Application_Model_Decorator::removeAllDecorator($frm);
  	$this->view->frm_payment = $frm;
  
  	$frm = new Loan_Form_FrmSearchLoan();
  	$frm = $frm->AdvanceSearch();
  	Application_Model_Decorator::removeAllDecorator($frm);
  	$this->view->frm_search = $frm;
  
  	$frmpopup = new Application_Form_FrmPopupGlobal();
  	$this->view->footerReport = $frmpopup->getFooterReport();
  	$this->view->headerReport = $frmpopup->getLetterHeadReport();
  
  }
  function rptExpenseAction(){
  	if($this->getRequest()->isPost()){
  		$search=$this->getRequest()->getPost();
  	}else{
  		$search = array(
  				"adv_search"=>'',
  				"supplier_id"=>"",
  				"branch_id"=>-1,
  				"ordering"=>1,
  				"category_id_expense"=>-1,
  				'payment_type'=>-1,
  				'start_date'=> date('Y-m-d'),
  				'end_date'=>date('Y-m-d'),
  				"cheque_issuer_search"=>"",
  		);
  	}
  	$this->view->search=$search;
  	$db  = new Report_Model_DbTable_DbIncomeexpense();
  	$this->view->row = $db->getAllExpenseReport($search);
  	$this->view->rowCommissionPayment = $db->getAllCommissionPayment($search);
  
  	$frm = new Loan_Form_FrmSearchLoan();
  	$frm = $frm->AdvanceSearch();
  	Application_Model_Decorator::removeAllDecorator($frm);
  	$this->view->frm_search = $frm;
  	 
  	$key = new Application_Model_DbTable_DbKeycode();
  	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
  	if($this->getRequest()->isPost()){
  		$search = $this->getRequest()->getPost();
  		$search['client_name'] = empty($search['client_name'])?0:$search['client_name'];
  		$search['land_id'] = empty($search['land_id'])?0:$search['land_id'];
  	}else{
  		$search = array(
  				'client_name'=>0,
  				'land_id'=>0,
  				'start_date'  => date('Y-m-d'),
  				'end_date'    => date('Y-m-d'),
  				'txtsearch' => '',
  				'branch_id'=>-1,
  				'co_khname'=>-1,
  				'search_status'=>-1,
  				"category_id_expense"=>-1,);
  	}
  
  	$this->view->rscomisison = $db->getAllCommission($search);
  	 
  	$db  = new Report_Model_DbTable_DbLandreport();
  	$this->view->houserepair =$db->getAllIncomeOtherPayment($search,13);
  	 
  	$frmpopup = new Application_Form_FrmPopupGlobal();
  	$this->view->footerReport = $frmpopup->getFooterReport();
  	$this->view->headerReport = $frmpopup->getLetterHeadReport();
  }
  
  function rptIncexpOtherAction(){
  	if($this->getRequest()->isPost()){
  		$search=$this->getRequest()->getPost();
  	}
  	else{
  		$search = array(
  				"adv_search"=>'',
  				"branch_id"=>-1,
  				"status"=>-1,
  				'land_id'=>-1,
  				"ordering"=>1,
  				'client_name' => -1,
  				'streetlist'=>'',
  				'start_date'=> date('Y-m-d'),
  				'end_date'=>date('Y-m-d'),
  				'payment_type'=>-1,
  				'payment_method'=>-1,
  				'user_id'=>-1,
  				"cheque_issuer_search"=>"",
  		);
  	}
  	$this->view->search=$search;
  	$db  = new Report_Model_DbTable_DbIncomeexpense();
  	$this->view->row = $db->getAllIncome($search);
  	$this->view->rowExpense = $db->getAllExpenseReport($search);
  
  	$frm = new Loan_Form_FrmSearchLoan();
  	$frm = $frm->AdvanceSearch();
  	Application_Model_Decorator::removeAllDecorator($frm);
  	$this->view->frm_search = $frm;
  
  	$frmpopup = new Application_Form_FrmPopupGlobal();
  	$this->view->footerReport = $frmpopup->getFooterReport();
  	$this->view->headerReport = $frmpopup->getLetterHeadReport();
  }
  function rptIncomeAction(){
  	if($this->getRequest()->isPost()){
  		$search=$this->getRequest()->getPost();
  	}
  	else{
  		$search = array(
  				"adv_search"=>'',
  				"branch_id"=>-1,
  				"status"=>-1,
  				"category_id"=>-1,
  				"ordering"=>1,
  				'land_id'=>-1,
  				'user_id'=>-1,
  				'client_name'=>'',
  				'start_date'=> date('Y-m-d'),
  				'end_date'=>date('Y-m-d'),
  		);
  	}
  	$this->view->search=$search;
  	$db  = new Report_Model_DbTable_DbIncomeexpense();
  	$this->view->row = $db->getAllIncome($search);
  
  	$db  = new Report_Model_DbTable_DbLandreport();
  	$this->view->houserepair =$db->getAllIncomeOtherPayment($search,12);
  	 
  	$frm = new Loan_Form_FrmSearchLoan();
  	$frm = $frm->AdvanceSearch();
  	Application_Model_Decorator::removeAllDecorator($frm);
  	$this->view->frm_search = $frm;
  	$this->view->rssearch = $search;
  	 
  	$frmpopup = new Application_Form_FrmPopupGlobal();
  	$this->view->footerReport = $frmpopup->getFooterReport();
  	$this->view->headerReport = $frmpopup->getLetterHeadReport();
  }
  function rptRevenueExpenseAction(){
  	if($this->getRequest()->isPost()){
  		$search=$this->getRequest()->getPost();
  	}
  	else{
  		$search = array(
  				'branch_id'=>0,
  				'start_date'=> date('Y-m-d'),
  				'end_date'=>date('Y-m-d'),
  				'property_type'=>'',
  				'streetlist'=>'',
  
  		);
  	}
  	$this->view->list_end_date=$search;
  	$this->view->search=$search;
  	$db  = new Report_Model_DbTable_DbIncomeexpense();
  
  	$this->view->expense = $db->getExpenseCategory($search,2);
  	$this->view->withdraw_capital = $db->getExpenseCategory($search,1);
  	$this->view->expense_comission = $db->getAllComissionExpense($search);
  	$this->view->totalComissionPay = $db->getTotalComissionPayment($search);
  	 
  	$this->view->saleicome = $db->geIncomeFromSale($search,null);
  	 
  	$this->view->money_deposit = $db->geIncomeFromSale($search,1);
  	$this->view->money_schedule = $db->geIncomeFromSale($search,0);
  	$this->view->money_install = $db->geIncomeFromSale($search,3);
  	 
  	$this->view->moneyCredit = $db->getSaleAmountCreditPayment($search);
  	 
  	$this->view->income = $db->getIncomeCategory($search);
  	//   	$this->view->income_changehouse = $db->getIncomeChangehouse($search);
  	$this->view->income_changehouse = $db->getIncomeRepairhouse($search,12);
  	$this->view->expense_changehouse = $db->getIncomeRepairhouse($search,13);
  	 
  	$db = new Application_Model_DbTable_DbGlobal();
  	$street = $db->getAllStreetForOpt();
  	$this->view->street = $street;
  	 
  	$frm = new Loan_Form_FrmSearchLoan();
  	$frm = $frm->AdvanceSearch();
  	Application_Model_Decorator::removeAllDecorator($frm);
  	$this->view->frm_search = $frm;
  	 
  	$frmpopup = new Application_Form_FrmPopupGlobal();
  	$this->view->footerReport = $frmpopup->getFooterReport();
  	$this->view->headerReport = $frmpopup->getLetterHeadReport();
  }
  function submitentryincomeAction(){
  	$db  = new Report_Model_DbTable_DbLandreport();
  	$key = new Application_Model_DbTable_DbKeycode();
  	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
  	if($this->getRequest()->isPost()){
  		$data = $this->getRequest()->getPost();
  		$db = new Report_Model_DbTable_DbIncomeexpense();
  		$db->submitClosingEngryIncome($data);
  		Application_Form_FrmMessage::Sucessfull("Closing Entry Success", "/report/incomeexpense/rpt-closingincome",1);
  	}
  }
  function submitentryexpenseAction(){
  	$key = new Application_Model_DbTable_DbKeycode();
  	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
  	if($this->getRequest()->isPost()){
  		$data = $this->getRequest()->getPost();
  		$db = new Report_Model_DbTable_DbIncomeexpense();
  		$db->submitClosingEngryExpense($data);
  		Application_Form_FrmMessage::Sucessfull("Closing Entry Success", "/report/incomeexpense/rpt-closingexpense");
  	}
  }
  function expenseDetailAction(){
  	$id =$this->getRequest()->getParam('id');
  	$id = empty($id)?0:$id;
  
  	$db  = new Report_Model_DbTable_DbIncomeexpense();
  	$row =$db->getExpensebyid($id);
  
  	$this->view->rs = $row;
  
  	if(empty($row)){
  		Application_Form_FrmMessage::Sucessfull("RECORD_NOTFUND",'/report/incomeexpense/rpt-expense',2);
  		exit();
  	}
  	$search = array(
  			'id'=>$id
  	);
  	$this->view->document=$db->getExpenseDocumentbyid($search);
  
  	$key = new Application_Model_DbTable_DbKeycode();
  	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
  		
  	$frmpopup = new Application_Form_FrmPopupGlobal();
  	$this->view->footer = $frmpopup->getFooterReceipt();
  	$this->view->officailreceipt = $frmpopup->templateExpenseReceipt();
  }
  function receiptExpenseAction(){
  	$id =$this->getRequest()->getParam('id');
  	$id = empty($id)?0:$id;
  
  	$db  = new Report_Model_DbTable_DbIncomeexpense();
  	$row =$db->getExpensebyid($id);
  
  	$this->view->rs = $row;
  	if(empty($row)){
  		Application_Form_FrmMessage::Sucessfull("RECORD_NOTFUND",'/report/incomeexpense/rpt-expense',2);
  		exit();
  	}
  	$key = new Application_Model_DbTable_DbKeycode();
  	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
  		
  	$frmpopup = new Application_Form_FrmPopupGlobal();
  	$this->view->footer = $frmpopup->getFooterReceipt();
  	$this->view->officailreceipt = $frmpopup->templateExpenseReceipt();
  }
  public function rptExpenseBycateAction(){
  	try{
  		if($this->getRequest()->isPost()){
  			$search=$this->getRequest()->getPost();
  		}else{
  			$search=array(
  					'txtsearch' =>'',
  					'branch_id'	=>0,
  					'user'	=>'',
  					'start_date'=>date('Y-m-d'),
  					'end_date'=>date('Y-m-d'),
  					'cheque_issuer_search'=>""
  			);
  		}
  		$this->view->search = $search;
  		$db  = new Report_Model_DbTable_DbIncomeexpense();
  		$this->view->row = $db->getAllExpensebyCate($search);
  
  		$this->view->expense_changehouse = $db->getIncomeRepairhouse($search,13);
  
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
  					'search_status'=>-1,
  			);
  		}
  
  		//   		$this->view->rscomisison = $db->getAllCommission($search);
  		$this->view->expense_comission = $db->getAllComissionExpense($search);
  
  		$frmpopup = new Application_Form_FrmPopupGlobal();
  		$this->view->footerReport = $frmpopup->getFooterReport();
  		$this->view->headerReport = $frmpopup->getLetterHeadReport();
  
  	}catch(Exception $e){
  		Application_Form_FrmMessage::message("APPLICATION_ERROR");
  		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
  	}
  	$frm = new Loan_Form_FrmSearchLoan();
  	$frm = $frm->AdvanceSearch();
  	Application_Model_Decorator::removeAllDecorator($frm);
  	$this->view->frm_search = $frm;
  }
  
  function receiptOtherincomeAction(){
  	$id =$this->getRequest()->getParam('id');
  	$id = empty($id)?0:$id;
  	$repair =$this->getRequest()->getParam('repair');
  	$db  = new Report_Model_DbTable_DbIncomeexpense();
  	 
  	if (!empty($repair)){
  		$row =$db->getOtherIncomePaymentById($id);
  	}else{
  		$row =$db->getIncomeById($id);
  	}
  	$this->view->rs = $row;
  	if(empty($row)){
  		Application_Form_FrmMessage::Sucessfull("RECORD_NOTFUND",'/report/incomeexpense/rpt-income',2);
  		exit();
  	}
  	$key = new Application_Model_DbTable_DbKeycode();
  	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
  	 
  	$frmpopup = new Application_Form_FrmPopupGlobal();
  	$this->view->footer = $frmpopup->getFooterReceipt();
  	$this->view->officailreceipt = $frmpopup->templateIncomeReceipt();
  }
  function credithistoryAction(){
  	$db  = new Report_Model_DbTable_DbLandreport();
  	$id = $this->getRequest()->getParam('id');
  	$id = empty($id)?0:$id;
  	$rs=$db->getCreditBySaleid($id);
  	$this->view->loantotalcollect_list =$rs;
  	if(empty($rs)){
  		Application_Form_FrmMessage::Sucessfull("RECORD_NOTFUND","/loan/index",2);
  		exit();
  	}
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
  				'payment_process'=>-1,
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
  function rptSaleCommissionAction(){
  	$db  = new Report_Model_DbTable_DbIncomeexpense();
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
  				'commission_type'=>'',
  				'search_status'=>-1);
  	}
  	$this->view->search =$search;
  	$row = $db->getSaleCommission($search);
  	$this->view->row = $row;
  
  	$frm=new Other_Form_FrmStaff();
  	$row=$frm->FrmAddStaff();
  	Application_Model_Decorator::removeAllDecorator($row);
  	$this->view->frm_staff=$row;
  
  	$frmpopup = new Application_Form_FrmPopupGlobal();
  	$this->view->footerReport = $frmpopup->getFooterReport();
  }
  
  
  function commissionpaymentreceiptAction(){
  	$db  = new Report_Model_DbTable_DbIncomeexpense();
  	$id = $this->getRequest()->getParam('id');
  	$id = empty($id)?0:$id;
  	$row = $db->getCommissionPaymentById($id);
  	if (empty($row)){
  		Application_Form_FrmMessage::Sucessfull("NO RECORD","/incexp/comissionpayment",2);
  		exit();
  	}
  	$this->view->row = $row;
  	$this->view->rs =  $db->getCommissionPaymentDetail($id);;
  	 
  	$key = new Application_Model_DbTable_DbKeycode();
  	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
  	 
  	$frmpopup = new Application_Form_FrmPopupGlobal();
  	$this->view->footer = $frmpopup->getFooterReceipt();
  }
  function  rptCommissionStaffAction(){
  	$db  = new Report_Model_DbTable_DbIncomeexpense();
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
  	$this->view->search =$search;
  	$this->view->staff_list = $db->getALLCommissionStaff($search);
  	 
  	$frm=new Other_Form_FrmStaff();
  	$row=$frm->FrmAddStaff();
  	Application_Model_Decorator::removeAllDecorator($row);
  	$this->view->frm_staff=$row;
  	$this->view->rscomisison = $db->getAllCommission($search);
  	$this->view->rsCommissinPaymentDetail = $db->getCommissionPaymentDetailList($search);
  	 
  	$frmpopup = new Application_Form_FrmPopupGlobal();
  	$this->view->footerReport = $frmpopup->getFooterReport();
  	$this->view->headerReport = $frmpopup->getLetterHeadReport();
  }
  
  function commissionbalanceAction(){
  	$db  = new Report_Model_DbTable_DbIncomeexpense();
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
  				'commission_type'=>'',
  				'search_status'=>-1);
  	}
  	$this->view->search=$search;
  	$row = $db->getCommissionBalance($search);
  	$this->view->row = $row;
  	 
  	$frm=new Other_Form_FrmStaff();
  	$row=$frm->FrmAddStaff();
  	Application_Model_Decorator::removeAllDecorator($row);
  	$this->view->frm_staff=$row;
  	 
  	$frmpopup = new Application_Form_FrmPopupGlobal();
  	$this->view->footerReport = $frmpopup->getFooterReport();
  	$this->view->headerReport = $frmpopup->getLetterHeadReport();
  }
  function commissionreceiptAction(){
  	$db  = new Report_Model_DbTable_DbIncomeexpense();
  	$id = $this->getRequest()->getParam('id');
  	$id = empty($id)?0:$id;
  	$row = $db->getComissionById($id);
  	if (empty($row)){
  		Application_Form_FrmMessage::Sucessfull("NO RECORD","/incexp/comission",2);
  		exit();
  	}
  	$this->view->row = $row;
  	 
  	$key = new Application_Model_DbTable_DbKeycode();
  	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
  	 
  	$frmpopup = new Application_Form_FrmPopupGlobal();
  	$this->view->footer = $frmpopup->getFooterReceipt();
  }
  function rptPaymentchangehouseAction(){
  	if($this->getRequest()->isPost()){
  		$search=$this->getRequest()->getPost();
  	}
  	else{
  		$search = array(
  				"adv_search"=>'',
  				"branch_id"=>-1,
  				'land_id'=>-1,
  				'client_name' => -1,
  				'start_date'=> date('Y-m-d'),
  				'end_date'=>date('Y-m-d'),
  				'payment_type'=>-1,
  				'payment_method'=>-1,
  				'user_id'=>-1,
  				'type'=>"",
  				'category_id'=>"",
  		);
  	}
  	$this->view->search=$search;
  	 
  	$db  = new Report_Model_DbTable_DbLandreport();
  	$this->view->houserepair =$db->getAllIncomeOtherPayment($search,12);
  	$this->view->houserepairExpense =$db->getAllIncomeOtherPayment($search,13);
  
  	$frm = new Loan_Form_FrmSearchLoan();
  	$frm = $frm->AdvanceSearch();
  	Application_Model_Decorator::removeAllDecorator($frm);
  	$this->view->frm_search = $frm;
  
  	$frm = new Incexp_Form_FrmSearchLoanType();
  	$frm = $frm->AdvanceSearch();
  	Application_Model_Decorator::removeAllDecorator($frm);
  	$this->view->frm_searchloantype = $frm;
  	 
  	$frmpopup = new Application_Form_FrmPopupGlobal();
  	$this->view->footerReport = $frmpopup->getFooterReport();
  }
  
  
  function rptBoreyIncomeLateAction(){
  	if($this->getRequest()->isPost()){
  		$search=$this->getRequest()->getPost();
  	}
  	else{
		
		$dbgb = new Setting_Model_DbTable_DbGeneral();
    	$alert = $dbgb->geLabelByKeyName('payment_day_alert');
    	$todayDate= date('Y-m-d');
    	if (!empty($alert['keyValue'])){
    		$amt_day = $alert['keyValue'];
    		$todayDate= date('Y-m-d',strtotime("+$amt_day day"));
    	}
  		$search = array(
  				"adv_search"=>'',
  				"branch_id"=>-1,
  				"status"=>-1,
  				"category_id"=>-1,
  				"ordering"=>1,
  				'land_id'=>-1,
  				'user_id'=>-1,
  				'client_name'=>'',
  				'end_date'=>$todayDate,
  		);
  	}
  	$this->view->search=$search;
  	$db  = new Report_Model_DbTable_DbIncomeexpense();
  	$this->view->row = $db->getCustomerNearlyPaymentBoreyFee($search);
  
  	$db  = new Report_Model_DbTable_DbLandreport();
  	$this->view->houserepair =$db->getAllIncomeOtherPayment($search,12);
  	 
  	$frm = new Loan_Form_FrmSearchLoan();
  	$frm = $frm->AdvanceSearch();
  	Application_Model_Decorator::removeAllDecorator($frm);
  	$this->view->frm_search = $frm;
  	$this->view->rssearch = $search;
  	 
  	$frmpopup = new Application_Form_FrmPopupGlobal();
  	$this->view->footerReport = $frmpopup->getFooterReport();
  	$this->view->headerReport = $frmpopup->getLetterHeadReport();
  }
}