<?php
class Accounting_AccountcateController extends Zend_Controller_Action {
	public function init()
	{
		/* Initialize action controller here */
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	
	public function indexAction(){
		try{
			$db = new Accounting_Model_DbTable_DbAccountcate();
			$rs_rows= $db->getAllaccountcate($search=null);//call frome model
			$glClass = new Application_Model_GlobalClass();
			$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
			$list = new Application_Form_Frmtable();
			$collumns = array("Cate_namekh ","Cate_nameen ","Parent","Account_type","Date","Deplay","Status");
			$link=array(
					'module'=>'accounting','controller'=>'accountcate','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns,$rs_rows,array('cate_namekh'=>$link,'categoryname_eng'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			echo $e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			 
		}
	
	}
	
	public function addAction()
	{
		if($this->getRequest()->isPost()){
			$accdata=$this->getRequest()->getPost();
			$db_acc = new Accounting_Model_DbTable_DbAccountcate();
			try {
				$db = $db_acc->addaccountcate($accdata);
				Application_Form_FrmMessage::Sucessfull('ការ​បញ្ចូល​​ជោគ​ជ័យ', self::REDIRECT_URL);
			} catch (Exception $e) {
				$this->view->msg = 'ការ​បញ្ចូល​មិន​ជោគ​ជ័យ';
			}
		}
		$fm = new Accounting_Form_Frmaccountcate();
		$frm = $fm->Frmaccountcate();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_fixedasset = $frm;
	}

	public function editAction(){
		if($this->getRequest()->isPost()){
			$accdata=$this->getRequest()->getPost();	
			$db_acc = new Accounting_Model_DbTable_DbAccountcate();				
			try {
				$db = $db_acc->updataccountcate($accdata);				
				Application_Form_FrmMessage::Sucessfull('ការ​បញ្ចូល​​ជោគ​ជ័យ', self::REDIRECT_URL);		
			} catch (Exception $e) {
				$this->view->msg = 'ការ​បញ្ចូល​មិន​ជោគ​ជ័យ';
			}
		
		}
		$id = $this->getRequest()->getParam('id');
		$db = new Accounting_Model_DbTable_DbAccountcate();
		$row  = $db->geteaccountcatebyid($id);
		$fm = new Accounting_Form_Frmaccountcate();
		$frm = $fm->Frmaccountcate($row);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_fixedasset = $frm;
	}
	
}
