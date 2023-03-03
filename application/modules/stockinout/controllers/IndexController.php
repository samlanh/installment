<?php
class Stockinout_IndexController extends Zend_Controller_Action {
	public function init()
	{
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		$rs_rows=array();
		$db = new Stockinout_Model_DbTable_DbReceiveStock();
		try{
			if(!empty($this->getRequest()->isPost())){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
					'adv_search'=>'',
					'branch_id'=>-1,
					'status'=>-1,
					'supplierId'=>-1,
					'verifyStatus'=>-1,
					'start_date'=> date('Y-m-d'),
					'end_date'=>date('Y-m-d'),
				);
			}
			$search['transactionType']=1;
			
			$rs_rows= $db->getAllReceiveStock($search);
			
			}catch (Exception $e){
				Application_Form_FrmMessage::message("Application Error");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
			
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH_NAME","DOCUMENT_RECEIV_TYPE","DNORIV_NO","VERIFY_STATUS","DELIVER","TRUCK_NUMBER","COUNTER","RECEIVE_DATE","SUPPLIER_NAME","PO_NO","REQUEST_NO","USER","STATUS");
			$link=array('module'=>'stockinout','controller'=>'index','action'=>'edit');
			$this->view->list=$list->getCheckList(10, $collumns,$rs_rows,array('projectName'=>$link,'dnType'=>$link,
					'isIssueInvoice'=>$link,'dnNumber'=>$link,
					'plateNo'=>$link,'driverName'=>$link));
			
			$frm_search = new Application_Form_FrmAdvanceSearchStock();
			$frm = $frm_search->AdvanceSearch();
			Application_Model_Decorator::removeAllDecorator($frm);
			$this->view->frm_search = $frm;
	}
	function addAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {	
				if(empty($_data)){
					Application_Form_FrmMessage::Sucessfull("NO_DATA","/stockinout/index/add",2);
				}else{
					$db = new Stockinout_Model_DbTable_DbReceiveStock();
					$db->addReceiveStock($_data);
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/stockinout/index/add");
				}
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
			$fm = new Stockinout_Form_FrmReceiveStock();
			$frm = $fm->FrmReceivStock();
			Application_Model_Decorator::removeAllDecorator($frm);
			$this->view->frmReceivStock = $frm;
	}
	function editAction(){
		$db = new Stockinout_Model_DbTable_DbReceiveStock();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				if(empty($_data)){
					Application_Form_FrmMessage::Sucessfull("NO_DATA","/stockinout/index/add",2);
				}else{
					$db->updateDataReceive($_data);
				}
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/stockinout/index/index");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		$id = $this->getRequest()->getParam('id');
		$id = empty($id)?0:$id;
		$result = $db->getDataRow($id);
		if(empty($id) OR empty($result) OR ($result['isIssueInvoice']==1)){
			Application_Form_FrmMessage::Sucessfull("NO_DATA","/stockinout/index/index",2);
		}
		$this->view->photo = $result['photoDn'];
		$fm = new Stockinout_Form_FrmReceiveStock();
		$frm = $fm->FrmReceivStock($result);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frmReceivStock = $frm;
		$this->view->rsDn = $result;
	}
	function verifyAction(){
		$db = new Stockinout_Model_DbTable_DbReceiveStock();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try{
				$db->verifyDN($_data);
				Application_Form_FrmMessage::Sucessfull("VERIFIED_SUCCESSED","/stockinout/index");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		$id = $this->getRequest()->getParam('id');
		$id = empty($id)?0:$id;
		if(empty($id)){
			Application_Form_FrmMessage::Sucessfull("NO_DATA","//",2);
		}
		$param = array(
				'dnId'=>$id);
		$rs = $db->getDNById($param);
		$this->view->rsRow = $rs;
		
		$this->view->dnDetail = $db->getDNDetailById($id);
	}
	function getallproductbypoAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db_com = new Stockinout_Model_DbTable_DbReceiveStock();
			$id = $db_com->getAllProductByPO($data);
			print_r(Zend_Json::encode($id));
			exit();
		}
	}
}

