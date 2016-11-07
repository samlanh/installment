<?php

class Callecterall_SalecallecterallController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    	
    }

    public function addAction()
    {
    	
       $fm = new Callecterall_Form_Frmsalecallecterall();
	   $frm = $fm->Frmsalecallecterall(); 
	   Application_Model_Decorator::removeAllDecorator($frm);
	   $this->view->Form_Frmcallecterall = $frm;
	   
    }
}

   