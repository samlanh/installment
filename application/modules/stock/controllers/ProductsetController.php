<?php
class Stock_ProductsetController extends Zend_Controller_Action {
	private $type = array(1=>'service',2=>'program');
	const REDIRECT_URL = '/stock/productset';
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
			if($this->getRequest()->isPost()){
    			$search = $this->getRequest()->getPost();
    		}
    		else{
    			$search=array(
    				'advance_search' => "",
	    			'items_search'=>"",
    				'start_date'=> date('Y-m-d'),
    				'end_date'=>date('Y-m-d'),
    				'status_search'=>1,
    			);
    		}
    		$type=3; //Product
			$db =  new Stock_Model_DbTable_DbGlobalProduct();
			$rs_rows = $db->getAllProductSet($search,$type);
			$list = new Application_Form_Frmtable();
			$collumns = array("PRODUCT_CODE","PRODUCT_NAME","PRODUCT_CATEGORY","SELL_PRICE",
					"DATE","BY_USER","STATUS");
			$link=array(
					'module'=>'stock','controller'=>'productset','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(10, $collumns, $rs_rows,array('title'=>$link,'title_en'=>$link,'code'=>$link,'degree'=>$link,));
			}catch (Exception $e){
				Application_Form_FrmMessage::message("Application Error!");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
			$frm = new Global_Form_FrmItemsDetail();
	    	$frm->FrmAddItemsDetail(null,$type);
	    	Application_Model_Decorator::removeAllDecorator($frm);
	    	$this->view->frm_items = $frm;
	}
	public function addAction(){
		$db = new Stock_Model_DbTable_DbGlobalProduct();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try{
				$row = $db->addProductSet($_data);
				if(isset($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL."/index");
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL."/add");
				}
				
				Application_Form_FrmMessage::message("INSERT_SUCCESS");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$type=3; //Product
		$frm = new Global_Form_FrmItemsDetail();
		$frm->FrmAddItemsDetail(null,$type);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_items = $frm;

		$product_type=1;
		$d_row= $db->getAllProductsNormal($product_type);
		array_unshift($d_row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
	    array_unshift($d_row, array ( 'id' => "",'name' =>$this->tr->translate("SELECT_PRODUCT")));
	    $this->view->productlist=$d_row;
	    
	    $db = new Application_Model_DbTable_DbGlobal();
	    $d_row = $db->getAllItems(3);
	    array_unshift($d_row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
	    $this->view->cat_rows = $d_row;
	}
	public function editAction(){
		$id=$this->getRequest()->getParam('id');
		$db = new Stock_Model_DbTable_DbGlobalProduct();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try{
				$rs = $db->updateProductSet($_data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS",self::REDIRECT_URL."/index");
				exit();
			}catch(Exception $e){
				Application_Form_FrmMessage::message("EDIT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		    $type=3; //Product
		    $row =$db->getItemsDetailById($id,$type,1);
		    $this->view->pro_detail=$db->getProductSetDetailById($id);
		  
		    $frm = new Global_Form_FrmItemsDetail();
		    $frm->FrmAddItemsDetail($row,$type);
		    Application_Model_Decorator::removeAllDecorator($frm);
		    $this->view->frm_items = $frm;
		    
		    $product_type=1;
		    $d_row= $db->getAllProductsNormal($product_type);
		    array_unshift($d_row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		    array_unshift($d_row, array ( 'id' => "",'name' =>$this->tr->translate("SELECT_PRODUCT")));
		    $this->view->productlist=$d_row;
	}
	public function copyAction(){
		$id=$this->getRequest()->getParam('id');
		$db = new Stock_Model_DbTable_DbGlobalProduct();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try{
				$rs = $db->addProductSet($_data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL."/index");
				exit();
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$type=3; //Product
		$row =$db->getItemsDetailById($id,$type,1);
		$this->view->pro_detail=$db->getProductSetDetailById($id);
	
		$frm = new Global_Form_FrmItemsDetail();
		$frm->FrmAddItemsDetail($row,$type);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_items = $frm;
	
		$product_type=1;
		$d_row= $db->getAllProductsNormal($product_type);
		array_unshift($d_row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		array_unshift($d_row, array ( 'id' => "",'name' =>$this->tr->translate("SELECT_PRODUCT")));
		$this->view->productlist=$d_row;
	}
	function refreshproductAction(){
		if($this->getRequest()->isPost()){
			try{
				$data = $this->getRequest()->getPost();
				$db = new Stock_Model_DbTable_DbGlobalProduct();
				$product_type=1;
				$d_row= $db->getAllProductsNormal($product_type);
				array_unshift($d_row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
				array_unshift($d_row, array ( 'id' => "",'name' =>$this->tr->translate("SELECT_PRODUCT")));
				print_r(Zend_Json::encode($d_row));
				exit();
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
	}
}