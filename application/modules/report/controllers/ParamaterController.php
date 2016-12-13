<?php
class Report_ParamaterController extends Zend_Controller_Action {
    public function init()
    {    	
     /* Initialize action controller here */
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
  				'search_status'=>-1);
  	}
  	$this->view->staff_list = $db->getAllstaff($search);
  	$frm=new Other_Form_FrmStaff();
  	$row=$frm->FrmAddStaff();
  	Application_Model_Decorator::removeAllDecorator($row);
  	$this->view->frm_staff=$row;
  }
  function  rptVillageAction(){
  	$db  = new Report_Model_DbTable_DbParamater();
  
  	$key = new Application_Model_DbTable_DbKeycode();
  	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
  	if($this->getRequest()->isPost()){
  		$search = $this->getRequest()->getPost();
  	}else {
  		$search = array('adv_search' => '',
  				'search_status' => -1,
  				'province_name'=>0,
  				'district_name'=>'',
  				'commune_name'=>'');
  	}
  	$this->view->village_list = $db->getAllVillage($search);
  	$frm = new Other_Form_FrmVillage();
  	$frms = $frm->FrmAddVillage();
  	Application_Model_Decorator::removeAllDecorator($frms);
  	$this->view->frm_village= $frms;
  	
//   	$db= new Application_Model_DbTable_DbGlobal();
//   	$this->view->district = $db->getAllDistricts();
//   	$this->view->commune_name = $db->getCommune();
  	$this->view->result = $search;
  }
  function rptZoneAction(){
  	$db  = new Report_Model_DbTable_DbParamater();
  	$this->view->zone_list = $db->getAllZone();
  	$key = new Application_Model_DbTable_DbKeycode();
  	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
  	$frm = new Other_Form_FrmZone();
  	$frm_co=$frm->FrmAddZone();
  	Application_Model_Decorator::removeAllDecorator($frm_co);
  	$this->view->frm_zone = $frm_co;
  	if($this->getRequest()->isPost()){
  		$data = $this->getRequest()->getPost();
  		//print_r($data);exit();
  		if(isset($data['btn_search'])){
  			$this->view->zone_list = $db->getAllZone($data);
  		}else{
  		$collumn = array("zone_id","zone_num","modify_date","status");
  		$this->exportFileToExcel('ln_zone',$db->getAllZone(),$collumn);
  		}
  	}else $search = array('txtsearch' => '');
  }
  function rptHolidayAction(){
  	$db  = new Report_Model_DbTable_DbParamater();
  	$key = new Application_Model_DbTable_DbKeycode();
  	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
  	$frm = new Other_Form_FrmHoliday();
  	$frm = $frm->FrmAddHoliday();
  	Application_Model_Decorator::removeAllDecorator($frm);
  	$this->view->frm_holiday = $frm;
  	if($this->getRequest()->isPost()){
  		$data = $this->getRequest()->getPost();
  		if(isset($data['btn_search'])){
  			//print_r($data);exit();
  			$this->view->holiday_list = $db->getAllHoliday($data);
  			$a = $db->getAllHoliday($data);
  		}else{
  		//print_r($data);exit();
	  		$collumn = array("id","holiday_name","amount_day","start_date","end_date","status","modify_date","note");
	  		$this->exportFileToExcel('ln_holiday',$db->getAllHoliday(),$collumn);
  		}
  	}else {  		
  		$data = array('adv_search' => '',
						'search_status' => -1,
						'start_date'=> date('Y-m-01'),
						'end_date'=>date('Y-m-d')); 
  		$this->view->holiday_list = $db->getAllHoliday($data);
  	}
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
  function rptBranchAction(){
  	$db  = new Report_Model_DbTable_DbParamater();
  	$this->view->branch_list = $db->getAllBranch();
  	$key = new Application_Model_DbTable_DbKeycode();
  	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
  	$fm = new Other_Form_Frmbranch();
  	$frm = $fm->Frmbranch();
  	Application_Model_Decorator::removeAllDecorator($frm);
  	$this->view->frm_branch = $frm;
  	if($this->getRequest()->isPost()){
  		$search = $this->getRequest()->getPost();
  		if(isset($search['btn_search'])){	
	  		$this->view->branch_list = $db->getAllBranch($search);
  		}else {
  			$collumn = array("br_id","branch_namekh","branch_nameen","br_address","branch_code","branch_tel",
  				"status","fax","other","displayby");
  			$this->exportFileToExcel('ln_branch',$db->getAllBranch(),$collumn);
  		}
  	}else $data = array('adv_search' => '');
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
  				'start_date'=> date('Y-m-d'),
  				'end_date'=>date('Y-m-d')
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
  function rptCancelSaleAction(){ // by Vandy
  	 
  	if($this->getRequest()->isPost()){
  		$search=$this->getRequest()->getPost();
  	}else{
  		$search = array(
  				'adv_search'=>'',
  				'property_type'=>'',
  				'branch_id_search' => -1,
  				'from_date_search'=> date('Y-m-d'),
  				'to_date_search'=>date('Y-m-d'));
  	}
  	$db  = new Report_Model_DbTable_DbParamater();
  	$this->view->row = $db->getCancelSale($search);
  	 
  	$fm = new Loan_Form_FrmCancel();
  	$frm = $fm->FrmAddFrmCancel();
  	Application_Model_Decorator::removeAllDecorator($frm);
  	$this->view->frm_cancel = $frm;
  	//$this->view->frm_property=$row;
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
  				'start_date'=> date('Y-m-d'),
  				'end_date'=>date('Y-m-d'),
  		);
  	}
  	$this->view->list_end_date=$search;
  	$db  = new Report_Model_DbTable_DbParamater();
  	$this->view->row = $db->getAllIncome($search);
  	 
  	$frm = new Loan_Form_FrmSearchLoan();
  	$frm = $frm->AdvanceSearch();
  	Application_Model_Decorator::removeAllDecorator($frm);
  	$this->view->frm_search = $frm;
  }
  function rptExpenseAction(){ // by Vandy
  
 	 if($this->getRequest()->isPost()){
    			$search=$this->getRequest()->getPost();
   	}else{
    		$search = array(
    		"adv_search"=>'',
    		"branch_id"=>-1,
    		"category_id_expense"=>-1,
    		'start_date'=> date('Y-m-d'),
    		'end_date'=>date('Y-m-d'),
    	);
    }
    $this->view->list_end_date=$search;
  	$db  = new Report_Model_DbTable_DbParamater();
  	$this->view->row = $db->getAllExpense($search);
  
  	$frm = new Loan_Form_FrmSearchLoan();
  	$frm = $frm->AdvanceSearch();
  	Application_Model_Decorator::removeAllDecorator($frm);
  	$this->view->frm_search = $frm;
  }
  function rptDailyCashAction(){ // by Vandy
  
  	if($this->getRequest()->isPost()){
  		$search=$this->getRequest()->getPost();
  	}
  	else{
  		$search = array(
  				"adv_search"=>'',
  				"branch_id"=>-1,
  				"status"=>-1,
  				'start_date'=> date('Y-m-d'),
  				'end_date'=>date('Y-m-d'),
  		);
  	}
  	$this->view->list_end_date=$search;
  	$db  = new Report_Model_DbTable_DbParamater();
  	$this->view->row = $db->getAllIncome($search);
  	$this->view->rowExpense = $db->getAllExpense($search);
  	$this->view->rowSoldIncome = $db->getSoldIncome($search);
  	$this->view->collectMoney = $db->getCollectPayment($search);
  
  	$frm = new Loan_Form_FrmSearchLoan();
  	$frm = $frm->AdvanceSearch();
  	Application_Model_Decorator::removeAllDecorator($frm);
  	$this->view->frm_search = $frm;
  }
  function rptAgreementAction(){
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
  }
  function rptSaleHistoryAction(){
  	if($this->getRequest()->isPost()){
  		$search=$this->getRequest()->getPost();
  	}
  	else{
  		$search = array(
  				"adv_search"=>'',
  				"branch_id"=>-1,
  				"status"=>-1,
  				"property_type"=>'',
  				'start_date'=> date('Y-m-d'),
  				'end_date'=>date('Y-m-d'),
  		);
  	}
  	$db  = new Report_Model_DbTable_DbParamater();
  	$this->view->row = $db->getSaleHistory($search);
  	
  	$frm = new Loan_Form_FrmSearchLoan();
  	$frm = $frm->AdvanceSearch();
  	Application_Model_Decorator::removeAllDecorator($frm);
  	$this->view->frm_search = $frm;
  }
}

