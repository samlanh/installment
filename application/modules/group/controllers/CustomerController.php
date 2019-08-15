<?php
class Group_CustomerController extends Zend_Controller_Action {
	const REDIRECT_URL = '/group/index';
	public function init()
	{
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		$dbc = new Group_Model_DbTable_DbCustomer();
		try{
			
			if($this->getRequest()->isPost()){
				$formdata=$this->getRequest()->getPost();
				$search = array(
						'adv_search' => $formdata['adv_search'],
						'status'=>$formdata['status'],		
						'know_by'=>$formdata['know_by'],
						'statusreq'=>$formdata['statusreq'],
						'start_date'=> $formdata['start_date'],
						'end_date'=>$formdata['end_date']						
					);
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
			$glClass = new Application_Model_GlobalClass();
			$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
			$list = new Application_Form_Frmtable();
			$collumns = array("CUSTOMER_NAME","PHONE","KNOW_BY","DATE","FROM_PRICE","TO_PRICE","REQUIREDMENT","TYPE","DESCRIPTION","STATUS_REQ","BY_USER","STATUS");
			$link=array(
					'module'=>'group','controller'=>'customer','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(10, $collumns, $rs_rows,array('name'=>$link,'phone'=>$link));
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
		$db = new Group_Model_DbTable_DbCustomer();
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
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/group/customer/add");

			}catch (Exception $e){
				Application_Form_FrmMessage::message("Application Error");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$row = $db->getAllstatusreqForOpt();
		array_unshift($row, array('id'=>'','name' => $tr->translate("CHOOSE_STATUS_REQ")),array('id'=>'-1','name' => $tr->translate("ADD_NEW")));
		$this->view->statusreq = $row;
		$fm = new Group_Form_FrmCustomer();
		$frm = $fm->FrmAddCustomer();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_customer = $frm;		
	}
	public function editAction(){

		$id = $this->getRequest()->getParam("id");
		$db = new Group_Model_DbTable_DbCustomer();
		if($this->getRequest()->isPost()){
			try{
				// Check Session Expire
				$dbgb = new Application_Model_DbTable_DbGlobal();
				$checkses = $dbgb->checkSessionExpire();
				if (empty($checkses)){
					$dbgb->reloadPageExpireSession();
					exit();
				}
				
				$data = $this->getRequest()->getPost();
				$data['id']=$id;
				$db->add($data);
				Application_Form_FrmMessage::Sucessfull('EDIT_SUCCESS',"/group/customer");
			}catch (Exception $e){
				Application_Form_FrmMessage::message("EDIT_FAILE");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
	
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$row = $db->getAllstatusreqForOpt();
		array_unshift($row, array('id'=>'','name' => $tr->translate("CHOOSE_STATUS_REQ")),array('id'=>'-1','name' => $tr->translate("ADD_NEW")));
		$this->view->statusreq = $row;
		
		$row = $db->getById($id);
	        $this->view->row=$row;
		if(empty($row)){
			$this->_redirect("/group/customer");
		}
		
		$allContact = $db->AllHistoryContact($id);
		$this->view->history = $allContact;
		
		$fm = new Group_Form_FrmCustomer();
		$frm = $fm->FrmAddCustomer($row);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_customer = $frm;
	}
	
	public function contactAction(){
	
		$db = new Group_Model_DbTable_DbCustomer();
		$id = $this->getRequest()->getParam("id");
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try{
				// Check Session Expire
				$dbgb = new Application_Model_DbTable_DbGlobal();
				$checkses = $dbgb->checkSessionExpire();
				if (empty($checkses)){
					$dbgb->reloadPageExpireSession();
					exit();
				}
					
				$row = $db->addContactHistory($_data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/group/customer");
				exit();
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				echo $e->getMessage();
			}
		}
		$row = $db->getById($id);
		if (empty($row)){
			Application_Form_FrmMessage::Sucessfull("No Record","/group/customer");
		}
		$this->view->row = $row;
		$allContact = $db->AllHistoryContact($id);
		$this->view->history = $allContact;
		$frm = new Group_Form_FrmContactHistory();
		$frm->FrmAddCRMContactHistory($row);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_crmhistory = $frm;
	}

}