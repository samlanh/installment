<?php
class Group_KnowbyController extends Zend_Controller_Action {
	const REDIRECT_URL = '/group/knowby';
    public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
			$db = new Group_Model_DbTable_DbKnowBy();
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
					'adv_search' => '',
					'status' => -1,);
			}
			$rs_rows= $db->geteAllKnowByList($search);
			$list = new Application_Form_Frmtable();
			
			$collumns = array("TITLE","USER_NAME","STATUS");
			$link=array(
					'module'=>'group','controller'=>'knowby','action'=>'edit',
			);
 			$this->view->list=$list->getCheckList(10, $collumns,$rs_rows,array('title'=>$link,'note'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		
		$frm_search = new Loan_Form_FrmSearchLoan();
		$frm = $frm_search->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
   }
   function addAction(){
	   	if($this->getRequest()->isPost()){
	   		$_data = $this->getRequest()->getPost();
	   		$db = new Group_Model_DbTable_DbKnowBy();
	   		try{
	   			// Check Session Expire
	   			$dbgb = new Application_Model_DbTable_DbGlobal();
	   			$checkses = $dbgb->checkSessionExpire();
	   			if (empty($checkses)){
	   				$dbgb->reloadPageExpireSession();
	   				exit();
	   			}
	   			if (empty($_data)){
					Application_Form_FrmMessage::Sucessfull("File Attachment to large can't upload and Save data !",self::REDIRECT_URL,2);
					exit();
				}
	   			$db->addKnowBy($_data);
	   				if(!empty($_data['save_new'])){
					Application_Form_FrmMessage::Sucessfull('INSERT_SUCCESS', self::REDIRECT_URL . '/add');
					}else{
						Application_Form_FrmMessage::Sucessfull('INSERT_SUCCESS', self::REDIRECT_URL );
					}
	   		}catch(Exception $e){
	   			Application_Form_FrmMessage::message("INSERT_FAIL");
	   			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	   		}
	   	} 	
	   	$frm = new Group_Form_FrmKnowBy();
	   	$frm_pro=$frm->FrmKnowBy();
	   	Application_Model_Decorator::removeAllDecorator($frm_pro);
	   	$this->view->frm_property_type = $frm_pro;
   }
   function editAction(){
	   	$id = $this->getRequest()->getParam("id");
	   	$dbKn = new Group_Model_DbTable_DbKnowBy();
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
	   			$dbKn->addKnowBy($_data);
	   			Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS",self::REDIRECT_URL);
	   		}catch(Exception $e){
	   			Application_Form_FrmMessage::message("EDIT_FAIL");
	   			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	   		}
	   	}
	   	$row = $dbKn->getKnowById($id);
	   	$this->view->row = $row;
	   	$frm = new Group_Form_FrmKnowBy();
	   	$frm_pro=$frm->FrmKnowBy($row);
   		Application_Model_Decorator::removeAllDecorator($frm_pro);
   		$this->view->frm_property_type = $frm_pro;
   }
}