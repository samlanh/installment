<?php
class Loan_ItemsmeterialController extends Zend_Controller_Action {
	const REDIRECT_URL = '/loan/itemsmeterial';
    public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
			$db = new Loan_Model_DbTable_DbItems();
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
					'adv_search' => '',
					'status_search' => -1,);
			}
			$rs_rows= $db->geteAllItemsMaterial($search);
			$list = new Application_Form_Frmtable();
			$collumns = array("TITLE","NOTE","USER_NAME","STATUS");
			$link=array(
					'module'=>'loan','controller'=>'itemsmeterial','action'=>'edit',
			);
 			$this->view->list=$list->getCheckList(10, $collumns,$rs_rows,array('title'=>$link,'note'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$frm = new Loan_Form_FrmItems();
   		$frm_pro=$frm->FrmItem();
   		Application_Model_Decorator::removeAllDecorator($frm_pro);
   		$this->view->frm_property_type = $frm_pro;
   }
   function addAction(){
	   	if($this->getRequest()->isPost()){
	   		$_data = $this->getRequest()->getPost();
	   		$db = new Loan_Model_DbTable_DbItems();
	   		try{
	   			// Check Session Expire
	   			$dbgb = new Application_Model_DbTable_DbGlobal();
	   			$checkses = $dbgb->checkSessionExpire();
	   			if (empty($checkses)){
	   				$dbgb->reloadPageExpireSession();
	   				exit();
	   			}
	   			
	   			$db->addItems($_data);
				if(!empty($_data['save_new'])){
					Application_Form_FrmMessage::Sucessfull('INSERT_SUCCESS', self::REDIRECT_URL . '/add');
				}
				Application_Form_FrmMessage::Sucessfull('INSERT_SUCCESS', self::REDIRECT_URL . '/index');
	   		}catch(Exception $e){
	   			Application_Form_FrmMessage::message("INSERT_FAIL");
	   			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	   		}
	   	} 	
	   	$frm = new Loan_Form_FrmItems();
	   	$frm_pro=$frm->FrmItem();
	   	Application_Model_Decorator::removeAllDecorator($frm_pro);
	   	$this->view->frm_property_type = $frm_pro;
   }
   function editAction(){
	   	$id = $this->getRequest()->getParam("id");
		$id = empty($id)?0:$id;
	   	$db_co = new Loan_Model_DbTable_DbItems();
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
	   			$db_co->addItems($_data);
	   			Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",'/loan/itemsmeterial');
	   		}catch(Exception $e){
	   			Application_Form_FrmMessage::message("INSERT_FAIL");
	   			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	   		}
	   	}
	   	$row = $db_co->getItemsById($id);
	   	$frm = new Loan_Form_FrmItems();
	   	$frm_pro=$frm->FrmItem($row);
   		Application_Model_Decorator::removeAllDecorator($frm_pro);
   		$this->view->frm_property_type = $frm_pro;
   }
}