<?php
class accounting_AccountnameController extends Zend_Controller_Action {
	public function init()
	{
		/* Initialize action controller here */
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function addAction()
	{
		if($this->getRequest()->isPost()){
			$accdata=$this->getRequest()->getPost();
			$db_acc = new Accounting_Model_DbTable_DbAccountname();
			try {
				$db = $db_acc->addaccountname($accdata);
				Application_Form_FrmMessage::Sucessfull('ការ​បញ្ចូល​​ជោគ​ជ័យ', self::REDIRECT_URL);
			} catch (Exception $e) {
				$this->view->msg = 'ការ​បញ្ចូល​មិន​ជោគ​ជ័យ';
			}
		}
		
		$fm = new Accounting_Form_Frmaccountname();
		$frm = $fm->FrmAccount();
		//echo ($frm);exit;
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_accountname = $frm;
	}
	public function editAction(){
	if($this->getRequest()->isPost()){
			$accdata=$this->getRequest()->getPost();
			$db_acc = new Accounting_Model_DbTable_DbAccountname();
			try {
				$db = $db_acc->updateaccountname($accdata);
				Application_Form_FrmMessage::Sucessfull('ការ​បញ្ចូល​​ជោគ​ជ័យ', self::REDIRECT_URL);
			} catch (Exception $e) {
				$this->view->msg = 'ការ​បញ្ចូល​មិន​ជោគ​ជ័យ';
			}
		}
		$id = $this->getRequest()->getParam('id');
		$db = new Accounting_Model_DbTable_DbAccountname();
		$row  = $db->geteaccountnamebyid($id);
		$fm = new Accounting_Form_Frmaccountname();
		$frm = $fm->FrmAccount($row);
		//echo ($frm);exit;
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_accountname = $frm;
	}
	public function indexAction(){
		try{
			$db = new Accounting_Model_DbTable_DbAccountname();
			$rs_rows= $db->getAllaccountname($search=null);//call frome model
			$glClass = new Application_Model_GlobalClass();
			$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
			$list = new Application_Form_Frmtable();
			$collumns = array("Account_code ","Account_name_kh ","Account_name_en","Displayby","Disc","Option_acc",
					"Type","Category_id","Parent_id","Date","Status");
			$link=array(
					'module'=>'accounting','controller'=>'accountname','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns,$rs_rows,array('account_code'=>$link,'account_name_kh'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			echo $e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	
		}
	
	}
	
}

	