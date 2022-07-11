<?php
class Product_MeasureController extends Zend_Controller_Action {
	public function init()
	{
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		$db = new Product_Model_DbTable_DbMeasure();
		try{
			if(!empty($this->getRequest()->isPost())){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
					'title'=>'',
				);
			}
			$rs_rows= $db->getAllMeasure($search);
			$list = new Application_Form_Frmtable();
			$collumns = array("MEASURE_TITLE","BY_USER","CREATE_DATE","STATUS");
			$link=array('module'=>'product','controller'=>'measure','action'=>'edit');
			$this->view->list=$list->getCheckList(10, $collumns,$rs_rows,array('name'=>$link));
			$this->view->search = $search;
			
			}catch (Exception $e){
				Application_Form_FrmMessage::message("Application Error");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
			
		
	}
	function addAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {		
				$db = new Product_Model_DbTable_DbMeasure();
				$db->addMeasure($_data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/product/measure/add");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
	}
	function editAction(){
		$db = new Product_Model_DbTable_DbMeasure();
		
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$db->updateMeasure($_data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/product/measure/index");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$id = $this->getRequest()->getParam('id');
		$id = empty($id)?0:$id;
		if(empty($id)){
			Application_Form_FrmMessage::Sucessfull("NO_DATA","/product/measure/index");
		}
		$this->view->rs = $db->getMeasureById($id);
	}
}

