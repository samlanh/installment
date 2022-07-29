<?php
class Invpayment_DepositinvController extends Zend_Controller_Action {
	const REDIRECT_URL = '/invpayment/depositinv';
	const INVOICE_TYPE = 2;//Deposit Invoice
	public function indexAction(){
		//$db = new ();
		try{
			$db = new Invpayment_Model_DbTable_DbDepositInvoice();
			
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
			$search['ivType']=self::INVOICE_TYPE;
			$rs_rows=array();
			$rs_rows= $db->getAllDepositInvoice($search);//
			
			
			$list = new Application_Form_Frmtable();
    		$collumns = array("PROJECT_NAME","INVOICE_NO","RECEIVE_DATE","SUPPLIER_INVOICE","INVOICE_DATE","PO_NO","SUPPLIER","TOTAL","STATUS","BY");
    		$link=array(
    				'module'=>'invpayment','controller'=>'depositinv','action'=>'edit',
    		);
    		$this->view->list=$list->getCheckList(10, $collumns, $rs_rows , array('branch_name'=>$link,'purchaseNo'=>$link,));
			
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
		$db = new Invpayment_Model_DbTable_DbDepositInvoice();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {		
				
				$_data['ivType']=self::INVOICE_TYPE;
				$db->addDepositInvoice($_data);
	    		Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL."/index");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		
    	$frm = new Invpayment_Form_FrmInvoice();
    	$frm->FrmInvoices(null);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm = $frm;
	}
	function editAction(){
		$tr=Application_Form_FrmLanguages::getCurrentlanguage();
		$db = new Invpayment_Model_DbTable_DbDepositInvoice();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$_data['ivType']=self::INVOICE_TYPE;
				$db->editDepositInvoice($_data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS",self::REDIRECT_URL."/index");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		$id = $this->getRequest()->getParam('id');
		$id = empty($id)?0:$id;
		if(empty($id)){
			Application_Form_FrmMessage::Sucessfull("NO_DATA",self::REDIRECT_URL."/index");
			exit();
		}
		
		$row = $db->getDataRow($id);
		$this->view->row = $row;
		if ($row['status']==0){
    		Application_Form_FrmMessage::Sucessfull($tr->translate('ALREADY_VOID'), self::REDIRECT_URL."/index");
    		exit();
    	}
		
		$dbGBstock = new Application_Model_DbTable_DbGlobalStock();
		$arrStep = array(
				'keyIndex'=>self::INVOICE_TYPE,
				'typeKeyIndex'=>1,
			);
		$invoiceType = $dbGBstock->invoiceTypeKey($arrStep);
		
		if ($row['ivType']!=$invoiceType){
    		Application_Form_FrmMessage::Sucessfull($tr->translate('NO_DATA'), self::REDIRECT_URL."/index");
    		exit();
    	}
		
		$arrFilter = array(
						'id'=>$id,
					);
		$this->view->rowdetail = $db->getInvoiceDetailById($arrFilter);
		$arrFilter['isService']=1;
		$this->view->rowdetailServicce = $db->getInvoiceDetailById($arrFilter);
		
		
		$frm = new Invpayment_Form_FrmInvoice();
    	$frm->FrmInvoices($row);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm = $frm;
	}
}

