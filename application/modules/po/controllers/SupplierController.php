<?php
class Po_SupplierController extends Zend_Controller_Action {
	const REDIRECT_URL = '/po/supplier';
	public function init()
	{
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		//$db = new ();
		try{
			$db = new Po_Model_DbTable_DbSupplier();
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
			$rs_rows=array();
			$rs_rows= $db->getAllSupplier($search);//
			
			$list = new Application_Form_Frmtable();
    		$collumns = array("SUPPLIER_NAME","TEL","CONTACT_NAME","CONTACT_NUMBER","RECEIVER_NAME","BANK_NUMBER","EMAIL","SUPPLIER_TYPE","STATUS","BY");
    		$link=array(
    				'module'=>'po','controller'=>'supplier','action'=>'edit',
    		);
    		$this->view->list=$list->getCheckList(10, $collumns, $rs_rows , array('supplierName'=>$link,'supplierTel'=>$link,));
			
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

		$db = new Po_Model_DbTable_DbSupplier();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {		
				
				
				$db->addSupplier($_data);
	    		Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL."/index");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
    	$frm = new Po_Form_FrmSupplier();
    	$frm->FrmSupplier(null);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm = $frm;
		
	}
	function editAction(){
		
		$db = new Po_Model_DbTable_DbSupplier();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {		
				
				$db->addSupplier($_data);
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

    	$frm = new Po_Form_FrmSupplier();
    	$frm->FrmSupplier($row);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm = $frm;
		
	}
	
	function getSupplierinfoAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Po_Model_DbTable_DbSupplier();
			$id = empty($data['supplierId'])?0:$data['supplierId'];
			$_row =$db->getDataRow($id);
			print_r(Zend_Json::encode($_row));
			exit();
			
		}
	}

	function getallsupplierAction(){

		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobalStock();
			$results=$db->getAllSupplier();
				
			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		
			array_unshift($results, array ('id' => 0,'name' =>$tr->translate("SELECT_SUPLLER")));
			if(!empty($data['noBtnNew'])){
			}else{
				array_unshift($results, array ('id' => -1,'name' =>$tr->translate("ADD_NEW")));
			}
			
			
				
			print_r(Zend_Json::encode($results));
			exit();
		}
	}
}

