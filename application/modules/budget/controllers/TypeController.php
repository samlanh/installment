<?php
class Budget_TypeController extends Zend_Controller_Action {
	public function init()
	{
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		$db = new Budget_Model_DbTable_DbBudgetType();
		$rs_rows= array();
		try{
			if(!empty($this->getRequest()->isPost())){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
					'adv_search'=>'',
					'status'	=>-1,
					'start_date'=> date('Y-m-d'),
					'end_date'=>date('Y-m-d'),
				);
				}
				$rs_rows = $db->getAllBudgetType($search);//
			}catch (Exception $e){
				Application_Form_FrmMessage::message("Application Error");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
			
			$list = new Application_Form_Frmtable();
			$collumns = array("PARENT_BUDGET_TYPE","BUDGET_TYPE_TITLE","CREATE_DATE","BY_USER","STATUS");
			$link=array('module'=>'budget','controller'=>'type','action'=>'edit');
			$this->view->list=$list->getCheckList(10, $collumns,$rs_rows,array('parentTitle'=>$link,'budgetTitle'=>$link));
			
			$frm = new Application_Form_FrmAdvanceSearchStock();
			$frm = $frm->AdvanceSearch();
			Application_Model_Decorator::removeAllDecorator($frm);
			$this->view->frm_search = $frm;
	}
	function addAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {		
				$db = new Budget_Model_DbTable_DbBudgetType();
				$db->addBudgetType($_data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/budget/type/add");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$fm = new Budget_Form_FrmBudgetType();
		$frm = $fm->FrmAddBudgetType();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frmBudget = $frm;
	}
	function editAction(){
		$db = new Budget_Model_DbTable_DbBudgetType();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$db->updateBudgetType($_data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/budget/type/index");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("UPDATE_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		$id = $this->getRequest()->getParam('id');
		$id = empty($id)?0:$id;
		if(empty($id)){
			Application_Form_FrmMessage::Sucessfull("NO_DATA","/budget/type");
		}
		
		$result = $db->getDataRow($id);
		
		$fm = new Budget_Form_FrmBudgetType();
		$frm = $fm->FrmAddBudgetType($result);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frmBudget = $frm;
	}
}

