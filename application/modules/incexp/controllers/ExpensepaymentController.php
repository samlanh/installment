<?php
class Incexp_ExpensepaymentController extends Zend_Controller_Action {
	private $activelist = array('មិនប្រើ​ប្រាស់', 'ប្រើ​ប្រាស់');
	private $type = array(1=>'service',2=>'program');
	const REDIRECT_URL = '/incexp/expensepayment';
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function start(){
		return ($this->getRequest()->getParam('limit_satrt',0));
	}
	public function indexAction(){
		try{
			if($this->getRequest()->isPost()){
    			$search = $this->getRequest()->getPost();
    		}
    		else{
    			$search=array(
    							'branch_search' => '',
    							'adv_search' => '',
    					        'supplier_search'=>'',
    							'paid_by_search'=>'',
    							'start_date'=> date('Y-m-d'),
    							'end_date'=>date('Y-m-d'),
    							'status_search'=>'',
    					);
    		}
			$db =  new Incexp_Model_DbTable_DbExpensePayment();
			$rows = $db->getAllPurchasePayment($search);
			$rs_rows=new Application_Model_GlobalClass();
			$rs_rows=$rs_rows->getImgActive($rows, BASE_URL);
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH_NAME","RECEIPT_NO","SUPPLIER_NAME","BALANCE","TOTAL_PAID","TOTAL_DUE","PAID_BY",
					"DATE","STATUS");
			$link=array(
					'module'=>'incexp','controller'=>'expensepayment','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(10, $collumns, $rs_rows,array('branch_name'=>$link,'receipt_no'=>$link,'supplier_name'=>$link,));
			}catch (Exception $e){
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
		
	}
	public function addAction(){
		$db = new Incexp_Model_DbTable_DbExpensePayment();
		if($this->getRequest()->isPost()){
		$_data = $this->getRequest()->getPost();
			try{
				$row = $db->addPaymentReceipt($_data);
				
				if(isset($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL."/index");
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL."/add");
				}
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				echo $e->getMessage();
			}
		}
		
		$this->view->cheque_issue  = $db->getAllChequeIssue();
		
		$frm = new Incexp_Form_FrmExpensePayment();
		$frm->FrmAddPurchasePayment(null);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_payment = $frm;
		
	}
	
	public function editAction(){
		$db = new Incexp_Model_DbTable_DbExpensePayment();
		$id=$this->getRequest()->getParam('id');
		$row = $db->getPurchasePaymentById($id);
		if (empty($row)){
			Application_Form_FrmMessage::Sucessfull("No Record",self::REDIRECT_URL."/index");
			exit();
		}else if ($row['is_closed']==1){
			Application_Form_FrmMessage::Sucessfull("This Payment already closing",self::REDIRECT_URL."/index",2);
			exit();
		}else if ($row['status']==0){
			Application_Form_FrmMessage::Sucessfull("This Record already void",self::REDIRECT_URL."/index",2);
			exit();
		}
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try{
				$db->updatePaymentReceipt($_data);
				if(isset($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL."/index");
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL."/add");
				}
				
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				echo $e->getMessage();
			}
		}
		$this->view->cheque_issue  = $db->getAllChequeIssue();
		$frm = new Incexp_Form_FrmExpensePayment();
		$frm->FrmAddPurchasePayment($row);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_payment = $frm;
	
	}
	
	function voidAction(){
		$db = new Incexp_Model_DbTable_DbExpensePayment();
		$id=$this->getRequest()->getParam('id');
		if (!empty($id)){
			try{
				$row = $db->getPurchasePaymentById($id);
				if (empty($row)){
					Application_Form_FrmMessage::Sucessfull("No Record",self::REDIRECT_URL."/index",2);
					exit();
				}else if ($row['is_closed']==1){
					Application_Form_FrmMessage::Sucessfull("This Payment already closing",self::REDIRECT_URL."/index",2);
					exit();
				}else if ($row['status']==0){
					Application_Form_FrmMessage::Sucessfull("This Record already void",self::REDIRECT_URL."/index",2);
					exit();
				}
				$db->voidPaymentReceipt($id,$row['branch_id']);
				Application_Form_FrmMessage::Sucessfull("Void Successfully",self::REDIRECT_URL."/index");
			}catch (Exception $e){
				Application_Form_FrmMessage::message("Void Payment Faile");
				echo $e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}	
		}else{
			Application_Form_FrmMessage::message("No Payment Receipt to void! please check again.");
			$this->_redirect("/sale/paymentreceipt");
				
		}	
			
	}
	function getallpuchasebysupplierAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db_com = new Incexp_Model_DbTable_DbExpensePayment();
			$id = $db_com->getPurchaseBySupplier($data);
			print_r(Zend_Json::encode($id));
			exit();
		}
	}
	function getallpuchasebysuppliereditAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db_com = new Incexp_Model_DbTable_DbExpensePayment();
			$id = $db_com->getPurchaseBySupplierEdit($data);
			print_r(Zend_Json::encode($id));
			exit();
		}
	}
	
}