<?php
class Stock_CustomerPaymentController extends Zend_Controller_Action {
	protected $tr;
	public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();
	}
	
	public function indexAction(){
		try{
			if($this->getRequest()->isPost()){
    			$search = $this->getRequest()->getPost();
    		}
    		else{
    			$search=array(
    					'title'	        =>	'',
    					'cus_name'		=>	0,
    					'status_search'	=> 1
				);
    		}
			$db = new Accounting_Model_DbTable_DbCustomerPayment();
			$rs_rows = $db->getAllCustomer($search);
			
			$list = new Application_Form_Frmtable();
    		$collumns = array("CUS_ID","RECEIPT_NO","CUS_NAME","PHONE","EMAIL","START_DATE","END_DATE","STATUS_PAID","USER","STATUS","PRINT");
    		$link=array(
    				'module'=>'stock','controller'=>'customerpayment','action'=>'edit',
    		);
    		$link1=array(
    				'module'=>'stock','controller'=>'customerpayment','action'=>'view',
    		);
    		$this->view->list=$list->getCheckList(0, $collumns, $rs_rows , array('customer_code'=>$link,'rent_receipt_no'=>$link,'first_name'=>$link,'phone'=>$link,'view'=>$link1));
			
			$db = new Registrar_Model_DbTable_DbRegister();
			$this->view->all_student_name = $db->getAllGerneralOldStudentName();
			$this->view->all_student_code = $db->getAllGerneralOldStudent();
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		
		$frm_major = new Accounting_Form_FrmSearchMajor();
		$frm_search = $frm_major->FrmMajors();
		Application_Model_Decorator::removeAllDecorator($frm_search);
		$this->view->frm_search = $frm_search;
		
	}
	
    public function addAction()
    {
    	$db = new Accounting_Model_DbTable_DbCustomerPayment();
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		try {
    			$db->addCusPayment($_data);
    			if(!empty($_data['save_close'])){
    				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", "/stock/customerpayment/index");
    			}else{
    				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", "/stock/customerpayment/add");
    			}
    		} catch (Exception $e) {
    			Application_Form_FrmMessage::message($this->tr->translate('INSERT_FAIL'));
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    			echo $e->getMessage();
    		}
    	}
    	
    	$this->view->cus_id=$db->getCusId();
    	$this->view->receipt_no=$db->getReceiptNo();
    	$this->view->cus=$db->getOldCustomer();
    	$this->view->reil=$db->getReilMoney();
    }
    
	public function editAction(){
    	$db = new Accounting_Model_DbTable_DbCustomerPayment();
    	$id=$this->getRequest()->getParam('id');
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		$_data['id']=$id;
    		try {
    			$db->editCustomerPayment($_data);
    			if(!empty($_data['save_new'])){
    				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS", "/stock/customerpayment/index");
    			}else{
    				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS", "/stock/customerpayment/index");
    			}
    		} catch (Exception $e) {
    			Application_Form_FrmMessage::message($this->tr->translate('INSERT_FAIL'));
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    			echo $e->getMessage();
    		}
    	}
    	$row=$this->view->row=$db->getCustomerById($id);
    	if($row['last_piad']==0){
    		Application_Form_FrmMessage::Sucessfull($this->tr->translate('Can not edit.!!!'), "/stock/customerpayment/index");
    	}
    	$this->view->cus_id=$db->getCusId();
    	$this->view->receipt_no=$db->getReceiptNo();
    	$this->view->cus=$db->getOldCustomer();
    	$this->view->reil=$db->getReilMoney();
    }
    
    public function viewAction(){
    	$db = new Accounting_Model_DbTable_DbCustomerPayment();
    	$id=$this->getRequest()->getParam('id');
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		$_data['id']=$id;
    		try {
    			$db->editCustomerPayment($_data);
    			if(!empty($_data['save_new'])){
    				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", "/stock/customerpayment/index");
    			}else{
    				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", "/stock/customerpayment/index");
    			}
    		} catch (Exception $e) {
    			Application_Form_FrmMessage::message($this->tr->translate('INSERT_FAIL'));
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    			echo $e->getMessage();
    		}
    	}
    	$row=$this->view->row=$db->getCustomerById($id);
//     	if($row['last_piad']==0){
//     		Application_Form_FrmMessage::Sucessfull($this->tr->translate('Can not edit.!!!'), "/accounting/customerpayment/index");
//     	}
    	$this->view->cus_id=$db->getCusId();
    	$this->view->receipt_no=$db->getReceiptNo();
    	$this->view->cus=$db->getOldCustomer();
    	$this->view->reil=$db->getReilMoney();
    }
    
    function getCustomerInfoAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Accounting_Model_DbTable_DbCustomerPayment();
    		$cus_info= $db->getCustomerInfo($data['cus_id']);
    		print_r(Zend_Json::encode($cus_info));
    		exit();
    	}
    }
}
