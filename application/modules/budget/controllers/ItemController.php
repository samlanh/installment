<?php
class Budget_ItemController extends Zend_Controller_Action {
	public function init()
	{
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		$db = new Budget_Model_DbTable_DbbudgetItem();
		$rs_rows=array();
		
		try{
			if(!empty($this->getRequest()->isPost())){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
					'adv_search'=>'',
					'status'=>-1,
					'start_date'=> date('Y-m-d'),
					'end_date'=>date('Y-m-d'),
				);
			}
			
			$rs_rows= $db->getAllBudgetItems($search);//
			
			}catch (Exception $e){
				Application_Form_FrmMessage::message("Application Error");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
			
			$list = new Application_Form_Frmtable();
			$collumns = array("BUDGET_ITEM","PARENT_BUDGET_ITEM","BUDGET_TYPE","CREATE_DATE","BY_USER","STATUS");
			$link=array('module'=>'budget','controller'=>'item','action'=>'edit');
			$this->view->list=$list->getCheckList(10, $collumns,$rs_rows,array('budgetTitle'=>$link,'budgetType'=>$link));
			
			$frm = new Application_Form_FrmAdvanceSearchStock();
			$frm = $frm->AdvanceSearch();
			Application_Model_Decorator::removeAllDecorator($frm);
			$this->view->frm_search = $frm;
		
	}
	function addAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {		
				$db = new Budget_Model_DbTable_DbbudgetItem();
				$db->addBudgetItem($_data);
				//Application_Form_FrmMessage::message("INSERT_SUCCESS");
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/budget/item/");
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
		$db = new Budget_Model_DbTable_DbbudgetItem();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$db->updateBudgetItem($_data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/budget/item/index");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("UPDATE_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		$id = $this->getRequest()->getParam('id');
		$id = empty($id)?0:$id;
		if(empty($id)){
			Application_Form_FrmMessage::Sucessfull("NO_DATA","/budget/item",2);
		}
		
		$result = $db->getDataRow($id);
		
		$fm = new Budget_Form_FrmBudgetType();
		$frm = $fm->FrmAddBudgetItem($result);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frmBudget = $frm;
	}
	function getAllbudgetitemAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			
			$db = new  Application_Model_DbTable_DbGlobalStock();
			$results =  $db->getAllBudgetItem($parent = 0, $spacing = '', $cate_tree_array = '',null,$data);
			
			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
				
			array_unshift($results, array ('id' => 0,'name' =>$tr->translate("SELECT_BUDGET_ITEM")));
			
			if(!empty($data['noBtnNew'])){
			}else{
				array_unshift($results, array ('id' => -1,'name' =>$tr->translate("ADD_NEW")));
			}
			
			print_r(Zend_Json::encode($results));
			exit();
		}
	}
}

