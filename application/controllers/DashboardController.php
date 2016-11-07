<?php

class DashboardController extends Zend_Controller_Action
{
    public function indexAction()
    {
       $this->_helper->layout()->disableLayout();
        
    }
	public  function errorAction(){
		$this->_helper->layout()->disableLayout();
	}
	public  function menuAction(){
		$this->_helper->layout()->disableLayout();
	}
}





