<?php
class Api_IndexController extends Zend_Controller_Action {
	
// 	const REDIRECT_URL='/project';
	protected $tr;
	public function init()
	{
		$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();
		/* Initialize action controller here */
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
  		$this->_helper->layout()->disableLayout();
		$db = new Api_Model_DbTable_Dbapi();
		print_r(Zend_Json::encode($db->getAllSold()));
		exit();
	}
	public function salebytypeAction(){
		$this->_helper->layout()->disableLayout();
		$db = new Api_Model_DbTable_Dbapi();
		print_r(Zend_Json::encode($db->getSalebyType()));
		exit();
	}
	public function incomeAction(){
		$this->_helper->layout()->disableLayout();
		$db = new Api_Model_DbTable_Dbapi();
		print_r(Zend_Json::encode($db->getAllIncome()));
		exit();
	}
	function expenseAction(){
		$db = new Api_Model_DbTable_Dbapi();
		$rs_expense = $db->getExpenseType();
		$rs_comission = $db->getAllComissionbyType();
		$rs = array_merge($rs_expense,$rs_comission);
		
		$total_expense = 0;
		if(!empty($rs)){
			foreach($rs as $r){
				$total_expense = $total_expense+$r['total_expense'];	
			}
		}
		$total_expense = array('total_expense'=>number_format($total_expense,2));
		$rs = array('total_expense'=>$total_expense,'expense_bytype'=>$rs);
		print_r(Zend_Json::encode($rs));
		exit();
	}
	function expensedetailAction(){
		$db = new Api_Model_DbTable_Dbapi();
		$rs_expense = $db->getAllDetailExpense();
		$rs_comission = $db->getAllComissionDetail();
		$rs = array_merge($rs_expense,$rs_comission);
		print_r(Zend_Json::encode($rs));
		exit();
	}
	function expectincomeAction(){
		$db = new Api_Model_DbTable_Dbapi();
		print_r(Zend_Json::encode($db->getALLLoanExpectIncome()));
		exit();
	}
	function outstandingAction(){
		$db = new Api_Model_DbTable_Dbapi();
		print_r(Zend_Json::encode($db->getAllOutstadingLoan()));
		exit();
	}
	function cancelsaleAction(){
		$db = new Api_Model_DbTable_Dbapi();
		print_r(Zend_Json::encode($db->getAllSaleCancel()));
		exit();
	}
	function getdailyincomeAction(){
		$db = new Api_Model_DbTable_Dbapi();
		print_r(Zend_Json::encode($db->getDailyIncome()));
		exit();
	}
	function getdailyexpenseAction(){
		$db = new Api_Model_DbTable_Dbapi();
		print_r(Zend_Json::encode($db->getDailyExpense()));
		exit();
	}
	function dailycollectAction(){//ត្រូវបង់
		$db = new Api_Model_DbTable_Dbapi();
		print_r(Zend_Json::encode($db->getAllCollectPayment()));
		exit();
	}
}

