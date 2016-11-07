<?php

class Callecterall_ChengcallecterallController extends Zend_Controller_Action
{
	const REDIRECT_URL='/callecterall';
    public function init()
    {
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());    	
    }
	public function indexAction(){
		try{
			$db = new Group_Model_DbTable_DbChangeCollteral();
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'adv_search' => '',
						'status_search' => -1);
			}//print_r($search);exit();
			$rs_rows= $db->getAllChangeCollteral($search);//call frome model
			$glClass = new Application_Model_GlobalClass();
			$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH","OWNER_CODE","OWNER_NAME","FROM","TO","COLLTERAL_TYPE","NUMBER_CODE","OWNER","DATE","NOTE","STATUS","USER_ID");
			$link=array(
					'module'=>'callecterall','controller'=>'chengcallecterall','action'=>'edit',
			);
			// 			print_r($rs_rows);exit();
			$this->view->list=$list->getCheckList(0, $collumns,$rs_rows,array('branch_id'=>$link,'owner_code_id'=>$link,'owner_id'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			echo $e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$fm = new Group_Form_Frmchangecollteral();
		$frm = $fm->FrmChangeCollteral();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_changeCollteral = $frm;
	}
    public function addAction()
    {
    	if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Group_Model_DbTable_DbChangeCollteral();
			try {				
				if(!empty($data['save_new'])){
					$db = $db->addChangeCollteral($data);
					Application_Form_FrmMessage::message('ការ​បញ្ចូល​​ជោគ​ជ័យ');
				}else{
					$db = $db->addChangeCollteral($data);
					Application_Form_FrmMessage::Sucessfull('ការ​បញ្ចូល​​ជោគ​ជ័យ', self::REDIRECT_URL . '/chengcallecterall/index');
				}
			} catch (Exception $e) { 
				echo $e->getMessage();exit();
				$this->view->msg = 'ការ​បញ្ចូល​មិន​ជោគ​ជ័យ';
			}
		}
		$fm = new Group_Form_Frmchangecollteral();
		$frm = $fm->FrmChangeCollteral();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_changeCollteral = $frm;
	   
    }
    function getChangeinfoAction(){
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$db = new Callecterall_Model_DbTable_DbChangeCallecterall();
    		$row = $db->getOwnerbyid($data['owner_id']);
    		print_r(Zend_Json::encode($row));
    		exit();
    	}
    }
    public function editAction()
    {
    	$db = new Group_Model_DbTable_DbChangeCollteral();
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		try {
    			//print_r($data);exit();    			
    			if(!empty($data['save_new'])){
    				$db = $db->updateChangeCollteral($data);
    				Application_Form_FrmMessage::message('ការ​បញ្ចូល​​ជោគ​ជ័យ');
    			}else{
    				$db = $db->updateChangeCollteral($data);
    				Application_Form_FrmMessage::Sucessfull('ការ​បញ្ចូល​​ជោគ​ជ័យ', self::REDIRECT_URL. '/chengcallecterall/index');
    			}
    			
    		} catch (Exception $e) {
    			$this->view->msg = 'ការ​បញ្ចូល​មិន​ជោគ​ជ័យ';
    		}
    	}
    	$id = $this->getRequest()->getParam('id');
    	if(empty($id)){
    		Application_Form_FrmMessage::Sucessfull('ការ​បញ្ចូល​​ជោគ​ជ័យ', self::REDIRECT_URL);
    	}
    	$row  = $db->getChangeCollteralbyid($id);
    	$fm = new Group_Form_Frmchangecollteral();
    	$frm = $fm->FrmChangeCollteral($row);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_changeCollteral = $frm;
    
    }
    public function getOwnerinfoAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db =new Group_Model_DbTable_DbChangeCollteral();
    		$row=$db->getOwnerInfo($data['owner_id']);
    		print_r(Zend_Json::encode($row));
    		exit();
    	}
    }
}