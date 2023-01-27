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
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$isAddMeasure = $this->getRequest()->getParam('isAddMeasure');

		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {		
				$db = new Product_Model_DbTable_DbMeasure();
				$db->addMeasure($_data);
				if(!empty($isAddMeasure)){
					$alert = $tr->translate("INSERT_SUCCESS");
					echo "<script> alert('".$alert."');</script>";
		    		echo "<script>window.close();</script>";
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/product/measure/add");

				}	
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
		$result = $db->getMeasureById($id);
		
		$this->view->rs = $result;
	}
	function getAllmeasureAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Product_Model_DbTable_DbMeasure();
			$results=$db->getAllMeasureList();
			
			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
			
			array_unshift($results, array ('id' => 0,'name' =>$tr->translate("PLEASE_SELECT_MEASURE")));
			array_unshift($results, array ('id' => -1,'name' =>$tr->translate("ADD_NEW")));
			
			print_r(Zend_Json::encode($results));
			exit();
		}
	}
}

