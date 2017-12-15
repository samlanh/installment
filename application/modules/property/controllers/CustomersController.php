<?php
class Property_customersController extends Zend_Controller_Action {
	const REDIRECT_URL = '/property/customers';
	public function init()
	{
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
			$db = new Property_Model_DbTable_DbClient();
			if($this->getRequest()->isPost()){
				$formdata=$this->getRequest()->getPost();
				if (!empty($formdata['show_all'])){ $showall=$formdata['show_all'];}else{$showall='';}
				$search = array(
// 						'branch_id'=>$formdata['branch_id'],
						'adv_search' => $formdata['adv_search'],
						'status'=>$formdata['status'],
						'start_date'=> $formdata['start_date'],
						'end_date'=>$formdata['end_date'],
						'show_all'=>$showall,
						);
			}
			else{
				$search = array(
// 						'branch_id'=>-1,
						'adv_search' => '',
						'status' => -1,
						'show_all'=>'',
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'));
				
			}
			
			$rs_rows= $db->getAllClients($search);
			$glClass = new Application_Model_GlobalClass();
			$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
			$list = new Application_Form_Frmtable();
			$collumns = array("CUSTOMER_CODE","CLIENTNAME_KH","SEX","PHONE","CURRENT_ADDRESS",
					"DATE","BY_USER","STATUS","ប្រវិត្តិរូប","កែប្រែ");
			$link=array(
					'module'=>'property','controller'=>'customers','action'=>'edit',
			);
			$link1=array(
					'module'=>'property','controller'=>'customers','action'=>'view',
			);
			$this->view->list=$list->getCheckList(3, $collumns, $rs_rows,array('client_number'=>$link,'name_kh'=>$link,'ប្រវិត្តិរូប'=>$link1,'កែប្រែ'=>$link1));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	
		$frm = new Application_Form_FrmAdvanceSearch();
		$frm = $frm->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
		
		$fm = new Property_Form_FrmClient();
		$frm = $fm->FrmAddClient();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_client = $frm;
		
		$this->view->result=$search;	
	}
	public function addAction(){
		if($this->getRequest()->isPost()){
				$data = $this->getRequest()->getPost();
				$data['old_photo']=null;
				$db = new Property_Model_DbTable_DbClient();
				try{
				 if(isset($data['save_new'])){
					$id= $db->addClient($data);
					Application_Form_FrmMessage::message("ការ​បញ្ចូល​ជោគ​ជ័យ !");
				}
				else if (isset($data['save_close'])){
					$id= $db->addClient($data);
					Application_Form_FrmMessage::message("ការ​បញ្ចូល​ជោគ​ជ័យ !");
					Application_Form_FrmMessage::redirectUrl("/property/customers");
				}
			}catch (Exception $e){
				Application_Form_FrmMessage::message("Application Error");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$db = new Application_Model_DbTable_DbGlobal();
		
		$client_type = $db->getclientdtype();
		array_unshift($client_type,array(
		'id' => -1,
		'name' => '---Add New ---',
		 ) );
		$this->view->clienttype = $client_type;
		
		$fm = new Property_Form_FrmClient();
		$frm = $fm->FrmAddClient();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_client = $frm;
		
		$dbpop = new Application_Form_FrmPopupGlobal();
		
	}
	public function editAction(){
		$db = new Property_Model_DbTable_DbClient();
		$id = $this->getRequest()->getParam("id");
		if($this->getRequest()->isPost()){
			try{
				$data = $this->getRequest()->getPost();
				$data['id'] = $id;
				$db->addClient($data);
				Application_Form_FrmMessage::Sucessfull('EDIT_SUCCESS',"/property/customers");
			}catch (Exception $e){
				Application_Form_FrmMessage::message("EDIT_FAILE");
				echo $e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$id = $this->getRequest()->getParam("id");
		$row = $db->getClientById($id);
	        $this->view->row=$row;
		$this->view->photo = $row['photo_name'];
		if(empty($row)){
			$this->_redirect("/group/client");
		}
		$fm = new Property_Form_FrmClient();
		$frm = $fm->FrmAddClient($row);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_client = $frm;
		
		
		$db = new Application_Model_DbTable_DbGlobal();
		$client_type = $db->getclientdtype();
		array_unshift($client_type,array(
				'id' => -1,
				'name' => '---Add New ---',
		) );
		$this->view->clienttype = $client_type;
	}
	function viewAction(){
		$id = $this->getRequest()->getParam("id");
		$db = new Property_Model_DbTable_DbClient();
		$this->view->client_list = $db->getClientDetailInfo($id);
	}
	function deleteAction(){
		$id = $this->getRequest()->getParam("id");
		$db = new Property_Model_DbTable_DbClient();
		if (!empty($id)){
			try{
				$db->deleteClient($id);
				Application_Form_FrmMessage::Sucessfull('DELETE_SUCCESS',"/property/customers");
			}catch (Exception $e){
				Application_Form_FrmMessage::message("DELETE_FAILE");
				echo $e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}else{
			$this->_redirect("/group/index");
		}
	}
	public function addNewclientAction(){//ajax
		if($this->getRequest()->isPost()){
			$db = new Property_Model_DbTable_DbClient();
			$data = $this->getRequest()->getPost();
			$_data['status']=1;
			$id = $db->addClientByAjax($data);
			print_r(Zend_Json::encode($id));
			exit();
		}
	}
	
}

