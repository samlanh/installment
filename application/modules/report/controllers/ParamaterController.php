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
  
  
  function rptAgreementAction(){
  	
  	$db = new Report_Model_DbTable_DbParamater();
  	if($this->getRequest()->isPost()){
  		$data=$this->getRequest()->getPost();
	  	try {
	  		//$db->addOversoldPrice($data);
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
	  	
	  	$dbp = new Project_Model_DbTable_DbProject();
	  	$this->view->branchinfo = $dbp->getBranchById($rsagreement['branch_id']);//for to get bank info
  }
  //--by seyha --

  function rptAgreementEngAction(){
  	
	$db = new Report_Model_DbTable_DbParamater();
	if($this->getRequest()->isPost()){
		$data=$this->getRequest()->getPost();
		try {
			//$db->addOversoldPrice($data);
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
  	}
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
	
	
}