<?php
class Incexp_ProductController extends Zend_Controller_Action {
	private $activelist = array('មិនប្រើ​ប្រាស់', 'ប្រើ​ប្រាស់');
	private $type = array(1=>'service',2=>'program');
	const REDIRECT_URL = '/incexp/product';
	public function init()
	{
		header('content-type: text/html; charset=utf8');
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function start(){
		return ($this->getRequest()->getParam('limit_satrt',0));
	}
	public function indexAction()
    {
    	try{
	    	$db = new Incexp_Model_DbTable_DbProduct();
	    	if($this->getRequest()->isPost()){
	    		$search=$this->getRequest()->getPost();
	    	}
	    	else{
	    		$search = array(
	    				'advance_search' => "",
	    				'items_search'=>"",
	    				'product_type_search'=>-1,
	    				'status_search' => -1
	    		);
	    	}
	    	$type=3; //Product
	    	$rs_rows= $db->getAllProduct($search,$type);
	    	$glClass = new Application_Model_GlobalClass();
	    	$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
	    	$list = new Application_Form_Frmtable();
	    	$collumns = array("CODE","PRODUCT_NAME","PRODUCT_CATEGORY","PRICE","MODIFY_DATE","BY_USER","STATUS");
	    	$link=array(
	    			'module'=>'incexp','controller'=>'product','action'=>'edit',
	    	);
	    	$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('code'=>$link ,'title'=>$link ,'degree'=>$link));
	    	
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    	
    	$frm = new Incexp_Form_FrmProduct();
    	$frm->FrmAddProduct(null,$type);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_items = $frm;
    }  
    public function addAction(){
    	$db = new Incexp_Model_DbTable_DbProduct();
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		try {
    			$sms="INSERT_SUCCESS";
    			$_major_id = $db->AddProduct($_data);
    			if($_major_id==-1){
    				$sms = "RECORD_EXIST";
					Application_Form_FrmMessage::Sucessfull($sms, self::REDIRECT_URL."/index",2);
					exit();
    			}
    			if(!empty($_data['save_close'])){
    				Application_Form_FrmMessage::Sucessfull($sms, self::REDIRECT_URL."/index");
    			}else{
    				Application_Form_FrmMessage::Sucessfull($sms, self::REDIRECT_URL."/add");
    			}
    		}catch (Exception $e){
    			Application_Form_FrmMessage::message("INSERT_FAIL");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    			echo $e->getMessage();
    		}
    			
    	}
    	$type=3; //Product
    	$frm = new Incexp_Form_FrmProduct();
    	$frm->FrmAddProduct(null,$type);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_items = $frm;
    	
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	
    	$model = new Application_Model_DbTable_DbGlobal();

    	
    }
    
    public function editAction(){
    	$db = new Incexp_Model_DbTable_DbProduct();
    	$id = $this->getRequest()->getParam("id");
    	if($this->getRequest()->isPost()){
    		try{
	    		$_data = $this->getRequest()->getPost();
	    		$db->updateProduct($_data);
	    		Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS", self::REDIRECT_URL."/index");
	    		exit();
    		}catch(Exception $e){
    			Application_Form_FrmMessage::message("Application Error");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
    	}
    	$type=3; //Product
    	$row =$db->getItemsDetailById($id);
    	if (empty($row)){
    		Application_Form_FrmMessage::Sucessfull("NO_RECORD", self::REDIRECT_URL."/index",2);
    	}
    	$this->view->row = $row;
    	
    	
    	$frm = new Incexp_Form_FrmProduct();
    	$frm->FrmAddProduct($row,$type);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_items = $frm;
    	
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	 
		
    }
	function refreshproductAction(){
    	if($this->getRequest()->isPost()){
    		try{
    			$data = $this->getRequest()->getPost();
    			$db = new Incexp_Model_DbTable_DbProduct();
				$category = empty($data['items_id'])?null:$data['items_id'];
    			$d_row= $db->getAllProducts($category);
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
	function getproductinfoAction(){
    	if($this->getRequest()->isPost()){
    		try{
    			$data = $this->getRequest()->getPost();
    			$db = new Incexp_Model_DbTable_DbProduct();
				$row =$db->getItemsDetailById($data['pro_id']);			
    			print_r(Zend_Json::encode($row));
    			exit();
    		}catch(Exception $e){
    			Application_Form_FrmMessage::message("INSERT_FAIL");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
    	}
    }
	
}