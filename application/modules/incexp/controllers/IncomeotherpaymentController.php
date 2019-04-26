<?php

class Incexp_IncomeOtherpaymentController extends Zend_Controller_Action
{
	//const REDIRECT_URL = '/incexp/expense';
	
    public function init()
    {
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }
    public function indexAction()
    {
    	try{
    		$db = new Incexp_Model_DbTable_DbIncomeOtherPayment();
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
    					'type'=>'-1'
    			);
    		}
    		$this->view->adv_search = $search;
			$rs_rows= $db->getAllIncomePayment($search);//call frome model
    		$glClass = new Application_Model_GlobalClass();
    		$rs_row = $glClass->getImgActive($rs_rows, BASE_URL, true);
    		$list = new Application_Form_Frmtable();
    		$collumns = array("BRANCH_NAME","CUSTOMER_NAME","PROPERTY_CODE","TITLE","RECEIPT","TYPE","CATEGORY","BALANCE","TOTAL_PAID","REMAIN","PAYMENT_TYPE","DATE","BY_USER","STATUS");
    		$link=array('module'=>'incexp','controller'=>'incomeotherpayment','action'=>'edit');
    		$this->view->list=$list->getCheckList(0, $collumns,$rs_row,array('branch_name'=>$link,'client_name'=>$link,'house_no'=>$link));
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
    	
    	$frm = new Incexp_Form_FrmSearchLoanType();
    	$frm = $frm->AdvanceSearch();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_searchloantype = $frm;
    }
    public function addAction()
    {
    	$db = new Incexp_Model_DbTable_DbIncomeOtherPayment();
    	if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();	
			try {
				$db->addIncome($data);
				if(!empty($data['saveclose'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/incexp/incomeotherpayment");
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/incexp/incomeotherpayment/add");
				}				
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
    	$pructis=new Incexp_Form_FrmOtherIncomePayment();
    	$frm = $pructis->FrmAddIncomeother();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_incomeotherpayment=$frm;
    	
    	$key = new Application_Model_DbTable_DbKeycode();
    	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
    	$frmpopup = new Application_Form_FrmPopupGlobal();
    	$this->view->footer = $frmpopup->getFooterReceipt();
    }
 
    public function editAction()
    {
    	$id = $this->getRequest()->getParam('id');
    	$id = empty($id)?0:$id;
    	$db = new Incexp_Model_DbTable_DbIncomeOtherPayment();
    	if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();	
			try {
				$db->editIncomePayment($data);
				Application_Form_FrmMessage::Sucessfull("UPDATE_SUCCESS","/incexp/incomeotherpayment");			
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("UPDATE_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$row = $db->getOtherIncomePaymentById($id);
		$this->view->row = $row;
		if(empty($row)){
			Application_Form_FrmMessage::Sucessfull("RECORD_NOTFUND","/incexp/incomeotherpayment");
			exit();
		}
    	$pructis=new Incexp_Form_FrmOtherIncomePayment();
    	$frm = $pructis->FrmAddIncomeother($row);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_incomeotherpayment=$frm;
    	
    	$key = new Application_Model_DbTable_DbKeycode();
    	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
    	$frmpopup = new Application_Form_FrmPopupGlobal();
    	$this->view->footer = $frmpopup->getFooterReceipt();
    }
    public function getAllotherincomeAction(){
   		if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$db = new Incexp_Model_DbTable_DbIncomeOtherPayment();
    		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    		$result = $db->getAllOtherIncome($data['branch_id']);
    		array_unshift($result, array ( 'id' => -1, 'name' => $tr->translate("CHOOSE_CUSTOEMR")) );
    		print_r(Zend_Json::encode($result));
    		exit();
    	}
    }
    public function getAllotherincomeeditAction(){
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$db = new Incexp_Model_DbTable_DbIncomeOtherPayment();
    		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    		$result = $db->getAllOtherIncome($data['branch_id'],$data['otherincome_id']);
    		array_unshift($result, array ( 'id' => -1, 'name' => $tr->translate("CHOOSE_CUSTOEMR")) );
    		print_r(Zend_Json::encode($result));
    		exit();
    	}
    }
    public function getotherincomeinfoAction(){
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$db = new Incexp_Model_DbTable_DbIncomeOtherPayment();
    		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    		$result = $db->getotherincomeinfo($data['otherincome_id']);
    		print_r(Zend_Json::encode($result));
    		exit();
    	}
    }
    
    public function getCategorybytypeAction(){
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    		$db = new Incexp_Model_DbTable_DbIncome();
    		$result = $db->getAllIncomeCategory($data['cate_type']);
    		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
//     		array_unshift($result, array ( 'id' => -1,'name' => $tr->translate("ADD_NEW")));
    		
    		print_r(Zend_Json::encode($result));
    		exit();
    	}
    }
    
//     function getInvoiceNoAction(){
    	 
//     	if($this->getRequest()->isPost()){
//     		$data = $this->getRequest()->getPost();
//     		$db = new Incexp_Model_DbTable_DbIncomeOtherPayment();
//     		$result = $db->getInvoiceNoOtherIncomePayment($data['branch_id']);
//     		//array_unshift($result, array ( 'id' => -1, 'name' => 'Select Customer') );
//     		print_r(Zend_Json::encode($result));
//     		exit();
//     	}
    	 
//     }
}