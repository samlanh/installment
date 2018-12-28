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
	public function incomeAction(){
		$this->_helper->layout()->disableLayout();
		$db = new Api_Model_DbTable_Dbapi();
		print_r(Zend_Json::encode($db->getAllIncome()));
		exit();
	}
	function expenseAction(){
		$db = new Api_Model_DbTable_Dbapi();
		print_r(Zend_Json::encode($db->getExpenseType()));
		exit();
	}
	function expensedetailAction(){
		$db = new Api_Model_DbTable_Dbapi();
		print_r(Zend_Json::encode($db->getAllDetailExpense()));
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
}

