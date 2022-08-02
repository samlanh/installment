<?php
class Budget_SetupController extends Zend_Controller_Action {
	public function init()
	{
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		$db = new Budget_Model_DbTable_DbInitilizeBudget();
		$rs_rows=array();
		try{
			if(!empty($this->getRequest()->isPost())){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
					'adv_search'=>'',
					'branch_id'=>-1,
					'budgetItem'=>0,
					'budgetType'=>0,
					'status'	=>-1,
					'start_date'=> date('Y-m-d'),
					'end_date'=>date('Y-m-d'),
				);
			}
			
			$rs_rows= $db->getAllBudgetProject($search);
			
			}catch (Exception $e){
				Application_Form_FrmMessage::message("Application Error");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
			
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH_NAME","BUDGET_TYPE","BUDGET_ITEM","INITIALIZE_BUDGET_AMOUNT","BUDGET_AMT_ALERT","CREATE_DATE","BY_USER","STATUS");
			$link=array('module'=>'','controller'=>'','action'=>'edit');
			$this->view->list=$list->getCheckList(10, $collumns,$rs_rows,array(''=>$link));
			
			$frm = new Application_Form_FrmAdvanceSearch();
			$frm = $frm->AdvanceSearch();
			Application_Model_Decorator::removeAllDecorator($frm);
			$this->view->frm_search = $frm;
			
			$db = new  Application_Model_DbTable_DbGlobalStock();
			$results =  $db->getAllBudgetItem($parent = 0, $spacing = '', $cate_tree_array = '',null,null);
				
			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
			array_unshift($results, array ('id' => 0,'name' =>$tr->translate("PLEASE_SELECT_BUDGET")));
				
			$this->view->budgetItem = $results;
			
			$rsType =  $db->getAllBudgetType($parent = 0, $spacing = '', $cate_tree_array = '',null,null);
			
			array_unshift($rsType, array ('id' => 0,'name' =>$tr->translate("PLEASE_SELECT_BUDGET")));
			$this->view->budgetType = $rsType;
			
			$this->view->datSearch = $search;
	}
	function addAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {		
				$db = new Budget_Model_DbTable_DbInitilizeBudget();
				$db->addAmountBudgetItem($_data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/budget/setup/add");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$fm = new Budget_Form_FrmBudgetType();
		$frm = $fm->FrmAddBudgetItem();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frmBudget = $frm;
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
}

