<?php
class Property_rentController extends Zend_Controller_Action {
	private $activelist = array('មិនប្រើ​ប្រាស់', 'ប្រើ​ប្រាស់');
    public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	private $sex=array(1=>'M',2=>'F');
	public function indexAction(){
		try{
		    if($this->getRequest()->isPost()){
 				$search = $this->getRequest()->getPost();
 			}
			else{
				$search = array(
						'adv_search'=>'',
						'land_blog'=> 0,
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
						'show_all'=>'',
						'status'=>-1,
						 );
			}
			$db = new Property_Model_DbTable_DbRent();
			$rs_rows= $db->getRent($search);
			$this->view->row = $rs_rows;
// 			$glClass = new Application_Model_GlobalClass();
// 			$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
// 			$list = new Application_Form_Frmtable();
// 			$collumns = array("BUY_NO","LAND_TYPE","WIDTH","HEIGHT","SIZE","PRICE","LOCATION","BUY_DATE","STATUS");
// 			$link_info=array('module'=>'property','controller'=>'index','action'=>'edit',);
// 			$this->view->list=$list->getCheckList(2, $collumns, $rs_rows,array('buy_no'=>$link_info,'land_type'=>$link_info,'width'=>$link_info,'height'=>$link_info,'size'=>$link_info),0);
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			echo $e->getMessage();
		}	
		$frm = new Application_Form_FrmAdvanceSearch();
		$frmsearch=$frm->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($frmsearch);
		$this->view->frm_search = $frmsearch;
		
		$frm = new Property_Form_FrmBuyland();
		$frm_buy=$frm->FrmBuyLand();
		Application_Model_Decorator::removeAllDecorator($frm_buy);
		$this->view->frm_buy = $frm_buy;
  }
  function addAction()
  {
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$_db = new Property_Model_DbTable_DbRent();
				$_db->addRent($_data);
				if(!empty($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/property/rent");
				}else{
					Application_Form_FrmMessage::message("INSERT_SUCCESS");
				}
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		$frm = new Property_Form_FrmRent();
		$frm_rent=$frm->FrmRent();
		Application_Model_Decorator::removeAllDecorator($frm_rent);
		$this->view->frm_rent = $frm_rent;
		
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$db = new Application_Model_DbTable_DbGlobal();
		$co_name = $db->getAllCoNameOnly();
		array_unshift($co_name,array(
				'id' => -1,
				'name' => '---Add New ---',
		) );
		$this->view->co_name=$co_name;
		
		$customer = $db->getAllClientname();
		array_unshift($customer,array('id' =>'','name' => $tr->translate("SELECT_CLIENT")),
				array('id' => -1,		'name' => $tr->translate("ADD_NEW"),
				) );
		$this->view->customer = $customer;
		
		$dbpop = new Application_Form_FrmPopupGlobal();
		$this->view->formcustomer = $dbpop->frmPopupCustomer();

	}	
	public function editAction(){
		$_db = new Property_Model_DbTable_DbRent();
		$id = $this->getRequest()->getParam('id');
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			$_data['id'] = $id;
			try{
				$_db->updateRent($_data);
				Application_Form_FrmMessage::Sucessfull("UPDATE_SUCCESS","/property/rent");
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("UPDATE_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($err =$e->getMessage());
			}
		}
		$db_property = new Group_Model_DbTable_DbLand();
		$property_type = $db_property->getPropertyType();
		array_unshift($property_type, array('id'=>'','name' => "-----ជ្រើសរើស-----"), array('id'=>'-1', 'name'=>'Add New Property Type'));
		$this->view->pro_type = $property_type;
		
		$rs = $_db->getRentById($id);
		$this->view->rs = $rs;
		
		$frm = new Property_Form_FrmRent();
		$frm_rent=$frm->FrmRent($rs,2);
		Application_Model_Decorator::removeAllDecorator($frm_rent);
		$this->view->frm_rent = $frm_rent;
		
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$db = new Application_Model_DbTable_DbGlobal();
		$co_name = $db->getAllCoNameOnly();
		array_unshift($co_name,array(
				'id' => -1,
				'name' => '---Add New ---',
		) );
		$this->view->co_name=$co_name;
		
		$customer = $db->getAllClientname();
		array_unshift($customer,array('id' =>'','name' => $tr->translate("SELECT_CLIENT")),
				array('id' => -1,		'name' => $tr->translate("ADD_NEW"),
				) );
		$this->view->customer = $customer;
		
		$dbpop = new Application_Form_FrmPopupGlobal();
		$this->view->formcustomer = $dbpop->frmPopupCustomer();
	
	}	
	function addStaffAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Property_Model_DbTable_DbRent();
			$id = $db->addStaff($data);
			print_r(Zend_Json::encode($id));
			exit();
		}
	}
	function getalllandAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Property_Model_DbTable_DbRent();
			$id = $db->getAllLand($data['land_blog']);
			print_r(Zend_Json::encode($id));
			//echo $id;
			exit();
		}
	}
	function getlandinfoAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Property_Model_DbTable_DbRent();
			$id = $db->getLandInfo($data['land_id'],$data['type']);
			print_r(Zend_Json::encode($id));
			exit();
		}
	}
	function getalllandforeditAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Property_Model_DbTable_DbRent();
			$id = $db->getAllLandForEdit($data['land_blog'],$data['sale_id']);
			print_r(Zend_Json::encode($id));
			exit();
		}
	}
	function getlandblogAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Property_Model_DbTable_DbRent();
			$id = $db->getLandBlog($data['seller']);
			print_r(Zend_Json::encode($id));
			exit();
		}
	}
}

