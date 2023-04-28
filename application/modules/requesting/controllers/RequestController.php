<?php
class Requesting_RequestController extends Zend_Controller_Action
{
	const REDIRECT_URL = '/requesting/request';
	public function init()
	{
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction()
	{
		try {
			if ($this->getRequest()->isPost()) {
				$search = $this->getRequest()->getPost();
			} else {
				$search = array(
					'adv_search' => "",
					'branch_id' => -1,
					'start_date' => date('Y-m-d'),
					'end_date' => date('Y-m-d'),
					'status' => -1,
					'reqPOStatus' => -1,
				);
			}
			$this->view->search = $search;
			$db = new Requesting_Model_DbTable_DbRequest();
			$rs_rows = $db->getAllRequestPO($search);

			$list = new Application_Form_Frmtable();
			$collumns = array("PROJECT_NAME", "REQUEST_NO", "REQUEST_NO_FROM", "PURPOSE", "DATE", "CHECKING_STATUS", "CHECKING_BY", "PCHECKING_STATUS", "PCHECKING_BY", "APPROVED_STATUS", "APPROVED_BY", "USER", "PROCESSING_STATUS", "PO_STATUS", "STATUS");
			$link = array(
				'module' => 'requesting', 'controller' => 'request', 'action' => 'edit',
			);
			$this->view->list = $list->getCheckList(10, $collumns, $rs_rows, array('branch_name' => $link, 'requestNo' => $link));
		} catch (Exception $e) {
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("APPLICATION_ERROR");
		}

		$frm_search = new Application_Form_FrmAdvanceSearchStock();
		$frm = $frm_search->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
	}
	public function addAction()
	{
		$dbGbSt = new Application_Model_DbTable_DbGlobalStock();
		$notify = array(
			"userAction" => 2,
			"typeNotify" => "toCheckingRequest",
		);
		$db = new Requesting_Model_DbTable_DbRequest();
		if ($this->getRequest()->isPost()) {
			try {
				$data = $this->getRequest()->getPost();
				$requestId = $db->addRequestPO($data);

				$row = $db->getRequestPOById($requestId);
				if (!empty($row)) {
					$notify["notificationId"]  = $requestId;
					$notify["branchId"]  = $row["projectId"];
					$dbGbSt->pushNotificationForAndroid($notify);
				}


				if (isset($data['save_close'])) {
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", self::REDIRECT_URL . "/index");
				} else {
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", self::REDIRECT_URL . "/add");
				}
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("APPLICATION_ERROR");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$frm = new Requesting_Form_FrmRequest();
		$frm->FrmRequestPO(null);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm = $frm;
	}
	public function editAction()
	{

		$dbGbSt = new Application_Model_DbTable_DbGlobalStock();
		$notify = array(
			"userAction" => 2,
			"typeNotify" => "Requesting",
		);

		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$db = new Requesting_Model_DbTable_DbRequest();
		$id = $this->getRequest()->getParam('id');
		$id = empty($id) ? 0 : $id;
		if ($this->getRequest()->isPost()) {
			try {
				$data = $this->getRequest()->getPost();
				$db->editRequestPO($data);

				$row = $db->getRequestPOById($id);
				if (!empty($row)) {
					$notify["notificationId"]  = $id;
					$notify["notificationSubTitle"]  = $row["branch_name"] . " " . $row["requestNo"];
					$notify["branchId"]  = $row["projectId"];
					$dbGbSt->pushNotificationForAndroid($notify);
				}

				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS", self::REDIRECT_URL . "/index");
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("APPLICATION_ERROR");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$row = $db->getRequestPOById($id);
		if (empty($row)) {
			Application_Form_FrmMessage::Sucessfull("NO_RECORD", self::REDIRECT_URL . "/index", 2);
			exit();
		}


		$arrStep = array(
			'stepNum' => $row['processingStatus'],
			'typeStep' => 2,
		);
		$processingStatusTitle = $dbGbSt->requestingProccess($arrStep);
		if ($row['processingStatus'] > 1) {
			Application_Form_FrmMessage::Sucessfull($tr->translate('REQUEST_IS_ON_PROCCESING') . " " . $processingStatusTitle, self::REDIRECT_URL . "/index", 2);
			exit();
		}
		/*
		if ($row['checkingStatus']>0){
    		Application_Form_FrmMessage::Sucessfull("REQUEST_IS_ON_PROCCESING", self::REDIRECT_URL."/index",2);
    		exit();
    	}
		*/
		$this->view->row = $row;
		$this->view->rowdetail = $db->getRequestPODetailById($row);

		$frm = new Requesting_Form_FrmRequest();
		$frm->FrmRequestPO($row);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm = $frm;
	}

	function getallproductAction()
	{
		if ($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobalStock();
			$_row = $db->getAllProduct($data);

			$tr = Application_Form_FrmLanguages::getCurrentlanguage();

			$labelProductOrService = $tr->translate("SELECT_PRODUCT");
			if (!empty($data['isService'])) {
				$labelProductOrService = $tr->translate("SELECT_SERVICE");
			}
			array_unshift($_row, array(
				'id' => 0,
				'name' => $labelProductOrService,
			));
			if (!empty($data['isAddnew'])) {
				array_unshift($_row, array('id' => -1, 'name' => $tr->translate("ADD_NEW"),));
			}
			print_r(Zend_Json::encode($_row));
			exit();
		}
	}

	function productinfoAction()
	{
		if ($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobalStock();
			$_row = $db->getProductInfoByLocation($data);
			print_r(Zend_Json::encode($_row));
			exit();
		}
	}

	function getrequestnoAction()
	{
		if ($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobalStock();
			$_row = $db->generateRequestNo($data);
			print_r(Zend_Json::encode($_row));
			exit();
		}
	}

	function getallapprovedrequestAction()
	{
		if ($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobalStock();
			$_row = $db->getAllApprovedRequest($data);

			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
			array_unshift($_row, array(
				'id' => 0,
				'name' => $tr->translate("SELECT_REQUEST_NO"),
			));
			print_r(Zend_Json::encode($_row));
			exit();
		}
	}

	function getRequestinfoAction()
	{
		if ($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$db = new Requesting_Model_DbTable_DbRequest();
			$_row = $db->getRequestPOInfoById($data);
			print_r(Zend_Json::encode($_row));
			exit();
		}
	}
	function getRequestinfohtmlAction()
	{
		if ($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$db = new Requesting_Model_DbTable_DbRequest();
			$_row = $db->getRequestInfoHTML($data);
			print_r(Zend_Json::encode($_row));
			exit();
		}
	}
}
