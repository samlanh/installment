<?php

class Loan_IncomeOtherController extends Zend_Controller_Action
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
    		$db = new Loan_Model_DbTable_DbIncomeother();
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
    					'client_name'=>-1,
    					'land_id'=>-1,
    					'payment_method'=>'',
    			);
    		}
    		$this->view->adv_search = $search;
			$rs_rows= $db->getAllIncome($search);//call frome model
    		$glClass = new Application_Model_GlobalClass();
    		$rs_row = $glClass->getImgActive($rs_rows, BASE_URL, true);
    		$list = new Application_Form_Frmtable();
    		$collumns = array("BRANCH_NAME","CUSTOMER_NAME","PROPERTY_CODE","RECEIPT_NO","TOTAL_INCOME","NOTE","DATE","BY_USER","STATUS","PRINT");
    		$link=array('module'=>'loan','controller'=>'incomeother','action'=>'edit');
    		$link1=array('module'=>'loan','controller'=>'incomeother','action'=>'description');
    		$this->view->list=$list->getCheckList(10, $collumns,$rs_row,array('បោះពុម្ភ'=>$link1,'branch_name'=>$link,'client_name'=>$link,'title'=>$link,'house_no'=>$link));
    		$this->view->row = $rs_row;
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    	
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
			if (empty($data)){
				Application_Form_FrmMessage::Sucessfull("File Attachment to large can't upload and Save data !","/loan/incomeother",2);
				exit();
			}
			$db = new Loan_Model_DbTable_DbIncomeother();				
			try {
				$db->addIncome($data);
				if(!empty($data['saveclose'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/loan/incomeother");
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/loan/incomeother/add");
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
    	$frm = $pructis->FrmAddIncomeother();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_incomeother=$frm;
    	
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
			if (empty($data)){
				Application_Form_FrmMessage::Sucessfull("File Attachment to large can't upload and Save data !","/loan/incomeother",2);
				exit();
			}
			$db = new Loan_Model_DbTable_DbIncomeother();	
			$data['old_photo']=null;
			try {
				$db->updateIncome($data,$id);				
				Application_Form_FrmMessage::Sucessfull('UPDATE_SUCESS', "/loan/incomeother");		
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		$db = new Loan_Model_DbTable_DbIncomeother();
		$row  = $db->getincomebyid($id);
		if ($row['is_fullpaid']==1){
			Application_Form_FrmMessage::Sucessfull('PAYMENT_READY', "/loan/incomeother",2);
			exit();
		}
		
		$dboinp = new Loan_Model_DbTable_DbIncomeOtherPayment();
		$check = $dboinp->checkOtherIncomeInpay($id);
		if (!empty($check)){
			Application_Form_FrmMessage::Sucessfull('HAS_SOME_PAYMENT_READY', "/loan/incomeother",2);
			exit();
		}
		$this->view->rows = $db->getincomeDetailbyid($id);
		$this->view->document=$db->getDocumentClientById($id);
		$row['payment_id']=0;
		$this->view->row = $row;
		
    	$pructis=new Loan_Form_Frmexpense();
    	$frm = $pructis->FrmAddIncomeother($row);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_income=$frm;
    	
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
    public function descriptionAction(){
    	$id=$this->getRequest()->getParam("id");
    	$db= new Loan_Model_DbTable_DbIncomeother();
		$this->view->rows = $db->getincomeDetailbyid($id);
		$this->view->rs = $db->getincomebyid($id);
    }
    public function receiptAction(){
    	$id=$this->getRequest()->getParam("id");
    	$_db= new Loan_Model_DbTable_DbIncomeother();
 		$this->view->income = $_db->getincomeDetailbyid($id);
 		$this->view->rs = $_db->getincomebyid($id);
    }
    
    function getInvoiceNoAction(){
    	 
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$db = new Loan_Model_DbTable_DbIncomeother();
    		$result = $db->getInvoiceNolnOtherincome($data['branch_id']);
    		//array_unshift($result, array ( 'id' => -1, 'name' => 'Select Customer') );
    		print_r(Zend_Json::encode($result));
    		exit();
    	}
    	 
    }
}