<?php
class Invpayment_ReceivechequeController extends Zend_Controller_Action {
	const REDIRECT_URL = '/invpayment/receivecheque';
	public function indexAction(){
		//$db = new ();
		try{
			$db = new Invpayment_Model_DbTable_DbIssueCheque();
			
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
			$rs_rows= $db->getAllIssueChequePayment($search);//
			
			
			$list = new Application_Form_Frmtable();
    		$collumns = array("PROJECT_NAME","DATE","RECEIVED_CHEQUE_NAME","PAYMENT_NO","SUPPLIER","STATUS","BY");
    		$link=array(
    				'module'=>'invpayment','controller'=>'receivecheque','action'=>'edit',
    		);
    		$this->view->list=$list->getCheckList(10, $collumns, $rs_rows , array('branch_name'=>$link,'receiverName'=>$link,));
			
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
		$db = new Invpayment_Model_DbTable_DbIssueCheque();
		$id = $this->getRequest()->getParam('id');
		$id = empty($id)?0:$id;
		$row = null;
		print_r($id);exit();
		if(empty($id)){
			$rs = $db->getDataRowIsseueCheque($id);
			if(!empty($rs)){
				$row = $rs;
				$this->view->row = $rs;
				
			}
		}
		
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {		
				
				$db->receiveChequePaymentInvoice($_data);
	    		Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL."/index");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		
    	$frm = new Invpayment_Form_FrmCheque();
    	$frm->FrmCheque($row);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm = $frm;
	}
	
	function editAction(){
		$tr=Application_Form_FrmLanguages::getCurrentlanguage();
		$db = new Invpayment_Model_DbTable_DbIssueCheque();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {		
				
				$db->editIssueChequePaymentInvoice($_data);
	    		Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS",self::REDIRECT_URL."/index");
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
		$row = $db->getDataRowIsseueCheque($id);
		$this->view->row = $row;
		if ($row['status']==0){
    		Application_Form_FrmMessage::Sucessfull($tr->translate('ALREADY_VOID'), self::REDIRECT_URL."/index");
    		exit();
    	}
		if (!empty($row['drawUserId'])){
    		Application_Form_FrmMessage::Sucessfull($tr->translate('ALREADY_WITHDRAW'), self::REDIRECT_URL."/index");
    		exit();
    	}
		
    	$frm = new Invpayment_Form_FrmCheque();
    	$frm->FrmCheque($row);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm = $frm;
	}
}

