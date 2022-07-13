<?php
class Product_IndexController extends Zend_Controller_Action {
	public function init()
	{
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		$db = new Product_Model_DbTable_DbProduct();
		$rs_rows=array();
		try{
			if(!empty($this->getRequest()->isPost())){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
					'adv_search'=>'',
					'isService'=>-1,
					'isCountStock'=>-1,
					'categoryId'=>0,
					'budgetItem'=>0,
					'measureId'=>0,
						
					'status'=>-1,
					'start_date'=> date('Y-m-d'),
					'end_date'=>date('Y-m-d'),
				);
			}
			$rs_rows= $db->getAllProductData($search);
			
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
			
			$list = new Application_Form_Frmtable();
			$collumns = array("PRODUCT_NAME","PRODUCT_CODE","BAR_CODE","PRODUCT_CATEGORY","MEASURE","SERVICE_PRODUCT",
							  "IS_COUNT_STOCK","COSTING","BUDGET_ITEM","BY_USER","CREATE_DATE","STATUS");
			$link=array('module'=>'','controller'=>'','action'=>'edit');
			$this->view->list=$list->getCheckList(10, $collumns,$rs_rows,array(''=>$link));
			
			$frm = new Application_Form_FrmAdvanceSearchStock();
			$frm = $frm->AdvanceSearch();
			Application_Model_Decorator::removeAllDecorator($frm);
			$this->view->frm_search = $frm;
			
			$frm = new Product_Form_Frmproduct();
			$frm = $frm->FrmSearchProduct();
			Application_Model_Decorator::removeAllDecorator($frm);
			$this->view->frmSearchProduct = $frm;
	}
	function addAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {		
				$db = new Product_Model_DbTable_DbProduct();
				$db->addNewProduct($_data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/product/index/add");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$frm = new Product_Form_Frmproduct();
		$frm = $frm->FrmAddProduct();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frmProduct = $frm;
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

