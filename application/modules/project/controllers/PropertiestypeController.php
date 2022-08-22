<?php
class Project_propertiestypeController extends Zend_Controller_Action {
	const REDIRECT_URL = '/project';
    public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
			$db = new Project_Model_DbTable_DbProperyType();
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
					'adv_search' => '',
					'status_search' => -1,);
			}
			$rs_rows= $db->geteAllPropertyType($search);
			$list = new Application_Form_Frmtable();
			$collumns = array("PROPERTY_TYPE","NOTE","USER_NAME","STATUS");
			$link=array(
					'module'=>'project','controller'=>'propertiestype','action'=>'edit',
			);
 			$this->view->list=$list->getCheckList(10, $collumns,$rs_rows,array('type_nameen'=>$link,'note'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$frm = new Project_Form_FrmPropertiestype();
   		$frm_pro=$frm->FrmPropertiesType();
   		Application_Model_Decorator::removeAllDecorator($frm_pro);
   		$this->view->frm_property_type = $frm_pro;
   }
   function addAction(){
	   	if($this->getRequest()->isPost()){
	   		$_data = $this->getRequest()->getPost();
	   		$db = new Project_Model_DbTable_DbProperyType();
	   		try{
	   			// Check Session Expire
	   			$dbgb = new Application_Model_DbTable_DbGlobal();
	   			$checkses = $dbgb->checkSessionExpire();
	   			if (empty($checkses)){
	   				$dbgb->reloadPageExpireSession();
	   				exit();
	   			}
	   			if (empty($_data)){
					Application_Form_FrmMessage::Sucessfull("File Attachment to large can't upload and Save data !","/project/propertiestype",2);
					exit();
				}
				
	   			$db->addPropery($_data);
	   				if(!empty($_data['save_new'])){
						Application_Form_FrmMessage::message('INSERT_SUCCESS');
					}else{
						Application_Form_FrmMessage::Sucessfull('INSERT_SUCCESS', self::REDIRECT_URL . '/propertiestype');
					}
	   		}catch(Exception $e){
	   			Application_Form_FrmMessage::message("INSERT_FAIL");
	   			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	   		}
	   	} 	
	   	$frm = new Project_Form_FrmPropertiestype();
	   	$frm_pro=$frm->FrmPropertiesType();
	   	Application_Model_Decorator::removeAllDecorator($frm_pro);
	   	$this->view->frm_property_type = $frm_pro;
   }
   function editAction(){
	   	$id = $this->getRequest()->getParam("id");
	   	$db_co = new Project_Model_DbTable_DbProperyType();
	   	if($this->getRequest()->isPost()){
	   		$_data = $this->getRequest()->getPost();
	   		try{
	   			// Check Session Expire
	   			$dbgb = new Application_Model_DbTable_DbGlobal();
	   			$checkses = $dbgb->checkSessionExpire();
	   			if (empty($checkses)){
	   				$dbgb->reloadPageExpireSession();
	   				exit();
	   			}
				if (empty($_data)){
					Application_Form_FrmMessage::Sucessfull("File Attachment to large can't upload and Save data !","/project/propertiestype",2);
					exit();
				}
	   			
	   			$_data['id']= $id;
	   			$db_co->addPropery($_data);
	   			Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS",'/project/propertiestype');
	   		}catch(Exception $e){
	   			Application_Form_FrmMessage::message("EDIT_FAIL");
	   			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	   		}
	   	}
	   	$row = $db_co->getPropertyTypeById($id);
	   	$this->view->row = $row;
	   	$frm = new Project_Form_FrmPropertiestype();
	   	$frm_pro=$frm->FrmPropertiesType($row);
   		Application_Model_Decorator::removeAllDecorator($frm_pro);
   		$this->view->frm_property_type = $frm_pro;
   }
   function addPropertytypeAction(){ // ajax from land controllert
	   	if($this->getRequest()->isPost()){
	   		$_data = $this->getRequest()->getPost();
	   		$_db = new Project_Model_DbTable_DbProperyType();
	   		$id = $_db->ajaxPropertytype($_data);
	   		print_r(Zend_Json::encode($id));
	   		exit();
	   	}
   }
}