<?php

class Incexp_ExpenseController extends Zend_Controller_Action
{
	const REDIRECT_URL = '/incexp/expense';
	
    public function init()
    {
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }

    public function indexAction()
    {
    	try{
    		$db = new Incexp_Model_DbTable_DbExpense();
    		if($this->getRequest()->isPost()){
    			$formdata=$this->getRequest()->getPost();
    		}
    		else{
    			$formdata = array(
    					"adv_search"=>'',
    					"branch_id"=>-1,
     					"category_id_expense"=>-1,
    					'payment_type'=>-1,
    					'supplier_id'=>'',
    					'start_date'=> date('Y-m-d'),
    					'end_date'=>date('Y-m-d'),
    			);
    		}
    		$this->view->adv_search = $formdata;
			$rs_rows= $db->getAllExpense($formdata);//call frome model
    		$glClass = new Application_Model_GlobalClass();
    		$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
    		$list = new Application_Form_Frmtable();
    		$collumns = array("BRANCH_NAME","SUPPLIER","EXPENSE_TITLE","RECEIPT_NO","EXPENSE_FOR","CATEGORY","TOTAL_EXPENSE",
    					"NOTE","DATE","BY_USER","STATUS");
    		$link=array(
    				'module'=>'incexp','controller'=>'expense','action'=>'edit',
    		);
    		$this->view->list=$list->getCheckList(0, $collumns,$rs_rows,array('branch_name'=>$link,'supplier'=>$link,'title'=>$link,'invoice'=>$link));
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    	$frm = new Loan_Form_FrmSearchLoan();
    	$frm = $frm->AdvanceSearch();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_search = $frm;
    	
    	$pructis=new Incexp_Form_Frmexpense();
    	$frm = $pructis->FrmAddExpense();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_expense=$frm;
    }
    public function addAction()
    {
    	if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();	
			$db = new Incexp_Model_DbTable_DbExpense();	
			$test = $db->getBranchId();
			try {
				$db->addExpense($data);
				if(!empty($data['saveclose'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/incexp/expense");
				}else{
					Application_Form_FrmMessage::message("INSERT_SUCCESS");
				}				
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
    	$pructis=new Incexp_Form_Frmexpense();
    	$frm = $pructis->FrmAddExpense();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_expense=$frm;
    	
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$db = new Incexp_Model_DbTable_DbExpense();
    	$result = $db->getAllExpenseCategory();
    	array_unshift($result, array ( 'id' => -1,'name' => $tr->translate("ADD_NEW")));
    	$this->view->all_category = $result;
    	
    	$key = new Application_Model_DbTable_DbKeycode();
    	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
    	
    	$frmpopup = new Application_Form_FrmPopupGlobal();
    	$this->view->footer = $frmpopup->getFooterReceipt();
    }
    public function editAction()
    {
    	$id = $this->getRequest()->getParam('id');
    	if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();	
			$data['id'] = $id;
			$db = new Incexp_Model_DbTable_DbExpense();				
			try {
				$db->updatExpense($data);				
				Application_Form_FrmMessage::Sucessfull('UPDATE_SUCESS', self::REDIRECT_URL);		
			} catch (Exception $e) {
				$this->view->msg = 'UPDATE_FAIL';
			}
		}
		$id = $this->getRequest()->getParam('id');
		$db = new Incexp_Model_DbTable_DbExpense();
		$row  = $db->getexpensebyid($id);
		$this->view->row = $row;
		
    	$pructis=new Incexp_Form_Frmexpense();
    	$frm = $pructis->FrmAddExpense($row);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_expense=$frm;
		
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$db = new Incexp_Model_DbTable_DbExpense();
    	$result = $db->getAllExpenseCategory();
    	array_unshift($result, array ( 'id' => -1,'name' => $tr->translate("ADD_NEW")));
    	$this->view->all_category = $result;
    	
    	$key = new Application_Model_DbTable_DbKeycode();
    	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
    	
    	$frmpopup = new Application_Form_FrmPopupGlobal();
    	$this->view->footer = $frmpopup->getFooterReceipt();
    }

    
    function addCategoryAction(){
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$db = new Incexp_Model_DbTable_DbIncome();
    		$ex_rate = $db->AddNewCategory($data,2);
    		print_r(Zend_Json::encode($ex_rate));
    		exit();
    	}
    }
    function getsupplierAction(){
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$db = new Application_Model_DbTable_DbGlobal();
    		$rows = $db->getAllSupplier();
    		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    		array_unshift($rows,array ( 'id' => "",'name' => $tr->translate("SELECT_SUPPLIER")), array ( 'id' => -1,'name' => $tr->translate("ADD_NEW")));
    		print_r(Zend_Json::encode($rows));
    		exit();
    	}
    }
    
//     function getAllCustomerAction(){
//     	if($this->getRequest()->isPost()){
//     		$data = $this->getRequest()->getPost();
//     		$db = new Incexp_Model_DbTable_DbIncome();
//     		$result = $db->getAllCustomer($data['branch_id']);
//     		array_unshift($result, array ( 'id' => -1, 'name' => 'Select Customer') );
//     		print_r(Zend_Json::encode($result));
//     		exit();
//     	}
//     }		Incexp_Model_DbTable_DbExpense
    
    function getInvoiceNoAction(){
    	 
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$_db = new Incexp_Model_DbTable_DbExpense();
    		$result = $_db->getInvoiceNo($data['branch_id']);
    		//array_unshift($result, array ( 'id' => -1, 'name' => 'Select Customer') );
    		print_r(Zend_Json::encode($result));
    		exit();
    	}
    	 
    }
    
    
    
    
    
}







