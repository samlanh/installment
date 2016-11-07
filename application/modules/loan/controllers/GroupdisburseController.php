<?php
class Loan_GroupDisburseController extends Zend_Controller_Action {
	public function init()
	{
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	private $sex=array(1=>'M',2=>'F');
	public function indexAction(){
		try{
			$db = new Loan_Model_DbTable_DbLoanGroup();
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'txt_search'=>'',
						'customer_code'=> -1,
						'repayment_method' => -1,
						'branch_id' => -1,
						'co_id' => -1,
						'status' => -1,
						'currency_type'=>-1,
						'pay_every'=>-1,
						'start_date'=> date('Y-m-01'),
						'end_date'=>date('Y-m-d'),
				);
			}
			$rs_rows= $db->getAllGroupLoan($search);
			$glClass = new Application_Model_GlobalClass();
			$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH_NAME","LOAN_NO","CUSTOMER_NAME","COMUNE_NAME_EN","LOAN_AMOUNT","INTEREST_RATE","REPAYMENT_TYPE","TERM_BORROW","ZONE_NAME","CO_NAME",
				"STATUS");
			$link=array(
					'module'=>'loan','controller'=>'groupdisburse','action'=>'view',
			);
			$link_info=array('module'=>'loan','controller'=>'groupdisburse','action'=>'edit',);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('branch'=>$link,'loan_number'=>$link,'payment_method'=>$link_info,'client_name_kh'=>$link_info,'client_name_en'=>$link_info,'total_capital'=>$link_info),0);
		
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$frm = new Loan_Form_FrmSearchLoan();
		$frm = $frm->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
	}
  function addAction()
  {
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$db = new Loan_Model_DbTable_DbLoanGroup();
				$db->addNewLoanGroup($_data);
				if(!empty($_data['saveclose'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/loan/groupdisburse/index");
				}else{
					Application_Form_FrmMessage::message("INSERT_SUCCESS");
				}
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$frm = new Loan_Form_FrmLoan();
		$frm_loan=$frm->FrmAddLoan();
		Application_Model_Decorator::removeAllDecorator($frm_loan);
		$this->view->frm_loan = $frm_loan;
		$frmpopup = new Application_Form_FrmPopupGlobal();

		$this->view->frmpupoploantype = $frmpopup->frmPopupLoanTye();
 		$this->view->frmPopupZone = $frmpopup->frmPopupZone();

		$db_option = new Application_Model_GlobalClass();
		$this->view->member_option = $db_option->getAllClientGroupOption();
		$this->view->code_option = $db_option->getAllClientCodeOption();
		
		$db = new Setting_Model_DbTable_DbLabel();
		$this->view->setting=$db->getAllSystemSetting();
		
		// 		$this->view->frmPopupCommune = $frmpopup->frmPopupCommune();
		// 		$this->view->frmPopupDistrict = $frmpopup->frmPopupDistrict();
		// 		$this->view->frmPopupVillage = $frmpopup->frmPopupVillage();
		// 		$this->view->frmpupopclient = $frmpopup->frmPopupClient();
		// 		$this->view->frmPopupCO = $frmpopup->frmPopupCO();
		
	}
	function viewAction(){
		$id = $this->getRequest()->getParam('id');
		$db_g = new Application_Model_DbTable_DbGlobal();
		if(empty($id)){
			Application_Form_FrmMessage::Sucessfull("RECORD_NOT_FUND","/loan/index/index");
		}
		$db = new Loan_Model_DbTable_DbLoanGroup();
		$row = $db->getLoanGroupviewById($id);
		$this->view->tran_rs = $row;
		
		$rows = $db->getAllMemberLoanById($id);
		$this->view->list_members = $rows;
		if(empty($rows)){
			Application_Form_FrmMessage::Sucessfull("RECORD_NOT_EXIST","/loan/groupdisburse/index");
		}
	
	}
	function editAction()
	{
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$db = new Loan_Model_DbTable_DbLoanGroup();
				$effect =  $db->upDateLoanDisburse($_data);
				if($effect==true){
					Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/loan/GroupDisburse/index");
				}else{
					Application_Form_FrmMessage::Sucessfull("EDIT_FAIL","/loan/groupdisburse/index");
				}
			}catch (Exception $e) {
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				Application_Form_FrmMessage::Sucessfull("EDIT_FAIL","/loan/groupdisburse/index");
			}
		}
		$id = $this->getRequest()->getParam('id');
		$db = new Loan_Model_DbTable_DbLoanIL();
		$row = $db->getTranLoanByIdWithBranch($id,2);
		
		$db_g = new Application_Model_DbTable_DbGlobal();
		$rs = $db_g->getLoanFundExist($id);
		if($rs==true){
			Application_Form_FrmMessage::Sucessfull("LOAN_FUND_EXIST","/loan/groupdisburse/index");
		}
		
		$frm = new Loan_Form_FrmLoan();
		$frm_loan=$frm->FrmAddLoan($row);
		$this->view->client_id = $row['client_id'];
		$db = new Loan_Model_DbTable_DbLoanGroup();
		$rows = $db->getAllMemberLoanById($id);
		$this->view->list_members = $rows;
		if(empty($rows)){
			Application_Form_FrmMessage::Sucessfull("RECORD_NOT_EXIST","/loan/groupdisburse/index");
		}
		Application_Model_Decorator::removeAllDecorator($frm_loan);
		$this->view->frm_loan = $frm_loan;
		$frmpopup = new Application_Form_FrmPopupGlobal();
		$this->view->frmpupopclient = $frmpopup->frmPopupClient();
		$this->view->frmPopupCO = $frmpopup->frmPopupCO();
		$this->view->frmPopupZone = $frmpopup->frmPopupZone();
		$this->view->frmPopupCommune = $frmpopup->frmPopupCommune();
		$this->view->frmPopupDistrict = $frmpopup->frmPopupDistrict();
		$this->view->frmPopupVillage = $frmpopup->frmPopupVillage();
		$db_option = new Application_Model_GlobalClass();
		$this->view->member_option = $db_option->getAllClientGroupOption();
		$this->view->code_option = $db_option->getAllClientCodeOption();
		
		$db = new Setting_Model_DbTable_DbLabel();
		$this->view->setting=$db->getAllSystemSetting();
	}	
	function veiwloangroupAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			$_dbmodel = new Loan_Model_DbTable_DbLoanGroupTest();
			$rows_return=$_dbmodel->addNewLoanGroupTest($_data);
			print_r(Zend_Json::encode($rows_return));
			exit();
		}
	
	}
// 	public function addloanAction(){
// 		if($this->getRequest()->isPost()){
// 			$data=$this->getRequest()->getPost();
// 			$db = new Loan_Model_DbTable_DbLoan();
// 			$id = $db->addNewLoanGroup($data);
// 			$suc = array('sms'=>'ប្រាក់ឥណទានត្រូវបានបញ្ចូលដោយជោគជ័យ !');
// 			print_r(Zend_Json::encode($suc));
// 			exit();
// 		}
// 	}
	function getclientgroupAction()
	{
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Loan_Model_DbTable_DbLoanGroup();
			$result = $db->getAllMemberByGroup($data['group_id']);
			print_r(Zend_Json::encode($result));
			exit();
		}
	}
}

