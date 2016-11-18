<?php
class Other_TermController extends Zend_Controller_Action {
	const REDIRECT_URL='/other';
	private $activelist = array('មិនប្រើ​ប្រាស់', 'ប្រើ​ប្រាស់');
	protected $tr;
    public function init()
    {    	
     /* Initialize action controller here */
    	$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	function indexAction(){
		$db = new Other_Model_DbTable_DbTerm();
		if($this->getRequest()->isPost()){
			try{
				$data = $this->getRequest()->getPost();
				$db->addTermcondiction($data);
				Application_Form_FrmMessage::message("ការ​បញ្ចូល​ជោគ​ជ័យ !");
			}catch (Exception $e){
				Application_Form_FrmMessage::message("SAVE_FAILE");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$row = $db->termCondiction();
		$this->view->term = $row;
		$fm = new Other_Form_Frmterm();
		$frm = $fm->Frmterm($row);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_term = $frm;
		
	}
   
}

