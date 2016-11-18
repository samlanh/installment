<?php
class Group_propertiestypeController extends Zend_Controller_Action {
	private $activelist = array('មិនប្រើ​ប្រាស់', 'ប្រើ​ប្រាស់');
	const REDIRECT_URL = '/group';
    public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
			$db = new Group_Model_DbTable_DbProperyType();
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'adv_search' => '',
						'status_search' => -1,);
			}
			$rs_rows= $db->geteAllPropertyType($search);
			$glClass = new Application_Model_GlobalClass();
			$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
			$list = new Application_Form_Frmtable();
			$collumns = array("TITLE","NOTE","USER_NAME","STATUS");
			$link=array(
					'module'=>'group','controller'=>'propertiestype','action'=>'edit',
			);
 			$this->view->list=$list->getCheckList(0, $collumns,$rs_rows,array('type_nameen'=>$link,'note'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			echo $e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		
		$frm = new Group_Form_FrmPropertiestype();
   		$frm_pro=$frm->FrmPropertiesType();
   		Application_Model_Decorator::removeAllDecorator($frm_pro);
   		$this->view->frm_property_type = $frm_pro;
	
	}
	
   function addAction(){
   	if($this->getRequest()->isPost()){
   		$_data = $this->getRequest()->getPost();
   		$db = new Group_Model_DbTable_DbProperyType();
   		 
   		try{
   			$db->addPropery($_data);
   				if(!empty($_data['save_new'])){
					Application_Form_FrmMessage::message('ការ​បញ្ចូល​​ជោគ​ជ័យ');
				}else{
					Application_Form_FrmMessage::Sucessfull('ការ​បញ្ចូល​​ជោគ​ជ័យ', self::REDIRECT_URL . '/propertiestype/index');
				}
   		}catch(Exception $e){
   			Application_Form_FrmMessage::message("ការ​បញ្ចូល​មិន​ជោគ​ជ័យ");
   			$err =$e->getMessage();
   			Application_Model_DbTable_DbUserLog::writeMessageError($err);
   		}
   	}
   	
   	$frm = new Group_Form_FrmPropertiestype();
   	$frm_pro=$frm->FrmPropertiesType();
   	Application_Model_Decorator::removeAllDecorator($frm_pro);
   	$this->view->frm_property_type = $frm_pro;
   }
   function editAction(){
   	$id = $this->getRequest()->getParam("id");
   	$db_co = new Group_Model_DbTable_DbProperyType();
   	if($this->getRequest()->isPost()){
   		$_data = $this->getRequest()->getPost();
   		try{
   			$_data['id']= $id;
   			$db_co->addPropery($_data);
   			Application_Form_FrmMessage::Sucessfull("ការ​បញ្ចូល​ជោគ​ជ័យ !",'/group/propertiestype');
   		}catch(Exception $e){
   			Application_Form_FrmMessage::message("ការ​បញ្ចូល​មិន​ជោគ​ជ័យ");
   			$err =$e->getMessage();
   			Application_Model_DbTable_DbUserLog::writeMessageError($err);
   		}
   	}
   	$row = $db_co->getPropertyTypeById($id);
   	$frm = new Group_Form_FrmPropertiestype();
   	$frm_pro=$frm->FrmPropertiesType($row);
   	Application_Model_Decorator::removeAllDecorator($frm_pro);
   		$this->view->frm_property_type = $frm_pro;
   
   }
   public  function addPropertytypeAction(){ // ajax from land controllert
   	if($this->getRequest()->isPost()){
   		$_data = $this->getRequest()->getPost();
   		$_db = new Group_Model_DbTable_DbProperyType();
   		$id = $_db->ajaxPropertytype($_data);
   		print_r(Zend_Json::encode($id));
   		exit();
   	}
   }
}

