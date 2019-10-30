<?php
class Stock_RequestproductController extends Zend_Controller_Action {
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
    				'branch_id'=>'',
    				'request_for' => -1,
    				'for_section' => -1,
    				'start_date'  => date('Y-m-d'),
    				'end_date'    => date('Y-m-d'),
    				'status_search'=>-1,
    			);
    		}
			$db =  new Stock_Model_DbTable_DbRequestProduct();
			$rs_rows = $db->getAllRequest($search);
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH","REQUEST_NO","REQUEST_FOR","FOR_SECTION","PURPOSE","REQUEST_DATE","TOTAL","USER","STATUS");
			$link=array(
					'module'=>'stock','controller'=>'requestproduct','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('request_no'=>$link,'request_for'=>$link,'purpose'=>$link,));
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("Application Error");
		}
		$_pur =  new Stock_Model_DbTable_DbRequestProduct();
		$req_for = $_pur->getAllRequestFor();
		$this->view->rq_for = $req_for;
		
		$for_section = $_pur->getAllForSection();
		$this->view->for_section = $for_section;
		
		$this->view->search = $search;
		$form=new Stock_Form_FrmSearchProduct();
		$form=$form->FrmSearchProduct();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
	}
	public function addAction(){
		if($this->getRequest()->isPost()){
		$_data = $this->getRequest()->getPost();
		try{
				$db = new Stock_Model_DbTable_DbRequestProduct();
				$row = $db->addRequest($_data);
				if(isset($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/stock/requestproduct");
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/stock/requestproduct/add");
				}
				Application_Form_FrmMessage::message("INSERT_SUCCESS");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		
		$_pur = new Stock_Model_DbTable_DbRequestProduct();
		$this->view->rq_code=$_pur->getRequestCode();
		
		$req_for = $_pur->getAllRequestFor();
		array_unshift($req_for, array ( 'id' => -1,'name' => $this->tr->translate("ADD_NEW")));
		$this->view->rq_for = $req_for;
		
		$for_section = $_pur->getAllForSection();
		array_unshift($for_section, array ( 'id' => -1,'name' => $this->tr->translate("ADD_NEW")));
		$this->view->for_section = $for_section;
		
		$model = new Application_Model_DbTable_DbGlobal();
		$branch = $model->getAllBranchName();
		$this->view->branchopt = $branch;
		
		$db = new Stock_Model_DbTable_DbProduct();
		$d_row= $db->getAllProductsNormal(2);//
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
				$db = new Stock_Model_DbTable_DbRequestProduct();
				 $db->updateRequest($_data);
				if(isset($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/stock/requestproduct");
				}else{
					Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/stock/requestproduct");
				}
				Application_Form_FrmMessage::message("INSERT_SUCCESS");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$_pur = new Stock_Model_DbTable_DbRequestProduct();
		$row = $_pur->getRequestById($id);
		if (empty($row)){
			Application_Form_FrmMessage::Sucessfull("No Record","/stock/requestproduct");
			exit();
		}
		$this->view->row= $row;
		$this->view->row_detail=$_pur->getRequestDetail($id);
		$this->view->rq_code=$_pur->getRequestCode();
		$req_for = $_pur->getAllRequestFor();
		array_unshift($req_for, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		$this->view->rq_for = $req_for;
		
		$for_section = $_pur->getAllForSection();
		array_unshift($for_section, array ( 'id' => -1,'name' => $this->tr->translate("ADD_NEW")));
		$this->view->for_section = $for_section;
		
		$model = new Application_Model_DbTable_DbGlobal();
		$branch = $model->getAllBranchName();
		$this->view->branchopt = $branch;
		
		$db = new Stock_Model_DbTable_DbProduct();
		$d_row= $db->getAllProductsNormal(2);//
		array_unshift($d_row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		array_unshift($d_row, array ( 'id' => "",'name' =>$this->tr->translate("SELECT_PRODUCT")));
		$this->view->product= $d_row;
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
    function getProductqtyAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Stock_Model_DbTable_DbRequestProduct();
    		$gty= $db->getProductQty($data['branch_id'],$data['pro_id']);
    		print_r(Zend_Json::encode($gty));
    		exit();
    	}
    }
    function getProBylocationAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Stock_Model_DbTable_DbRequestProduct();
    		$gty= $db->getAllProductBybranch($data['branch_id']);
    		//array_unshift($gty, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
    		array_unshift($gty, array ( 'id' => "",'name' =>$this->tr->translate("SELECT_PRODUCT")));
    		print_r(Zend_Json::encode($gty));
    		exit();
    	}
    }
    function addRequestForAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Stock_Model_DbTable_DbRequestProduct();
    		$request_for = $db->addNewRequestFor($data);
    		print_r(Zend_Json::encode($request_for));
    		exit();
    	}
    }
    function addForSectionAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Stock_Model_DbTable_DbRequestProduct();
    		$for_section = $db->addNewForSection($data);
    		print_r(Zend_Json::encode($for_section));
    		exit();
    	}
    }
    function getreceiptAction(){
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$branch_id = $data['branch_id'];
    		$_pur = new Stock_Model_DbTable_DbRequestProduct();
    		$itemsCode=$_pur->getRequestCode($branch_id);
    		print_r(Zend_Json::encode($itemsCode));
    		exit();
    	}
    }
}