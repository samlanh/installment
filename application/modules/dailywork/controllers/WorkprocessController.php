<?php
class Dailywork_WorkprocessController extends Zend_Controller_Action
{
public function init()
    {
        /* Initialize action controller here */
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }
    
    public function indexAction()
    {
    	$db =new Dailywork_Model_DbTable_DbDailywork();
    	if($this->getRequest()->isPost()){
    		$search = $this->getRequest()->getPost();	
    		$search['start_date']=date("Y-m-d",strtotime($search['start_date']));
    		$search['end_date']=date("Y-m-d",strtotime($search['end_date']));
    	}else{
    		$search =array(
    				'adv_search'	=> '',
					'work' 			=>'',
    				'user' 			=>'',
    				'projectname' 	=>'',
    				'start_date'	=> date('Y-m-d'),
					'end_date'		=>date('Y-m-d'),
					'status'		=>	2
    				);
    	}
    	$this->view->row=$search;
    	$rows = $db->getDailyworkProcess($search);
    	$this->view->row_rs = $rows;
    	$list = new Application_Form_Frmtable();
    	$columns=array("WORK_TITLE" , "CUSTOMER_NAME","TASK","DATE" , "BY_USER","DESCRIPTION","STATUS");
    	$link=array(
    			'module'=>'dailywork','controller'=>'workprocess','action'=>'edit',
    	);
    	$this->view->list=$list->getCheckList(0, $columns, $rows, array('work_name'=>$link,'projectname'=>$link,'customer_name'=>$link));
 		$this->view->rst=$rows;
 		
 		$db = new Dailywork_Form_FrmDailywork();
 		$this->view->frm_search = $db->add();
	}
	public function addAction()
	{
		if ($this->getRequest()->isPost()){
			try {
				$data = $this->getRequest()->getPost();
				$dailywork = new Dailywork_Model_DbTable_DbDailywork();
				$dailywork->addprocesswork($data);
				if(isset($data['save_close'])){
					Application_Form_FrmMessage::Sucessfull("Complete", "/dailywork/workprocess");
				}else{
					Application_Form_FrmMessage::Sucessfull("Complete", "/workprocess/index/add");
				}
			}
			catch (Exception $e){
			Application_Form_FrmMessage::messageError("INSERT_ERROR",$err = $e->getMessage());
			}
		}
		$fm=new Dailywork_Form_Frmworkprocess();
		$frm_dailywork=$fm->add();
		Application_Model_Decorator::removeAllDecorator($frm_dailywork);
		$this->view->frm_dailywork= $frm_dailywork;
		
		$this->view->form_customer = $frm_dailywork;
		$this->view->frm_project= $frm_dailywork;
	}// Add Product 
	public function editAction(){
		$id=$this->getRequest()->getParam('id');
    	$db = new Dailywork_Model_DbTable_DbDailywork();
    	$row= $db->getDailyworkProcessById($id);
    	$this->view->row_rs=$row;
    	if ($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$data['id']=$id;
    		$product = new Dailywork_Model_DbTable_DbDailywork();
    	 	$product->edittprocesswork($data);
    		Application_Form_FrmMessage::message("EDIT_SUCESS");
    		Application_Form_FrmMessage::redirectUrl("/dailywork/workprocess");
    	}
    	if(empty($id)){
    		Application_Form_FrmMessage::Sucessfull("NO_RECORD_EDIT", "/dailywork/workprocess");
    	}
    	$fm=new Dailywork_Form_Frmworkprocess();
    	$frm_dailywork=$fm->add($row);
    	Application_Model_Decorator::removeAllDecorator($frm_dailywork);
    	$this->view->frm_dailywork= $frm_dailywork;
    	
    	$formpopup = new Sales_Form_FrmCustomer(null);
    	$formpopup = $formpopup->Formcustomer(null);
    	Application_Model_Decorator::removeAllDecorator($formpopup);
    	$this->view->form_customer = $formpopup;
   }
  function getworkdetailAction(){
  	if($this->getRequest()->isPost()){
  		$post=$this->getRequest()->getPost();
  		$db = new Dailywork_Model_DbTable_DbDailywork();
  		$rs = $db ->getworkbyId($post['work_id']);
  		echo Zend_Json::encode($rs);
  		exit();
  	}
  }
  
  public function addTaskAction(){
		if($this->getRequest()->isPost()){
			try {
				$post=$this->getRequest()->getPost();
				$add_customer = new Dailywork_Model_DbTable_DbDailywork();
				$customer_id = $add_customer->addTask($post);
				$result = array('task_id'=>$customer_id);
				echo Zend_Json::encode($result);
				exit();
			}catch (Exception $e){
				$result = array('err'=>$e->getMessage());
				echo Zend_Json::encode($result);
				exit();
			}
		}
	}
}

