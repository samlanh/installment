<?php
class Stock_SupplierController extends Zend_Controller_Action {
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
    				'adv_search' => '',
    				'start_date'=> date('Y-m-d'),
    				'end_date'=>date('Y-m-d'),
    				'status_search'=>-1,
    			);
    		}
			$db =  new Stock_Model_DbTable_DbSupplier();
			$rows = $db->getAllSupplier($search);
			$rs_rows=new Application_Model_GlobalClass();
			$rs_rows=$rs_rows->getImgActive($rows, BASE_URL);
			$list = new Application_Form_Frmtable();
			$collumns = array("SUPPLIER_NAME","Tel","EMAIL","STATUS","DATE",);
			$link=array(
					'module'=>'stock','controller'=>'supplier','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('tel'=>$link,'sup_name'=>$link,'sex'=>$link,));
			}catch (Exception $e){
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				Application_Form_FrmMessage::message("Application Error");
			}
			$form=new Stock_Form_FrmSupplier();
			$form=$form->FrmSupplier();
			Application_Model_Decorator::removeAllDecorator($form);
			$this->view->form_search=$form;
	}
	public function addAction(){
		if($this->getRequest()->isPost()){
		$_data = $this->getRequest()->getPost();
			try{
				$db = new Stock_Model_DbTable_DbSupplier();
				$row = $db->addSupplier($_data);
				if(isset($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/stock/supplier");
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/stock/supplier/add");
				}
				Application_Form_FrmMessage::message("INSERT_SUCCESS");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$fm = new Stock_Form_FrmSupplier();
		$frm = $fm->FrmSupplier();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm = $frm;
	}
	public function editAction(){
		$id=$this->getRequest()->getParam('id');
		$id= empty($id)?0:$id;
		$db = new Stock_Model_DbTable_DbSupplier();
		if($this->getRequest()->isPost()){
		$_data = $this->getRequest()->getPost();
			try{
				$row = $db->updateSupplier($_data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/stock/supplier");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("EDIT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$row = $db->getSupplierById($id);
		if (empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD","/stock/supplier",2);
			exit();
		}
		$fm = new Stock_Form_FrmSupplier();
		$frm = $fm->FrmSupplier($row);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm = $frm;
	}
	function deplicateAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Stock_Model_DbTable_DbSupplier();
			$pro_cate = $db->checkDeplicateRecord($data);
			print_r(Zend_Json::encode($pro_cate));
			exit();
		}
	}
}