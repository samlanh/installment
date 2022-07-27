<?php
class Stockinout_IndexController extends Zend_Controller_Action {
	public function init()
	{
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		$rs_rows=array();
		$db = new Stockinout_Model_DbTable_DbReceiveStock();
		try{
			if(!empty($this->getRequest()->isPost())){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
					'adv_search'=>'',
					'branch_id'=>-1,
					'status'=>-1,
					'supplierId'=>-1,
					'start_date'=> date('Y-m-d'),
					'end_date'=>date('Y-m-d'),
				);
			}
			
			$rs_rows= $db->getAllReceiveStock($search);
			
			}catch (Exception $e){
				Application_Form_FrmMessage::message("Application Error");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
			
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH_NAME","DOCUMENT_RECEIV_TYPE","DNORIV_NO","DELIVER","TRUCK_NUMBER","COUNTER","RECEIVE_DATE","SUPPLIER_NAME","PO_NO","REQUEST_NO","USER","STATUS");
			$link=array('module'=>'stockinout','controller'=>'index','action'=>'edit');
			$this->view->list=$list->getCheckList(10, $collumns,$rs_rows,array(''=>$link));
			
			$frm_search = new Application_Form_FrmAdvanceSearchStock();
			$frm = $frm_search->AdvanceSearch();
			Application_Model_Decorator::removeAllDecorator($frm);
			$this->view->frm_search = $frm;
	}
	function addAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {		
				$db = new Stockinout_Model_DbTable_DbReceiveStock();
				$db->addReceiveStock($_data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/stockinout/index/add");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
			$fm = new Stockinout_Form_FrmReceiveStock();
			$frm = $fm->FrmReceivStock();
			Application_Model_Decorator::removeAllDecorator($frm);
			$this->view->frmReceivStock = $frm;
	}
	function editAction(){
		//$db = new Loan_Model_DbTable_DbCancel();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		//$fm = new Loan_Form_FrmCancel();
		//$frm = $fm->FrmAddFrmCancel();
		//Application_Model_Decorator::removeAllDecorator($frm);
		//$this->view->frm_loan = $frm;
		$id = $this->getRequest()->getParam('id');
		$id = empty($id)?0:$id;
		if(empty($id)){
			Application_Form_FrmMessage::Sucessfull("NO_DATA","//");
		}
	}
	function verifyAction(){
		$id = $this->getRequest()->getParam('id');
		$id = empty($id)?0:$id;
		if(empty($id)){
			Application_Form_FrmMessage::Sucessfull("NO_DATA","//");
		}
		$db = new Stockinout_Model_DbTable_DbReceiveStock();
		$rs = $db->getDNById($id);
		$this->view->rsRow = $rs;
	
	}
	function getallproductbypoAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db_com = new Stockinout_Model_DbTable_DbReceiveStock();
			$id = $db_com->getAllProductByPO($data);
			print_r(Zend_Json::encode($id));
			exit();
		}
	}
}

