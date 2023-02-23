<?php

class Invpayment_CateexpenseController extends Zend_Controller_Action
{
	const REDIRECT_URL = '/po/cateexpense';
	
    public function init()
    {
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }

    public function indexAction()
    {
    	try{ 
    		$db = new Invpayment_Model_DbTable_DbCateExpense();
    		if($this->getRequest()->isPost()){
    			$search=$this->getRequest()->getPost();
    		}
    		else{
    			$search = array(
    					"adv_search"=>'',
    					"currency_type"=>-1,
    					"status"=>-1,
    					'start_date'=> date('Y-m-d'),
    					'end_date'=>date('Y-m-d'),
    			);
    		}
    		
    		$this->view->adv_search = $search;
    		
			$rs_rows= $db->getAllCateIncome($search);//call frome model
			$this->view->row = $rs_rows;
			$list = new Application_Form_Frmtable();
			$collumns = array("TITLE","PARENT","ACCOUNT_CODE","CREATE_DATE","USER","STATUS");
			$link=array(
				'module'=>'invpayment','controller'=>'cateexpense','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(10, $collumns, $rs_rows, array('title'=>$link,'parentName'=>$link,'accountCode'=>$link,));

    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}

    	$frm = new Invpayment_Form_FrmSearchexpense();
    	$frm = $frm->AdvanceSearch();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_search = $frm;
    }
    public function addAction()
    {
		$db = new Invpayment_Model_DbTable_DbCateExpense();
    	if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();	
			try {
				$sms = "INSERT_SUCCESS";
				$cate = $db->addCateExpense($data);
				if($cate==-1){
					$sms = "RECORD_EXIST";
				}
				if(!empty($data['save_close'])){
					Application_Form_FrmMessage::Sucessfull($sms,"/invpayment/cateexpense");
				}else{
					Application_Form_FrmMessage::message($sms,"/invpayment/cateexpense/add");
				}				
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		$this->view->parent = $db->getParentCateExpense();
    }
 
    public function editAction()
    {
    	if($this->getRequest()->isPost()){
    		$id = $this->getRequest()->getParam('id');
			$data=$this->getRequest()->getPost();	
			$data['id']=$id;
			$db = new Invpayment_Model_DbTable_DbCateExpense();				
			try {
				$db->updateCateExpense($data);				
				Application_Form_FrmMessage::Sucessfull('EDIT_SUCCESS', "/invpayment/cateexpense");		
			} catch (Exception $e) {
				$this->view->msg = 'EDIT_FAIL';
			}
		}
		
		$id = $this->getRequest()->getParam('id');
		$db = new Invpayment_Model_DbTable_DbCateExpense();
		$row  = $db->getCateExpenseById($id);
		$this->view->rs = $row;
		
		$this->view->parent = $db->getParentCateExpense($id);
    }

}







