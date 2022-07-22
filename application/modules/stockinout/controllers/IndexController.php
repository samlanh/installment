<?php
class Stockinout_IndexController extends Zend_Controller_Action {
	public function init()
	{
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		//$rs_rows=array();
		//$db = new ();
		try{
			if(!empty($this->getRequest()->isPost())){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
					'adv_search'=>'',
					'branch_id'=>-1,
					'start_date'=> date('Y-m-d'),
					'end_date'=>date('Y-m-d'),
				);
			}
			
			//$rs_rows= $db->getAllSentSMS($search);//
			
			}catch (Exception $e){
				Application_Form_FrmMessage::message("Application Error");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
			$list = new Application_Form_Frmtable();
			$collumns = array("CREATE_DATE","BY_USER");
			$link=array('module'=>'','controller'=>'','action'=>'edit');
			$this->view->list=$list->getCheckList(10, $collumns,$rs_rows,array(''=>$link));
// 			$frm = new Application_Form_FrmAdvanceSearch();
// 			$frm = $frm->AdvanceSearch();
// 			Application_Model_Decorator::removeAllDecorator($frm);
// 			$this->view->frm_search = $frm;
		
	}
	function addAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {		
				//$db = new Loan_Model_DbTable_DbCancel();
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","");
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

