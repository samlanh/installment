<?php
class Project_StreetController extends Zend_Controller_Action {
	const REDIRECT_URL='/project/street';
	public function init()
	{
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
			$db = new Project_Model_DbTable_DbStreet();
			if($this->getRequest()->isPost()){
				$formdata=$this->getRequest()->getPost();
					$search = array(
						'adv_search' => $formdata['adv_search'],
					);
			}
			else{
				$search = array(
						'adv_search' => '',
					);
			}
			$rs_rows= $db->getAllStreet($search);
			$list = new Application_Form_Frmtable();
			$collumns = array("STREET_NAME","STREET_CODE",);
			$link=array(
					'module'=>'project','controller'=>'street','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(10, $collumns, $rs_rows,array('street'=>$link,'street_code'=>$link));
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
		$this->_redirect(self::REDIRECT_URL."/");
		exit();
	}
	
	public function editAction(){
		$id = $this->getRequest()->getParam("id");
		$id = empty($id)?0:$id;
		$db = new Project_Model_DbTable_DbStreet();
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
				$db->editStreet($data);
				Application_Form_FrmMessage::Sucessfull('EDIT_SUCCESS',"/project/street");
			}catch (Exception $e){
				Application_Form_FrmMessage::message("EDIT_FAILE");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$row = $db->getStreetById($id);
		if (empty($row)){
			Application_Form_FrmMessage::Sucessfull('NO_RECORD',"/project/street",2);
			exit();
		}
		$frm = new Project_Form_FrmStreet();
		$frm = $frm->FrmStreet($row);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm = $frm;
	}
	
}