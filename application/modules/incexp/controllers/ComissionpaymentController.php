<?php
class Incexp_ComissionpaymentController extends Zend_Controller_Action {
	
	public function init()
	{
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	
	public function indexAction()
	{
		try{
			$db = new Incexp_Model_DbTable_DbComissionpayment();
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
					    'adv_search'=>'',
						'staff_id'=>-1,
						'land_id'=>0,
						'branch_id_search' => -1,
						'status' => -1,
						'from_date_search'=> date('Y-m-d'),
						'to_date_search'=>date('Y-m-d'));
			}
			$rs_rows= $db->getAllComissionPayment($search);//call frome model
			$list = new Application_Form_Frmtable();
			$collumns = array("PROJECT_NAME","RECIEPT_NO","AGENCY_NAME","CATEGORY","PAYMENT_TYPE","TOTAL_PAID","TOTAL_DUE","DATE",
					"BY_USER","STATUS");
			$link=array(
					'module'=>'incexp','controller'=>'comissionpayment','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(10,$collumns,$rs_rows,array());
			$this->view->search = $search;
			
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$fm = new Incexp_Form_FrmCommision();
		$frm = $fm->FrmAddFrmCancel();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_cancel = $frm;
	}
	public function addAction(){
		$_dbmodel = new Incexp_Model_DbTable_DbComissionpayment();
		if($this->getRequest()->isPost()){//check condition return true click submit button
			$_data = $this->getRequest()->getPost();
			try {
				// Check Session Expire
				$dbgb = new Application_Model_DbTable_DbGlobal();
				$checkses = $dbgb->checkSessionExpire();
				if (empty($checkses)){
					$dbgb->reloadPageExpireSession();
					exit();
				}
				$_dbmodel->addComissionPayment($_data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/incexp/comissionpayment");			
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$fm = new Incexp_Form_FrmCommisionPayment();
		$frm = $fm->FrmAddFrmCommisionPayment();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_loan = $frm;
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		
		$result = $_dbmodel->getAllChequeIssue();
		array_unshift($result, array('id'=>-1,'name' => $tr->translate("ADD_NEW")));
		$this->view->cheque_issue = $result;
		
		$db = new Loan_Model_DbTable_DbExpense();
		$result = $db->getAllExpenseCategory();
		array_unshift($result, array ( 'id' => -1,'name' => $tr->translate("ADD_NEW")));
		$this->view->all_category = $result;
		
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data=$key->getKeyCodeMiniInv(TRUE);
		
		$db = new Application_Model_DbTable_DbGlobal();
		$co_name = $db->getAllCoNameOnly();
		array_unshift($co_name,array(
				'id' => -1,
				'name' => $tr->translate("ADD_NEW"),
		) );
		$this->view->co_name=$co_name;
		
		$frmpopup = new Application_Form_FrmPopupGlobal();
		$this->view->footer = $frmpopup->getFooterReceipt();
		
	}
	public function editAction(){
		$id = $this->getRequest()->getParam('id');
		$id = empty($id)?0:$id;
		$_dbmodel = new Incexp_Model_DbTable_DbComissionpayment();
		if($this->getRequest()->isPost()){//check condition return true click submit button
			$_data = $this->getRequest()->getPost();
			$_data['id']=$id;
			try {
				// Check Session Expire
				$dbgb = new Application_Model_DbTable_DbGlobal();
				$checkses = $dbgb->checkSessionExpire();
				if (empty($checkses)){
					$dbgb->reloadPageExpireSession();
					exit();
				}
				$_dbmodel->editComissionPayment($_data);
				Application_Form_FrmMessage::Sucessfull("UPDATE_SUCCESS","/incexp/comissionpayment");	
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("UPDATE_FAIL");
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
	      }
	    $row  = $_dbmodel->getCommissionPaymentById($id);
	    $this->view->row = $row;
	    if(empty($row)){
	    	Application_Form_FrmMessage::Sucessfull("RECORD_NOTFUND","/incexp/comissionpayment",2);
	    	exit();
	    }
	    $tr = Application_Form_FrmLanguages::getCurrentlanguage();
	    $result = $_dbmodel->getAllChequeIssue();
	    array_unshift($result, array('id'=>-1,'name' => $tr->translate("ADD_NEW")));
	    $this->view->cheque_issue = $result;
	    
		$fm = new Incexp_Form_FrmCommisionPayment();
		$frm = $fm->FrmAddFrmCommisionPayment($row);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_loan = $frm;
		
		
		$db = new Loan_Model_DbTable_DbExpense();
		$result = $db->getAllExpenseCategory();
		array_unshift($result, array ( 'id' => -1,'name' => $tr->translate("ADD_NEW")));
		$this->view->all_category = $result;
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data=$key->getKeyCodeMiniInv(TRUE);
		$this->view->category_id = $row['category'];
		
		$db = new Application_Model_DbTable_DbGlobal();
		$co_name = $db->getAllCoNameOnly();
		array_unshift($co_name,array(
				'id' => -1,
				'name' => $tr->translate("ADD_NEW"),
		) );
		$this->view->co_name=$co_name;
		
		$frmpopup = new Application_Form_FrmPopupGlobal();
		$this->view->footer = $frmpopup->getFooterReceipt();
		
		
	}
	
	function getallcommisonbyagencyAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db_com = new Incexp_Model_DbTable_DbComissionpayment();
			$id = $db_com->getCommisionByAgency($data);
			print_r(Zend_Json::encode($id));
			exit();
		}
	}
	function getallcommisonbyagencyeditAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db_com = new Incexp_Model_DbTable_DbComissionpayment();
			$id = $db_com->getCommisionByAgencyEdit($data);
			print_r(Zend_Json::encode($id));
			exit();
		}
	}
   
}

