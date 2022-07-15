<?php
class Po_IndexController extends Zend_Controller_Action {
	
	const REDIRECT_URL = '/po/index';
	const STEP_REQUEST = 5;
	const PURCHASE_TYPE = 1;//From Requesting
	public function init()
	{
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		//$db = new ();
		try{
			$db = new Po_Model_DbTable_DbPurchasing();
			if(!empty($this->getRequest()->isPost())){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
					'adv_search'=>'',
					'branch_id'=>-1,
					'status'=>-1,
					'start_date'=> date('Y-m-d'),
					'end_date'=>date('Y-m-d'),
				);
			}
			$search['purchaseType']=self::PURCHASE_TYPE;
			$rs_rows=array();
			$rs_rows= $db->getAllPO($search);//
			
			
			$list = new Application_Form_Frmtable();
    		$collumns = array("PROJECT_NAME","PO_NO","SUPPLIER","DATE","REQUEST_NO","REQUEST_DATE","REQUEST_BY","TOTAL","STATUS","BY");
    		$link=array(
    				'module'=>'po','controller'=>'index','action'=>'edit',
    		);
    		$this->view->list=$list->getCheckList(10, $collumns, $rs_rows , array('branch_name'=>$link,'purchaseNo'=>$link,));
			
			}catch (Exception $e){
				Application_Form_FrmMessage::message("Application Error");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
			
		$frm_search = new Application_Form_FrmAdvanceSearchStock();
		$frm = $frm_search->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
		
	}
	function addAction(){
		
		$id = $this->getRequest()->getParam('id');
		if(!empty($id)){
			$dbRq = new Requesting_Model_DbTable_DbRequest();
			$row = $dbRq->getRequestPOById($id);
			$this->view->reqResult =  $row;
			if ($row['approveStatus']!=1 AND ($row['processingStatus']!=4 OR $row['processingStatus']!=5)){
				Application_Form_FrmMessage::Sucessfull("NO_DATA", self::REDIRECT_URL."/index");
				exit();
			}
			
		}
		
		$db = new Po_Model_DbTable_DbPurchasing();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {		
				
				$_data['stepNum']=self::STEP_REQUEST;
				$_data['purchaseType']=self::PURCHASE_TYPE;
				$db->addPurchasingRequest($_data);
	    		Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL."/index");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		
    	$frm = new Po_Form_FrmPurchase();
    	$frm->FrmPurchase(null);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm = $frm;
		
	}
	function editAction(){
		$db = new Po_Model_DbTable_DbPurchasing();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS",self::REDIRECT_URL."/index");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		$id = $this->getRequest()->getParam('id');
		$id = empty($id)?0:$id;
		if(empty($id)){
			Application_Form_FrmMessage::Sucessfull("NO_DATA",self::REDIRECT_URL."/index");
			exit();
		}
		
		$row = $db->getDataRow($id);
		$this->view->row = $row;
		$this->view->rowdetail = $db->getPODetailById($id);
		
		$frm = new Po_Form_FrmPurchase();
    	$frm->FrmPurchase($row);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm = $frm;
	}
	
	function getpurchasenoAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobalStock();
			$_row =$db->generatePurchaseNo($data);
			print_r(Zend_Json::encode($_row));
			exit();
			
		}
	}
}

