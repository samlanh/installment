<?php
class accounting_GeneraljurnalController extends Zend_Controller_Action {
	public function init()
	{
		/* Initialize action controller here */
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
			try{
				$db = new Accounting_Model_DbTable_DbGeneraljurnal();
				if($this->getRequest()->isPost()){
					$search=$this->getRequest()->getPost();
				}
				else{
					$search = array(
							'adv_search' => '',
							'status' => -1);
				}
					
				$rs_rows= $db->getAllJurnalEntry($search);
				$list = new Application_Form_Frmtable();
				$collumns = array("BRANCH_NAME","ENTRY_CODE","INVOICE","DEBIT","CREDIT","STATUS","BY");
				$link=array(
						'module'=>'accounting','controller'=>'generaljurnal','action'=>'edit',
				);
				$this->view->list=$list->getCheckList(0, $collumns,$rs_rows,array('branch_name'=>$link,'receipt_number'=>$link,'journal_code'=>$link,'debit'=>$link,'credit'=>$link));
			}catch (Exception $e){
				Application_Form_FrmMessage::message("Application Error");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		
			$fms = new Accounting_Form_FrmChartaccount();
			$frms = $fms->FrmChartaccount();
			Application_Model_Decorator::removeAllDecorator($frms);
			$this->view->frm_chartaccount = $frms;
		
		
	}
	public function addAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$_dbmodel = new Accounting_Model_DbTable_DbJournal();
				$_dbmodel->addnewJournal($_data);
				if(!empty($_data['saveclose'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/accounting/generaljurnal");
				}else{
					Application_Form_FrmMessage::message("INSERT_SUCCESS");
				}
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$fm = new Accounting_Form_FrmGeneraljurnal();
		$frm = $fm->FrmGeneraljurnal();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_fixedasset = $frm;
		$db = new Accounting_Model_DbTable_DbJournal();
		$this->view->row_parents = $db->getAllParrentAccount(1);
		$this->view->row_accountname = $db->getAllParrentAccount(1,1);
	
	}
	public function editAction(){
		$_dbmodel = new Accounting_Model_DbTable_DbGeneraljurnal();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$_db = new Accounting_Model_DbTable_DbJournal();
				$_db->upDateJournal($_data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/accounting/generaljurnal");
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		$id = $this->getRequest()->getParam('id');
		$row  = $_dbmodel->getjurnalEntryById($id);
		$this->view->jdetail = $_dbmodel->getjurnalEntryDetail($id);
		
		$fm = new Accounting_Form_FrmGeneraljurnal();
		$frm = $fm->FrmGeneraljurnal($row);
		
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_fixedasset = $frm;
		$db = new Accounting_Model_DbTable_DbJournal();
		$this->view->row_parents = $db->getAllParrentAccount(1);
		$this->view->row_accountname = $db->getAllParrentAccount(1,1);
	
	}
	function getJcodeAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Accounting_Model_DbTable_DbJournal();
			$acc_names = $db->getJEntryCode($data['branch_id']);
			print_r(Zend_Json::encode($acc_names));
			exit();
		}
	}
	function getParentptionAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Accounting_Model_DbTable_DbJournal();
			$option = $db->getAllAccountByParrents($data['parent'],1);
			print_r(Zend_Json::encode($option));
			exit();
		}
	}
	function getParentaccountAction(){//2 request
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Accounting_Model_DbTable_DbJournal();
			$acc_names = $db->getAllAccountByParrents($data['parent']);
			print_r(Zend_Json::encode($acc_names));
			exit();
		}
	}
	function getParentidAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Accounting_Model_DbTable_DbJournal();
			$acc_names = $db->getParrentIdByAccountId($data['acccount_id']);
			print_r(Zend_Json::encode($acc_names));
			exit();
		}
	}
}
