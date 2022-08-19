<?php

class Issue_StepoptionController extends Zend_Controller_Action
{
	const REDIRECT_URL = '/issue/stepoption';
	
    public function init()
    {
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }
    public function indexAction()
    {
    	try{
    		$db = new Issue_Model_DbTable_DbStepOption();
    		if($this->getRequest()->isPost()){
    			$formdata=$this->getRequest()->getPost();
    		}
    		else{
    			$formdata = array(
    					"adv_search"=>'',
    					"status"=>-1,
    			);
    		}
    		
			$rs_rows= $db->getAllStepOption($formdata);//call frome model
    		$list = new Application_Form_Frmtable();
    		$collumns = array("TITLE","STATUS");
    		$link=array(
    				'module'=>'issue','controller'=>'stepoption','action'=>'edit',
    		);
    		$this->view->list=$list->getCheckList(0, $collumns,$rs_rows,array('title'=>$link,'status'=>$link));
    		
    		$this->view->search = $formdata;
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		echo $e->getMessage();
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    	$frm = new Loan_Form_FrmSearchLoan();
    	$frm = $frm->AdvanceSearch();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_search = $frm;
    }
    public function addAction()
    {
    	if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();	
			$db = new Issue_Model_DbTable_DbStepOption();				
			try {
				$db->addStepOption($data);
				if(!empty($data['saveclose'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL);
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL."/add");
				}				
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
    	$pructis=new Issue_Form_FrmStepOption();
    	$frm = $pructis->FrmAddStepOption();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_stepoption=$frm;
    }
 
    public function editAction()
    {
    	$db = new Issue_Model_DbTable_DbStepOption();
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		try {
    			$db->updateStepOption($data);
    			Application_Form_FrmMessage::Sucessfull("UPDATE_SUCCESS",self::REDIRECT_URL);
    		} catch (Exception $e) {
    			Application_Form_FrmMessage::message("UPDATE_FAIL");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
    	}
    	
    	$id = $this->getRequest()->getParam('id');
    	$id = empty($id)?0:$id;
    	$row = $db->getStepoptionBYID($id);
    	if (empty($row)){
    		Application_Form_FrmMessage::Sucessfull("NO_RECORD",self::REDIRECT_URL,2);
    		exit();
    	}
    	$this->view->row = $row;
    	$pructis=new Issue_Form_FrmStepOption();
    	$frm = $pructis->FrmAddStepOption($row);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_stepoption=$frm;
    }
    
}







