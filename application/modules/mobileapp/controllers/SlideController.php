<?php
class Mobileapp_slideController extends Zend_Controller_Action {
	private $activelist = array('មិនប្រើ​ប្រាស់', 'ប្រើ​ប្រាស់');
    public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function addAction(){
		$this->_redirect("/mobileapp/slide");
	}
	public function indexAction(){
		try{
			$db = new Mobileapp_Model_DbTable_DbSlide();
			if($this->getRequest()->isPost()){
				$data=$this->getRequest()->getPost();
				$db->updateslideshow($data);
				$this->_redirect("/mobileapp/slide");
			}
			$this->view->slide = $db->getSlideShow();			
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	
	public function iconAction(){
		
	}
}

