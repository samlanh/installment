<?php
class Product_InitqtyController extends Zend_Controller_Action {
	const REDIRECT_URL = '/product/initqty';
	public function init()
	{
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		$db = new Product_Model_DbTable_DbinitilizeQtybyProject();
		$rs_rows=array();
		try{
			
			if(!empty($this->getRequest()->isPost())){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
					'adv_search'=>'',
					'branch_id'=>-1,
					'isCountStock'=>-1,
					'categoryId'=>0,
					'budgetItem'=>0,
					'measureId'=>0,
					'status'=>-1,
				);
			}
			$rs_rows= $db->getAllProductLocation($search);
			
			
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
			
			$list = new Application_Form_Frmtable();
			$collumns = array("PROJECT_NAME","PRODUCT_NAME","BAR_CODE","CURRENT_QTY","MEASURE","CATEGORY_NAME");
			$link=array('module'=>'product','controller'=>'initqty','action'=>'edit');
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
		$db = new Product_Model_DbTable_DbinitilizeQtybyProject();
    	if($this->getRequest()->isPost()){
	    	try{
	    		$data = $this->getRequest()->getPost();
	    		$db->addProductInitQty($data);
	    		Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL."/add");
	    	}catch(Exception $e){
	    		Application_Form_FrmMessage::message("APPLICATION_ERROR");
	    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	    	}
    	}
    	$frm = new Requesting_Form_FrmRequest();
    	$frm->FrmRequestPO(null);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm = $frm;
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

