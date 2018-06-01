<?php
class Group_CustomerController extends Zend_Controller_Action {
	const REDIRECT_URL = '/group/index';
	public function init()
	{
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
			$db = new Group_Model_DbTable_DbCustomer();
			if($this->getRequest()->isPost()){
				$formdata=$this->getRequest()->getPost();
				$search = array(
						'adv_search' => $formdata['adv_search'],
						'status'=>$formdata['status'],						
						'start_date'=> $formdata['start_date'],
						'end_date'=>$formdata['end_date']						
						);
			}
			else{
				$search = array(
						'adv_search' => '',
						'status' => -1,					
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d')								
						);
			}
			$rs_rows= $db->getAllInfo($search);
			$glClass = new Application_Model_GlobalClass();
			$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH_NAME","PHONE","DATE","FROM_PRICE","TO_PRICE","REQUIREDMENT","TYPE","DESCRIPTION","BY_USER","STATUS");
			$link=array(
					'module'=>'group','controller'=>'customer','action'=>'edit',
			);
			
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('name'=>$link,'phone'=>$link));

		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$frm = new Application_Form_FrmAdvanceSearch();
		$frm = $frm->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
		$this->view->result=$search;	
		
		$fm = new Group_Form_FrmClient();
		$frmserch = $fm->FrmLandInfo();
		Application_Model_Decorator::removeAllDecorator($frmserch);
		$this->view->frm_land = $frmserch;
	}

	public function addAction(){
		$db = new Group_Model_DbTable_DbCustomer();
		if($this->getRequest()->isPost()){
				$data = $this->getRequest()->getPost();
				try{
			
					$id= $db->add($data);
					Application_Form_FrmMessage::message("ការ​បញ្ចូល​ជោគ​ជ័យ !");

			}catch (Exception $e){
				Application_Form_FrmMessage::message("Application Error");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		$fm = new Group_Form_FrmCustomer();
		$frm = $fm->FrmAddCustomer();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_customer = $frm;		
		
	}

	public function editAction(){

		$id = $this->getRequest()->getParam("id");
		$db = new Group_Model_DbTable_DbCustomer();
		if($this->getRequest()->isPost()){
			try{
				$data = $this->getRequest()->getPost();
				$data['id']=$id;
				$db->add($data);
				Application_Form_FrmMessage::Sucessfull('EDIT_SUCCESS',"/group/customer");
			}catch (Exception $e){
				Application_Form_FrmMessage::message("EDIT_FAILE");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
	
		$row = $db->getById($id);
	        $this->view->row=$row;
		if(empty($row)){
			$this->_redirect("/group/customer");
		}
		$fm = new Group_Form_FrmCustomer();
		$frm = $fm->FrmAddCustomer($row);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_customer = $frm;
	}

}