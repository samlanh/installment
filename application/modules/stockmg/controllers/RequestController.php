<?php
class Stockmg_RequestController extends Zend_Controller_Action {
	const REDIRECT_URL = '/stockmg/request';
	public function init()
    {    	
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
					'adv_search'=>"",
					'branch_id' => -1,
					'start_date'=> date('Y-m-d'),
					'end_date'=>date('Y-m-d'),
					'status'=>-1,
				);
    		}
    		$this->view->search = $search;
			$db = new Stockmg_Model_DbTable_DbRequest();
			$rs_rows = $db->getAllRequestPO($search);
			
			$list = new Application_Form_Frmtable();
    		$collumns = array("PROJECT_NAME","REQUEST_NO","REQUEST_NO_FROM","PURPOSE","DATE","CHECKING_STATUS","CHECKING_BY","USER","STATUS");
    		$link=array(
    				'module'=>'stockmg','controller'=>'request','action'=>'edit',
    		);
    		$this->view->list=$list->getCheckList(10, $collumns, $rs_rows , array('branch_name'=>$link,'requestNo'=>$link ));
    		
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
		}
		
		$frm_search = new Application_Form_FrmAdvanceSearchStock();
		$frm = $frm_search->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
		
	}
    public function addAction()
    {	
    	$db = new Stockmg_Model_DbTable_DbRequest();
    	if($this->getRequest()->isPost()){
	    	try{
	    		$data = $this->getRequest()->getPost();
	    		$db->addRequestPO($data);
	    		if(isset($data['save_close'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL."/index");
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL."/add");
				}
	    	}catch(Exception $e){
	    		Application_Form_FrmMessage::message("APPLICATION_ERROR");
	    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	    	}
    	}
    	$frm = new Stockmg_Form_FrmRequest();
    	$frm->FrmRequestPO(null);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm = $frm;
    }
	public function editAction(){
		$db = new Stockmg_Model_DbTable_DbRequest();
		$id=$this->getRequest()->getParam('id');
		$id = empty($id)?0:$id;
		if($this->getRequest()->isPost()){
	    	try{
	    		$data = $this->getRequest()->getPost();
	    		$db->editRequestPO($data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS",self::REDIRECT_URL."/index");
	    	}catch(Exception $e){
	    		Application_Form_FrmMessage::message("APPLICATION_ERROR");
	    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	    	}
    	}
    	$row = $db->getRequestPOById($id);
    	if (empty($row)){
    		Application_Form_FrmMessage::Sucessfull("NO_RECORD", self::REDIRECT_URL."/index");
    		exit();
    	}
    	$this->view->row = $row;
    	$this->view->rowdetail = $db->getRequestPODetailById($row);
    	
    	$frm = new Stockmg_Form_FrmRequest();
    	$frm->FrmRequestPO($row);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm = $frm;
	}
	
	function getallproductAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobalStock();
			$_row =$db->getAllProduct($data);
			
			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
			array_unshift($_row,array(
					'id' => 0,
					'name' => $tr->translate("SELECT_PRODUCT"),
			) );
			print_r(Zend_Json::encode($_row));
			exit();
			
		}
	}
	
	function productinfoAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobalStock();
			$_row =$db->getProductInfoByLocation($data);
			print_r(Zend_Json::encode($_row));
			exit();
			
		}
	}
	
	function getrequestnoAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobalStock();
			$_row =$db->generateRequestNo($data);
			print_r(Zend_Json::encode($_row));
			exit();
			
		}
	}
	
	
}