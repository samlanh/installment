<?php
class Stockinout_WorktypeController extends Zend_Controller_Action {
	public function init()
	{
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		$db = new Stockinout_Model_DbTable_DbWorkType();
		$rs_rows= array();
		try{
			if(!empty($this->getRequest()->isPost())){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
					'adv_search'=>'',
					'status'	=>-1,
					'start_date'=> date('Y-m-d'),
					'end_date'=>date('Y-m-d'),
				);
				}
				$rs_rows = $db->getAllWorkType($search);//
			}catch (Exception $e){
				Application_Form_FrmMessage::message("Application Error");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
			
			$list = new Application_Form_Frmtable();
			$collumns = array("WORK_TYPE_TITLE","PARENT_WORKTYPE","CREATE_DATE","BY_USER","STATUS");
			$link=array('module'=>'stockinout','controller'=>'worktype','action'=>'edit');
			$this->view->list=$list->getCheckList(10, $collumns,$rs_rows,array('parentTitle'=>$link,'workTitle'=>$link));
			
			$frm = new Application_Form_FrmAdvanceSearchStock();
			$frm = $frm->AdvanceSearch();
			Application_Model_Decorator::removeAllDecorator($frm);
			$this->view->frm_search = $frm;
	}
	function addAction(){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$isaddworktype = $this->getRequest()->getParam('isAddWorkType');
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {		
				$db = new Stockinout_Model_DbTable_DbWorkType();
				$db->addWorkType($_data);
				if(!empty($isaddworktype)){
					
					$alert = $tr->translate("INSERT_SUCCESS");
					echo "<script> alert('".$alert."');</script>";
		    		echo "<script>window.close();</script>";

				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/stockinout/worktype/add");
				}
				
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$fm = new Stockinout_Form_FrmWorkType();
		$frm = $fm->FrmAddWorkType();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frmWorkType = $frm;
	}
	function editAction(){
		$db = new Stockinout_Model_DbTable_DbWorkType();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$db->updateWorkType($_data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/stockinout/worktype/index");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("UPDATE_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		$id = $this->getRequest()->getParam('id');
		$id = empty($id)?0:$id;
		if(empty($id)){
			Application_Form_FrmMessage::Sucessfull("NO_DATA","/stockinout/worktype",2);
		}
		
		$result = $db->getDataRow($id);
		
		$fm = new Stockinout_Form_FrmWorkType();
		$frm = $fm->FrmAddWorkType($result);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frmWorkType = $frm;
	}
	function getallworktypeAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db_com = new Application_Model_DbTable_DbGlobalStock();
			$result = $db_com->getAllWorkType(0, '','',null);
			
			$tr= Application_Form_FrmLanguages::getCurrentlanguage();
			
			if(isset($data['select'])){
				array_unshift($result, array('id'=>0,'name'=>$tr->translate("SELECT_WORK_TYPE")));
			}
			
			if(isset($data['addnew'])){
				array_unshift($result, array('id'=>-1,'name'=>$tr->translate('ADD_NEW')));
			}
			print_r(Zend_Json::encode($result));
			exit();
		}
	}
}

