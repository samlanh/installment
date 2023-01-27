<?php
class Stockinout_TransferoutController extends Zend_Controller_Action {
	
	const REDIRECT_URL = '/stockinout/transferout';
	public function init()
	{
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		$rs_rows=array();
		$db = new Stockinout_Model_DbTable_DbTransfer();
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
			
				$rs_rows= $db->getAllTransfer($search);
			
			}catch (Exception $e){
				Application_Form_FrmMessage::message("Application Error");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH_NAME","TRANFER_NO","TRANSFER_STOCK_DATE","DRIVER",
					"DISTRIBUTOR","TO_PROJECT","RECEIVER","USAGE_FOR","BY_USER","RECEIVE_STATUS","STATUS");
			$link=array('module'=>'stockinout','controller'=>'transferout','action'=>'edit');
			$this->view->list=$list->getCheckList(10, $collumns,$rs_rows,array('projectName'=>$link,'transferNo'=>$link));
			
			$frm = new Application_Form_FrmAdvanceSearch();
			$frm = $frm->AdvanceSearch();
			Application_Model_Decorator::removeAllDecorator($frm);
			$this->view->frm_search = $frm;
		
	}
	function addAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {		
				$db = new Stockinout_Model_DbTable_DbTransfer();
				$_data['isApproved']=1;
				$db->addTransferStock($_data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL);
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$fm = new Stockinout_Form_FrmTransfer();
		$frm = $fm->FrmTransfer();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm = $frm;
	}
	function editAction(){
		$db = new Stockinout_Model_DbTable_DbTransfer();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {		
				
				$db->addTransferStock($_data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL);
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
		
		$row = $db->getDataRow($id);
		$rowDetail = $db->getDataRowDetail($id);
		$this->view->row = $row;
		$this->view->rowDetail = $rowDetail;
		
		if(empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_DATA", self::REDIRECT_URL,2);
			exit();
		}else if ($row['status']==0){
			Application_Form_FrmMessage::Sucessfull("ALREADY_DEACTIVE", self::REDIRECT_URL,2);
			exit();
		}else if ($row['isCompletedReceive']==1){
			Application_Form_FrmMessage::Sucessfull("COMPLETED_RECEIVED_TRANSFERING", self::REDIRECT_URL,2);
			exit();
		}
		
		$checking = $db->checkTransferInReceived($id);
		if(!empty($checking)){
			Application_Form_FrmMessage::Sucessfull("TRANSFERING_HAS_SOME_RECEIVED", self::REDIRECT_URL,2);
			exit();
		}
		$fm = new Stockinout_Form_FrmTransfer();
		$frm = $fm->FrmTransfer($row);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm = $frm;
		
		
	}
	function getrequestnoAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db_com = new Application_Model_DbTable_DbGlobalStock();
			$id = $db_com->generateTransferNo($data);
			print_r(Zend_Json::encode($id));
			exit();
		}
	}
}

