<?php

class Home_FrontdeskController extends Zend_Controller_Action
{
    public function indexAction()
    {
       $this->_helper->layout()->disableLayout();
        
    }
	function frontdeskdetailAction(){
		$this->_helper->layout()->disableLayout();
		$db = new Home_Model_DbTable_DbFrontdesk();
		$propertyId = $this->getRequest()->getParam("propertyId");
		if(empty($id)){
			//Application_Form_FrmMessage::Sucessfull("NO_DATA","/home/frontdesk/index");
		}
		$saleId = $this->getRequest()->getParam("saleId");

		// print_r($propertyId);
		// exit();
		$data = array(
			"propertyId"=> $propertyId,
			"saleId"=> $saleId 
		);

		$this->view->resultData = $db->frontdeskResultInfo($data);
		
	}
	public  function errorAction(){
		$this->_helper->layout()->disableLayout();
	}
	public  function menuAction(){
		$this->_helper->layout()->disableLayout();
	
	}
}





