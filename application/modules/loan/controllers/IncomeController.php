<?php

class Loan_IncomeController extends Zend_Controller_Action
{
	//const REDIRECT_URL = '/loan/expense';
	
    public function init()
    {
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }
    public function indexAction()
    {
    	try{
    		$db = new Loan_Model_DbTable_DbIncome();
    		if($this->getRequest()->isPost()){
    			$search=$this->getRequest()->getPost();
    		}
    		else{
    			$search = array(
    					"adv_search"=>'',
    					"branch_id"=>-1,
    					"category_id"=>'',
    					'start_date'=> date('Y-m-d'),
    					'end_date'=>date('Y-m-d'),
    					'land_id'=>-1,
    					'client_name'=>-1,
    			);
    		}
    		$this->view->adv_search = $search;
			$rs_rows= $db->getAllIncome($search);//call frome model
    		$glClass = new Application_Model_GlobalClass();
    		$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
    		$list = new Application_Form_Frmtable();
    		$collumns = array("BRANCH_NAME","CUSTOMER_NAME","PROPERTY_CODE","INCOME_TITLE","RECEIPT_NO","CATEGORY","TOTAL_INCOME","NOTE","DATE","BY_USER","STATUS",'PRINT');
    		$link=array('module'=>'loan','controller'=>'income','action'=>'edit');
    		$link1=array('module'=>'report','controller'=>'loan','action'=>'receipt-otherincome');
    		$this->view->list=$list->getCheckList(0, $collumns,$rs_rows,array('បោះពុម្ភ'=>$link1,'branch_name'=>$link,'client_name'=>$link,'title'=>$link,'invoice'=>$link));
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		echo $e->getMessage();
    	}
//     	$frm = new Loan_Form_Frmexpense();
//     	Application_Model_Decorator::removeAllDecorator($frm);
//     	$this->view->frm_search = $frm;
    	
    	$frm = new Loan_Form_FrmSearchLoan();
    	$frm = $frm->AdvanceSearch();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_search = $frm;
    	$this->view->rssearch = $search;
    }
    public function addAction()
    {
    	if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();	
			$db = new Loan_Model_DbTable_DbIncome();				
			try {
				$db->addIncome($data);
				if(!empty($data['saveclose'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/loan/income");
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/loan/income/add");
				}				
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$db = new Loan_Model_DbTable_DbIncome();
		$result = $db->getAllIncomeCategory();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		array_unshift($result, array ( 'id' => -1,'name' => $tr->translate("ADD_NEW")));
		$this->view->all_category = $result;
		
    	$pructis=new Loan_Form_Frmexpense();
    	$frm = $pructis->FrmAddExpense();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_expense=$frm;
    	
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
			$db = new Loan_Model_DbTable_DbIncome();				
			try {
				$db->updateIncome($data,$id);				
				Application_Form_FrmMessage::Sucessfull('UPDATE_SUCESS', "/loan/income");		
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("UPDATE_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		$db = new Loan_Model_DbTable_DbIncome();
		$row  = $db->getexpensebyid($id);
		$row['payment_id']=0;
		$this->view->row = $row;
		
    	$pructis=new Loan_Form_Frmexpense();
    	$frm = $pructis->FrmAddExpense($row);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_expense=$frm;
    	
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$db = new Loan_Model_DbTable_DbIncome();
    	$result = $db->getAllIncomeCategory();
    	array_unshift($result, array ( 'id' => -1,'name' => $tr->translate("ADD_NEW")));
    	$this->view->all_category = $result;
    	
    	$key = new Application_Model_DbTable_DbKeycode();
    	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
    	
    	$frmpopup = new Application_Form_FrmPopupGlobal();
    	$this->view->footer = $frmpopup->getFooterReceipt();
    }
    
    function getRateAction(){
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
	    	$db = new Loan_Model_DbTable_DbIncome();
	    	$ex_rate = $db->getExchangeRate();
	    	//array_unshift($makes, array ( 'id' => -1, 'name' => 'បន្ថែមថ្មី') );
	    	print_r(Zend_Json::encode($ex_rate));
	    	exit();
    	}
    }
    function getparentbyidAction(){
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$db = new Loan_Model_DbTable_DbIncome();
    		$cate_id = "";
    		if(!empty($data['cateid'])){
    			$cate_id = $data['cateid'];
    		}
    		$parentrs = $db->getAllIncomeCategoryParent($data['type'],$cate_id);
    		print_r(Zend_Json::encode($parentrs));
    		exit();
    	}
    }
    function addCategoryAction(){
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$db = new Loan_Model_DbTable_DbIncome();
    		$ex_rate = $db->AddNewCategory($data,1);
    		//array_unshift($makes, array ( 'id' => -1, 'name' => 'បន្ថែមថ្មី') );
    		print_r(Zend_Json::encode($ex_rate));
    		exit();
    	}
    }
    
    function getAllCustomerAction(){
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$db = new Loan_Model_DbTable_DbIncome();
    		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    		$result = $db->getAllCustomer($data['branch_id']);
    		array_unshift($result, array ( 'id' => -1, 'name' => $tr->translate("CHOOSE_CUSTOEMR")) );
    		print_r(Zend_Json::encode($result));
    		exit();
    	}
    }

    function getInvoiceNoAction(){
    	
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$db = new Loan_Model_DbTable_DbIncome();
    		$result = $db->getInvoiceNo($data['branch_id']);
    		//array_unshift($result, array ( 'id' => -1, 'name' => 'Select Customer') );
    		print_r(Zend_Json::encode($result));
    		exit();
    	}
    	
    }
    
    
    
}







