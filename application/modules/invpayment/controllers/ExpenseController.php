<?php
class Invpayment_ExpenseController extends Zend_Controller_Action
{
	const REDIRECT_URL = '/po/expense';
	
    public function init()
    {
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }
    public function indexAction()
    {
    	try{
    		$db = new Invpayment_Model_DbTable_DbExpense();
    		if($this->getRequest()->isPost()){
    			$formdata=$this->getRequest()->getPost();
    		}
    		else{
    			$formdata = array(
    					"advanceFilter"=>'',
    				//	"paymentMethod"=>-1,
    					// "status"=>-1,
    					 "branch_id"=>-1,
    					'start_date'=> date('Y-m-d'),
    					'end_date'=>date('Y-m-d'),
    			);
    		}
    		
    		$this->view->adv_search = $formdata;
    		
			$rs_rows= $db->getAllExpense($formdata);//call frome model
    		$list = new Application_Form_Frmtable();
    		$collumns = array("BRANCH_NAME","EXPENSE_TITLE","BUDGET_ITEM","RECEIPT_NO","RECEIVER","PAYMENT_METHOD","BANK_NAME","ACCOUNT_AND_CHEQUE_NO","TOTAL_EXPENSE","DATE","STATUS");
    		$link=array(
    				'module'=>'invpayment','controller'=>'expense','action'=>'edit',
    		);
    		$this->view->list=$list->getCheckList(10, $collumns,$rs_rows,array('projectName'=>$link,'expenseTitle'=>$link,'budgetItem'=>$link,'paymentNo'=>$link));
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
		$form = new Invpayment_Form_FrmExpense();
    	$frm = $form->FrmExpense();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_search = $frm;
    }
    public function addAction()
    {
    	if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();	
			$db = new Invpayment_Model_DbTable_DbExpense();				
			try {
				$db->addExpense($data);
				if(!empty($data['savenew'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/invpayment/expense/add");
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/invpayment/expense");
				}				
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}

    	$pructis=new Invpayment_Form_Frmexpense();
    	$frm = $pructis->FrmExpense();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_expense=$frm;
    	$db = new Application_Model_GlobalClass();
    	$this->view->expenseopt = $db->getAllExpenseIncomeType(5);
    	
    	$db = new Invpayment_Model_DbTable_DbCateExpense();
    	$this->view->parent = $db->getParentCateExpense();
    	
    //	$_db = new Application_Form_FrmGlobal();
    //	$this->view->header = $_db->getHeaderReceipt();
    }
    public function editAction()
    {
    	$id = $this->getRequest()->getParam('id');
    	if($this->getRequest()->isPost()){
			try{
				$data = $this->getRequest()->getPost();
				$db = new Invpayment_Model_DbTable_DbExpense();	
				$db->updateData($data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/invpayment/expense");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("EDIT_FAIL");
		
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				echo $e->getMessage();
			}
		}

		$id = $this->getRequest()->getParam('id');
		$db = new Invpayment_Model_DbTable_DbExpense();
		$row  = $db->getexpensebyid($id);
		$this->view->row = $row;
		$this->view->rows = $db->getexpenseDetailbyid($id);

    	$pructis=new Invpayment_Form_Frmexpense();
    	$frm = $pructis->FrmExpense($row);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_expense=$frm;
    	$dbg = new Application_Model_GlobalClass();
    	$this->view->expenseopt = $dbg->getAllExpenseIncomeType(5);
    	
    	$dbe = new Invpayment_Model_DbTable_DbCateExpense();
    	$this->view->parent = $dbe->getParentCateExpense();
    }
    function getReceiptNumberAction(){
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$db = new Registrar_Model_DbTable_DbIncome();
    		$invoice = $db->getReceiptNumber($data['branch_id'],2);
    		print_r(Zend_Json::encode($invoice));
    		exit();
    	}
    }
    function addCateExpenseAction(){
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$db = new Invpayment_Model_DbTable_DbExpense();
    		$id = $db->addCateExpense($data);
    		print_r(Zend_Json::encode($id));
    		exit();
    	}
    }
}