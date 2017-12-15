<?php
class Property_blogController extends Zend_Controller_Action {
	private $activelist = array('មិនប្រើ​ប្រាស់', 'ប្រើ​ប្រាស់');
	const REDIRECT_URL = '/property';
    public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
			$db = new Property_Model_DbTable_DbLandBlog();
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'adv_search' => '',
						'show_all'=>'',
						'status_search' => -1,);
			}
			$rs_rows= $db->geteAllLandBlog($search);
			$glClass = new Application_Model_GlobalClass();
			$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
			$list = new Application_Form_Frmtable();
			$collumns = array("TITLE","NOTE","USER_NAME","STATUS");
			$link=array(
					'module'=>'property','controller'=>'blog','action'=>'edit',
			);
 			$this->view->list=$list->getCheckList(3, $collumns,$rs_rows,array('title_kh'=>$link,'note'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			echo $e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		
		$frm = new Property_Form_FrmLandBlog();
   		$frm_pro=$frm->FrmLandBlog();
   		Application_Model_Decorator::removeAllDecorator($frm_pro);
   		$this->view->frm_property_type = $frm_pro;
	
	}
	
   function addAction(){
   	if($this->getRequest()->isPost()){
   		$_data = $this->getRequest()->getPost();
   		$db = new Property_Model_DbTable_DbLandBlog();
   		 
   		try{
   			$db->addLandBlog($_data);
   				if(!empty($_data['save_new'])){
					Application_Form_FrmMessage::message('ការ​បញ្ចូល​​ជោគ​ជ័យ');
				}else{
					Application_Form_FrmMessage::Sucessfull('ការ​បញ្ចូល​​ជោគ​ជ័យ', self::REDIRECT_URL . '/blog/index');
				}
   		}catch(Exception $e){
   			Application_Form_FrmMessage::message("ការ​បញ្ចូល​មិន​ជោគ​ជ័យ");
   			$err =$e->getMessage();
   			Application_Model_DbTable_DbUserLog::writeMessageError($err);
   		}
   	}
   	
   	$frm = new Property_Form_FrmLandBlog();
   	$frm_pro=$frm->FrmLandBlog();
   	Application_Model_Decorator::removeAllDecorator($frm_pro);
   	$this->view->frm_land_blog = $frm_pro;
   }
   function editAction(){
   	$id = $this->getRequest()->getParam("id");
   	$db_co = new Property_Model_DbTable_DbLandBlog();
   	if($this->getRequest()->isPost()){
   		$_data = $this->getRequest()->getPost();
   		try{
   			$_data['id']= $id;
   			$db_co->addLandBlog($_data);
   			Application_Form_FrmMessage::Sucessfull("ការ​កែប្រែជោគ​ជ័យ !",'/property/blog');
   		}catch(Exception $e){
   			Application_Form_FrmMessage::message("ការ​កែប្រែមិន​ជោគ​ជ័យ");
   			$err =$e->getMessage();
   			Application_Model_DbTable_DbUserLog::writeMessageError($err);
   		}
   	}
   	$row = $db_co->getLandBlogById($id);
   	$frm = new Property_Form_FrmLandBlog();
   	$frm_pro=$frm->FrmLandBlog($row);
   	Application_Model_Decorator::removeAllDecorator($frm_pro);
   	$this->view->frm_land_blog = $frm_pro;
   
   }
   function deleteAction(){
   	$id = $this->getRequest()->getParam("id");
   	$db = new Property_Model_DbTable_DbLandBlog();
   	if (!empty($id)){
   		try{
   			$db->deleteLandBlog($id);
   			Application_Form_FrmMessage::Sucessfull('DELETE_SUCCESS',"/property/blog");
   		}catch (Exception $e){
   			Application_Form_FrmMessage::message("DELETE_FAILE");
   			echo $e->getMessage();
   			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
   		}
   	}else{
   		$this->_redirect("/property/blog");
   	}
   }
   public  function addlandblogAction(){ // ajax from land controllert
   	if($this->getRequest()->isPost()){
   		$_data = $this->getRequest()->getPost();
   		$_db = new Property_Model_DbTable_DbLandBlog();
   		$id = $_db->ajaxLandBlog($_data);
   		print_r(Zend_Json::encode($id));
   		exit();
   	}
   }
}

