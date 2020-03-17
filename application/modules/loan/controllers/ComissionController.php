<?php
class Loan_ComissionController extends Zend_Controller_Action {
	
	public function init()
	{
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	
	public function indexAction()
	{
		try{
			$db = new Loan_Model_DbTable_DbComission();
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
					    'adv_search'=>'',
						'staff_id'=>-1,
						'branch_id_search' => -1,
						'from_date_search'=> date('Y-m-d'),
						'to_date_search'=>date('Y-m-d'));
			}
			$rs_rows= $db->getComissionSale($search);//call frome model
			$glClass = new Application_Model_GlobalClass();
			$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
			$list = new Application_Form_Frmtable();
			$collumns = array("PROJECT_NAME","SALE_NO","CLIENT_NAME","PROPERTY_TYPE","PROPERTY_CODE","STREET","SOLD_PRICE","STAFF_NAME","COMMISSION","DATE","STATUS");
			$link=array(
					'module'=>'loan','controller'=>'comission','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(10,$collumns,$rs_rows,array());
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$fm = new Loan_Form_FrmCommision();
		$frm = $fm->FrmAddFrmCancel();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_cancel = $frm;
	}
	public function addAction(){
		if($this->getRequest()->isPost()){//check condition return true click submit button
			$_data = $this->getRequest()->getPost();
			try {		
				$_dbmodel = new Loan_Model_DbTable_DbComission();
				if(isset($_data['save'])){
					$_dbmodel->addSaleComission($_data);
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/loan/comission/add");
				}elseif(isset($_data['save_close'])){
					$_dbmodel->addSaleComission($_data);
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/loan/comission");
				}else{
					$_dbmodel->addSaleComission($_data);
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/loan/comission");
				}				
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$fm = new Loan_Form_FrmCommision();
		$frm = $fm->FrmAddFrmCancel();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_loan = $frm;
		
		$db = new Loan_Model_DbTable_DbExpense();
		$result = $db->getAllExpenseCategory();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		array_unshift($result, array ( 'id' => -1,'name' => $tr->translate("ADD_NEW")));
		$this->view->all_category = $result;
		
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data=$key->getKeyCodeMiniInv(TRUE);
		
		$db = new Application_Model_DbTable_DbGlobal();
		$co_name = $db->getAllCoNameOnly();
		array_unshift($co_name,array(
				'id' => -1,
				'name' => $tr->translate("ADD_NEW"),
		) );
		$this->view->co_name=$co_name;
		
		$frmpopup = new Application_Form_FrmPopupGlobal();
		$this->view->footer = $frmpopup->getFooterReceipt();
	}
	public function editAction(){
		$id = $this->getRequest()->getParam('id');
		$_dbmodel = new Loan_Model_DbTable_DbComission();
		if($this->getRequest()->isPost()){//check condition return true click submit button
			$_data = $this->getRequest()->getPost();
			$_data['id']=$id;
			try {
				if(isset($_data['save'])){
					$_dbmodel->editCommission($_data);
				}elseif(isset($_data['save_close'])){
					$_dbmodel->editCommission($_data);
				}else{
					$_dbmodel->editCommission($_data);
				}
				Application_Form_FrmMessage::Sucessfull("UPDATE_SUCCESS","/loan/comission");
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("UPDATE_FAIL");
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
	      }
	    $row  = $_dbmodel->getComissionById($id);
	    $this->view->row = $row;
		$fm = new Loan_Form_FrmCommision();
		$frm = $fm->FrmAddFrmCancel($row);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_loan = $frm;
		
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$db = new Loan_Model_DbTable_DbExpense();
		$result = $db->getAllExpenseCategory();
		array_unshift($result, array ( 'id' => -1,'name' => $tr->translate("ADD_NEW")));
		$this->view->all_category = $result;
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data=$key->getKeyCodeMiniInv(TRUE);
		$this->view->category_id = $row['category_id'];
		
		$db = new Application_Model_DbTable_DbGlobal();
		$co_name = $db->getAllCoNameOnly();
		array_unshift($co_name,array(
				'id' => -1,
				'name' => $tr->translate("ADD_NEW"),
		) );
		$this->view->co_name=$co_name;
		
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
			$db = new Loan_Model_DbTable_DbComission();
			$dataclient=$db->getSaleNoByProject($data['branch_id'],$data['sale_id']);
			print_r(Zend_Json::encode($dataclient));
			exit();
		}
	}
	function getInfoAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Loan_Model_DbTable_DbComission();
			$dataclient=$db->getCientAndPropertyInfo($data['sale_id']);
			print_r(Zend_Json::encode($dataclient));
			exit();
		}
	}
}

