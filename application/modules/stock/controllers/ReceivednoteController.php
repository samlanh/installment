<?php
class Stock_ReceivednoteController extends Zend_Controller_Action {
	const REDIRECT_URL = '/stock/receivednote';
	public function init()
    {    	
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}

    public function indexAction()
    {	
    	$db = new Stock_Model_DbTable_DbReceivedNote();
    	try{
    		if($this->getRequest()->isPost()){
    			$search = $this->getRequest()->getPost();
    			$this->view->row_ace=$search;
    		}
    		else{
    			$search=array(
    					'title' => '',
    					'start_date' =>date("Y-m-d"),
    					'end_date' =>date("Y-m-d"),
    			);
    		}
    		$rs_rows= $db->getAllTransfer($search);
    		$list = new Application_Form_Frmtable();
    		$collumns = array("BRANCH","RECEIVED_NUMBER","DATE","FROM_BRANCH","NOTE","BY_USER","STATUS");
    		$link=array(
    				'module'=>'stock','controller'=>'receivednote','action'=>'edit',
    		);
    		$this->view->list=$list->getCheckList(0, $collumns, $rs_rows , array('branch_name'=>$link,'receive_date'=>$link,'receive_no'=>$link,'tolocation'=>$link));
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("APPLICATION_ERROR");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		echo $e->getMessage();
    	}
    	 
    	$form=new Stock_Form_FrmSearchProduct();
    	$form->FrmSearchProduct();
    	Application_Model_Decorator::removeAllDecorator($form);
    	$this->view->form_search=$form;
    }
	public function addAction(){
		$db = new Stock_Model_DbTable_DbReceivedNote();
		if($this->getRequest()->isPost()){
			try{
				$data = $this->getRequest()->getPost();
				$db->addReceiveNoteTransfer($data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", self::REDIRECT_URL."/add");
			}catch(Exception $e){
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				Application_Form_FrmMessage::message("APPLICATION_ERROR");
			}
		}
		$fm = new Stock_Form_FrmReceived();
		$frm = $fm->FrmReceived();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm = $frm;
	}
	public function editAction(){
		$db = new Stock_Model_DbTable_DbReceivedNote();
		$id = $this->getRequest()->getParam("id");
		$id = empty($id)?0:$id;
		if($this->getRequest()->isPost()){
			try{
				$data = $this->getRequest()->getPost();
				$db->updateReceiveNoteTransfer($data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", self::REDIRECT_URL."");
			}catch(Exception $e){
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				Application_Form_FrmMessage::message("APPLICATION_ERROR");
			}
		}
		$row = $db->getReceiveNoteById($id);
		$this->view->row = $row;
		if (empty($row)){
			Application_Form_FrmMessage::Sucessfull("NORECORD", self::REDIRECT_URL."",2);
			exit();
		}
		$this->view->detail = $db->getReceiveNoteDetailById($id);
		$fm = new Stock_Form_FrmReceived();
		$frm = $fm->FrmReceived($row);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm = $frm;
	}
	function getreceivenoAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Stock_Model_DbTable_DbReceivedNote();
			$rs = $db->getReceiveNoteNo($data['branch_id']);
			if(empty($rs)){
				$rs = "";
			}
			print_r(Zend_Json::encode($rs));
			exit();
		}
	}
	function gealltransferAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Stock_Model_DbTable_DbReceivedNote();
			
			$transfer_id = empty($data['transfer_id'])?null:$data['transfer_id'];
			
			$rs = $db->getAllTranferByToBranch($data['branch_id'],$transfer_id);
			print_r(Zend_Json::encode($rs));
			exit();
		}
	}
	function gettransferinfoAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Stock_Model_DbTable_DbReceivedNote();
			$rs = $db->getTransferInfo($data['transfer_id']);
			print_r(Zend_Json::encode($rs));
			exit();
		}
	}
	function gealltransferdetailAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Stock_Model_DbTable_DbReceivedNote();
			$rs = $db->getTransferDetail($data['transfer_id']);
			print_r(Zend_Json::encode($rs));
			exit();
		}
	}
}