<?php
class Rent_SettingController extends Zend_Controller_Action {
	const REDIRECT_URL = '/rent/setting';
	public function init()
    {    	
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
					'search'=>"",
    				'degree'=>"",
    				'academic_year'=>"",
				);
    		}
    		$this->view->search = $search;
			$db = new Rent_Model_DbTable_DbSetting();
			$rs_rows = $db->getAllSetting($search);
			$glClass = new Application_Model_GlobalClass();
			$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
			$list = new Application_Form_Frmtable();
    		$collumns = array("TITLE","STATUS","USER");
    		$link=array(
    				'module'=>'rent','controller'=>'setting','action'=>'edit',
    		);
    		$this->view->list=$list->getCheckList(10, $collumns, $rs_rows , array('start_date'=>$link,'title'=>$link ));
    		
		}catch (Exception $e){
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
    public function addAction()
    {	
    	$db = new Rent_Model_DbTable_DbSetting();
    	if($this->getRequest()->isPost()){
	    	try{
	    		$data = $this->getRequest()->getPost();
	    		$db->addMetionSetting($data);
	    		if(isset($data['save_close'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL."/index");
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL."/add");
				}
	    	}catch(Exception $e){
	    		Application_Form_FrmMessage::message("APPLICATION_ERROR");
	    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	    	}
    	}
    	$frm = new Rent_Form_FrmSetting();
    	$frm->FrmAddMetionSetting(null);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm = $frm;
    }
	public function editAction(){
		$db = new Rent_Model_DbTable_DbSetting();
		$id=$this->getRequest()->getParam('id');
		 
		if($this->getRequest()->isPost()){
	    	try{
	    		$data = $this->getRequest()->getPost();
	    		$db->editMentionSettingID($data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS",self::REDIRECT_URL."/index");
	    	}catch(Exception $e){
	    		Application_Form_FrmMessage::message("APPLICATION_ERROR");
	    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	    	}
    	}
    	$row = $db->getMentionSettingById($id);
    	if (empty($row)){
    		Application_Form_FrmMessage::Sucessfull("NO_RECORD", self::REDIRECT_URL."/index");
    		exit();
    	}
    	$this->view->row = $row;
    	$this->view->rowdetail = $db->getMentionSettingDetailById($id);
    	
    	$frm = new Rent_Form_FrmSetting();
    	$frm->FrmAddMetionSetting($row);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm = $frm;
	}
	public function copyAction(){
		$db = new Rent_Model_DbTable_DbSetting();
		$id=$this->getRequest()->getParam('id');
			
		if($this->getRequest()->isPost()){
			try{
				$data = $this->getRequest()->getPost();
				$db->addMetionSetting($data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS",self::REDIRECT_URL."/index");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("APPLICATION_ERROR");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$row = $db->getMentionSettingById($id);
		if (empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD", self::REDIRECT_URL."/index");
			exit();
		}
		$this->view->row = $row;
		$this->view->rowdetail = $db->getMentionSettingDetailById($id);
		 
		$frm = new Rent_Form_FrmSetting();
		$frm->FrmAddMetionSetting($row);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm = $frm;
	}
	function checkMentionExistingAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Rent_Model_DbTable_DbSetting();
			$row = $db->checkMentionAlreayExist($data);
			print_r(Zend_Json::encode($row));
			exit();
		}
	}
}