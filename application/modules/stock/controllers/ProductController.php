<?php
class Stock_ProductController extends Zend_Controller_Action {
	const REDIRECT_URL = '/stock/product';
	public function init()
	{
		header('content-type: text/html; charset=utf8');
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction()
    {
    	try{
	    	$db = new Stock_Model_DbTable_DbProduct();
	    	if($this->getRequest()->isPost()){
	    		$search=$this->getRequest()->getPost();
	    	}
	    	else{
	    		$search = array(
    				'advance_search' => "",
    				'items_search'=>"",
    				'is_onepayment'=>-1,
    				'product_type_search'=>-1,
    				'status_search' => -1,
    				'is_onepayment'=>-1,
    				'auto_payment'=>-1
	    		);
	    	}
	    	$type=3; //Product
	    	$rs_rows= $db->getAllProduct($search,$type);
	    	$list = new Application_Form_Frmtable();
	    	$collumns = array("CODE","PRODUCT_NAME","PRODUCT_CATEGORY","UNIT_COST","QTY","TYPE","ONE_PAYMENT","MODIFY_DATE","BY_USER","STATUS");
	    	$link=array(
	    			'module'=>'stock','controller'=>'product','action'=>'edit',
	    	);
	    	$this->view->list=$list->getCheckList(10, $collumns, $rs_rows,array('code'=>$link ,'title'=>$link ,'title_en'=>$link ,'degree'=>$link));
	    	
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    	
    	$frm = new Stock_Form_FrmItemsDetail();
    	$frm->FrmAddItemsDetail(null,$type);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_items = $frm;
    }  
    public function addAction(){
    	$db = new Stock_Model_DbTable_DbProduct();
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		try {
    			$sms="INSERT_SUCCESS";
    			$_major_id = $db->AddProduct($_data);
    			if($_major_id==-1){
    				$sms = "RECORD_EXIST";
    			}
    			if(!empty($_data['save_close'])){
    				Application_Form_FrmMessage::Sucessfull($sms, self::REDIRECT_URL."/index");
    			}else{
    				Application_Form_FrmMessage::Sucessfull($sms, self::REDIRECT_URL."/add");
    			}
    		}catch (Exception $e){
    			Application_Form_FrmMessage::message("INSERT_FAIL");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
    	}
    	$type=3; //Product
    	$frm = new Stock_Form_FrmItemsDetail();
    	$frm->FrmAddItemsDetail(null,$type);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_items = $frm;
    	
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	
    	$db = new Application_Model_DbTable_DbGlobal();
    	$branch = $db->getAllBranchByUser();
    	array_unshift($branch, array ( 'id' => "",'name' => $tr->translate("SELECT_LOCATION")));
    	$this->view->branchopt = $branch;
    	
    	$_db = new Stock_Model_DbTable_DbGlobalProduct();
    	$d_row = $_db->getAllItems(3);
    	array_unshift($d_row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
    	$this->view->degree = $d_row;
    }
    
    public function editAction(){
    	$db = new Stock_Model_DbTable_DbProduct();
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
    	$row =$db->getItemsDetailById($id,$type);
    	if (empty($row)){
    		Application_Form_FrmMessage::Sucessfull("NO_RECORD", self::REDIRECT_URL."/index");
    	}
    	$this->view->row = $row;
    	$this->view->productBranch = $db->getProductLocation($id);
    	
    	$frm = new Stock_Form_FrmItemsDetail();
    	$frm->FrmAddItemsDetail($row,$type);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_items = $frm;
    	
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	 
    	$db = new Application_Model_DbTable_DbGlobal();
    	$branch = $db->getAllBranchByUser();
    	array_unshift($branch, array ( 'id' => "",'name' => $tr->translate("SELECT_LOCATION")));
    	$this->view->branchopt = $branch;
    	
    	$_db = new Stock_Model_DbTable_DbGlobalProduct();
    	$d_row = $_db->getAllItems(3);
    	array_unshift($d_row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
    	$this->view->degree = $d_row;
    }
    public function copyAction(){
    	$db = new Stock_Model_DbTable_DbProduct();
    	$id = $this->getRequest()->getParam("id");
    	if($this->getRequest()->isPost()){
    		try{
    			$_data = $this->getRequest()->getPost();
    			$db->AddProduct($_data);
    			Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", self::REDIRECT_URL."/index");
    			exit();
    		}catch(Exception $e){
    			Application_Form_FrmMessage::message("Application Error");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
    	}
    	$type=3; //Product
    	$row =$db->getItemsDetailById($id,$type);
    	if (empty($row)){
    		Application_Form_FrmMessage::Sucessfull("NO_RECORD", self::REDIRECT_URL."/index");
    	}
    	$this->view->row = $row;
    	$this->view->productBranch = $db->getProductLocation($id);
    	 
    	$frm = new Stock_Form_FrmItemsDetail();
    	$frm->FrmAddItemsDetail($row,$type);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_items = $frm;
    	 
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    
    	$model = new Application_Model_DbTable_DbGlobal();
    	$branch = $model->getAllBranchByUser();
    	array_unshift($branch, array ( 'id' => "",'name' => $tr->translate("SELECT_LOCATION")));
    	$this->view->branchopt = $branch;
    	
    	$_db = new Stock_Model_DbTable_DbGlobalProduct();
    	$d_row = $_db->getAllItems(3);
    	array_unshift($d_row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
    	$this->view->degree = $d_row;
    }
    
    function deplicateproAction(){
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$db = new Stock_Model_DbTable_DbGlobalProduct();
    		$pro_cate = $db->CheckProductHasExit($data);
    		print_r(Zend_Json::encode($pro_cate));
    		exit();
    	}
    }
    function getproductbyacateAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Application_Model_DbTable_DbGlobal();
    		$group = $db->getAllGradeStudyByDegree($data['category']);
    		array_unshift($group, array ( 'id' =>'','name' =>$this->tr->translate("SELECT_PRODUCT")));
    		print_r(Zend_Json::encode($group));
    		exit();
    	}
    }
}