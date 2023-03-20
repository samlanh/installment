<?php
class Invpayment_PaymentController extends Zend_Controller_Action
{
	const REDIRECT_URL = '/invpayment/payment';
	public function init()
	{
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL') || define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction()
	{

		try {
			$db = new Invpayment_Model_DbTable_DbPayment();
			if (!empty($this->getRequest()->isPost())) {
				$search = $this->getRequest()->getPost();
			} else {
				$search = array(
					'adv_search' => '',
					'branch_id' => -1,
					'statusAcc' => -1,
					'start_date' => date('Y-m-d'),
					'end_date' => date('Y-m-d'),
				);
			}
			$rs_rows = array();
			$rs_rows = $db->getAllPayment($search); //


			$list = new Application_Form_Frmtable();
			$collumns = array("PROJECT_NAME", "PAYMENT_NO", "SUPPLIER", "DATE", "PAYMENT_METHOD", "BANK", "ACCOUNT_AND_CHEQUE_NO", "TOTAL_PAID", "STATUS", "BY");
			$link = array(
				'module' => 'invpayment',
				'controller' => 'payment',
				'action' => 'edit',
			);
			$this->view->list = $list->getCheckList(10, $collumns, $rs_rows, array('branch_name' => $link, 'paymentNo' => $link, ));

		} catch (Exception $e) {
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}

		$frm_search = new Application_Form_FrmAdvanceSearchStock();
		$frm = $frm_search->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;

	}
	function addAction()
	{

		$db = new Invpayment_Model_DbTable_DbPayment();
		if ($this->getRequest()->isPost()) {
			$_data = $this->getRequest()->getPost();
			try {

				$db->issuePaymentInvoice($_data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", self::REDIRECT_URL . "/index");
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}


		$frm = new Invpayment_Form_FrmPayment();
		$frm->FrmPayment(null);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm = $frm;

	}
	function editAction()
	{

		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$db = new Invpayment_Model_DbTable_DbPayment();
		if ($this->getRequest()->isPost()) {
			$_data = $this->getRequest()->getPost();
			try {
				$db->editPaymentInvoice($_data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS", self::REDIRECT_URL . "/index");
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}

		$id = $this->getRequest()->getParam('id');
		$id = empty($id) ? 0 : $id;
		if (empty($id)) {
			Application_Form_FrmMessage::Sucessfull("NO_DATA", self::REDIRECT_URL . "/index", 2);
			exit();
		}

		$arrSearch = array(
			'branch_id' => 1,
			'supplierId' => 2,
			'keyindex' => 1,

			'paymentId' => 1,
		);

		$row = $db->getDataRowPayment($id);
		$this->view->row = $row;
		if (empty($row)) {
			Application_Form_FrmMessage::Sucessfull($tr->translate('NO_DATA'), self::REDIRECT_URL . "/index", 2);
			exit();
		}
		if ($row['status'] == 0) {
			Application_Form_FrmMessage::Sucessfull($tr->translate('ALREADY_VOID'), self::REDIRECT_URL . "/index", 2);
			exit();
		}
		if ($row['isClosed'] == 1) {
			Application_Form_FrmMessage::Sucessfull($tr->translate('PAYMENT_IS_ALREADY_CLOSED'), self::REDIRECT_URL . "/index", 2);
			exit();
		}
		$frm = new Invpayment_Form_FrmPayment();
		$frm->FrmPayment($row);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm = $frm;

	}

	function getpaymentnoAction()
	{
		if ($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobalStock();
			$_row = $db->generatePaymentNo($data);
			print_r(Zend_Json::encode($_row));
			exit();

		}
	}

	function getallpaymentAction()
	{
		if ($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobalStock();
			$_row = $db->getAllPaymentRecord($data);

			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
			array_unshift($_row, array(
				'id' => 0,
				'name' => $tr->translate("SELECT_PAYMENT_NO"),
			)
			);
			print_r(Zend_Json::encode($_row));
			exit();

		}
	}
	function getpaymentinfoAction()
	{
		if ($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$db = new Invpayment_Model_DbTable_DbPayment();
			$paymentId = empty($data['paymentId']) ? 0 : $data['paymentId'];
			$_row = $db->getDataRowPayment($paymentId);
			print_r(Zend_Json::encode($_row));
			exit();

		}
	}
}