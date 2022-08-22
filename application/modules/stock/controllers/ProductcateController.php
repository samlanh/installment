<?php
class Stock_ProductcateController extends Zend_Controller_Action {
	const REDIRECT_URL = '/stock/productcate';
    public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
    public function indexAction()
    {
    	$db_dept=new Stock_Model_DbTable_DbProductCate();
    	if($this->getRequest()->isPost()){
    		$search=$this->getRequest()->getPost();
    	}
    	else{
    		$search = array(
    			'advance_search' => "",
    			'schoolOption_search'=>"",
    			'status_search' => -1
    		);
    	}
    	$type =3; //product category
        $rs_rows = $db_dept->getAllItems($search,$type);
      
        
    	$list = new Application_Form_Frmtable();
    	$collumns = array("TITLE","TITLE_EN","SHORTCUT","ORDERING","NOTE","BY_USER","CREATE_DATE","MODIFY_DATE","STATUS");
    	$link=array(
    			'module'=>'stock','controller'=>'productcate','action'=>'edit',
    	);
    	$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('title'=>$link,'title_en'=>$link,'schoolOption'=>$link));
    	
    	$frm = new Stock_Form_FrmItems();
    	$frm->FrmAddDegree(null);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_degree = $frm;
    }
    function addAction(){
    	if($this->getRequest()->isPost()){
    		try{
    			$sms="INSERT_SUCCESS";
    			$_data = $this->getRequest()->getPost();
    			$db = new Stock_Model_DbTable_DbProductCate();
    			$degree_id= $db->AddDegree($_data);
    			if($degree_id==-1){
    				$sms = "RECORD_EXIST";
					Application_Form_FrmMessage::Sucessfull($sms, self::REDIRECT_URL."/index",2);
    			}
    			if(isset($_data['save_close'])){
    				Application_Form_FrmMessage::Sucessfull($sms, self::REDIRECT_URL."/index");
    			}
    			Application_Form_FrmMessage::Sucessfull($sms,  self::REDIRECT_URL."/add");
    		}catch (Exception $e) {
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    			Application_Form_FrmMessage::message("Application Error!");
    		}
    	}
    	$frm = new Stock_Form_FrmItems();
    	$frm->FrmAddDegree(null);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_degree = $frm;
    }
    public function editAction(){
    	$db = new Stock_Model_DbTable_DbProductCate();
    	$id= $this->getRequest()->getParam("id");
    	if($this->getRequest()->isPost()){
    		try{
    			$_data = $this->getRequest()->getPost();
    			$db->UpdateDegree($_data,$id);
    			Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS", self::REDIRECT_URL."/index");
    			exit();
    		}catch (Exception $e) {
    			Application_Form_FrmMessage::message("Application Error!");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
    	}
    	$type =3; //service category
    	$row =$db->getDegreeById($id,$type);
    	if (empty($row)){
    		Application_Form_FrmMessage::Sucessfull("NO_RECORD", self::REDIRECT_URL."/index",2);
    	}
    	$this->view->row = $row;
    	
    	$frm = new Stock_Form_FrmItems();
    	$frm->FrmAddDegree($row);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_degree = $frm;
    }
}