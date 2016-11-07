<?php

class Rsvacl_IndexController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }

    public function indexAction()
    {
    	Application_Form_FrmMessage::redirector('/rsvacl/user/index');
	}		 
    
}

