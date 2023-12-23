<?php

class Incexp_InvexpenseController extends Zend_Controller_Action
{
	const REDIRECT_URL = '/incexp/invexpense';
    public function init()
    {
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }
    public function indexAction()
    {
    	try{
    		$db = new Incexp_Model_DbTable_DbInvExpense();
    		if($this->getRequest()->isPost()){
    			$formdata=$this->getRequest()->getPost();
    		}
    		else{
    			$formdata = array(
    					"adv_search"=>'',
    					"branch_id"=>-1,
     					"category_id_expense"=>-1,
    					'payment_type'=>-1,
    					'supplier_id'=>'',
    					'status'=>-1,
    					'cheque_issuer_search'=>'',
    					'start_date'=> date('Y-m-d'),
    					'end_date'=>date('Y-m-d'),
    			);
    		}
    		$this->view->adv_search = $formdata;
			$rs_rows= $db->getAllInvExpensee($formdata);//call frome model
    		$list = new Application_Form_Frmtable();
    		$collumns = array("BRANCH_NAME","SUPPLIER","EXPENSE_TITLE","INVOICE","CATEGORY","TOTAL_EXPENSE",
    					"DATE","BY_USER","STATUS");
    		$link=array(
    				'module'=>'incexp','controller'=>'invexpense','action'=>'edit',
    		);
    		$this->view->list=$list->getCheckList(10, $collumns,$rs_rows,array('branch_name'=>$link,'supplier'=>$link,'title'=>$link,'invoice'=>$link));
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    	$frm = new Loan_Form_FrmSearchLoan();
    	$frm = $frm->AdvanceSearch();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_search = $frm;
    	
    	$pructis=new Incexp_Form_FrmInvExpense();
    	$frm = $pructis->FrmAddInvExpense();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_expense=$frm;
    }
    public function addAction()
    {
    	if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();	
			$db = new Incexp_Model_DbTable_DbInvExpense();	
// 			$test = $db->getBranchId();
			try {
				// Check Session Expire
				$dbgb = new Application_Model_DbTable_DbGlobal();
				$checkses = $dbgb->checkSessionExpire();
				if (empty($checkses)){
					$dbgb->reloadPageExpireSession();
					exit();
				}
				
				if (empty($data)){
					Application_Form_FrmMessage::Sucessfull("File Attachment to large can't upload and Save data !","/incexp/invexpense",2);
					exit();
				}
			
				$db->addInvExpense($data);
				if(!empty($data['saveclose'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/incexp/invexpense");
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/incexp/invexpense/add");
				}				
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
    	$pructis=new Incexp_Form_FrmInvExpense();
    	$frm = $pructis->FrmAddInvExpense();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_expense=$frm;
    	
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$dbExp = new Incexp_Model_DbTable_DbExpense();
    	$result = $dbExp->getAllExpenseCategory();
    	array_unshift($result, array ( 'id' => -1,'name' => $tr->translate("ADD_NEW")));
    	$this->view->all_category = $result;
    	
    	$key = new Application_Model_DbTable_DbKeycode();
    	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
    	
    	$frmpopup = new Application_Form_FrmPopupGlobal();
    	$this->view->footer = $frmpopup->getFooterReceipt();
		$this->view->officailreceipt = $frmpopup->templateExpenseReceipt();
    }
    public function editAction()
    {
    	$id = $this->getRequest()->getParam('id');
    	$id = empty($id)?0:$id;
    	if($this->getRequest()->isPost()){
    		
    		// Check Session Expire
    		$dbgb = new Application_Model_DbTable_DbGlobal();
    		$checkses = $dbgb->checkSessionExpire();
    		if (empty($checkses)){
    			$dbgb->reloadPageExpireSession();
    			exit();
    		}
    		
			$data=$this->getRequest()->getPost();	
			$data['id'] = $id;
			$db = new Incexp_Model_DbTable_DbInvExpense();				
			try {
				if (empty($data)){
					Application_Form_FrmMessage::Sucessfull("File Attachment to large can't upload and Save data !","/incexp/expense",2);
					exit();
				}
				
				$db->updatInvExpense($data);				
				Application_Form_FrmMessage::Sucessfull('UPDATE_SUCESS', self::REDIRECT_URL);		
			} catch (Exception $e) {
				$this->view->msg = 'UPDATE_FAIL';
			}
		}
		$id = $this->getRequest()->getParam('id');
		$id = empty($id)?0:$id;
		$db = new Incexp_Model_DbTable_DbInvExpense();
		$row  = $db->getInvExpenseById($id);
		if(empty($row)){
			Application_Form_FrmMessage::Sucessfull("RECORD_NOTFUND",self::REDIRECT_URL,2);
			exit();
		}else if ($row['is_paid']==1){
			Application_Form_FrmMessage::Sucessfull("This Expense Already Payment",self::REDIRECT_URL,2);
			exit();
		}
		
		$haspay = $db->checkHaspayment($id);
		if (!empty($haspay)){
			Application_Form_FrmMessage::Sucessfull("This Expense has paid on some payment ready",self::REDIRECT_URL,2);
			exit();
		}
		
		$this->view->row = $row;
		$this->view->row_pur_detai=$db->getExpenseDetail($id);	
		
    	$pructis=new Incexp_Form_FrmInvExpense();
    	$frm = $pructis->FrmAddInvExpense($row);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_expense=$frm;
		
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$dbExp = new Incexp_Model_DbTable_DbExpense();
    	$result = $dbExp->getAllExpenseCategory();
    	array_unshift($result, array ( 'id' => -1,'name' => $tr->translate("ADD_NEW")));
    	$this->view->all_category = $result;
    	
    	$key = new Application_Model_DbTable_DbKeycode();
    	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
    	
    	$frmpopup = new Application_Form_FrmPopupGlobal();
    	$this->view->footer = $frmpopup->getFooterReceipt();
		$this->view->officailreceipt = $frmpopup->templateExpenseReceipt();
    }
    
    
    
    
    
}







