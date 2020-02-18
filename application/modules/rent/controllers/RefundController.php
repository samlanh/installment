<?php
class Rent_RefundController extends Zend_Controller_Action {
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
				$search = array(
						'adv_search'=>'',
						'client_name'=> '',
						'branch_id' => -1,
						'status' => -1,
						'payment_method'=>0,
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
						 );
			}
			$db = new Rent_Model_DbTable_DbRefund();
			$rs_rows= $db->getAllRefund($search);
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH_NAME","RENT_NO","REFUND_DATE","PAYMENT_TYPE","NUMBER","REFUND_AMOUNT",
				"BY_USER","STATUS");
			$link_info=array('module'=>'rent','controller'=>'refund','action'=>'edit',);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array(),0);
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}	
		$frm_search = new Rent_Form_FrmRefund();
		$frm = $frm_search->FrmSearchRefund();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
  }
  function addAction()
  {
  		$_dbmodel = new Rent_Model_DbTable_DbRefund();
		if($this->getRequest()->isPost()){
			// Check Session Expire
			$dbgb = new Application_Model_DbTable_DbGlobal();
			$checkses = $dbgb->checkSessionExpire();
			if (empty($checkses)){
				$dbgb->reloadPageExpireSession();
				exit();
			}
		   $_data = $this->getRequest()->getPost();
		   try {
				
				$_dbmodel->addRefund($_data);
				if(!empty($_data['saveclose'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/rent/refund");
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/rent/refund/add");
				}
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$this->cheque_issue = $_dbmodel->getAllChequeIssue();
		
		$frm = new Rent_Form_FrmRefund();
		$frm_loan=$frm->FrmAddRefund();
		Application_Model_Decorator::removeAllDecorator($frm_loan);
		$this->view->frm = $frm_loan;
		
		
	}	
}