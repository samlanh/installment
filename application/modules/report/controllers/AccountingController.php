<?php
class Report_AccountingController extends Zend_Controller_Action {
	private $activelist = array('មិនប្រើ​ប្រាស់', 'ប្រើ​ប្រាស់');
    public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
  function indexAction(){
  	
  }
function rptFixedAssetAction(){
	
}
function  rptBuyFixedAssetAction(){
	
}
  function rptGeneralfJurnalAction(){
  	
}
function rptSaleFixedAssetAction(){
	
}
function rptIncomeAction(){
	
}
function rptBalanceSheetAction(){
	
}
function rptIncomeStatementAction(){
	
}
function rptAccountNameAction(){
	
}
function rptLegerAction(){
	$db  = new Report_Model_DbTable_DbAccounting();
	if($this->getRequest()->isPost()){
		$search = $this->getRequest()->getPost();
	}
	else{
		$search = array(
				'branch_id'=>'',
				'adv_search'=>'',
				'pay_every'=>-1,
				'currency_type'=>'',
				'start_date'=> date('Y-m-d'),
				'end_date'=>date('Y-m-d'));
	}
	
	$this->view->jurnal_list=$db->getAllLegerReport($search);
	print_r($db->getAllLegerReport($search));
	$this->view->list_end_date=$search;
	
	$frm = new Loan_Form_FrmSearchLoan();
	$frm = $frm->JurnalSearch();
	Application_Model_Decorator::removeAllDecorator($frm);
	$this->view->frm_search = $frm;
	
	$key = new Application_Model_DbTable_DbKeycode();
	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
	
}
function rptBalanceAction(){
	
}
function rptClosingAction(){
	
}
function rptCashflowAction(){
	
}
function rptJurnalAction(){
	$db  = new Report_Model_DbTable_DbAccounting();
	if($this->getRequest()->isPost()){
		$search = $this->getRequest()->getPost();
	}
	else{
		$search = array(
			'branch_id'=>'',
		    'adv_search'=>'',
		 	'pay_every'=>-1,
		 	'currency_type'=>'',
		    'start_date'=> date('Y-m-d'),
			'end_date'=>date('Y-m-d'));
	}
	 
	$this->view->jurnal_list=$db->getGaneralJurnal($search);
	$this->view->list_end_date=$search;
	
	$frm = new Loan_Form_FrmSearchLoan();
	$frm = $frm->JurnalSearch();
	Application_Model_Decorator::removeAllDecorator($frm);
	$this->view->frm_search = $frm;
	 
	$key = new Application_Model_DbTable_DbKeycode();
	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
}
	function rptAccountcateAction(){
		
	}
	function rptChartaccountAction(){
		
	}
	function rptOwnerequityAction(){
		
	}
	function rptBalancsheetAction(){
		
	}
	function rptOwerEquityAction(){
		
	}
	function rptTrialbalanceAction(){
		$db  = new Report_Model_DbTable_DbAccounting();
		if($this->getRequest()->isPost()){
			$search = $this->getRequest()->getPost();
		}
		else{
			$search = array(
					'branch_id'=>'',
					'adv_search'=>'',
					'pay_every'=>-1,
					'currency_type'=>'',
					'start_date'=> date('Y-m-d'),
					'end_date'=>date('Y-m-d'));
		}
		
		$this->view->jurnal_list=$db->getGaneralJurnal($search);
		$this->view->list_end_date=$search;
		
		$frm = new Loan_Form_FrmSearchLoan();
		$frm = $frm->JurnalSearch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
		
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data=$key->getKeyCodeMiniInv(TRUE);
	}
}

