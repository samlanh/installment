<?php
class Product_CategoryController extends Zend_Controller_Action {
	public function init()
	{
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		$db = new Product_Model_DbTable_DbCategory();
		$rs_rows= array();
		try{
			if(!empty($this->getRequest()->isPost())){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
					'adv_search'=>'',
					'status'	=>-1,
					'start_date'=> date('Y-m-d'),
					'end_date'=>date('Y-m-d'),
				);
				}
				$rs_rows = $db->getAllCategory($search);//
			}catch (Exception $e){
				Application_Form_FrmMessage::message("Application Error");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
			
			$list = new Application_Form_Frmtable();
			$collumns = array("PARENT_CATEGORY","CATEOGRY_TITLE","IS_MATERIAL","CREATE_DATE","BY_USER","STATUS");
			$link=array('module'=>'product','controller'=>'category','action'=>'edit');
			$this->view->list=$list->getCheckList(10, $collumns,$rs_rows,array('parentTitle'=>$link,'categoryName'=>$link));
			
			$frm = new Application_Form_FrmAdvanceSearchStock();
			$frm = $frm->AdvanceSearch();
			Application_Model_Decorator::removeAllDecorator($frm);
			$this->view->frm_search = $frm;
		
	}
	function addAction(){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$isaddcat = $this->getRequest()->getParam('isAddCategory');
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {		
				$db = new Product_Model_DbTable_DbCategory();
				$db->addCategory($_data);
				if(!empty($isaddcat)){
					
					$alert = $tr->translate("INSERT_SUCCESS");
					echo "<script> alert('".$alert."');</script>";
		    		echo "<script>window.close();</script>";

				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/product/category/add");
				}
				
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$fm = new Product_Form_FrmCategory();
		$frm = $fm->FrmAddCategory();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frmCategory = $frm;
	}
	function editAction(){
		$db = new Product_Model_DbTable_DbCategory();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$db->updateCategory($_data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/product/category/index");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("UPDATE_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		$id = $this->getRequest()->getParam('id');
		$id = empty($id)?0:$id;
		if(empty($id)){
			Application_Form_FrmMessage::Sucessfull("NO_DATA","/product/category",2);
		}
		
		$result = $db->getDataRow($id);
		
		$fm = new Product_Form_FrmCategory();
		$frm = $fm->FrmAddCategory($result);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frmCategory = $frm;
	}
	function getAllcategoryAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobalStock();
			$results=$db->getAllCategoryProduct();
			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
			
			array_unshift($results, array ('id' => 0,'name' =>$tr->translate("SELECT_CATEGORY")));
			array_unshift($results, array ('id' => -1,'name' =>$tr->translate("ADD_NEW")));
			
			print_r(Zend_Json::encode($results));
			exit();
		}
	}
}

