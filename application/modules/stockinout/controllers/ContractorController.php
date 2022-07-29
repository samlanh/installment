<?php
class Stockinout_ContractorController extends Zend_Controller_Action {
	public function init()
	{
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		$rs_rows=array();
		$db = new Stockinout_Model_DbTable_DbContractor();
		try{
			if(!empty($this->getRequest()->isPost())){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
					'adv_search'=>'',
					'branch_id'=>-1,
					'status'=>-1,
					'start_date'=> date('Y-m-d'),
					'end_date'=>date('Y-m-d'),
				);
			}
			
			$rs_rows= $db->getAllStaffWorker($search);
			
			}catch (Exception $e){
				Application_Form_FrmMessage::message("Application Error");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH_NAME","WORKER_NAME","GENDER","ADDRESS","TEL","POSITION","CREATE_DATE","BY_USER","STATUS");
			$link=array('module'=>'stockinout','controller'=>'contractor','action'=>'edit');
			$this->view->list=$list->getCheckList(10, $collumns,$rs_rows,array('branch_name'=>$link,'staffName'=>$link,'gender'=>$link));
			
			$frm = new Application_Form_FrmAdvanceSearch();
			$frm = $frm->AdvanceSearch();
			Application_Model_Decorator::removeAllDecorator($frm);
			$this->view->frm_search = $frm;
	}
	function addAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {		
				$db = new Stockinout_Model_DbTable_DbContractor();
				$db->addWorker($_data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/stockinout/contractor/add");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$fm = new Stockinout_Form_FrmStaffWorker();
		$frm = $fm->FrmWorker();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frmWorker = $frm;
	}
	function editAction(){
		$db = new Stockinout_Model_DbTable_DbContractor();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$db->updateWorker($_data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/stockinout/contractor");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$id = $this->getRequest()->getParam('id');
		$id = empty($id)?0:$id;
		if(empty($id)){
			Application_Form_FrmMessage::Sucessfull("NO_DATA","/stockinout/contractor/index");
		}
		
		$row = $db->getDataRow($id);
		
		$fm = new Stockinout_Form_FrmStaffWorker();
		$frm = $fm->FrmWorker($row);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frmWorker = $frm;
		
	}
	function getallcontractorAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db_com = new Application_Model_DbTable_DbGlobalStock();
			$id = $db_com->getAllContractorbyBranch($data);
			print_r(Zend_Json::encode($id));
			exit();
		}
	}
}

