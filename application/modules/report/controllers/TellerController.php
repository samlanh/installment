<?php
class Report_TellerController extends Zend_Controller_Action {
	private $activelist = array('មិនប្រើ​ប្រាស់', 'ប្រើ​ប្រាស់');
    public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
  function indexAction(){
  	
  }
  function rptTellerAction(){
  	
  }
  function rptChangMoneyAction(){
  	
  }
  function rptExchangMoneyAction(){
  	
  }
  function rptScopeChangMoneyAction(){
  	
  }
  function rptListChangMoneyAction(){
  	
  }
  function rptTypeChangMoneyAction(){
  	
  }
}

