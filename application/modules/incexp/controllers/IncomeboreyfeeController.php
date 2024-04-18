<?php

class Incexp_IncomeboreyfeeController extends Zend_Controller_Action
{
    public function init()
    {
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }
    public function indexAction()
    {
    	try{
    		$db = new Incexp_Model_DbTable_DbIncomeboreyfee();
    		if($this->getRequest()->isPost()){
    			$search=$this->getRequest()->getPost();
    		}
    		else{
    			$search = array(
    					"adv_search"=>'',
    					"branch_id"=>-1,
    					"category_id"=>'',
    					'start_date'=> date('Y-m-d'),
    					'end_date'=>date('Y-m-d'),
    					'land_id'=>-1,
    					'client_name'=>-1,
    					"status"=>-1,
    			);
    		}
    		$this->view->adv_search = $search;
			$rs_rows= $db->getAllIncomeBoreyFee($search);//call frome model
    		
    		$list = new Application_Form_Frmtable();
    		$collumns = array("BRANCH_NAME","CUSTOMER_NAME","PROPERTY_CODE","INCOME_TITLE","RECEIPT_NO","CATEGORY","PAYMENT_TYPE","TOTAL_INCOME","NOTE","DATE","BY_USER","STATUS");
    		$link=array('module'=>'incexp','controller'=>'incomeboreyfee','action'=>'edit');
    		$link1=array('module'=>'report','controller'=>'incomeexpense','action'=>'receipt-otherincome');
    		$this->view->list=$list->getCheckList(10, $collumns,$rs_rows,array('បោះពុម្ភ'=>$link1,'branch_name'=>$link,'client_name'=>$link,'title'=>$link,'invoice'=>$link,'house_no'=>$link));
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}

    	
    	$frm = new Loan_Form_FrmSearchLoan();
    	$frm = $frm->AdvanceSearch();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_search = $frm;
    	$this->view->rssearch = $search;
    }
    public function addAction()
    {
    	if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();	
			$db = new Incexp_Model_DbTable_DbIncomeboreyfee();				
			try {
				
				// Check Session Expire
				$dbgb = new Application_Model_DbTable_DbGlobal();
				$checkses = $dbgb->checkSessionExpire();
				if (empty($checkses)){
					$dbgb->reloadPageExpireSession();
					exit();
				}
				
				$db->addIncomeBoreyFee($data);
				if(!empty($data['saveclose'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/incexp/incomeboreyfee");
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/incexp/incomeboreyfee/add");
				}				
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		
		
    	$pructis=new Incexp_Form_Frmexpense();
    	$frm = $pructis->FrmAddExpense();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_expense=$frm;
    	
    	$key = new Application_Model_DbTable_DbKeycode();
    	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
    	
    	$frmpopup = new Application_Form_FrmPopupGlobal();
    	$this->view->footer = $frmpopup->getFooterReceipt();
    	$this->view->officailreceipt = $frmpopup->templateIncomeReceipt();
		
		$dbInc = new Incexp_Model_DbTable_DbIncome();
		$result = $dbInc->getAllIncomeCategory();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		array_unshift($result, array ( 'id' => -1,'name' => $tr->translate("ADD_NEW")));
		$this->view->all_category = $result;
    }
 
    public function editAction()
    {
    	$id = $this->getRequest()->getParam('id');
    	$id = empty($id)?0:$id;
    	if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();	
			$db = new Incexp_Model_DbTable_DbIncomeboreyfee();				
			try {
				// Check Session Expire
				$dbgb = new Application_Model_DbTable_DbGlobal();
				$checkses = $dbgb->checkSessionExpire();
				if (empty($checkses)){
					$dbgb->reloadPageExpireSession();
					exit();
				}
				$db->updateIncomeBoreyFee($data);				
				Application_Form_FrmMessage::Sucessfull('UPDATE_SUCESS', "/incexp/incomeboreyfee");		
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("UPDATE_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		$db = new Incexp_Model_DbTable_DbIncomeboreyfee();
		$row  = $db->getexpensebyid($id);
		if(empty($row)){
			Application_Form_FrmMessage::Sucessfull("RECORD_NOTFUND","/incexp/incomeboreyfee",2);
			exit();
		}else if ($row['is_closed']==1){
			Application_Form_FrmMessage::Sucessfull("Unable edit closed record","/incexp/incomeboreyfee",2);
			exit();
		}
// 		$row['payment_id']=0;
		$this->view->row = $row;
		
    	$pructis=new Incexp_Form_Frmexpense();
    	$frm = $pructis->FrmAddExpense($row);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_expense=$frm;
    	
    	
    	$key = new Application_Model_DbTable_DbKeycode();
    	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
    	
    	$frmpopup = new Application_Form_FrmPopupGlobal();
    	$this->view->footer = $frmpopup->getFooterReceipt();
		$this->view->officailreceipt = $frmpopup->templateIncomeReceipt();
		
		$dbInc = new Incexp_Model_DbTable_DbIncome();
		$result = $dbInc->getAllIncomeCategory();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		array_unshift($result, array ( 'id' => -1,'name' => $tr->translate("ADD_NEW")));
		$this->view->all_category = $result;
    }
}