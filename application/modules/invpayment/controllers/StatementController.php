<?php
class Invpayment_StatementController extends Zend_Controller_Action {
	const REDIRECT_URL = '/invpayment/statement';
	const INVOICE_TYPE = 1;//DN Invoice
	public function indexAction(){
		//$db = new ();
		try{
			$db = new Invpayment_Model_DbTable_DbConcreteStatement();
			
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
		
			$rs_rows=array();
			$rs_rows= $db->getAllStatement($search);
			
			
			$list = new Application_Form_Frmtable();
    		$collumns = array("PROJECT_NAME","STATEMENT_NO","STATEMENT_DATE","SUPPLIER_STATE_NO","TOTAL","SUPPLIER","NOTE","BY","STATUS");
    		$link=array(
    				'module'=>'invpayment','controller'=>'statement','action'=>'edit',
    		);
    		$this->view->list=$list->getCheckList(10, $collumns, $rs_rows , array('projectName'=>$link,'stmentNo'=>$link,));
			
			}catch (Exception $e){
				Application_Form_FrmMessage::message("Application Error");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
			
			
			
		$frm_search = new Application_Form_FrmAdvanceSearchStock();
		$frm = $frm_search->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
		
	}
	function addAction(){

		$db = new Invpayment_Model_DbTable_DbConcreteStatement();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {		
				$db->addConcreteStatment($_data);
	    		Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL."/index");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
    	$frm = new Invpayment_Form_FrmStatement();
    	$frm->FrmgetStatement(null);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm = $frm;
	}
	public function viewAction(){
	
		$db = new Invpayment_Model_DbTable_DbConcreteStatement();
		try{
			$id = $this->getRequest()->getParam('id');
			$id = empty($id)?0:$id;
			$this->view->rows = $db->getConcreteStatement($id);

			$this->view->rs = $db->getStatementRow($id);
	
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
		}
		 
		$frmpopup = new Application_Form_FrmPopupGlobal();
		$this->view->footerReport = $frmpopup->getFooterReport();
		$this->view->headerReport = $frmpopup->getLetterHeadReport();
	}

	function getalldnAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Invpayment_Model_DbTable_DbDnconcrete();
			$_row =$db->getConcreteDnData($data);
			print_r(Zend_Json::encode($_row));
			exit();
			
		}
	}
	function getReceiveInfoAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Invpayment_Model_DbTable_DbDnconcrete();
			$_row =$db->getReceiveProductInfo($data);
			print_r(Zend_Json::encode($_row));
			exit();
			
		}
	}

// 	function editAction(){
// 		$tr=Application_Form_FrmLanguages::getCurrentlanguage();
// 		$db = new Invpayment_Model_DbTable_DbInvoice();
// 		if($this->getRequest()->isPost()){
// 			$_data = $this->getRequest()->getPost();
// 			try {
// 				$_data['ivType']=self::INVOICE_TYPE;
// 				$db->editIssueInvoice($_data);
// 				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS",self::REDIRECT_URL."/index");
// 			}catch(Exception $e){
// 				Application_Form_FrmMessage::message("INSERT_FAIL");
// 				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
// 			}
// 		}
		
// 		$id = $this->getRequest()->getParam('id');
// 		$id = empty($id)?0:$id;
// 		if(empty($id)){
// 			Application_Form_FrmMessage::Sucessfull("NO_DATA",self::REDIRECT_URL."/index",2);
// 			exit();
// 		}
		
// 		$row = $db->getDataRow($id);
// 		$this->view->row = $row;
// 		if (empty($row)){
//     		Application_Form_FrmMessage::Sucessfull($tr->translate('NO_DATA'), self::REDIRECT_URL."/index",2);
//     		exit();
//     	}
// 		if ($row['status']==0){
//     		Application_Form_FrmMessage::Sucessfull($tr->translate('ALREADY_VOID'), self::REDIRECT_URL."/index",2);
//     		exit();
//     	}
		
// 		$dbPMT = new Invpayment_Model_DbTable_DbPayment();
// 		$checkingPayment = $dbPMT->checkingInvoiceHavePayment($id);
// 		if (!empty($checkingPayment)){
//     		Application_Form_FrmMessage::Sucessfull($tr->translate('HAS_IN_PAYMENT_READY'), self::REDIRECT_URL."/index",2);
//     		exit();
//     	}
		
// 		$dbGBstock = new Application_Model_DbTable_DbGlobalStock();
// 		$arrStep = array(
// 				'keyIndex'=>self::INVOICE_TYPE,
// 				'typeKeyIndex'=>1,
// 			);
// 		$invoiceType = $dbGBstock->invoiceTypeKey($arrStep);
		
// 		if ($row['ivType']!=$invoiceType){
//     		Application_Form_FrmMessage::Sucessfull($tr->translate('NO_DATA'), self::REDIRECT_URL."/index",2);
//     		exit();
//     	}
		
// 		$arrFilter = array(
// 						'id'=>$id,
// 					);
// 		$this->view->rowdetail = $db->getInvoiceDetailById($arrFilter);
// 		$arrFilter['isService']=1;
// 		$this->view->rowdetailServicce = $db->getInvoiceDetailById($arrFilter);
		
		
// 		$frm = new Invpayment_Form_FrmInvoice();
//     	$frm->FrmInvoices($row);
//     	Application_Model_Decorator::removeAllDecorator($frm);
//     	$this->view->frm = $frm;
// 	}
	
	
// 	function getinvoicenoAction(){
// 		if($this->getRequest()->isPost()){
// 			$data = $this->getRequest()->getPost();
// 			$db = new Application_Model_DbTable_DbGlobalStock();
// 			$_row =$db->generateInvoiceNo($data);
// 			print_r(Zend_Json::encode($_row));
// 			exit();
			
// 		}
// 	}
	
// 	function getallsupplierinvAction(){
// 		if($this->getRequest()->isPost()){
// 			$data = $this->getRequest()->getPost();
// 			$db_com = new Invpayment_Model_DbTable_DbInvoice();
// 			$id = $db_com->getAllInvoiceBySupplier($data);
// 			print_r(Zend_Json::encode($id));
// 			exit();
// 		}
// 	}
// 	function getallsupplierinveditAction(){
// 		if($this->getRequest()->isPost()){
// 			$data = $this->getRequest()->getPost();
// 			$db_com = new Invpayment_Model_DbTable_DbInvoice();
// 			$id = $db_com->getAllInvoiceBySupplierEdit($data);
// 			print_r(Zend_Json::encode($id));
// 			exit();
// 		}
// 	}
	

// 	function dndetailAction(){
// 		if($this->getRequest()->isPost()){
// 			$data = $this->getRequest()->getPost();
// 			$db = new Invpayment_Model_DbTable_DbInvoice();
// 			$_row =$db->getDnDetailTotalByPurchase($data);
			
// 			print_r(Zend_Json::encode($_row));
// 			exit();
			
// 		}
// 	}
	

}

