<?php
class Other_HolidayController extends Zend_Controller_Action {
	protected $tr;
	const REDIRECT_URL ='/other';
	public function init()
	{
		/* Initialize action controller here */
		$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
			$db = new Other_Model_DbTable_DbHoliday();
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'adv_search' => '',
						'search_status' => -1,
						'start_date'=> date('Y-m-01'),
						'end_date'=>date('Y-m-d'));
			}
			$rs_rows= $db->getAllHoliday($search);
			$glClass = new Application_Model_GlobalClass();
			$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
			$list = new Application_Form_Frmtable();
			$collumns = array("HOLIDAY_NAME","AMOUNT","START_DATE","END_DATE","NOTE","STATUS","BY");
			$link=array(
					'module'=>'other','controller'=>'holiday','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('holiday_name'=>$link,'start_date'=>$link,'amount_day'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	
		$frm = new Other_Form_FrmHoliday();
		$frm = $frm->FrmAddHoliday();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_holiday = $frm;
	}
	function addAction()
	{
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$db = new Other_Model_DbTable_DbHoliday();
				$_major_id = $db->addHoliday($_data);
				if(!empty($_data['save_new'])){
					Application_Form_FrmMessage::message($this->tr->translate('INSERT_SUCCESS'));
				}else{
					Application_Form_FrmMessage::Sucessfull($this->tr->translate('INSERT_SUCCESS'), self::REDIRECT_URL . '/holiday/index');
				}
			} catch (Exception $e) {
				Application_Form_FrmMessage::message($this->tr->translate('INSERT_FAIL'));
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$frm = new Other_Form_FrmHoliday();
		$frm_holiday=$frm->FrmAddHoliday(null);
		Application_Model_Decorator::removeAllDecorator($frm_holiday);
		$this->view->frm_holiday = $frm_holiday;
	}
	function editAction()
	{
		$db =new  Other_Model_DbTable_DbHoliday();
		//$db->deleteHoliday();
		$db = new Other_Model_DbTable_DbHoliday();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$_major_id = $db->addHoliday($_data);
				Application_Form_FrmMessage::Sucessfull($this->tr->translate("EDIT_SUCCESS"),self::REDIRECT_URL.'/holiday/index');
			} catch (Exception $e) {
				Application_Form_FrmMessage::message($this->tr->translate("EDIT_FAIL"));
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$id = $this->getRequest()->getParam('id');
		$row = $db->getHolidayById($id);
		if(empty($row)){
			$this->_redirect('/other/holiday');
		}
		$frm = new Other_Form_FrmHoliday();
		$frm_holiday=$frm->FrmAddHoliday($row);
		Application_Model_Decorator::removeAllDecorator($frm_holiday);
		$this->view->frm_holiday = $frm_holiday;
	}
}
