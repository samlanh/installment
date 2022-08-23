<?php

class Loan_LoanTypeController extends Zend_Controller_Action
{
	protected $tr;
public function init()
    {
    	$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();
        /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }

    public function addAction()
    {
    	if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Loan_Model_DbTable_DbLoanType();
			try {
				$db->addViewType($data);
				if(isset($data['save_new'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/loan/loantype/add");
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/loan/loantype");
				}
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				$err = $e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
       $fm = new Loan_Form_Frmtype();
	   $frm = $fm->FrmViewType(); 
	   Application_Model_Decorator::removeAllDecorator($frm);
	   $this->view->Form_Frmcallecterall = $frm;
    }
    public function indexAction()
    {
    	try{
    		$db = new Loan_Model_DbTable_DbLoanType();
    		if($this->getRequest()->isPost()){
    			$search = $this->getRequest()->getPost();
    		}
    		else{
    			$search = array(
    					'adv_search' => '',
    					'type'=>-1,
    					'status' => -1);
    		}
    		$rs_rows= $db->getAllviewBYType($search);//call frome model
    		$glClass = new Application_Model_GlobalClass();
    		$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);   		
    		$this->view->row = $rs_rows;
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    	$frm = new Loan_Form_FrmSearchLoanType();
    	$frm = $frm->AdvanceSearch();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_search = $frm;
    }
    public function editAction()
    {
    if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Loan_Model_DbTable_DbLoanType();
			try {
				$db->updatViewById($data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/loan/loantype");
				
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("EDIT_FAIL");
				$err = $e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		
    	}
    	$id = $this->getRequest()->getParam('id');
    		
    	$db = new Loan_Model_DbTable_DbLoanType();
    	$row  = $db->getListViewById($id);
    	$fm = new Loan_Form_Frmtype();
    	$frm = $fm->FrmViewType($row);
	    Application_Model_Decorator::removeAllDecorator($frm);
	    $this->view->Form_Frmcallecterall = $frm;
	    $this->view->rs  = $row;
    }
}
?>
