<?php
class Loan_PaymentcombineController extends Zend_Controller_Action {
	
	const REDIRECT_URL = '/loan/paymentcombine';
    public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
			$db = new Loan_Model_DbTable_DbPaymentCombine();
				if($this->getRequest()->isPost()){
					$search=$this->getRequest()->getPost();
				}else{
					$search = array(
						'adv_search'  => '',
						'client_name' => -1,
						'start_date'  => date('Y-m-d'),
						'end_date'    => date('Y-m-d'),
						'branch_id'	  => -1,
						'paymnet_type'=> -1,
						'land_id'     => -1,
						'status'      =>-1,
						'payment_method'=>-1
					);
			}
			$this->view->search = $search;
			$rs_rows= $db->getAllPaymentCombine($search);
			$result = array();
			$list = new Application_Form_Frmtable();
			
			$collumns = array("BRANCH_NAME","CUSTOMER_NAME","RECIEPT_NO","DATE","PRINCIPAL","TOTAL_INTEREST",
							  "RECEIVE_AMOUNT","PAYMENT_METHOD","BY_USER","STATUS");
			
			$link=array('module'=>'loan','controller'=>'ilpayment','action'=>'edit',);
			$linkprint=array('module'=>'report','controller'=>'loan','action'=>'receipt',);
			$link_delete=array('module'=>'loan','controller'=>'ilpayment','action'=>'delete',);
			
			$this->view->list=$list->getCheckList(10, $collumns, $rs_rows,array());
		}catch(Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}	
		$frm = new Loan_Form_FrmSearchGroupPayment();
		$fm = $frm->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($fm);
		$this->view->frm_search = $fm;
	}
  
  function addAction()
  {
  	$rightclick = $this->getRequest()->getParam('rightclick');
  	$rightclick = empty($rightclick)?"":$rightclick;
  	$this->view->rightclick = $rightclick;
  	
  	$db = new Loan_Model_DbTable_DbPaymentCombine();
  	$db_global = new Application_Model_DbTable_DbGlobal();
		if($this->getRequest()->isPost()){
			// Check Session Expire
			$checkses = $db_global->checkSessionExpire();
			if (empty($checkses)){
				$db_global->reloadPageExpireSession();
				exit();
			}
			$_data = $this->getRequest()->getPost();
			try{
				if (empty($_data)){
					Application_Form_FrmMessage::Sucessfull("File Attachment to large can't upload and Save data !","/loan/paymentcombine",2);
					exit();
				}
				
				$receipt = $db->addPaymentCombine($_data);
				
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/loan/paymentcombine/");
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		
		$frm = new Loan_Form_FrmMultiSalePaymemt();
		$frm_loan=$frm->FrmMultiSalePaymemt();
		Application_Model_Decorator::removeAllDecorator($frm_loan);
		$this->view->frm = $frm_loan;
		
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data=$key->getKeyCodeMiniInv(TRUE);
		
		$frmpopup = new Application_Form_FrmPopupGlobal();
		$this->view->footer = $frmpopup->getFooterReceipt();
		$this->view->officailreceipt = $frmpopup->getOfficailReceipt();
	}
	
	function editAction(){
		$id = $this->getRequest()->getParam('id');
		$id = empty($id)?"0":$id;
		$db = new Loan_Model_DbTable_DbPaymentCombine();
		$paymentIl = $db->getCombinePaymentInfoById($id);
		if(empty($paymentIl)){
			Application_Form_FrmMessage::Sucessfull("RECORD_NOTFUND","/loan/paymentcombine",2);
			exit();
		}
		$db_global = new Application_Model_DbTable_DbGlobal();
		if($this->getRequest()->isPost()){
			// Check Session Expire
			$checkses = $db_global->checkSessionExpire();
			if (empty($checkses)){
				$db_global->reloadPageExpireSession();
				exit();
			}
			$_data = $this->getRequest()->getPost();
			try{
				if (empty($_data)){
					Application_Form_FrmMessage::Sucessfull("File Attachment to large can't upload and Save data !","/loan/paymentcombine",2);
					exit();
				}
				$receipt = $db->editPaymentCombine($_data);
				
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/loan/paymentcombine/");
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("EDIT_FAIL");
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$this->view->row = $paymentIl;
		$this->view->rowDetail = $db->getReceiptInCombinePaymentRS($id);
		
		$frm = new Loan_Form_FrmMultiSalePaymemt();
		$frm_loan=$frm->FrmMultiSalePaymemt($paymentIl);
		Application_Model_Decorator::removeAllDecorator($frm_loan);
		$this->view->frm = $frm_loan;
	}


	function getallsaleAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db_com = new Loan_Model_DbTable_DbPaymentCombine();
			$id = $db_com->getSaleForPaymentRecord($data);
			print_r(Zend_Json::encode($id));
			exit();
		}
	}
	
	function getPaymentPropertyAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Loan_Model_DbTable_DbPaymentCombine();
			$id = empty($data['id']) ? 0 : $data['id'];
			$row = $db->checkReceiptInCombinePayment($id);
			if(!empty($row)){
				print_r(Zend_Json::encode(TRUE));
				exit();
			}else{
				print_r(Zend_Json::encode(false));
				exit();
			}
			
		}
	}
	
	function voidreceiptAction(){
	 	
		$dbCombine = new Loan_Model_DbTable_DbPaymentCombine();
	 	try {
	 		if($this->getRequest()->isPost()){
				$data = $this->getRequest()->getPost();
				$id = empty($data['id'])?0:$data['id'];	
				$dbCombine->voidCombineReciept($data);
				print_r(Zend_Json::encode(1));
				exit();//Void Success
			}
	 		Application_Form_FrmMessage::Sucessfull("You no permission to delete","/loan/paymentcombine",2);
			exit();
	 	}catch (Exception $e) {
	 		Application_Form_FrmMessage::message("INSERT_FAIL");
	 		echo $e->getMessage();
	 	}
	}
	
	
}