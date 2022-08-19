<?php
class Loan_CancelController extends Zend_Controller_Action {
	public function init()
	{
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction()
	{
		try{
			$db = new Loan_Model_DbTable_DbCancel();
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
					    'adv_search'=>'',
						'client_name'=> -1,
						'branch_id_search' => -1,
						'land_id'=>-1,
						'from_date_search'=> date('Y-m-d'),
						'to_date_search'=>date('Y-m-d'));
			}
			$rs_rows= $db->getCancelSale($search);//call frome model
			$glClass = new Application_Model_GlobalClass();
			$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
			$list = new Application_Form_Frmtable();
			$collumns = array("PROJECT_NAME","CLIENT_NAME","PROPERTY_TYPE","PROPERTY_CODE","STREET","SOLD_PRICE","INSTALLMENT_PAID","PAID_AMOUNT","RETURN_MONEY_BACK","DATE",
					"BY_USER","STATUS");
			$link=array(
					'module'=>'loan','controller'=>'cancel','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(10,$collumns,$rs_rows,array('client_name'=>$link,'project_name'=>$link,'property_type'=>$link,'land_address'=>$link));
			
			$this->view->rssearch = $search;
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$fm = new Loan_Form_FrmCancel();
		$frm = $fm->FrmAddFrmCancel();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_cancel = $frm;
		
		$frm = new Loan_Form_FrmSearchLoan();
		$frm = $frm->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
	}
	public function addAction(){
		if($this->getRequest()->isPost()){
			// Check Session Expire
			$dbgb = new Application_Model_DbTable_DbGlobal();
			$checkses = $dbgb->checkSessionExpire();
			if (empty($checkses)){
				$dbgb->reloadPageExpireSession();
				exit();
			}
			$_data = $this->getRequest()->getPost();
			try {		
				$_dbmodel = new Loan_Model_DbTable_DbCancel();
				$_dbmodel->addCancelSale($_data);
				$_dbmodel->recordhistory($_data);
// 				Application_Form_FrmMessage::message("INSERT_SUCCESS");
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/loan/cancel");
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$fm = new Loan_Form_FrmCancel();
		$frm = $fm->FrmAddFrmCancel();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_loan = $frm;
		
		$db = new Loan_Model_DbTable_DbExpense();
		$result = $db->getAllExpenseCategory();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		array_unshift($result, array ( 'id' => -1,'name' =>$tr->translate("ADD_NEW")));
		$this->view->all_category = $result;
		
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data=$key->getKeyCodeMiniInv(TRUE);
		
		$frmpopup = new Application_Form_FrmPopupGlobal();
		$this->view->footer = $frmpopup->getFooterReceipt();
	}
	public function editAction(){
		$id = $this->getRequest()->getParam('id');
		$id = empty($id)?0:$id;
		$_dbmodel = new Loan_Model_DbTable_DbCancel();
		if($this->getRequest()->isPost()){//check condition return true click submit button
			// Check Session Expire
			$dbgb = new Application_Model_DbTable_DbGlobal();
			$checkses = $dbgb->checkSessionExpire();
			if (empty($checkses)){
				$dbgb->reloadPageExpireSession();
				exit();
			}
			$_data = $this->getRequest()->getPost();
			$_data['id']=$id;
			try {
				$_dbmodel->recordhistory($_data);//Record History user
				if(isset($_data['save'])){
						$_dbmodel->editCancelSale($_data);
				}elseif(isset($_data['save_close'])){
						$_dbmodel->editCancelSale($_data);
				}else{
					$_dbmodel->editCancelSale($_data);
				}
				
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/loan/cancel");
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("UPDATE_FAIL");
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
	      }
	    $row  = $_dbmodel->getCancelById($id);
	    if(empty($row)){
	    	Application_Form_FrmMessage::Sucessfull("RECORD_NOTFUND","/loan/cancel",2);
	    	exit();
	    }
	    $this->view->row = $row;
		$fm = new Loan_Form_FrmCancel();
		$frm = $fm->FrmAddFrmCancel($row);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_loan = $frm;
	
		$db = new Loan_Model_DbTable_DbExpense();
		$result = $db->getAllExpenseCategory();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		array_unshift($result, array ( 'id' => -1,'name' =>$tr->translate("ADD_NEW")));
		$this->view->all_category = $result;
		
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data=$key->getKeyCodeMiniInv(TRUE);
		
		$this->view->expense_id = $row['expense_id'];
		
		$frmpopup = new Application_Form_FrmPopupGlobal();
		$this->view->footer = $frmpopup->getFooterReceipt();
	}
    function getCancelNoAction(){// by vandy get property code
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobal();
			$dataclient=$db->getNewCacelCodeByBranch($data['branch_id']);
			print_r(Zend_Json::encode($dataclient));
			exit();
		}
	}
	function getSaleAction(){// by vandy get property code
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobal();
			$dataclient=$db->getSaleNoByProject($data['branch_id']);
			print_r(Zend_Json::encode($dataclient));
			exit();
		}
	}
	function getSaleclieAction(){// by vandy get property code
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Loan_Model_DbTable_DbCancel();
			$data['is_issueplong'] = empty($data['is_issueplong'])?0:1;
			$data['is_completed'] = empty($data['is_completed'])?0:1;
			$data['is_comission'] = empty($data['is_comission'])?0:1;
			
			$dataclient=$db->getSaleNoByProject($data['branch_id'],$data['sale_id'],$data['is_issueplong'],$data['is_completed'],$data['is_comission']);
			print_r(Zend_Json::encode($dataclient));
			exit();
		}
	}
	function getInfoAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Loan_Model_DbTable_DbCancel();
			$dataclient=$db->getCientAndPropertyInfo($data['sale_id']);
			print_r(Zend_Json::encode($dataclient));
			exit();
		}
	}
	
	
	function getSalecancleAction(){// by vandy get property code
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Loan_Model_DbTable_DbCancel();
			
			$dataclient=$db->getCancelSaleReturnBack($data);
			print_r(Zend_Json::encode($dataclient));
			exit();
		}
	}
	function getCancelinfoAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Loan_Model_DbTable_DbCancel();
			$dataclient=$db->getCancelById($data['id']);
			print_r(Zend_Json::encode($dataclient));
			exit();
		}
	}
}

