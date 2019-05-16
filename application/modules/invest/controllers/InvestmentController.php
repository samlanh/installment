<?php
class Invest_InvestmentController extends Zend_Controller_Action {
	private $activelist = array('មិនប្រើ​ប្រាស់', 'ប្រើ​ប្រាស់');
    public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	private $sex=array(1=>'M',2=>'F');
	public function indexAction(){
		try{
		    if($this->getRequest()->isPost()){
 				$search = $this->getRequest()->getPost();
 			}
			else{
				$search = array(
						'adv_search'=>'',
						'status' => -1,
						'investor_id'=>0,
						'broker_id'=>0,
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
						 );
			}
			$db = new Invest_Model_DbTable_DbInvestment();
			$rs_rows= $db->getAllInvestment($search);
			
			$list = new Application_Form_Frmtable();
			$collumns = array("INVEST_NO","INVESTOR","INVEST_DATE","AMOUNT_INVEST","DURATION_INVEST","BROKER","STATUS");
			$link=array('module'=>'invest','controller'=>'investment','action'=>'edit',);
			$this->view->list=$list->getCheckList(10, $collumns, $rs_rows,array(),0);
			
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}	
		
		$frm = new Application_Form_FrmAdvanceSearch();
		$frm = $frm->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
		
		$frm = new Invest_Form_FrmInvestment();
		$frm_loan=$frm->FrmAddInvestment();
		Application_Model_Decorator::removeAllDecorator($frm_loan);
		$this->view->frm = $frm_loan;
  }
  function addAction()
  {
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			
			try {
				$_dbmodel = new Invest_Model_DbTable_DbInvestment();
				$_dbmodel->addInvestment($_data);
				if(!empty($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/invest/investment");
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/invest/investment/add");
				}
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$frm = new Invest_Form_FrmInvestment();
		$frm_loan=$frm->FrmAddInvestment();
		Application_Model_Decorator::removeAllDecorator($frm_loan);
		$this->view->frm = $frm_loan;
		
	}
	
	function editAction()
	{
		$id = $this->getRequest()->getParam('id');
		$id = empty($id)?0:$id;
		
		$_dbmodel = new Invest_Model_DbTable_DbInvestment();
		$row = $_dbmodel->getInvestmentById($id);
		if (empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD","/invest/investment");
			exit();
		}
		
		$_hasPayment = $_dbmodel->checkInvestmentInWithdrawById($id);
		if (!empty($_hasPayment)){
			Application_Form_FrmMessage::Sucessfull("RECORD_HAS_SOME_WITHDRAW_CAN_NOT_EDIT","/invest/investment");
			exit();
		}
		
		$_hasPaymentBroker = $_dbmodel->checkInvestmentInWithdrawBrokerById($id);
		if (!empty($_hasPaymentBroker)){
			Application_Form_FrmMessage::Sucessfull("RECORD_BROKER_HAS_SOME_WITHDRAW_CAN_NOT_EDIT","/invest/investment");
			exit();
		}
		
		$this->view->detail = $_dbmodel->getInvestmentDetailById($id);
		$this->view->detailbroker = $_dbmodel->getInvestmentDetailBrokerById($id);
		
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
				
			try {
				
				$_dbmodel->editInvestment($_data);
				if(!empty($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/invest/investment");
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/invest/investment/add");
				}
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$frm = new Invest_Form_FrmInvestment();
		$frm_loan=$frm->FrmAddInvestment($row);
		Application_Model_Decorator::removeAllDecorator($frm_loan);
		$this->view->frm = $frm_loan;
	
	}
	
}

