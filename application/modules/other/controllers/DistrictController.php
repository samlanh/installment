<?php
class Other_DistrictController extends Zend_Controller_Action {
	const REDIRECT_URL='/other';
	protected $tr;
	public function init()
	{
		$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
			$db = new Other_Model_DbTable_DbDistrict();
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'adv_search' => '',
						'search_status' => -1);
			}
			$rs_rows= $db->getAllDistrict($search);
			$list = new Application_Form_Frmtable();
			$collumns = array("DISTRICT_CODE","DISTRICT_KH","DISTRICT_ENG","DISPLAY_BY","PROVINCE","DATE","STATUS","BY");
			$link=array(
					'module'=>'other','controller'=>'district','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('code'=>$link,'district_name'=>$link,'district_namekh'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		
		$fm = new Other_Form_FrmDistrict();
		$frm = $fm->FrmAddDistrict();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_district = $frm;
	}
	public function addAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try{
				$db_district = new Other_Model_DbTable_DbDistrict();
				$db_district->addDistrict($_data);
				if(!empty($_data['save_new'])){
					Application_Form_FrmMessage::message($this->tr->translate('INSERT_SUCCESS'));
				}else{
					Application_Form_FrmMessage::Sucessfull($this->tr->translate("INSERT_SUCCESS"),self::REDIRECT_URL .'/district/index');
				}
			}catch(Exception $e){
				Application_Form_FrmMessage::message($this->tr->translate("INSERT_FAIL"));
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$fm = new Other_Form_FrmDistrict();
		$frm = $fm->FrmAddDistrict();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_district = $frm;
	}
	public function editAction(){
		$db_district = new Other_Model_DbTable_DbDistrict();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try{
				$db_district->addDistrict($_data);
				Application_Form_FrmMessage::Sucessfull($this->tr->translate('EDIT_SUCCESS'),self::REDIRECT_URL . '/district/index');
			}catch(Exception $e){
				Application_Form_FrmMessage::message($this->tr->translate('EDIT_FAIL'));
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$id = $this->getRequest()->getParam("id");
		$row = $db_district->getDistrictById($id);
		if(empty($row)){
			$this->_redirect('other/district');
		}
		$fm = new Other_Form_FrmDistrict();
		$frm = $fm->FrmAddDistrict($row);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_district = $frm;
	}
	public function addNewdistrictAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$data['status']=1;
			$data['district_name']=$data['pop_district_name'];
			$db_district = new Other_Model_DbTable_DbDistrict();
			$id = $db_district->addDistrict($data);
			print_r(Zend_Json::encode($id));
			exit();
		}
	}
	function getDistrictAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db_district = new Other_Model_DbTable_DbDistrict();
			$rows = $db_district->getDistrictByIdProvince($data['pro_id']);
			array_unshift($rows, array ( 'id' => -1, 'name' => 'បន្ថែម​អ្នក​ទទួល​ថ្មី') );
			print_r(Zend_Json::encode($rows));
			exit();
		}
	}
	function addDistrictAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db_district = new Other_Model_DbTable_DbDistrict();
			$rows = $db_district->addDistrictByAjax($data);
// 			array_unshift($rows, array ( 'id' => -1, 'name' => 'បន្ថែម​អ្នក​ទទួល​ថ្មី') );
			print_r(Zend_Json::encode($rows));
			exit();
		}
	}
}
