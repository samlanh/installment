<?php
class Po_DirectpoController extends Zend_Controller_Action {
	const REDIRECT_URL = '/po/directpo';
	const PURCHASE_TYPE = 2;//Direct PO
	public function init()
	{
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		//$db = new ();
		try{
			$db = new Po_Model_DbTable_DbDirectPO();
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
			$rs_rows= $db->getAllDirectedPO($search);//
			
			
			$list = new Application_Form_Frmtable();
    		$collumns = array("PROJECT_NAME","PO_NO","SUPPLIER","DATE","PURCHASING_PURPOSE","TOTAL","STATUS","BY");
    		$link=array(
    				'module'=>'po','controller'=>'directpo','action'=>'edit',
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
		
		$db = new Po_Model_DbTable_DbDirectPO();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {		
				
				$_data['purchaseType']=self::PURCHASE_TYPE;
				$db->addDirectedPO($_data);
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
		
		$tr=Application_Form_FrmLanguages::getCurrentlanguage();
		$db = new Po_Model_DbTable_DbDirectPO();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$_data['purchaseType']=self::PURCHASE_TYPE;
				$db->editDirectedPO($_data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS",self::REDIRECT_URL."/index");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		$id = $this->getRequest()->getParam('id');
		$id = empty($id)?0:$id;
		if(empty($id)){
			Application_Form_FrmMessage::Sucessfull("NO_DATA",self::REDIRECT_URL."/index",2);
			exit();
		}
		
		$row = $db->getDataRow($id);
		$this->view->row = $row;
		if(empty($row)){
			Application_Form_FrmMessage::Sucessfull($tr->translate('NO_DATA'), self::REDIRECT_URL."/index",2);
			exit();
		}
		if ($row['status']==0){
    		Application_Form_FrmMessage::Sucessfull($tr->translate('ALREADY_VOID'), self::REDIRECT_URL."/index",2);
    		exit();
    	}
		if (!empty($row['inDepositInvoice'])){
    		Application_Form_FrmMessage::Sucessfull($tr->translate('ALREADY_DEPOSIT'), self::REDIRECT_URL."/index",2);
    		exit();
    	}
		$dbGBstock = new Application_Model_DbTable_DbGlobalStock();
		$arrStep = array(
				'keyIndex'=>self::PURCHASE_TYPE,
				'typeKeyIndex'=>1,
			);
		$purchaseType = $dbGBstock->purchasingTypeKey($arrStep);
		
		if ($row['purchaseType']!=$purchaseType){
    		Application_Form_FrmMessage::Sucessfull($tr->translate('NO_DATA'), self::REDIRECT_URL."/index",2);
    		exit();
    	}
		$this->view->rowdetail = $db->getPODetailById($id);
		
		
		$frm = new Po_Form_FrmPurchase();
    	$frm->FrmPurchase($row);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm = $frm;
	}
	
	function podetailAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Po_Model_DbTable_DbDirectPO();
			$_row =$db->getPODetailHtml($data);
			
			print_r(Zend_Json::encode($_row));
			exit();
			
		}
	}
}

