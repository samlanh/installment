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
				Application_Form_FrmMessage::Sucessfull("NO_RECORD", "/requesting/request",2);
				exit();
			}
			$this->view->row = $row;
			$this->view->rowdetail = $db->getRequestPODetailById($row);
		
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
		}
		
		$frmpopup = new Application_Form_FrmPopupGlobal();
		$this->view->printByFormat = $frmpopup->printByFormat();
	}
	public function requestInfoAction(){
		try{
			$db = new Report_Model_DbTable_DbStockMg();
			$id=$this->getRequest()->getParam('id');
    		$id = empty($id)?0:$id;
			$row = $db->getRequestPOById($id);
			if (empty($row)){
				Application_Form_FrmMessage::Sucessfull("NO_RECORD", "/requesting/request",2);
				exit();
			}
			$this->view->row = $row;
			$this->view->rowdetail = $db->getRequestPODetailById($row);
		
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
		}
		
		$frmpopup = new Application_Form_FrmPopupGlobal();
		$this->view->printByFormat = $frmpopup->printByFormat();
		
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
				Application_Form_FrmMessage::Sucessfull("NO_RECORD", "/po/index",2);
				exit();
			}
			$this->view->row = $row;
			$this->view->rowdetail = $db->getPODetailById($id);
		
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
		}
		
		$frmpopup = new Application_Form_FrmPopupGlobal();
		$this->view->printByFormat = $frmpopup->printByFormat();
	}
	
	public function purchaseInfoAction(){
		try{
			$db = new Report_Model_DbTable_DbAccountant();
			$id=$this->getRequest()->getParam('id');
    		$id = empty($id)?0:$id;
			$row = $db->getPurchasingById($id);
			if (empty($row)){
				Application_Form_FrmMessage::Sucessfull("NO_RECORD", "/po/index",2);
				exit();
			}
			$this->view->row = $row;
			$this->view->rowdetail = $db->getPODetailById($id);
		
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
		}
		
		$frmpopup = new Application_Form_FrmPopupGlobal();
		$this->view->printByFormat = $frmpopup->printByFormat();
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
	
	public function paymentLetterAction(){
		try{
			$db = new Report_Model_DbTable_DbAccountant();
			$id=$this->getRequest()->getParam('id');
    		$id = empty($id)?0:$id;
			$row = $db->getPaymentInvoiceById($id);
			if (empty($row)){
				Application_Form_FrmMessage::Sucessfull("NO_RECORD", "/invpayment/payment",2);
				exit();
			}
			$this->view->row = $row;
			$this->view->rowdetail = $db->getPaymentDetail($id);
		
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
		}
		
		$frmpopup = new Application_Form_FrmPopupGlobal();
		$this->view->printByFormat = $frmpopup->printByFormat();
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
			Application_Form_FrmMessage::Sucessfull("CLOSING_SUCCESS", "/report/stockmg/rpt-closing-payment",2);
			exit();
		}
	}
	public function rptUnclosingPaymentAction(){
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
					'closingStatus'=>1,
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
	
	function unclosingpaymentAction(){
		$db  = new Report_Model_DbTable_DbAccountant();
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data=$key->getKeyCodeMiniInv(TRUE);
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db->submitUnclosingPayment($data);
			Application_Form_FrmMessage::Sucessfull("UNCLOSING_SUCCESS", "/report/stockmg/rpt-unclosing-payment",2);
			exit();
		}
	}
	
	public function rptIssuechequeAction(){
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
				);
    		}
			
    		$this->view->search = $search;
			$db = new Report_Model_DbTable_DbAccountant();
			$rs_rows = $db->getAllIssueChequePayment($search);
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
	
	
	public function rptInvoiceAction(){
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
			$rs_rows = $db->getAllInvoice($search);
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
	
	public function invoiceLetterAction(){
		try{
			$db = new Report_Model_DbTable_DbAccountant();
			$id=$this->getRequest()->getParam('id');
    		$id = empty($id)?0:$id;
			$row = $db->getDataRowInvoice($id);
			if (empty($row)){
				Application_Form_FrmMessage::Sucessfull("NO_RECORD", "/invpayment/index",2);
				exit();
			}
			
			$arrFilter = array(
						'id'=>$id,
					);
			$this->view->row = $row;
		
			$this->view->rowdetail = $db->getInvoiceDetailById($arrFilter);
			$arrFilter['isService']=1;
			$this->view->rowdetailServicce = $db->getInvoiceDetailById($arrFilter);
			if(!empty($row['dnId'])){
					$this->view->DNList = $db->getDNByListOfInvoice($row['dnId']);
			}
		
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
		}
		
		$frmpopup = new Application_Form_FrmPopupGlobal();
		$this->view->printByFormat = $frmpopup->printByFormat();
	}
	
	public function rptInvpaymenthistoryAction(){
		try{
			$db = new Report_Model_DbTable_DbAccountant();
			$id=$this->getRequest()->getParam('id');
    		$id = empty($id)?0:$id;
			
			$row = $db->getDataRowInvoice($id);
			if (empty($row)){
				Application_Form_FrmMessage::Sucessfull("NO_RECORD", "/invpayment/index",2);
				exit();
			}
		
			$this->view->row = $row;
			$this->view->rowdetail = $db->getInvoicePaymentHistory($id);
			
    		
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
		}
		
		$frm_search = new Application_Form_FrmAdvanceSearchStock();
		$frm = $frm_search->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
		
		$frmpopup = new Application_Form_FrmPopupGlobal();
		$this->view->printByFormat = $frmpopup->printByFormat();
		
		$this->view->footerReport = $frmpopup->getFooterReport();
		$this->view->headerReport = $frmpopup->getLetterHeadReport();
	}
	public function rptBudgetAction(){
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
		}else{
			$search = array(
					"adv_search"=>'',
					"supplier_id"=>"",
					"branch_id"=>-1,
					"ordering"=>1,
					"category_id_expense"=>-1,
					'payment_type'=>-1,
					'start_date'=> date('Y-m-d'),
					'end_date'=>date('Y-m-d'),
					'monthlytype'=>1,
			);
		}
		$this->view->search=$search;
		$db  = new Report_Model_DbTable_DbStockReports();
		$this->view->rsBudgets = $db->getBudgetList($search);
		$frm = new Loan_Form_FrmSearchLoan();
		$frm = $frm->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
	}
	
	public function rptRequestProductSummaryAction(){
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
			$rs_rows = $db->getProductRequestSummary($search);
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
	
	
}