<?php
class Report_StockmgController extends Zend_Controller_Action {
    public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function indexAction(){
	}
	public function rptRequestListAction(){
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
				);
    		}
    		$this->view->search = $search;
			$db = new Report_Model_DbTable_DbStockMg();
			$rs_rows = $db->getAllRequestPOList($search);
    		$this->view->row=$rs_rows;
    		$this->view->search=$search;
    		
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
		}
		
		$frm_search = new Application_Form_FrmAdvanceSearchStock();
		$frm = $frm_search->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
		
		$frmpopup = new Application_Form_FrmPopupGlobal();
		$this->view->footerReport = $frmpopup->getFooterReport();
		$this->view->headerReport = $frmpopup->getLetterHeadReport();
	}
	public function requestLetterAction(){
		try{
			$db = new Report_Model_DbTable_DbStockMg();
			$id=$this->getRequest()->getParam('id');
    		$id = empty($id)?0:$id;
			$row = $db->getRequestPOById($id);
			if (empty($row)){
				Application_Form_FrmMessage::Sucessfull("NO_RECORD", "/stockmg/request");
				exit();
			}
			$this->view->row = $row;
			$this->view->rowdetail = $db->getRequestPODetailById($row);
		
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
		}
		
		
	}
	
	public function rptPurchasingAction(){
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
				);
    		}
			
    		$this->view->search = $search;
			$db = new Report_Model_DbTable_DbAccountant();
			$rs_rows = $db->getAllPurchasing($search);
    		$this->view->row=$rs_rows;
    		$this->view->search=$search;
    		
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
		}
		
		$frm_search = new Application_Form_FrmAdvanceSearchStock();
		$frm = $frm_search->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
		
		$frmpopup = new Application_Form_FrmPopupGlobal();
		$this->view->footerReport = $frmpopup->getFooterReport();
		$this->view->headerReport = $frmpopup->getLetterHeadReport();
	}
	
	public function purchaseLetterAction(){
		try{
			$db = new Report_Model_DbTable_DbAccountant();
			$id=$this->getRequest()->getParam('id');
    		$id = empty($id)?0:$id;
			$row = $db->getPurchasingById($id);
			if (empty($row)){
				Application_Form_FrmMessage::Sucessfull("NO_RECORD", "/stockmg/request");
				exit();
			}
			$this->view->row = $row;
			$this->view->rowdetail = $db->getPODetailById($id);
		
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
		}
		
		
	}
	
	public function rptPaymentAction(){
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
					'statusAcc'=>-1,
				);
    		}
			$search['closingStatus']=-1;
    		$this->view->search = $search;
			$db = new Report_Model_DbTable_DbAccountant();
			$rs_rows = $db->getAllPayment($search);
    		$this->view->row=$rs_rows;
    		$this->view->search=$search;
    		
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
		}
		
		$frm_search = new Application_Form_FrmAdvanceSearchStock();
		$frm = $frm_search->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
		
		$frmpopup = new Application_Form_FrmPopupGlobal();
		$this->view->footerReport = $frmpopup->getFooterReport();
		$this->view->headerReport = $frmpopup->getLetterHeadReport();
	}
	
	public function rptClosingPaymentAction(){
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
					'statusAcc'=>-1,
					'closingStatus'=>-1,
				);
    		}
			
    		$this->view->search = $search;
			$db = new Report_Model_DbTable_DbAccountant();
			$rs_rows = $db->getAllPayment($search);
    		$this->view->row=$rs_rows;
    		$this->view->search=$search;
    		
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
		}
		
		$frm_search = new Application_Form_FrmAdvanceSearchStock();
		$frm = $frm_search->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
		
		$frmpopup = new Application_Form_FrmPopupGlobal();
		$this->view->footerReport = $frmpopup->getFooterReport();
		$this->view->headerReport = $frmpopup->getLetterHeadReport();
	}
	
	function closingpaymentAction(){
		$db  = new Report_Model_DbTable_DbAccountant();
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data=$key->getKeyCodeMiniInv(TRUE);
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db->submitClosingPayment($data);
			Application_Form_FrmMessage::Sucessfull("CLOSING_SUCCESS", "/report/stockmg/rpt-closing-payment");
			exit();
		}
	}
	
}