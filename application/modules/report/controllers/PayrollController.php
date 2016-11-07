<?php
class Report_PayrollController extends Zend_Controller_Action {
	private $activelist = array('មិនប្រើ​ប្រាស់', 'ប្រើ​ប្រាស់');
    public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
  function indexAction(){
  	
  }
  function  rptIndividualClientAction(){
//   	$db  = new Report_Model_DbTable_DbPayroll();
//   	$this->view->staff_list = $db->getAllIndividual();
//   	$key = new Application_Model_DbTable_DbKeycode();
//   	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
  }
  function rptGroupClientAction(){
  	
  }
  function rptClientAgreementAction(){
  	
  }

  
}

