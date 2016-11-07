<?php
class Setting_LabelController extends Zend_Controller_Action {
	
	public function init()
	{
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction()
	{
		try{
			$db = new Setting_Model_DbTable_DbLabel();
			$rs_rows= $db->getAllLabelList($search=null);//call frome model
			$list = new Application_Form_Frmtable();
			$collumns = array("Key Name","Key Value");
			$link=array(
					'module'=>'setting','controller'=>'label','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns,$rs_rows,array('keyValue'=>$link,'keyName'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			echo $e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
// 		$db = new Setting_Model_DbTable_DbRestore();
// 		$db->getAllTruncateTable();
		$this->_helper->flashMessenger->addMessage(array("err_message" => 'unable to comply'));
	}
	function addAction(){
		$this->_redirect('/setting/index');
		
	}
	function editAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db  = new Setting_Model_DbTable_DbLabel();
			$db->updateLabel($data);
			Application_Form_FrmMessage::Sucessfull('ការកែប្រែ​​ជោគ​ជ័យ','/setting/Label');
		}
		$key = new Application_Model_DbTable_DbKeycode();
		$id = $this->getRequest()->getParam('id');
		$rs = $key->getLabelVaueById($id);
		$this->view->rs= $rs;
		
	}
	function getAllSqlTruncateAction(){
		
	}
	
}

