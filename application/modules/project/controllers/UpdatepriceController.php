<?php
class Project_UpdatepriceController extends Zend_Controller_Action {
	const REDIRECT_URL = '/project';
	public function init()
	{
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
			$db = new Project_Model_DbTable_Dbupdateprice();
			if($this->getRequest()->isPost()){
				$formdata=$this->getRequest()->getPost();
				$search = array(
					'adv_search' => $formdata['adv_search'],
					'streetlist'=>$formdata['streetlist'],
					'branch_id'=>$formdata['branch_id'],
				);
			}
			else{
				$search = array(
					'adv_search' => '',
					'branch_id'=>-1,
					'streetlist'=>''			
				);
			}
			$rs_rows= $db->getAllRoad($search);
			$this->view->rs = $rs_rows;
			
		}catch(Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$frm = new Application_Form_FrmAdvanceSearch();
		$frm = $frm->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
		$this->view->result=$search;	
		
		$fm = new Project_Form_FrmLand();
		$frmserch = $fm->FrmLandInfo();
		Application_Model_Decorator::removeAllDecorator($frmserch);
		$this->view->frm_land = $frmserch;
	}
	public function addAction(){
		$this->_redirect("/project/updateprice");
	}
	public function editAction(){
		$id = $this->getRequest()->getParam("id");
		$id = empty($id)?0:$id;
		$branch_id = $this->getRequest()->getParam("branch_id");
		$branch_id = empty($branch_id)?null:$branch_id;
		
		$property_type = $this->getRequest()->getParam("pro_type");
		$property_type = empty($property_type)?null:$property_type;
		
		$db = new Project_Model_DbTable_Dbupdateprice();
		if($this->getRequest()->isPost()){
			try{
				$data = $this->getRequest()->getPost();
				$data['branch_id']=$branch_id;
				$db->updatePrice($data);
				Application_Form_FrmMessage::Sucessfull('EDIT_SUCCESS',"/project/updateprice");
			}catch (Exception $e){
				Application_Form_FrmMessage::message("EDIT_FAILE");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$row = $db->getPropertiesByStreet($id,$branch_id,$property_type);
	    $this->view->result=$row;
		if(empty($row)){
			$this->_redirect("/group/updateprice");
		}
	}
}