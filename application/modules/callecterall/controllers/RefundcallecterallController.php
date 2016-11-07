<?php

class Callecterall_RefundcallecterallController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    	
    }
	public function indexAction(){
		
	}
    public function addAction()
    {
       $fm = new Callecterall_Form_Frmrefundcallecterall();
	   $frm = $fm->Frmrefundcallecterall(); 
	   Application_Model_Decorator::removeAllDecorator($frm);
	   $this->view->Form_Frmcallecterall = $frm;
	   
    }
}

   