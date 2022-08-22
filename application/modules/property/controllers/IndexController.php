<?php
class Property_indexController extends Zend_Controller_Action {
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
						 );
			}
			$db = new Property_Model_DbTable_DbBuyland();
			$rs_rows= $db->getBuyland($search);
			$this->view->row = $rs_rows;
// 			$glClass = new Application_Model_GlobalClass();
// 			$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
// 			$list = new Application_Form_Frmtable();
// 			$collumns = array("BUY_NO","LAND_BLOG","WIDTH","HEIGHT","SIZE","PRICE","LOCATION","BUY_DATE","STATUS");
// 			$link_info=array('module'=>'property','controller'=>'index','action'=>'edit',);
// 			$this->view->list=$list->getCheckList(2, $collumns, $rs_rows,array('buy_no'=>$link_info,'land_blog'=>$link_info,'width'=>$link_info,'height'=>$link_info,'size'=>$link_info),0);
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
				$_db = new Property_Model_DbTable_DbBuyland();
				$_db->addBuyLand($_data);
				if(!empty($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/property/index");
				}else{
					Application_Form_FrmMessage::message("INSERT_SUCCESS");
				}
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$db_property = new Group_Model_DbTable_DbLand();
		$property_type = $db_property->getPropertyType();
		array_unshift($property_type, array('id'=>'','name' => "-----ជ្រើសរើស-----"), array('id'=>'-1', 'name'=>'Add New Property Type'));
		$this->view->pro_type = $property_type;
		
		$frm = new Property_Form_FrmBuyland();
		$frm_buy=$frm->FrmBuyLand();
		Application_Model_Decorator::removeAllDecorator($frm_buy);
		$this->view->frm_buy = $frm_buy;
		
		$dbpop = new Application_Form_FrmPopupGlobal();
		$this->view->frmPopupPropertyType = $dbpop->frmPopupPropertyType();
		$this->view->frmPopuplandblog = $dbpop->frmPopupLandblog();
		
		$db_landblog = new Property_Model_DbTable_DbLandBlog();
		$landblog = $db_landblog->getLandBlog();
		array_unshift($landblog, array('id'=>'','name' => "-----ជ្រើសរើស-----"), array('id'=>'-1', 'name'=>'បន្ថែមថ្មី'));
		$this->view->land_blog = $landblog;
		
		$db = new Application_Model_DbTable_DbGlobal();
		$client_type = $db->getclientdtype();
		array_unshift($client_type,array('id' => -1,'name' => '--- បន្ថែមថ្មី ---',));
		array_unshift($client_type,array('id' => 0,'name' => '---Please Select ---',));
		$this->view->clienttype = $client_type;

	}	
	public function editAction(){
		$_db = new Property_Model_DbTable_DbBuyland();
		$id = $this->getRequest()->getParam('id');
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			//$_data['id'] = $id;
			try{
				$_db->updateBuyLand($_data);
				Application_Form_FrmMessage::Sucessfull("UPDATE_SUCCESS","/property/index");
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("UPDATE_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($err =$e->getMessage());
			}
		}
		$db_property = new Group_Model_DbTable_DbLand();
		$property_type = $db_property->getPropertyType();
		array_unshift($property_type, array('id'=>'','name' => "-----ជ្រើសរើស-----"), array('id'=>'-1', 'name'=>'Add New Property Type'));
		$this->view->pro_type = $property_type;
		
		$rs = $_db->getBuyLandById($id);
		$this->view->rs = $rs;
		$this->view->id = $id;
		$frm = new Property_Form_FrmBuyland();
		$frm_buy=$frm->FrmBuyLand($rs);
		Application_Model_Decorator::removeAllDecorator($frm_buy);
		$this->view->frm_buy = $frm_buy;
		
		$dbpop = new Application_Form_FrmPopupGlobal();
		$this->view->frmPopupPropertyType = $dbpop->frmPopupPropertyType();
		$this->view->frmPopuplandblog = $dbpop->frmPopupLandblog();
		
		$db_landblog = new Property_Model_DbTable_DbLandBlog();
		$landblog = $db_landblog->getLandBlog();
		array_unshift($landblog, array('id'=>'','name' => "-----ជ្រើសរើស-----"), array('id'=>'-1', 'name'=>'បន្ថែមថ្មី'));
		$this->view->land_blog = $landblog;
		
		$db = new Application_Model_DbTable_DbGlobal();
		$client_type = $db->getclientdtype();
		array_unshift($client_type,array('id' => -1,'name' => '--- បន្ថែមថ្មី ---',));
		array_unshift($client_type,array('id' => 0,'name' => '---Please Select ---',));
		$this->view->clienttype = $client_type;
	
	}	
	function deleteAction(){
		$id = $this->getRequest()->getParam("id");
		$db = new Property_Model_DbTable_DbBuyland();
		if (!empty($id)){
			try{
				$check = $db->checkLandIslock($id);
				if (!empty($check)){
					$db->deleteBuyLand($id);
					Application_Form_FrmMessage::Sucessfull('DELETE_SUCCESS',"/property/index");
				}else{
					Application_Form_FrmMessage::Sucessfull('ដីនេះបានដាក់លក់ឬក៏ជួលរួចហើយមិនអាចលុបបានទេ!',"/property/index",2);
				}
				
			}catch (Exception $e){
				Application_Form_FrmMessage::message("DELETE_FAILE");
				echo $e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}else{
			$this->_redirect("/property/index");
		}
	}
}

