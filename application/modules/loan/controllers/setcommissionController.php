<?php
class Loan_SetcommissionController extends Zend_Controller_Action {
	
	protected $tr;
    public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function indexAction(){
		try{
		    if($this->getRequest()->isPost()){
 				$search = $this->getRequest()->getPost();
 			}
			else{
				$search = array(
					'txt_search'=>'',
					'client_name'=> -1,
					'schedule_opt' => -1,
					'branch_id' => -1,
					'streetlist'=>'',
					'status' => -1,
					'co_id' => -1,
					'land_id'=>-1,
					'start_date'=> date('Y-m-d'),
					'end_date'=>date('Y-m-d'),
				);
			}
			$db = new Loan_Model_DbTable_DbSetCommission();
			$rs_rows= $db->getAllIndividuleLoan($search);
			$glClass = new Application_Model_GlobalClass();
			$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH_NAME","AGENCY_NAME","COMISSION_EXPECTED","COMISSION_PAID","PROPERTY_CODE","PAYMENT_TYPE","SOLD_PRICE",
					"PAID","BALANCE","DATE_BUY","BY_USER","STATUS","IS_CANCEL");
			$link_info=array('module'=>'loan','controller'=>'setcommission','action'=>'edit',);

			$this->view->list=$list->getCheckList(10, $collumns, $rs_rows,array(),0);
			
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}	
		$frm_search = new Loan_Form_FrmSearchLoan();
		$frm = $frm_search->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
		$this->view->rssearch = $search;

  }
	function addAction()
	{
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				// Check Session Expire
				$dbgb = new Application_Model_DbTable_DbGlobal();
				$checkses = $dbgb->checkSessionExpire();
				if (empty($checkses)){
					$dbgb->reloadPageExpireSession();
					exit();
				}
				
				$_dbmodel = new Loan_Model_DbTable_DbSetCommission();
				$_dbmodel->setCommissionAgency($_data);
				if(!empty($_data['saveclose'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/loan/setcommission");
				}
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/loan/setcommission/add");
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$frm = new Loan_Form_FrmSetCommission();
		$frm_loan=$frm->FrmAddSetCommission();
		Application_Model_Decorator::removeAllDecorator($frm_loan);
		$this->view->frm_loan = $frm_loan;
	}
	
	function editAction()
	{
		$id = $this->getRequest()->getParam("id");
		$id = empty($id)?0:$id;
		
		$_dbmodel = new Loan_Model_DbTable_DbSetCommission();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				// Check Session Expire
				$dbgb = new Application_Model_DbTable_DbGlobal();
				$checkses = $dbgb->checkSessionExpire();
				if (empty($checkses)){
					$dbgb->reloadPageExpireSession();
					exit();
				}
				
				$_dbmodel->editSetCommissionAgency($_data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/loan/setcommission");
				
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		
		$row = $_dbmodel->getSaleSetCommissionBySaleId($id);
		if(empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD","/loan/setcommission");
		}
		if($row['totoalCmminssionPaid']>=$row['full_commission']){
			//Application_Form_FrmMessage::Sucessfull("COMMISSION_PAYMENT_READY","/loan/setcommission");
		}
		$this->view->rs = $row;
		
		$frm = new Loan_Form_FrmSetCommission();
		$frm_loan=$frm->FrmAddSetCommission($row);
		Application_Model_Decorator::removeAllDecorator($frm_loan);
		$this->view->frm_loan = $frm_loan;
	}
	
	function getsalenosetcommissionAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Loan_Model_DbTable_DbSetCommission();
			$row = $db->getSaleNotSetCommission($data);
			print_r(Zend_Json::encode($row));
			exit();
		}
	}
	
}