<?php
class Invpayment_BankController extends Zend_Controller_Action {
	const REDIRECT_URL = '/invpayment/bank';
	public function init()
	{
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		//$db = new ();
		try{
			$db = new Invpayment_Model_DbTable_DbBank();
			if(!empty($this->getRequest()->isPost())){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
					'adv_search'=>'',
					'branch_id'=>-1,
					'status'=>-1,
					'start_date'=> "",
					'end_date'=>date('Y-m-d'),
				);
			}
			$rs_rows=array();
			$rs_rows= $db->getAllBank($search);//
			
			$list = new Application_Form_Frmtable();
    		$collumns = array("BANK_NAME","CREATE_DATE","BY","STATUS");
    		$link=array(
    				'module'=>'invpayment','controller'=>'bank','action'=>'edit',
    		);
    		$this->view->list=$list->getCheckList(10, $collumns, $rs_rows , array('bank_name'=>$link,'supplierTel'=>$link,));
			
			}catch (Exception $e){
				Application_Form_FrmMessage::message("Application Error");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
			
		$frm_search = new Application_Form_FrmAdvanceSearchStock();
		$frm = $frm_search->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
		
	}
	function addAction(){

		$db = new Invpayment_Model_DbTable_DbBank();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {		
				
				
				$db->addBank($_data);
	    		Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL."/index");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
    	$frm = new Invpayment_Form_FrmCheque();
    	$frm->FrmBank(null);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm = $frm;
		
	}
	function editAction(){
		
		$db = new Invpayment_Model_DbTable_DbBank();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {		
				
				$db->updateBank($_data);
	    		Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS",self::REDIRECT_URL."/index");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$id = $this->getRequest()->getParam('id');
		$id = empty($id)?0:$id;
		if(empty($id)){
			Application_Form_FrmMessage::Sucessfull("NO_DATA",self::REDIRECT_URL."/index",2);
			exit();
		}
		
		$row = $db->getDataRow($id);
		$this->view->row = $row;

    	$frm = new Invpayment_Form_FrmCheque();
    	$frm->FrmBank($row);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm = $frm;
		
	}
}

