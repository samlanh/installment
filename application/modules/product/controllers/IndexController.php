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
					'budgetType'=>0,
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
			$collumns = array("PRODUCT_NAME","PRODUCT_CODE","BAR_CODE","PRODUCT_CATEGORY","MEASURE","labelMeasure","SERVICE_PRODUCT",
							  "IS_COUNT_STOCK","BUDGET_ITEM","BY_USER","CREATE_DATE","STATUS");
			$link=array('module'=>'product','controller'=>'index','action'=>'edit');
			$this->view->list=$list->getCheckList(10, $collumns,$rs_rows,array('proName'=>$link,'proCode'=>$link,
					'barCode'=>$link,'categoryName'=>$link,'MeasureName'=>$link));
			
			$frm = new Application_Form_FrmAdvanceSearchStock();
			$frm = $frm->AdvanceSearch();
			Application_Model_Decorator::removeAllDecorator($frm);
			$this->view->frm_search = $frm;
			
			$frm = new Product_Form_Frmproduct();
			$frm = $frm->FrmSearchProduct();
			Application_Model_Decorator::removeAllDecorator($frm);
			$this->view->frmSearchProduct = $frm;
			$this->view->search =$search['budgetType'];
		    
		
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
		$db = new Product_Model_DbTable_DbProduct();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$db->updateProductData($_data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/product/index");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		$id = $this->getRequest()->getParam('id');
		$id = empty($id)?0:$id;
		$result = $db->getProductbyId($id);
		if(empty($id) OR empty($result)){
			Application_Form_FrmMessage::Sucessfull("NO_DATA","/product/index",2);
		}
		$this->view->photo = $result['image'];
		$frm = new Product_Form_Frmproduct();
		$frm = $frm->FrmAddProduct($result);
		
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frmProduct = $frm;
		
	}
	function viewAction(){
		$db = new Product_Model_DbTable_DbProduct();
		$id = $this->getRequest()->getParam('id');
		$id = empty($id)?0:$id;
		$result = $db->getProductDetailbyId($id);
		if(empty($id) OR empty($result)){
			Application_Form_FrmMessage::Sucessfull("NO_DATA","/product/index",2);
		}
		$this->view->rsProduct = $result;
		
		
		$dbs = new Application_Model_DbTable_DbGlobalStock();
		$this->view->productLocation = $dbs->getProductLocationbyProId(array('productId'=>$id));
	}

	function copyAction(){
		$db = new Product_Model_DbTable_DbProduct();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$db->addNewProduct($_data);
				Application_Form_FrmMessage::Sucessfull("COPY_SUCCESS","/product/index");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("COPY_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		$id = $this->getRequest()->getParam('id');
		$id = empty($id)?0:$id;
		$result = $db->getProductbyId($id);
		if(empty($id) OR empty($result)){
			Application_Form_FrmMessage::Sucessfull("NO_DATA","/product/index",2);
		}
		$this->view->photo = $result['image'];
		$frm = new Product_Form_Frmproduct();
		$frm = $frm->FrmAddProduct($result);
		
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frmProduct = $frm;
		
	}
}

