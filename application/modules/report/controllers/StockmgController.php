<?php
class Report_StockmgController extends Zend_Controller_Action {
    public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function indexAction(){
	}
	public function requestLetterAction(){
		try{
			$db = new Report_Model_DbTable_DbStockMg();
			$id=$this->getRequest()->getParam('id');
    		$id = empty($id)?0:$id;
			$row = $db->getRequestPOById($id);
			if (empty($row)){
				Application_Form_FrmMessage::Sucessfull("NO_RECORD", "/stockmg/request");
				exit();
			}
			$this->view->row = $row;
			$this->view->rowdetail = $db->getRequestPODetailById($row);
		
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
		}
		
		
	}
	
}