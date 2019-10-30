<?php
class Stock_PurchaseController extends Zend_Controller_Action {
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
			if($this->getRequest()->isPost()){
    			$search = $this->getRequest()->getPost();
    		}
    		else{
    			$search=array(
    				'title' => '',
    				'product' => '',
    				'branch_id' => '',
    				'supplier_id'=>'',
    				'start_date'=> date('Y-m-d'),
    				'end_date'=>date('Y-m-d'),
    				'status_search'=>-1,
    				);
    		}
			$db =  new Stock_Model_DbTable_DbPurchase();
			$rows = $db->getAllSupPurchase($search);
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH","PURCHASE_NO","INVOICE_NO","SUPPLIER_NAME","SEX","Tel","EMAIL","AMOUNT_DUE","PURCHASE_DATE","BY_USER","STATUS");
			$link=array(
					'module'=>'stock','controller'=>'purchase','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rows,array( 'branch_name'=>$link,'supplier_no'=>$link,
					'invoice_no'=>$link,'sup_name'=>$link,'sex'=>$link,));
			}catch (Exception $e){
				Application_Form_FrmMessage::message("Application Error");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
			$form=new Stock_Form_FrmSearchProduct();
			$form=$form->FrmSearchProduct();
			Application_Model_Decorator::removeAllDecorator($form);
			$this->view->form_search=$form;
	}
	public function addAction(){
		if($this->getRequest()->isPost()){
		$_data = $this->getRequest()->getPost();
			try{
				$db = new Stock_Model_DbTable_DbPurchase();
				$row = $db->addPurchase($_data);
				if(isset($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/stock/purchase");
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/stock/purchase/add");
				}
				Application_Form_FrmMessage::message("INSERT_SUCCESS");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$_pur = new Stock_Model_DbTable_DbPurchase();
		$this->view->pu_code=$_pur->getPurchaseCode();
		$this->view->sup_ids=$_pur->getSuplierName();
		
		$model = new Application_Model_DbTable_DbGlobal();
		$branch = $model->getAllBranchByUser();
		$this->view->branchopt = $branch;
		
		$db = new Stock_Model_DbTable_DbProduct();
		$d_row= $db->getAllProductsNormal();
		array_unshift($d_row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		array_unshift($d_row, array ( 'id' => "",'name' =>$this->tr->translate("SELECT_PRODUCT")));
		$this->view->product= $d_row;
	}
	public function editAction(){
		$id=$this->getRequest()->getParam('id');
		$id = empty($id)?0:$id;
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			$_data['id']=$id;
			try{
				$db = new Stock_Model_DbTable_DbPurchase();
				$row = $db->updatePurchase($_data,$id);
				if(isset($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/stock/purchase");
				}else{
					Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/stock/purchase/add");
				}
				Application_Form_FrmMessage::message("INSERT_SUCCESS");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$_pur = new Stock_Model_DbTable_DbPurchase();
		$row = $_pur->getSupplierById($id);
		//print_r($row);exit();
		if (empty($row)){
			Application_Form_FrmMessage::Sucessfull("No Record","/stock/purchase");
			exit();
		}else if ($row['is_paid']==1){
			Application_Form_FrmMessage::Sucessfull("This Purchase Already Payment","/stock/purchase");
			exit();
		}
		$haspay = $_pur->checkHaspayment($id);
		if (!empty($haspay)){
			Application_Form_FrmMessage::Sucessfull("This Purchase has paid on some payment ready","/stock/purchase");
			exit();
		}
		
		$this->view->pu_code=$_pur->getPurchaseCode();
		$this->view->sup_ids=$_pur->getSuplierName();
		$this->view->row_sup=$row;
		$this->view->row_pur_detai=$_pur->getSupplierProducts($id);		
		//$this->view->bran_name=$_pur->getAllBranch();
		$model = new Application_Model_DbTable_DbGlobal();
		$branch = $model->getAllBranchByUser();
		$this->view->branchopt = $branch;
		
		$db = new Stock_Model_DbTable_DbProduct();
		$d_row= $db->getAllProductsNormal();
		array_unshift($d_row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		array_unshift($d_row, array ( 'id' => "",'name' =>$this->tr->translate("SELECT_PRODUCT")));
		$this->view->products= $d_row;
	}
    function getSupplierInfoAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Stock_Model_DbTable_DbPurchase();
    		$row = $db->getSuplierInfo($data['sup_id']);
    		print_r(Zend_Json::encode($row));
    		exit();
    	}
    }
    
    function addProductAction(){
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		$_dbmodel = new Stock_Model_DbTable_DbPurchase();
    		$id = $_dbmodel->ajaxAddProduct($_data);
    		print_r(Zend_Json::encode($id));
    		exit();
    	}
    }
    function refreshproductAction(){
    	if($this->getRequest()->isPost()){
    		try{
    			$data = $this->getRequest()->getPost();
    			$db = new Stock_Model_DbTable_DbGlobalProduct();
    			$d_row= $db->getAllProductsNormal();
    			array_unshift($d_row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
    			array_unshift($d_row, array ( 'id' => "",'name' =>$this->tr->translate("SELECT_PRODUCT")));
    			print_r(Zend_Json::encode($d_row));
    			exit();
    		}catch(Exception $e){
    			Application_Form_FrmMessage::message("INSERT_FAIL");
    			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		}
    	}
    }
    
    function getpuchasecodeAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Application_Model_DbTable_DbGlobal();
    		$stu_no = $db->getPuchaseNo($data['branch_id']);
    		print_r(Zend_Json::encode($stu_no));
    		exit();
    	}
    }
}