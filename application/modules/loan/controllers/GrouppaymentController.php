<?php
class Loan_GroupPaymentController extends Zend_Controller_Action {
	public function init()
	{
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	private $sex=array(1=>'M',2=>'F');
	public function indexAction(){
		try{
			$db = new Loan_Model_DbTable_DbGroupPayment();
		if($this->getRequest()->isPost()){
				$formdata=$this->getRequest()->getPost();
				$search = array(
						'advance_search' 	=>  $formdata['advance_search'],
						'client_name'		=>	$formdata['g_client_name'],
						'start_date'		=>	$formdata['start_date'],
						'end_date'			=>	$formdata['end_date'],
						'status'			=>	$formdata['status'],
						'branch_id'			=>	$formdata['branch_id'],
						'co_id'				=>	$formdata['co_id'],
						);
			}
			else{
				$search = array(
						'adv_search' => '',
						'client_name' => -1,
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
						'branch_id'		=>	-1,
						'co_id'		=> -1,
						'status'=>"",);
			}
			$rs_rows= $db->getAllGroupPPayment($search);

			$list = new Application_Form_Frmtable();
			$collumns = array("Branch","Recirpt No","Loan No","Group Client","Total Principle","Total Interest","Penalize Amount","Total Payment","Recieve Amount","Payment Date","Due Date","CO Name"
				);
			$link=array(
					'module'=>'loan','controller'=>'grouppayment','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('receipt_no'=>$link,'team_group'=>$link,'date'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			echo $e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$frm = new Loan_Form_FrmSearchGroupPayment();
		$fm = $frm->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($fm);
		$this->view->frm_search = $fm;
	}
	function addAction()
	{
		$db = new Loan_Model_DbTable_DbGroupPayment();
		$db_global = new Application_Model_DbTable_DbGlobal();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			$identify = $_data["identity"];
			try {
				if($identify==""){
					Application_Form_FrmMessage::Sucessfull("Client is no loan to pay", "/loan/grouppayment");
				}else{
					$db->addGroupPayment($_data);
				}
			}catch (Exception $e) {
				echo $e->getMessage();
				Application_Form_FrmMessage::message("INSERT_FAIL");
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$frm = new Loan_Form_FrmIlPayment();
		$frm_loan=$frm->FrmGroupPayment();
		Application_Model_Decorator::removeAllDecorator($frm_loan);
		$this->view->frm_ilpayment = $frm_loan;
		
		$db_keycode = new Application_Model_DbTable_DbKeycode();
		$this->view->keycode = $db_keycode->getKeyCodeMiniInv();
		
		$this->view->graiceperiod = $db_keycode->getSystemSetting(9);
		
		$this->view->client = $db->getAllClient();
		$this->view->clientCode = $db->getAllClientCode();
				
		$session_user=new Zend_Session_Namespace('auth');
		$this->view->user_name = $session_user->last_name .' '. $session_user->first_name;
		
		$this->view->loan_number = $db_global->getLoanNumberByBranch(2);
	}
	function editAction()
	{
		$id = $this->getRequest()->getParam("id");
		$db = new Loan_Model_DbTable_DbGroupPayment();
		$db_global = new Application_Model_DbTable_DbGlobal();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			$identity = $_data["identity"];
			try {
				if($identity==""){
					Application_Form_FrmMessage::Sucessfull("Group Client no loan to pay!", "/loan/grouppayment");
				}else{
					$db->updateGroupPayment($_data);
// 					Application_Form_FrmMessage::Sucessfull("Update Success!", "/loan/GroupPayment");
					exit();
				}
			}catch (Exception $e) {
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$last_pay_date = $db_global->getLastDatePayment($id);
		$current_date = $db_global->getCurrentDatePayment($id);
		if($current_date<$last_pay_date){
			Application_Form_FrmMessage::Sucessfull("WARNNING_EDIT_LOAN","/loan/grouppayment/");
		}
		$rs = $db->getGroupPaymentById($id);
		$frm = new Loan_Form_FrmIlPayment();
		$frm_loan=$frm->FrmGroupPayment();
		Application_Model_Decorator::removeAllDecorator($frm_loan);
		$this->view->frm_ilpayment = $frm_loan;
		
		$this->view->reciept_money = $rs;
		$this->view->client = $db->getAllClient();
		$this->view->clientCode = $db->getAllClientCode();
		
		$db_keycode = new Application_Model_DbTable_DbKeycode();
		$this->view->keycode = $db_keycode->getKeyCodeMiniInv();
		
		$this->view->graiceperiod = $db_keycode->getSystemSetting(9);
		
		$session_user=new Zend_Session_Namespace('auth');
		$this->view->user_name = $session_user->last_name .' '. $session_user->first_name;
	
		$list = new Application_Form_Frmtable();
	
		$rs_receipt_detail = $db->getGroupPaymentDetail($id);
		$this->view->reciept_moneyDetail = $rs_receipt_detail;
		$this->view->group_id = $rs["group_id"];
		
		$this->view->loan_numbers = $db_global->getLoanNumberByBranch(2);
	}
	
	function cancelPaymentAction()
	{
		$db = new Loan_Model_DbTable_DbGroupPayment();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			$identity = $_data["identity"];
			try {
				$row = $db->cancelPaymnet($_data);
				print_r(Zend_Json::encode($row));
				exit();
			}catch (Exception $e) {
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
	}
	function getLoanDetailAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Loan_Model_DbTable_DbLoanIL();
			$row = $db->getGroupLoadDetail($data);
			print_r(Zend_Json::encode($row));
			exit();
		}
	}
	function getLoannumberAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Loan_Model_DbTable_DbGroupPayment();
			$row = $db->getLoanPaymentByLoanNumber($data);
			print_r(Zend_Json::encode($row));
			exit();
		}
	}
	
	function getLoanPaymentAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Loan_Model_DbTable_DbGroupPayment();
			$row = $db->getLoanByLoanNimber($data);
			print_r(Zend_Json::encode($row));
			exit();
		}
	}
	function getAllLoannumberAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Loan_Model_DbTable_DbGroupPayment();
			$row = $db->getAllLoanPaymentByLoanNumber($data);
			print_r(Zend_Json::encode($row));
			exit();
		}
	}
	function getClientByBranchAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$branch = $data["branch_id"];
			$db = new Loan_Model_DbTable_DbGroupPayment();
			$row = $db->getClientByBranch($branch);
			print_r(Zend_Json::encode($row));
			exit();
		}
	}
	function getAllLoanHasPayedAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Loan_Model_DbTable_DbGroupPayment();
			$row = $db->getAllLoanHasPayed($data);
			print_r(Zend_Json::encode($row));
			exit();
		}
	}
	
}

