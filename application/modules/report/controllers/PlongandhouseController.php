<?php
class Report_PlongandhouseController extends Zend_Controller_Action {
  public function init()
  {    	
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
  }
  function indexAction(){
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
  	$db  = new Report_Model_DbTable_Dbplongandhouse();
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
  function rptPlonglistnewAction(){
	if($this->getRequest()->isPost()){
		$search = $this->getRequest()->getPost();
	}else{
		$search = array(
				'adv_search'=>'',
				'land_id'=>0,
				'property_type'=>'',
				"branch_id"=> -1,
				'type_property_sale'=>-1,
				'date_type'=>1,
				'plong_step_option'=>-1,
				'plong_titletype'=>0,
				'plong_processtype'=>0,
				'process_status'=>0,
				'status_receive'=>-1,
				'times_filter'=>-1
		);
	}
	$this->view->datasearch = $search;
	$db  = new Report_Model_DbTable_Dbplongandhouse();
	$this->view->row = $db->plongListReportData($search);

	$frm=new Other_Form_FrmProperty();
	$row=$frm->FrmFrmProperty();
	Application_Model_Decorator::removeAllDecorator($row);
	$this->view->frm_property=$row;

	$frm = new Loan_Form_FrmSearchLoan();
	$frm = $frm->AdvanceSearch();
	Application_Model_Decorator::removeAllDecorator($frm);
	$this->view->frm_search = $frm;
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
  	$db  = new Report_Model_DbTable_Dbplongandhouse();
  	$this->view->row = $db->getAllPlongStep($search);
  
  	$frm = new Loan_Form_FrmSearchLoan();
  	$frm = $frm->AdvanceSearch();
  	Application_Model_Decorator::removeAllDecorator($frm);
  	$this->view->frm_search = $frm;
  	 
  	$frm = new Issue_Form_FrmPlongStep();
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
  /*from loan controller*/
  
  function rptPrintplongAction(){
  	$id = $this->getRequest()->getParam('id');
  	$_dbmodel = new Report_Model_DbTable_Dbplongandhouse();
  	$row  = $_dbmodel->getRecivePlongInfo($id);
  	$this->view->row = $row;
  }
  function rptReceivehouseAction(){
  	if($this->getRequest()->isPost()){
  		$search=$this->getRequest()->getPost();
  	}else{
  		$search = array(
  				'txt_search'=>'',
  				'branch_id' => -1,
  				'streetlist'=>'',
  				'status' => -1,
  				'land_id'=>-1,
  				'client_name'=>'',
  				'payment_id'=>0,
  				'give_status'=>0,
  				'start_date'=> date('Y-m-d'),
  				'end_date'=>date('Y-m-d'));
  	}
  	$this->view->search = $search;
  	$db  = new Report_Model_DbTable_Dbplongandhouse();
  	$this->view->row = $db->getReportAllIssueHouse($search);
  
  	$frm_search = new Loan_Form_FrmSearchLoan();
  	$frm = $frm_search->AdvanceSearch();
  	Application_Model_Decorator::removeAllDecorator($frm);
  	$this->view->frm_search = $frm;
  
  	$frmpopup = new Application_Form_FrmPopupGlobal();
  	$this->view->footerReport = $frmpopup->getFooterReport();
  	$this->view->headerReport = $frmpopup->getLetterHeadReport();
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
  	$this->view->search = $search;
  	$db  = new Report_Model_DbTable_Dbplongandhouse();
  	$this->view->row = $db->getCustomerReceivedPlong($search);
  	 
  	$fm = new Loan_Form_FrmCancel();
  	$frm = $fm->FrmAddFrmCancel();
  	Application_Model_Decorator::removeAllDecorator($frm);
  	$this->view->frm_cancel = $frm;
  	$frmpopup = new Application_Form_FrmPopupGlobal();
  	$this->view->footerReport = $frmpopup->getFooterReport();
  	$this->view->headerReport = $frmpopup->getLetterHeadReport();
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