<?php
class Report_ParamaterController extends Zend_Controller_Action {
    public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
  function indexAction(){
  }
  function  rptStaffAction(){
  	$db  = new Report_Model_DbTable_DbParamater();
  	$key = new Application_Model_DbTable_DbKeycode();
  	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
  	if($this->getRequest()->isPost()){
  		$search = $this->getRequest()->getPost();
  	}else{
  		$search = array(
  				'start_date'  => date('Y-m-d'),
	 			'end_date'    => date('Y-m-d'),
  				'txtsearch' => '',
  				'branch_id'=>-1,
  				'co_khname'=>-1,
				'co_sex'=>-1,
  				'search_status'=>-1);
  	}
  	$this->view->staff_list = $db->getAllstaff($search);
  	$this->view->search = $search;
  	$frm=new Other_Form_FrmStaff();
  	$row=$frm->FrmAddStaff();
  	Application_Model_Decorator::removeAllDecorator($row);
  	$this->view->frm_staff=$row;
  	
  	$frmpopup = new Application_Form_FrmPopupGlobal();
  	$this->view->footerReport = $frmpopup->getFooterReport();
	$this->view->headerReport = $frmpopup->getLetterHeadReport();
  }

  function rptPropertiesAction(){ // by Vandy
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
  	$this->view->search = $search;
  	$db  = new Report_Model_DbTable_DbParamater();
  	$this->view->row = $db->getAllProperties($search);
  	
  	$frm=new Other_Form_FrmProperty();
  	$row=$frm->FrmFrmProperty();
  	Application_Model_Decorator::removeAllDecorator($row);
  	$this->view->frm_property=$row;
	
	$frmpopup = new Application_Form_FrmPopupGlobal();
	$this->view->footerReport = $frmpopup->getFooterReport();
	$this->view->headerReport = $frmpopup->getLetterHeadReport();
  }
  function rptCancelSaleAction(){ // by Vandy
  	if($this->getRequest()->isPost()){
  		$search=$this->getRequest()->getPost();
  	}else{
  		$search = array(
  				'adv_search'=>'',
  				'property_type'=>'',
  				'client_name'=>-1,
  				'branch_id_search' => -1,
  				'from_date_search'=> date('Y-m-d'),
  				'to_date_search'=>date('Y-m-d'));
  	}
  	$this->view->search = $search;
  	$db  = new Report_Model_DbTable_DbParamater();
  	$this->view->row = $db->getCancelSale($search);
  	 
  	$fm = new Loan_Form_FrmCancel();
  	$frm = $fm->FrmAddFrmCancel();
  	Application_Model_Decorator::removeAllDecorator($frm);
  	$this->view->frm_cancel = $frm;
  	
  	$frmpopup = new Application_Form_FrmPopupGlobal();
  	$this->view->footerReport = $frmpopup->getFooterReport();
	$this->view->headerReport = $frmpopup->getLetterHeadReport();
  
  }
  function rptIncomeAction(){ // by Vandy
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
  	$db  = new Report_Model_DbTable_DbParamater();
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
  function rptExpenseAction(){ // by Vandy
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
  	$db  = new Report_Model_DbTable_DbParamater();
  	$this->view->row = $db->getAllExpense($search);
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
  	$this->view->search=$search;
  	$db  = new Report_Model_DbTable_DbParamater();
  	$this->view->row = $db->getAllIncome($search);
  	$this->view->rowExpense = $db->getAllExpense($search);
  	$this->view->collectMoney = $db->getCollectPayment($search);
  	
  	$this->view->rscomisison = $db->getAllCommission($search);
	
  	$this->view->rowExpensePayment = $db->getAllPurchasePayment($search);
	
  	$db  = new Report_Model_DbTable_DbLandreport();
  	$this->view->houserepair =$db->getAllIncomeOtherPayment($search,12);
  	$this->view->houserepairExpense =$db->getAllIncomeOtherPayment($search,13);
	
	
  	
  	$frm = new Loan_Form_FrmSearchLoan();
  	$frm = $frm->AdvanceSearch();
  	Application_Model_Decorator::removeAllDecorator($frm);
  	$this->view->frm_search = $frm;
  	
  	$frmpopup = new Application_Form_FrmPopupGlobal();
  	$this->view->footerReport = $frmpopup->getFooterReport();
	$this->view->headerReport = $frmpopup->getLetterHeadReport();
  }
  function rptAgreementAction(){
  	
  	$db = new Report_Model_DbTable_DbParamater();
  	if($this->getRequest()->isPost()){
  		$data=$this->getRequest()->getPost();
	  	try {
	  		$db->addOversoldPrice($data);
	  	} catch (Exception $e) {
	  		Application_Form_FrmMessage::message("INSERT_FAIL");
	  	}
  	}
  		
  	$id = $this->getRequest()->getParam("id");
  	$id = empty($id)?0:$id;
  		
  		$rsagreement = $db->getAgreementBySaleID($id);
  		if (empty($rsagreement)){
  			Application_Form_FrmMessage::Sucessfull("RECORD_NOTFUND","/loan/index",2);
  			exit();
  		}
	  	$this->view->termcodiction = $db->getTermCodiction();
	  	
	  	$this->view->agreement = $rsagreement;
	  	$this->view->agreementen = $db->getIssueHouseAgreementEnglish($id);;
	  	$this->view->sale_schedule = $db->getScheduleBySaleID($id,$rsagreement['payment_id']);
	  	$this->view->first_deposit = $db->getFirstDepositAgreement($id);
	  	
		$this->view->totalpaid = $db->getTotalPrinciplePaidById($id);
		$this->view->lastpaiddate = $db->getLastDatePaidById($id);
	  	$db_keycode = new Application_Model_DbTable_DbKeycode();
	  	$this->view->keyValue = $db_keycode->getKeyCodeMiniInv();
  }
  //--by seyha --

  function rptAgreementEngAction(){
  	
	$db = new Report_Model_DbTable_DbParamater();
	if($this->getRequest()->isPost()){
		$data=$this->getRequest()->getPost();
		try {
			$db->addOversoldPrice($data);
		} catch (Exception $e) {
			Application_Form_FrmMessage::message("INSERT_FAIL");
		}
	}
		
	$id = $this->getRequest()->getParam("id");
	$id = empty($id)?0:$id;
		
		$rsagreement = $db->getAgreementBySaleID($id);
		if (empty($rsagreement)){
			Application_Form_FrmMessage::Sucessfull("RECORD_NOTFUND","/loan/index",2);
			exit();
		}
		$this->view->termcodiction = $db->getTermCodiction();
		
		$this->view->agreement = $rsagreement;
		$this->view->sale_schedule = $db->getScheduleBySaleID($id,$rsagreement['payment_id']);
		$this->view->first_deposit = $db->getFirstDepositAgreement($id);
		
	  $this->view->totalpaid = $db->getTotalPrinciplePaidById($id);
	  $this->view->lastpaiddate = $db->getLastDatePaidById($id);
		$db_keycode = new Application_Model_DbTable_DbKeycode();
		$this->view->keyValue = $db_keycode->getKeyCodeMiniInv();
}
 
//----------------------------

  function authorizationLetterAction(){
  	$db  = new Report_Model_DbTable_DbParamater();
  	$id = $this->getRequest()->getParam("id");
  	if(!empty($id)){
  		$this->view->termcodiction = $db->getTermCodiction();
  		$rsagreement = $db->getAgreementBySaleID($id);
  		$this->view->agreement = $rsagreement;
  		$this->view->sale_schedule = $db->getScheduleBySaleID($id,$rsagreement['payment_id']);
  		$this->view->first_deposit = $db->getFirstDepositAgreement($id);
  
  		$db_keycode = new Application_Model_DbTable_DbKeycode();
  		$this->view->keyValue = $db_keycode->getKeyCodeMiniInv();
  	}else{
  		///$this->_redirect("/report/paramater");
  	}
  }
  /*function rptAgreementHouseAction(){
  	$db  = new Report_Model_DbTable_DbParamater();
  	$id = $this->getRequest()->getParam("id");
  	if(!empty($id)){
  		$this->view->termcodiction = $db->getTermCodiction();
  		$rsagreement = $db->getAgreementBySaleID($id);
  		$this->view->agreement = $rsagreement;
  		$this->view->sale_schedule = $db->getScheduleBySaleID($id,$rsagreement['payment_id']);
  		$db_keycode = new Application_Model_DbTable_DbKeycode();
  		$this->view->keyValue = $db_keycode->getKeyCodeMiniInv();
  	}else{
  		$this->_redirect("/report/paramater");
  	}
  }*/
  function rptSaleHistoryAction(){
  	if($this->getRequest()->isPost()){
  		$search=$this->getRequest()->getPost();
  	}
  	else{
  		$search = array(
  				"adv_search"=>'',
  				"branch_id"=>-1,
  				"client_name"=>-1,
  				"land_id"=>-1,
  				'start_date'=> date('Y-m-d'),
  				'end_date'=>date('Y-m-d'),
  		);
  	}
  	$db  = new Report_Model_DbTable_DbParamater();
  	$this->view->row = $db->getSaleHistory($search);
  	
  	$this->view->search = $search;
  	$frm = new Loan_Form_FrmSearchLoan();
  	$frm = $frm->AdvanceSearch();
  	Application_Model_Decorator::removeAllDecorator($frm);
  	$this->view->frm_search = $frm;
  	
  	$frmpopup = new Application_Form_FrmPopupGlobal();
  	$this->view->footerReport = $frmpopup->getFooterReport();
	$this->view->headerReport = $frmpopup->getLetterHeadReport();
  }
  function  rptCommissionStaffAction(){
  	$db  = new Report_Model_DbTable_DbParamater();
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
  	$db  = new Report_Model_DbTable_DbParamater();
  
  	$this->view->expense = $db->getExpenseCategory($search,2);
  	$this->view->withdraw_capital = $db->getExpenseCategory($search,1);
  	$this->view->expense_comission = $db->getAllComissionExpense($search);
	$this->view->totalComissionPay = $db->getTotalComissionPayment($search);
	
	$this->view->totalExpensePayment = $db->totalExpensePayment($search);
  	
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
  function commissionbalanceAction(){
  	$db  = new Report_Model_DbTable_DbParamater();
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
  	$db  = new Report_Model_DbTable_DbParamater();
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
  function customerrequireAction(){
  	$db  = new Report_Model_DbTable_DbParamater();
  	
  	if($this->getRequest()->isPost()){
  		$search = $this->getRequest()->getPost();
  	}else{
  		$search = array(
  				'start_date'  => date('Y-m-d'),
  				'end_date'    => date('Y-m-d'),
  				'adv_search' => '',
  				'know_by' => -1,
  				'statusreq'=>'',
  				'user' => '',
  				);
  	}
  	$this->view->search =$search;
  	$row = $db->getCustomerRequirement($search);
  	$this->view->row = $row;
  	
  	$frm = new Application_Form_FrmAdvanceSearch();
  	$frm = $frm->AdvanceSearch();
  	Application_Model_Decorator::removeAllDecorator($frm);
  	$this->view->frm=$frm;
  	
  	$frmpopup = new Application_Form_FrmPopupGlobal();
  	$this->view->footerReport = $frmpopup->getFooterReport();
	$this->view->headerReport = $frmpopup->getLetterHeadReport();
  }
  
  function verifyagreementAction(){
  	try {
  		$dbacc = new Application_Model_DbTable_DbUsers();
  		$rs = $dbacc->getAccessUrl('report','paramater','verifyagreement');
  		if(!empty($rs)){
  			$data=$this->getRequest()->getPost();
  			$db = new Report_Model_DbTable_DbParamater();
  			if(!empty($data)){
  				$db->verifyAgreement($data);
  				Application_Form_FrmMessage::Sucessfull("VERIFIED_SUCCESS","/report/paramater/rpt-agreement/id/".$data['sale_id']);
  			}
  		}
  		Application_Form_FrmMessage::Sucessfull("You no permission to verify","/report/paramater/rpt-agreement/id/".$data['sale_id'],2);
  	}catch (Exception $e) {
  		Application_Form_FrmMessage::message("INSERT_FAIL");
  		echo $e->getMessage();
  	}
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
  
  function rptPlongstepAction(){
  	if($this->getRequest()->isPost()){
  		$search=$this->getRequest()->getPost();
  	}
  	else{
  		$search = array(
  				"adv_search"=>'',
  				"branch_id"=>-1,
  				'land_id'=>-1,
  				'user_id'=>-1,
  				'process_status'=>0,
  				'client_name'=>'',
  				'start_date'=> date('Y-m-d'),
  				'end_date'=>date('Y-m-d'),
  		);
  	}
  	$this->view->search=$search;
  	$db  = new Report_Model_DbTable_DbParamater();
  	$this->view->row = $db->getAllPlongStep($search);
  	 
  	$frm = new Loan_Form_FrmSearchLoan();
  	$frm = $frm->AdvanceSearch();
  	Application_Model_Decorator::removeAllDecorator($frm);
  	$this->view->frm_search = $frm;
  	
  	$frm = new Loan_Form_FrmPlongStep();
  	$frm_loan=$frm->FrmPlongStep();
  	Application_Model_Decorator::removeAllDecorator($frm_loan);
  	$this->view->frm_searchplog = $frm_loan;
  	
  	$this->view->rssearch = $search;
  	
  	$_dbStepOpt = new Loan_Model_DbTable_DbStepOption();
  	$allStep = $_dbStepOpt->getAllStepOptions();
  	$this->view->allStep = $allStep; 
  	
  	$frmpopup = new Application_Form_FrmPopupGlobal();
  	$this->view->footerReport = $frmpopup->getFooterReport();
  }
  function rptClientAction($table='ln_account_name'){
  	 
  	$key = new Application_Model_DbTable_DbKeycode();
  	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
  	if($this->getRequest()->isPost()){
  		$search = $this->getRequest()->getPost();
  	}else{
  		$search = array(
  				'adv_search' => '',
  				'status' => -1,
  				'province'=>0,
  				'district'=>'',
  				'commune'=>'',
  				'village'=>'',
  				'start_date'=> date('Y-m-d'),
  				'end_date'=>date('Y-m-d')
  			);
  	}
	$search['branch_id'] = empty($search['branch_id'])?0:$search['branch_id'];
  	$this->view->result=$search;
  	$this->view->search=$search;
  
  	$db  = new Report_Model_DbTable_DbLnClient();
  	$this->view->client_list =$db->getAllLnClient($search);
  	 
  	$frm = new Application_Form_FrmAdvanceSearch();
  	$frm = $frm->AdvanceSearch();
  	Application_Model_Decorator::removeAllDecorator($frm);
  	$this->view->frm_search = $frm;
  	 
  	$fm = new Group_Form_FrmClient();
  	$frm = $fm->FrmAddClient();
  	Application_Model_Decorator::removeAllDecorator($frm);
  	$this->view->frm_client = $frm;
  	$db= new Application_Model_DbTable_DbGlobal();
  	$this->view->district = $db->getAllDistricts();
  	$this->view->commune = $db->getCommune();
  	$this->view->village = $db->getVillage();
  	 
  	$frmpopup = new Application_Form_FrmPopupGlobal();
  	$this->view->footerReport = $frmpopup->getFooterReport();
	$this->view->headerReport = $frmpopup->getLetterHeadReport();
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
  	$db  = new Report_Model_DbTable_DbParamater();
  	$this->view->row = $db->getAllIncome($search);
  
//   	$db  = new Report_Model_DbTable_DbLandreport();
//   	$this->view->houserepair =$db->getAllIncomeOtherPayment($search);

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
  	$db  = new Report_Model_DbTable_DbParamater();
  	$this->view->row = $db->getAllExpense($search);
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
  	 
//   	$db  = new Report_Model_DbTable_DbLandreport();
//   	$this->view->houserepair =$db->getAllIncomeOtherPayment($search,1);
	$db  = new Report_Model_DbTable_DbLandreport();
  	$this->view->houserepair =$db->getAllIncomeOtherPayment($search,13);
	
  	 
  	$frmpopup = new Application_Form_FrmPopupGlobal();
  	$this->view->footerReport = $frmpopup->getFooterReport();
  }
  
  function submitentryincomeAction(){
  	$db  = new Report_Model_DbTable_DbLandreport();
  	$key = new Application_Model_DbTable_DbKeycode();
  	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
  	if($this->getRequest()->isPost()){
  		$data = $this->getRequest()->getPost();
  		$db = new Report_Model_DbTable_DbParamater();
  		$db->submitClosingEngryIncome($data);
  		Application_Form_FrmMessage::Sucessfull("Closing Entry Success", "/report/paramater/rpt-closingincome");
  	}
  }
  function submitentryexpenseAction(){
  	$db  = new Report_Model_DbTable_DbLandreport();
  	$key = new Application_Model_DbTable_DbKeycode();
  	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
  	if($this->getRequest()->isPost()){
  		$data = $this->getRequest()->getPost();
  		$db = new Report_Model_DbTable_DbParamater();
  		$db->submitClosingEngryExpense($data);
  		Application_Form_FrmMessage::Sucessfull("Closing Entry Success", "/report/paramater/rpt-closingexpense");
  	}
  }
  
  function rptSaleCommissionAction(){
  	$db  = new Report_Model_DbTable_DbParamater();
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
  	$db  = new Report_Model_DbTable_DbParamater();
  	$this->view->row = $db->getAllIncome($search);
  	$this->view->rowExpense = $db->getAllExpense($search);
  	 
  	$frm = new Loan_Form_FrmSearchLoan();
  	$frm = $frm->AdvanceSearch();
  	Application_Model_Decorator::removeAllDecorator($frm);
  	$this->view->frm_search = $frm;
  	 
  	$frmpopup = new Application_Form_FrmPopupGlobal();
  	$this->view->footerReport = $frmpopup->getFooterReport();
	$this->view->headerReport = $frmpopup->getLetterHeadReport();
  }
  function rptPlonglistAction(){
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
  				'streetlist'=>'',
  				'process_status'=>0,
  				'plong_type'=>'',
  				'plong_processtype'=>0
  				);
  	}
  	$this->view->list_end_date = $search;
  	$db  = new Report_Model_DbTable_DbParamater();
  	$this->view->row = $db->getAllHeadproperties($search);
  	
  	$frm=new Other_Form_FrmProperty();
  	$row=$frm->FrmFrmProperty();
  	Application_Model_Decorator::removeAllDecorator($row);
  	$this->view->frm_property=$row;
	
	$frm = new Loan_Form_FrmSearchLoan();
	$frm = $frm->AdvanceSearch();
	Application_Model_Decorator::removeAllDecorator($frm);
	$this->view->frm_search = $frm;
  }
  
  function commissionpaymentreceiptAction(){
  	$db  = new Report_Model_DbTable_DbParamater();
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
  
  function rptCustomerContactAction(){
	  $db  = new Report_Model_DbTable_DbParamater();
	  $id = $this->getRequest()->getParam('id');
	  $id = empty($id)?0:$id;
	  $row = $db->getCustomerRequirmentById($id);
	  $allContact = $db->AllHistoryContact($id);
	 
	   if (empty($allContact)){
			Application_Form_FrmMessage::Sucessfull("NO RECORD","/report/paramater/customerrequire",2);
			exit();
	  }
	  
	  $this->view->row = $row;
	  $this->view->allContact = $allContact;
	  
	 
	  
	  $frm = new Application_Form_FrmAdvanceSearch();
	  $frm = $frm->AdvanceSearch();
	  Application_Model_Decorator::removeAllDecorator($frm);
	  $this->view->frm=$frm;
		
	  $frmpopup = new Application_Form_FrmPopupGlobal();
	  $this->view->footerReport = $frmpopup->getFooterReport();
  }
  
function rptContactListAction(){
  
	if($this->getRequest()->isPost()){
		$formdata=$this->getRequest()->getPost();
		$search = $formdata;
	}else{
		$search = array(
			'adv_search' => '',
			'status' => -1,		
			'proccessSearch'=>-1,
			'know_by'=>-1,			
			'start_date'=> date('Y-m-d'),
			'end_date'=>date('Y-m-d')								
			);
	}
	$db  = new Report_Model_DbTable_DbParamater();
	$row = $db->AllHistoryContactList($search);
	$this->view->row = $row;
  
	$frm = new Application_Form_FrmAdvanceSearch();
	$frm = $frm->AdvanceSearch();
	Application_Model_Decorator::removeAllDecorator($frm);
	$this->view->frm_search = $frm;
	$this->view->search=$search;
	
	$frmpopup = new Application_Form_FrmPopupGlobal();
	$this->view->footerReport = $frmpopup->getFooterReport();
	$this->view->headerReport = $frmpopup->getLetterHeadReport();
  }
  
  function rptPropertyAction(){ 
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
  	$db  = new Report_Model_DbTable_DbParamater();
  	$this->view->row = $db->getAllProperties($search);
  	
  	$frm=new Other_Form_FrmProperty();
  	$row=$frm->FrmFrmProperty();
  	Application_Model_Decorator::removeAllDecorator($row);
  	$this->view->frm_property=$row;
  }
  function rptAgreementChangeownerAction(){
  	$db  = new Report_Model_DbTable_DbParamater();
  	$id = $this->getRequest()->getParam("id");
  	$id = empty($id)?0:$id;
  		
  		$rsagreement = $db->getAgreementByChangeOwnerSaleID($id);
  		if (empty($rsagreement)){
  			Application_Form_FrmMessage::Sucessfull("RECORD_NOTFUND","/loan/index",2);
  			exit();
  		}
	  	$this->view->agreement = $rsagreement;
	  	
	  	$db_keycode = new Application_Model_DbTable_DbKeycode();
	  	$this->view->keyValue = $db_keycode->getKeyCodeMiniInv();
  }
  
  function rptUseractivityAction(){
  	$db  = new Report_Model_DbTable_DbParamater();
  	if($this->getRequest()->isPost()){
  		$search = $this->getRequest()->getPost();
  	}else{
  		$search = array(
  				"adv_search"=>'',
  				"branch_id"=>-1,
  				'user_id'=>-1,
  				'start_date'=> date('Y-m-d'),
  				'end_date'=>date('Y-m-d'),
				"keyword"=>'',
				);
  	}
  	$this->view->search =$search;
  	$row = $db->getUserActivity($search);
  	$this->view->row = $row;
  	
  	$frm = new Loan_Form_FrmSearchLoan();
  	$frm = $frm->AdvanceSearch();
  	Application_Model_Decorator::removeAllDecorator($frm);
  	$this->view->frm_search = $frm;
  	
  	$frmpopup = new Application_Form_FrmPopupGlobal();
  	$this->view->footerReport = $frmpopup->getFooterReport();
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
			$db = new Report_Model_DbTable_DbParamater();
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
	
	public function rptExpenseDetailAction(){
		try{
			$id=$this->getRequest()->getParam('id');
			
			$db = new Report_Model_DbTable_DbParamater();
			$row = $db->getexpensebyid($id);
			if (empty($row)){
				Application_Form_FrmMessage::Sucessfull("No Record","/report/paramater/rpt-expense",2);
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
			
			$db = new Report_Model_DbTable_DbParamater();
			$row = $db->getPurchasePaymentById($id);
			if (empty($row)){
				Application_Form_FrmMessage::Sucessfull("No Record","/report/paramater/rpt-expense-payment",2);
				exit();
			}
			$this->view->row = $row;
			$this->view->rowDetail = $db->getPurchasePaymentDetail($id);;
		}catch(Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	
	function rptRefundLetterAction(){
  	
		$db = new Report_Model_DbTable_DbParamater();
			
		$cancelId = $this->getRequest()->getParam("id");
		$cancelId = empty($cancelId)?0:$cancelId;
  		
  		$rsagreement = $db->getRefundLetterByID($cancelId);
  		if (empty($rsagreement)){
  			Application_Form_FrmMessage::Sucessfull("RECORD_NOTFUND","/loan/index",2);
  			exit();
  		}
		
		$rsagreement['amountReturnBack'] = empty($rsagreement['amountReturnBack'])?0:$rsagreement['amountReturnBack'];
		if ($rsagreement['amountReturnBack']<=0){
  			Application_Form_FrmMessage::Sucessfull("NO_REFUND_LETTER_FOR_THIS_RECORD","/loan/cancel",2);
  			exit();
  		}
		
		
		
	  	$this->view->termcodiction = $db->getTermCodiction();
	  	
		$id = empty($rsagreement['id'])?0:$rsagreement['id'];
	  	$this->view->agreement = $rsagreement;
	  	$this->view->sale_schedule = $db->getScheduleBySaleID($id,$rsagreement['payment_id']);
	  	$this->view->first_deposit = $db->getFirstDepositAgreement($id);
	  	
		$this->view->totalpaid = $db->getTotalPrinciplePaidById($id);
		$this->view->lastpaiddate = $db->getLastDatePaidById($id);
	  	$db_keycode = new Application_Model_DbTable_DbKeycode();
	  	$this->view->keyValue = $db_keycode->getKeyCodeMiniInv();
	}
	
	function rptSaleprofileAction()
	{
		$db  = new Report_Model_DbTable_DbParamater();
		$id = $this->getRequest()->getParam("id");
		$id = empty($id)?0:$id;
  		
  		$rsagreement = $db->getAgreementBySaleID($id);
  		if (empty($rsagreement)){
  			Application_Form_FrmMessage::Sucessfull("RECORD_NOTFUND","/loan/index",2);
  			exit();
  		}
	  	
	  	$this->view->agreement = $rsagreement;
	  	$this->view->lastSchedule =  $db->getLastRecordPaymentSchdule($id);
	  	
	  	$db_keycode = new Application_Model_DbTable_DbKeycode();
	  	$this->view->keyValue = $db_keycode->getKeyCodeMiniInv();
		
		$frmpopup = new Application_Form_FrmPopupGlobal();
  		$this->view->footerReport = $frmpopup->getFooterReport();
	}
	
	
	function updatenoteLayoutpropertyAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Report_Model_DbTable_DbParamater();
			$row = $db->updateNotePropertyLayoutNote($data);
			print_r(Zend_Json::encode($row));
			exit();
		}
	}
}