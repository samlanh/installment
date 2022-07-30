<?php
class Stockinout_UsageController extends Zend_Controller_Action {
	public function init()
	{
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		$rs_rows=array();
		$db = new Stockinout_Model_DbTable_DbStockout();
			try{
				if(!empty($this->getRequest()->isPost())){
					$search=$this->getRequest()->getPost();
				}
				else{
					$search = array(
						'adv_search'=>'',
						'branch_id'=>-1,
						'status'=>-1,
						'propertyType'=>'',
						'workType'=>0,
						'contractor'=>0,
						'staffWithdraw'=>0,
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
					);
				}
				$rs_rows = $db->getAllUsageStock($search);
				
			}catch (Exception $e){
				Application_Form_FrmMessage::message("Application Error");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
			$this->view->search = $search;
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH_NAME","REQUEST_NO_FROM","REQUEST_NO","REQUEST_DATE","STAFF_WITHDRAW",
					"CONTRACTOR_NAME","ConstructionWorker","PROPERTY_TYPE","LAND_CODE","USAGE_TYPE","WORK_TYPE","BY_USER","STATUS");
			$link=array('module'=>'stockinout','controller'=>'usage','action'=>'edit');
			
			$this->view->list=$list->getCheckList(10, $collumns,$rs_rows,array('projectName'=>$link,'requestNo'=>$link,'reqOutNo'=>$link,
					'requestDate'=>$link,'staffName'=>$link));
			
			$frm = new Application_Form_FrmAdvanceSearch();
			$frm = $frm->AdvanceSearch();
			Application_Model_Decorator::removeAllDecorator($frm);
			$this->view->frm_search = $frm;
			
			$fm = new Stockinout_Form_FrmStockOut();
			$frm = $fm->FrmWithdrawStock();
			Application_Model_Decorator::removeAllDecorator($frm);
			$this->view->frm_stock = $frm;
	}
	function addAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {		
				$db = new Stockinout_Model_DbTable_DbStockout();
				$db->addUsageStock($_data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/stockinout/usage");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$fm = new Stockinout_Form_FrmStockOut();
		$frm = $fm->FrmWithdrawStock();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm = $frm;
	}
	function editAction(){
		$db = new Stockinout_Model_DbTable_DbStockout();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$db->upateUsageStock($_data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/stockinout/usage");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$id = $this->getRequest()->getParam('id');
		$id = empty($id)?0:$id;
		if(empty($id)){
			Application_Form_FrmMessage::Sucessfull("NO_DATA","//");
		}
		$arr = $db->getDataRow($id);
		
		$fm = new Stockinout_Form_FrmStockOut();
		$frm = $fm->FrmWithdrawStock($arr);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm = $frm;
		$this->view->rs = $arr;
		$this->view->results = $db->getDataAllRow($id);
	}
	function getrequestnoAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db_com = new Application_Model_DbTable_DbGlobalStock();
			$id = $db_com->generateRequestUsageNo($data);
			print_r(Zend_Json::encode($id));
			exit();
		}
	}
}

