<?php
class Accounting_IncomeController extends Zend_Controller_Action {
	const REDIRECT_URL= "/accounting/Income";
	
    public function init()
    {    	
   
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}

    public function indexAction()
    {
    	try{
    		$db = new Accounting_Model_DbTable_DbIncome();
    		//     		if($this->getRequest()->isPost()){
    		//     			$search=$this->getRequest()->getPost();
    		//     		}
    		//     		else{
    		//     			$search = array(
    		//     					'adv_search' => '',
    		//     					'status' => -1);
    		//     		}
    		$rs_rows= $db->getAllasset($search=null);//call frome model
    		$glClass = new Application_Model_GlobalClass();
    		$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
    		$list = new Application_Form_Frmtable();
    		$collumns = array("BranchId ","Account No","Total Amount","For Date","Note","Date","Status");
    		$link=array(
    				'module'=>'accounting','controller'=>'expense','action'=>'edit',
    		);
    		$this->view->list=$list->getCheckList(0, $collumns,$rs_rows,array('account_id'=>$link,'total_amount'=>$link));
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		echo $e->getMessage();
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    	
    	
    }
    public function addAction()
    {
    	if($this->getRequest()->isPost()){
    		$agentdata=$this->getRequest()->getPost();
    		$db_agent = new Accounting_Model_DbTable_DbIncome();
    		try {
    			$db = $db_agent->addasset($agentdata);
    			//Application_Form_FrmMessage::Sucessfull('ការ​បញ្ចូល​​ជោគ​ជ័យ', self::REDIRECT_URL);
    		} catch (Exception $e) {
    			$this->view->msg = 'ការ​បញ្ចូល​មិន​ជោគ​ជ័យ';
    		}
    	
    }
    $fm = new Accounting_Form_Frmincome();
    $frm = $fm->FrmIncome();
    Application_Model_Decorator::removeAllDecorator($frm);
    $this->view->frm_income=$frm;
    
    }
    public function editAction()
    {
    	// action body
    	if($this->getRequest()->isPost()){
    		$agentdata=$this->getRequest()->getPost();
    		$db_agent = new Accounting_Model_DbTable_DbIncome();
    		try {
    			$db = $db_agent->updatasset($agentdata);
    			Application_Form_FrmMessage::Sucessfull('ការ​បញ្ចូល​​ជោគ​ជ័យ', self::REDIRECT_URL);
    		} catch (Exception $e) {
    			$this->view->msg = 'ការ​បញ្ចូល​មិន​ជោគ​ជ័យ';
    		}
    	}
    	$id = $this->getRequest()->getParam('id');
    	$db = new Accounting_Model_DbTable_DbIncome();
    	$row  = $db->getassetbyid($id);
    	$pructis=new Accounting_Form_Frmincome();
    	$frm = $pructis->FrmIncome($row);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_income=$frm;
    
   }

	 
}


