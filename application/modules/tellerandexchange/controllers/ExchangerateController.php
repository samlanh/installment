<?php

class Tellerandexchange_ExchangerateController extends Zend_Controller_Action
{

	const REDIRECT_URL = '/exchange';
	
    public function init()
    {
        /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	
    	//clear all other sessions
    	Application_Form_FrmSessionManager::clearSessionSearch();
    	
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }

    public function indexAction()
    {
        // action body	
        try {
	     
        } catch (Exception $e) {
        }	
    }

    
    public function addAction()
    {
       
        
    }
    public function exchangeAction()
    {
    	
    
    }
    
//     public function rateAction(){
//     	$db_rate=new Application_Model_DbTable_DbRate();
    	
//     	if($this->getRequest()->isPost()){
//     		$formdata=$this->getRequest()->getPost();
//     		$db_rate->setNewRate($formdata);
//     	}
    	
//     	$this->view->ratelist = $db_rate->getCurrentRate();
//     	$db_keycode = new Application_Model_DbTable_DbKeycode();
//     	$this->view->keycode = $db_keycode->getKeyCodeMiniInv();
//     	$session_user=new Zend_Session_Namespace('auth');
//     	$this->view->user_name = $session_user->last_name .' '. $session_user->first_name;
//     }
    public function showrateAction(){
    	
    }
}