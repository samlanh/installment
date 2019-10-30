<?php
class Stock_BrokenstockController extends Zend_Controller_Action {
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
    				'start_date'=> date('Y-m-d'),
    				'end_date'=>date('Y-m-d'),
    				'status_search'=>-1,
    			);
    		}
			$db =  new Stock_Model_DbTable_DbBrokenStock();
			$rows = $db->getAllBrokenStock($search);
			
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH","BROKEN_NO","TITLE","NOTE","DATE","TOTAL","USER","STATUS");
			$link=array(
					'module'=>'stock','controller'=>'brokenstock','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rows,array('branch_name'=>$link,'broken_no'=>$link,'request_name'=>$link,));
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			Application_Form_FrmMessage::message("Application Error!");
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
				$db = new Stock_Model_DbTable_DbBrokenStock();
				$row = $db->addBrokenStock($_data);
				if(isset($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/stock/brokenstock");
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/stock/brokenstock/add");
				}
				Application_Form_FrmMessage::message("INSERT_SUCCESS");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$_pur = new Stock_Model_DbTable_DbBrokenStock();
		
		$this->view->rq_code=$_pur->getBrokenCode();
		$this->view->bran_name=$_pur->getAllBranch();
		 
		$model = new Application_Model_DbTable_DbGlobal();
		$branch = $model->getAllBranchByUser();
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
				$db = new Stock_Model_DbTable_DbBrokenStock();
				$row = $db->updateAdjustStock($_data);
				if(isset($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/stock/brokenstock");
				}else{
					Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/stock/brokenstock");
				}
				Application_Form_FrmMessage::message("EDIT_SUCCESS");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("EDIT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$_pur = new Stock_Model_DbTable_DbBrokenStock();
		$row =$_pur->getAdjustStockById($id);
		if (empty($row)){
			Application_Form_FrmMessage::Sucessfull("No Record","/stock/adjuststock");
			exit();
		}
		$this->view->row = $row;
		$this->view->row_detail=$_pur->getAdjustStockDetail($id);
		$this->view->bran_name=$_pur->getAllBranch();
		 
		$model = new Application_Model_DbTable_DbGlobal();
		$branch = $model->getAllBranchByUser();
		$this->view->branchopt = $branch;
		
		$db = new Stock_Model_DbTable_DbProduct();
		$d_row= $db->getAllProductsNormal(2);//
		array_unshift($d_row, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		array_unshift($d_row, array ( 'id' => "",'name' =>$this->tr->translate("SELECT_PRODUCT")));
		$this->view->product= $d_row;
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
    
    function getreceiptAction(){
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$branch_id = $data['branch_id'];
    		$_dbcht = new Stock_Model_DbTable_DbBrokenStock();
    		$itemsCode = $_dbcht->getBrokenCode($branch_id);
    		print_r(Zend_Json::encode($itemsCode));
    		exit();
    	}
    }
}