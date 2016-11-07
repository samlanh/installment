<?php

class Accounting_ExpenseController extends Zend_Controller_Action
{
	const REDIRECT_URL = '/accounting/expense';
	
    public function init()
    {
        /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }

    public function indexAction()
    {
    	try{
    		$db = new Accounting_Model_DbTable_DbExpense();
			$rs_rows= $db->getAllExpense($search=null);//call frome model
    		$glClass = new Application_Model_GlobalClass();
    		$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
    		$list = new Application_Form_Frmtable();
    		$collumns = array("BranchId ","Account No","Total Amount","For Date","Note","Date","Status");
    		$link=array(
    				'module'=>'accounting','controller'=>'expense','action'=>'index',
    		);
    		$this->view->list=$list->getCheckList(0, $collumns,$rs_rows,array('account_id'=>$link,'total_amount'=>$link));
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		echo $e->getMessage();
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }

 //djalv odigja oslfc kfsdalfij doflkdslkffkmaslcds fkds fklfasdj fldsa fadij fodslflsd jflsdf lsdj foal
    
    public function addAction()
    {
    	if($this->getRequest()->isPost()){
			$agentdata=$this->getRequest()->getPost();	
			$db_agent = new Accounting_Model_DbTable_DbExpense();				
			try {
				$db = $db_agent->addexpense($agentdata);				
				Application_Form_FrmMessage::Sucessfull('ការ​បញ្ចូល​​ជោគ​ជ័យ', self::REDIRECT_URL);		
			} catch (Exception $e) {
				$this->view->msg = 'ការ​បញ្ចូល​មិន​ជោគ​ជ័យ';
			}
		}
    	$pructis=new Accounting_Form_Frmexpense();
    	$frm = $pructis->FrmAddExpense();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_expense=$frm;
    }
   
    public function viewAction()
    {
        // action body
        $ag_id = $this->getRequest()->getParam('ag_id');
		
		$db_agent = new Application_Model_DbTable_DbAgents();
		$this->view->agent_view = $db_agent->getAgentViewById($ag_id);
    }
     
    
 
    public function editAction()
    {
        // action body
    	if($this->getRequest()->isPost()){
			$agentdata=$this->getRequest()->getPost();	
			$db_agent = new Accounting_Model_DbTable_DbExpense();				
			try {
				$db = $db_agent->updatexpense($agentdata);				
				Application_Form_FrmMessage::Sucessfull('ការ​បញ្ចូល​​ជោគ​ជ័យ', self::REDIRECT_URL);		
			} catch (Exception $e) {
				$this->view->msg = 'ការ​បញ្ចូល​មិន​ជោគ​ជ័យ';
			}
		}
		$id = $this->getRequest()->getParam('id');
// 		if(empty($id)){
// 			Application_Form_FrmMessage::Sucessfull('ការ​បញ្ចូល​​ជោគ​ជ័យ', self::REDIRECT_URL);
// 		}
		$db = new Accounting_Model_DbTable_DbExpense();
		$row  = $db->getexpensebyid($id);
    	$pructis=new Accounting_Form_Frmexpense();
    	$frm = $pructis->FrmAddExpense($row);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_expense=$frm;
		
    	
    }


}







