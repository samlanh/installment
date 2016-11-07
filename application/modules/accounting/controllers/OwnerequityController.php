<?php
class accounting_OwnerequityController extends Zend_Controller_Action {
	public function init()
	{
		/* Initialize action controller here */
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function addAction(){
		if($this->getRequest()->isPost()){
			$agentdata=$this->getRequest()->getPost();	
			$db_agent = new Accounting_Model_DbTable_DbOwner();				
			try {
				$db = $db_agent->addownerequity($agentdata);				
				Application_Form_FrmMessage::message('ការ​បញ្ចូល​​ជោគ​ជ័យ');		
			} catch (Exception $e) {
				
				$this->view->msg = 'ការ​បញ្ចូល​មិន​ជោគ​ជ័យ';
			}
		}
    	
		$fm = new Accounting_Form_FrmOwnerequity();
		$frm = $fm->FrmOwnerequity();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_fixedasset = $frm;

	}
	public function indexAction(){
		if($this->getRequest()->isPost()){
			$db = new Group_Model_DbTable_DbClient();
			$data = $this->getRequest()->getPost();
			$_data['status']=1;
			$id = $db->addClient($data);
			print_r(Zend_Json::encode($id));
			exit();
		}
		
	}
}
