<?php
class Stockinout_TransferinController extends Zend_Controller_Action {
	const REDIRECT_URL = '/stockinout/transferin';
	public function init()
	{
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
			if($this->getRequest()->isPost()){
    			$search = $this->getRequest()->getPost();
    		}
    		else{
    			$search=array(
					'adv_search'=>"",
					'branch_id' => -1,
					'start_date'=> date('Y-m-d'),
					'end_date'=>date('Y-m-d'),
					'status'=>-1,
					'reqPOStatus'=>-1,
				);
    		}
    		$this->view->search = $search;
			$db = new Stockinout_Model_DbTable_DbReceiveTransfer();
			$rs_rows = $db->getAllReceiveTransferStock($search);
			
			$list = new Application_Form_Frmtable();
    		$collumns = array("PROJECT_NAME","RECEIVE_NO","RECEIVE_DATE","FROM_PROJECT","TRANFER_NO","TRANSFER_STOCK_DATE","DRIVER","DISTRIBUTOR","USER","STATUS");
    		$link=array(
    				'module'=>'stockinout','controller'=>'transferin','action'=>'edit',
    		);
    		$this->view->list=$list->getCheckList(10, $collumns, $rs_rows , array('projectName'=>$link,'receiveNo'=>$link ));
    		
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
		}
		
		$frm_search = new Application_Form_FrmAdvanceSearchStock();
		$frm = $frm_search->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
		
	}
	function addAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {		
				$db = new Stockinout_Model_DbTable_DbReceiveTransfer();
				$db->addReceiveTransferStock($_data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL);
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$fm = new Stockinout_Form_FrmTransfer();
		$frm = $fm->FrmTransferReceive();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm = $frm;
		
		
		$id = $this->getRequest()->getParam('id');
		$id = empty($id)?0:$id;
		$dbTr = new Stockinout_Model_DbTable_DbTransfer();
		$row = $dbTr->getDataRow($id);
		
		$this->view->rowTr = $row;
		
	}
	function editAction(){
		$db = new Stockinout_Model_DbTable_DbReceiveTransfer();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {		
				
				$db->editReceiveTransferStock($_data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS",self::REDIRECT_URL);
			}catch(Exception $e){
				Application_Form_FrmMessage::message("EDIT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}else{
			$id = $this->getRequest()->getParam('id');
			$id = empty($id)?0:$id;
			$row = $db->getDataRow($id);
			$this->view->row = $row;
			
			if(empty($row)){
				Application_Form_FrmMessage::Sucessfull("NO_DATA", self::REDIRECT_URL,2);
				exit();
			}else if ($row['status']==0){
				Application_Form_FrmMessage::Sucessfull("ALREADY_DEACTIVE", self::REDIRECT_URL,2);
				exit();
			}else if ($row['isClose']==1){
				Application_Form_FrmMessage::Sucessfull("ALREADY_CLOSING_ENTRY", self::REDIRECT_URL,2);
				exit();
			}
			
			$fm = new Stockinout_Form_FrmTransfer();
			$frm = $fm->FrmTransferReceive($row);
			Application_Model_Decorator::removeAllDecorator($frm);
			$this->view->frm = $frm;
		}
		
	}
	
	function getreceivenoAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db_com = new Application_Model_DbTable_DbGlobalStock();
			$id = $db_com->generateReceiveTransferNo($data);
			print_r(Zend_Json::encode($id));
			exit();
		}
	}
	function getalltrAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobalStock();
			$_row =$db->getAllTransferOut($data);
			
			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
			array_unshift($_row,array(
					'id' => 0,
					'name' => $tr->translate("SELECT_TRANSFER_NO"),
			) );
			print_r(Zend_Json::encode($_row));
			exit();
			
		}
	}
	function getallproductbytransferAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db_com = new Stockinout_Model_DbTable_DbReceiveTransfer();
			$id = $db_com->getAllProductByTransfer($data);
			print_r(Zend_Json::encode($id));
			exit();
		}
	}
}

