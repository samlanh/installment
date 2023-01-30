<?php
class Stockinout_StaffController extends Zend_Controller_Action {
	public function init()
	{
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		$rs_rows=array();
		$db = new Stockinout_Model_DbTable_DbWorker();
		try{
			if(!empty($this->getRequest()->isPost())){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
					'adv_search'=>'',
					'branch_id'=>-1,
					'status'=>-1,
					'start_date'=> date('Y-m-d'),
					'end_date'=>date('Y-m-d'),
				);
			}
			
			$rs_rows= $db->getAllStaffWorker($search);
			
			}catch (Exception $e){
				Application_Form_FrmMessage::message("Application Error");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH_NAME","WORKER_NAME","GENDER","ADDRESS","TEL","POSITION","CREATE_DATE","BY_USER","STATUS");
			$link=array('module'=>'stockinout','controller'=>'staff','action'=>'edit');
			$this->view->list=$list->getCheckList(10, $collumns,$rs_rows,array('branch_name'=>$link,'staffName'=>$link,'gender'=>$link));
			
			$frm = new Application_Form_FrmAdvanceSearch();
			$frm = $frm->AdvanceSearch();
			Application_Model_Decorator::removeAllDecorator($frm);
			$this->view->frm_search = $frm;
	}
	function addAction(){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$isaddstaff= $this->getRequest()->getParam('isAddStaffWithdraw');
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {		
				$db = new Stockinout_Model_DbTable_DbWorker();
				$db->addWorker($_data);
				if(!empty($isaddstaff)){
					
					$alert = $tr->translate("INSERT_SUCCESS");
					echo "<script> alert('".$alert."');</script>";
		    		echo "<script>window.close();</script>";

				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/stockinout/staff/add");
				}
				
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$fm = new Stockinout_Form_FrmStaffWorker();
		$frm = $fm->FrmWorker();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frmWorker = $frm;
	}
	function editAction(){
		$db = new Stockinout_Model_DbTable_DbWorker();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$db->updateWorker($_data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/stockinout/staff");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$id = $this->getRequest()->getParam('id');
		$id = empty($id)?0:$id;
		$row = $db->getDataRow($id);
		
		if(empty($id) OR empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_DATA","/stockinout/staff/index",2);
		}
		
		
		$fm = new Stockinout_Form_FrmStaffWorker();
		$frm = $fm->FrmWorker($row);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frmWorker = $frm;
		
	}
	function getallstaffAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db_com = new Application_Model_DbTable_DbGlobalStock();
			$result = $db_com->getAllStaffbyBranch($data);
			
			$tr= Application_Form_FrmLanguages::getCurrentlanguage();
			
			if(isset($data['select'])){
				array_unshift($result, array('id'=>0,'name'=>$tr->translate("SELECT_STAFF_WORKDER")));
			}
			
			if(isset($data['addnew'])){
				array_unshift($result, array('id'=>-1,'name'=>$tr->translate('ADD_NEW')));
			}
			print_r(Zend_Json::encode($result));
			exit();
		}
	}
}

