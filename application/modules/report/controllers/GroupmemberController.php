<?php
class Report_GroupMemberController extends Zend_Controller_Action {
    public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
  function indexAction(){
  	
  }
  function rptIndividulAction(){
  	
  }
  function rptClientInformationAction(){
	  	 
  }
  function rptClientAgreementAction(){
  	$db  = new Report_Model_DbTable_DbLnClient();
  	$this->view->calleteral_list = $db->getAllAgreement();
  	$key = new Application_Model_DbTable_DbKeycode();
  	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
  	if($this->getRequest()->isPost()){
  		$collumn = array("branch_id","code_call","co_id","getter_name","giver_name","date_delivery",
  				"client_code","contracts_borrow","mortgage_Contract","name_borrower");
  		$this->exportFileToExcel('ln_callect',$db->getAllAgreement(),$collumn);
  	}
  }
  function rptCalleteralAction(){
	  	$db  = new Report_Model_DbTable_DbLnClient();  	
	  	if($this->getRequest()->isPost()){
	  		$search = $this->getRequest()->getPost();
	  	}else{
	  		$search = array(
			'adv_search' => '',
			'status_search' => -1,
			'start_date'=> date('Y-m-d'),
			'end_date'=>date('Y-m-d'));
		}
		$this->view->result=$search;
		$this->view->calleteral_list = $db->getAllCalleteral($search);
	  	$fm=new Group_Form_Frmcallterals();
	  	$frm=$fm->FrmCallTeral();
	  	Application_Model_Decorator::removeAllDecorator($frm);
	  	$this->view->frm_callteral=$frm;
	  	
	  	$key = new Application_Model_DbTable_DbKeycode();
	  	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
  }
  function rptGroupAction($table='rms_setting'){
  	$db  = new Report_Model_DbTable_DbLnClient();
  	$rs=$db->getAllGroup();
  	$this->view->staff_list = $rs;
  	$key = new Application_Model_DbTable_DbKeycode();
  	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
  	if($this->getRequest()->isPost()){
  		$collumn = array("client_number","name_kh","name_en","sex","status","branch_name","pro_id","dis_id","com_id","village_id","spouse_name","phone");
  		$this->exportFileToExcel($table,$rs,$collumn);
  	}
  }
  function rptClientAction($table='ln_account_name'){
   
  	$key = new Application_Model_DbTable_DbKeycode();
  	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
  	if($this->getRequest()->isPost()){  		
  		$search = $this->getRequest()->getPost();
  	}else{
  		$search = array('adv_search' => '',
						'status' => -1,
  						'branch_id' => 0,  				
						'province'=>0,
						'district'=>'',
						'commune'=>'',
						'village'=>'',
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'));
  	}	
  
  	$this->view->result=$search;
  	 
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
  
  function rptAgreementAction(){
  	$key = new Application_Model_DbTable_DbKeycode();
  	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
  }
  function rptCalleteralChangeAction(){
  	$key = new Application_Model_DbTable_DbKeycode();
  	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
  	$db = new Report_Model_DbTable_DbLnClient(); 	
  	if($this->getRequest()->isPost()){
  		$search = $this->getRequest()->getPost();
  	}else{
		$search = array(
			'start_date'=> date('Y-m-d'),
		    'end_date'=>date('Y-m-d'),
			'adv_search' => '',
			'status_search' => -1);
	}
	$this->view->calleteral_list = $db->getAllChangeCollteral($search);//call frome model
	$this->view->result=$search;
  	$fm = new Group_Form_Frmchangecollteral();
  	$frm = $fm->FrmChangeCollteral();
  	Application_Model_Decorator::removeAllDecorator($frm);
  	$this->view->frm_changeCollteral = $frm;
//   	print_r($db->getAllChangeCollteral($search));
  }
  function rptCalleteralsAction(){
  	$db  = new Report_Model_DbTable_DbLnClient();
  	$key = new Application_Model_DbTable_DbKeycode();
  	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
  	$this->view->calleteral_list = $db->geteAllcallteral();
  	$fm=new Group_Form_Frmcallterals();
  	$frm=$fm->FrmCallTeral();
  	$this->view->frm_callteral=$frm;
  	if($this->getRequest()->isPost()){
  		$data = $this->getRequest()->getPost();
  		if(isset($data['btn_search'])){
  			$this->view->calleteral_list = $db->geteAllcallteral($data);
  		}
  	}else {
  		$search = array(
  				'adv_search' => '',
  				'status_search' => -1,
  				'start_date'=> date('Y-m-d'),
  				'end_date'=>date('Y-m-d'));
  	}
  	$this->view->result=$search;
  }
  function rptReturncollteralAction(){
  		
  		$db = new Report_Model_DbTable_DbRpt();
  		if($this->getRequest()->isPost()){
  			$search=$this->getRequest()->getPost();
  		}else{
  			$search = array(
  				'start_date'=> date('Y-m-d'),
  				'adv_search' => '',
  				'status_search' => -1,
  				'start_date'=> date('Y-m-d'),
  				'end_date'=>date('Y-m-d'));
  		}
  	  $this->view->calleteral_list = $db->getAllReturnCollteral($search);//call frome model
  	  $this->view->result = $search;
	  $fm = new Group_Form_Frmreturncollteral();
	  $frm = $fm->FrmReturnCollteral();
	  Application_Model_Decorator::removeAllDecorator($frm);
	  $this->view->frm_returnCollteral = $frm;
	  
	  $key = new Application_Model_DbTable_DbKeycode();
	  $this->view->data=$key->getKeyCodeMiniInv(TRUE);
 	}
 	function rptClientblacklistAction(){
 		$key = new Application_Model_DbTable_DbKeycode();
 		$this->view->data=$key->getKeyCodeMiniInv(TRUE);
 		$db = new Group_Model_DbTable_DbClientBlackList();
 		if($this->getRequest()->isPost()){
 			$search=$this->getRequest()->getPost();
 		}else{
 			$search = array(
 					'adv_search' => '',
    				'status_search' => -1,
    				'start_date'=> date('Y-m-d'),
  				    'end_date'=>date('Y-m-d'));
 			
 		}
 		$this->view->calleteral_list = $db->getAllBlackList($search);//call frome model
 		$frm = new Group_Form_FrmClientBlackList();
    	$frm = $frm->FrmClientBlackList();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_search = $frm;
    	$this->view->list_end_date =$search;
 	}
 	function calleteralLetterAction(){
 		$db  = new Report_Model_DbTable_DbLnClient();
 		$id =$this->getRequest()->getParam('id');
 		$this->view->calleteral_list = $db->getCalleteralByClient($id);
//  		print_r($db->getCalleteralByClient($id));
 	}
}
