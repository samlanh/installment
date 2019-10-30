<?php
class Stock_CutstockController extends Zend_Controller_Action {
	const REDIRECT_URL = '/stock/cutstock';
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
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
    							'branch_id' => '',
    							'adv_search' => '',
    					        'student_id'=>'',
    							'start_date'=> date('Y-m-d'),
    							'end_date'=>date('Y-m-d'),
    							'status_search'=>'',
    					);
    		}
			$db =  new Stock_Model_DbTable_DbCutStock();
			$rows = $db->getAllCutStock($search);
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH","RECEIPT_NO","STUDENT","BALANCE","TOTAL_RECEIVED","TOTAL_QTY_DUE",
					"DATE","STATUS");
			$link=array(
					'module'=>'stock','controller'=>'cutstock','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(10, $collumns, $rows,array('branch_name'=>$link,'receipt_no'=>$link,'supplier_name'=>$link,));
			}catch (Exception $e){
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
			$this->view->search = $search;
			
			$form=new Registrar_Form_FrmSearchInfor();
			$form->FrmSearchRegister();
			Application_Model_Decorator::removeAllDecorator($form);
			$this->view->frm_payment=$form;
	}
	public function addAction(){
		if($this->getRequest()->isPost()){
		$_data = $this->getRequest()->getPost();
			try{
				$db = new Stock_Model_DbTable_DbCutStock();
				$row = $db->addCutStock($_data);
				if(isset($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL."/index");
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL."/add");
				}
				Application_Form_FrmMessage::message("INSERT_SUCCESS");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				echo $e->getMessage();
			}
		}
		$id=$this->getRequest()->getParam('id');
		$stuid = empty($id)?0:$id;
		$db= new Foundation_Model_DbTable_DbStudent();
		$row = $db->getStudentById($stuid);
// 		print_r($row);exit();
		$this->view->stu = $row;
		
		$frm = new Stock_Form_FrmCutStock();
		$frm->FrmAddCutStock(null);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_payment = $frm;
		
	}
	public function editAction(){
		$id = $this->getRequest()->getParam("id");
		$id = empty($id)?0:$id;
		$db = new Stock_Model_DbTable_DbCutStock();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try{
				$row = $db->updateCutStock($_data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS",self::REDIRECT_URL."/index");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("EDIT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				echo $e->getMessage();
			}
		}
	
		$row = $db->getCutStockBYId($id);
		$this->view->row = $row;
		if (empty($row)){
			Application_Form_FrmMessage::Sucessfull("NO_RECORD",self::REDIRECT_URL."/index");
			exit();
		}else if ($row['is_closed']==1){
			Application_Form_FrmMessage::Sucessfull("This record already closing",self::REDIRECT_URL."/index");
			exit();
		}else if ($row['status']==0){
			Application_Form_FrmMessage::Sucessfull("This Record already void",self::REDIRECT_URL."/index");
			exit();
		}
		
		$frm = new Stock_Form_FrmCutStock();
		$frm->FrmAddCutStock($row);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_payment = $frm;
	
	}
	
	function voidAction(){
		$db = new Stock_Model_DbTable_DbCutStock();
		$id=$this->getRequest()->getParam('id');
		if (!empty($id)){
			try{
				$row = $db->getCutStockBYId($id);
				if (empty($row)){
					Application_Form_FrmMessage::Sucessfull("No Record",self::REDIRECT_URL."/index");
					exit();
				}else if ($row['is_closed']==1){
					Application_Form_FrmMessage::Sucessfull("This Payment already closing",self::REDIRECT_URL."/index");
					exit();
				}else if ($row['status']==0){
					Application_Form_FrmMessage::Sucessfull("This Record already void",self::REDIRECT_URL."/index");
					exit();
				}
				$db->voidCutStock($id,$row['branch_id']);
				Application_Form_FrmMessage::Sucessfull("Void Successfully",self::REDIRECT_URL."/index");
			}catch (Exception $e){
				Application_Form_FrmMessage::message("Void Payment Faile");
				echo $e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}else{
			Application_Form_FrmMessage::message("No Payment Receipt to void! please check again.");
			$this->_redirect("/sale/paymentreceipt");
	
		}
			
	}
	
	function getallpaydetailbystudentAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db_com = new Stock_Model_DbTable_DbCutStock();
			$id = $db_com->getStudentProductPaymentDetail($data);
			print_r(Zend_Json::encode($id));
			exit();
		}
	}
	function getallpaydetailbystudenteditAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db_com = new Stock_Model_DbTable_DbCutStock();
			$id = $db_com->getStudentProductPaymentDetailEdit($data);
			print_r(Zend_Json::encode($id));
			exit();
		}
	}
	function receiptAction(){
		$db = new Stock_Model_DbTable_DbCutStock();
		$id=$this->getRequest()->getParam('id');
		if (!empty($id)){
			try{
				$row = $db->getCutStockBYId($id);
				if (empty($row)){
					Application_Form_FrmMessage::Sucessfull("No Record",self::REDIRECT_URL."/index");
					exit();
				}
				$this->view->row = $row;
				$this->view->rowdetail = $db->getCutStockDetailBYId($id);
			}catch (Exception $e){
				Application_Form_FrmMessage::message("err");
				echo $e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}else{
			Application_Form_FrmMessage::message("No Payment Receipt ! please check again.");
			$this->_redirect("/stock/cutstock");
	
		}
			
	}
	function getreceiptAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$branch_id = $data['branch_id'];
			$_dbcht = new Stock_Model_DbTable_DbCutStock();
			$itemsCode = $_dbcht->getCutStockode($branch_id);
			print_r(Zend_Json::encode($itemsCode));
			exit();
		}
	}
	
}