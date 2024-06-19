<?php
class Group_TransferController extends Zend_Controller_Action {
	const REDIRECT_URL = '/group/transfer';
	public function init()
	{
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		$dbc = new Group_Model_DbTable_DbTranferCRM();
		try{
			
			if($this->getRequest()->isPost()){
				$formdata=$this->getRequest()->getPost();
				$search = $formdata;
			}
			else{
				$search = array(
						'adv_search' => '',
						'status' => -1,		
						'statusreq'=>'',
						'know_by'=>-1,			
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d')								
						);
			}
			$rs_rows= $dbc->getAllInfo($search);
			$list = new Application_Form_Frmtable();
			$collumns = array("CUSTOMER_NAME","PHONE","KNOW_BY","TO_USER","DATE","BY_USER","STATUS");
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array());
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$frm = new Application_Form_FrmAdvanceSearch();
		$frm = $frm->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
		$this->view->result=$search;	
	}
	public function addAction(){
		$db = new Group_Model_DbTable_DbTranferCRM();
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			try{
				// Check Session Expire
				$dbgb = new Application_Model_DbTable_DbGlobal();
				$checkses = $dbgb->checkSessionExpire();
				if (empty($checkses)){
					$dbgb->reloadPageExpireSession();
					exit();
				}
				
				$id= $db->add($data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/group/transfer");

			}catch (Exception $e){
				Application_Form_FrmMessage::message("Application Error");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		$fm = new Group_Form_FrmTransferCRM();
		$frm = $fm->FrmTransferCRM();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_customer = $frm;

		$dbpop = new Application_Form_FrmPopupGlobal();
		$this->view->frm_popupOther = $dbpop->frmPopupOther();
		
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->knowby = $db->getAllKnowBy(null,1);
	}
}