<?php
class Dailywork_IndexController extends Zend_Controller_Action
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
    				'adv_search' => '',
					'work' =>'',
    				'user' =>'',
    				'projectname' =>'',
    				'start_date'=> date('Y-m-d'),
					'end_date'=>date('Y-m-d')
    				);
    	}
    	$this->view->row=$search;
    	$rows = $db->getDailywork($search);
    	$this->view->row_rs = $rows;
    	$list = new Application_Form_Frmtable();
    	$columns=array("TASK_TITLE","WORK_TYPE","DATE" , "BY_USER","DESCRIPTION","STATUS");
    	$link=array(
    			'module'=>'dailywork','controller'=>'index','action'=>'edit',
    	);
    	$this->view->list=$list->getCheckList(0, $columns, $rows, array('projectname'=>$link,'work'=>$link,'user'=>$link));
 		$this->view->rst=$rows;
 		
 		$db = new Dailywork_Form_FrmDailywork();
 		$frm_dailywork = $db->add();
 		Application_Model_Decorator::removeAllDecorator($frm_dailywork);
 		$this->view->frm_search = $frm_dailywork;
	}
	public function addAction()
	{
		if ($this->getRequest()->isPost()){
			try {
				$data = $this->getRequest()->getPost();
				$dailywork = new Dailywork_Model_DbTable_DbDailywork();
				$dailywork->add($data);
				if(isset($data['save_close'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", "/dailywork/index");
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", "/dailywork/index/add");
				}
			}
			catch (Exception $e){
			Application_Form_FrmMessage::messageError("INSERT_ERROR",$err = $e->getMessage());
			}
		}
		$fm=new Dailywork_Form_FrmDailywork();
		$frm_dailywork=$fm->add();
		Application_Model_Decorator::removeAllDecorator($frm_dailywork);
		$this->view->frm_dailywork= $frm_dailywork;
		$this->view->form_customer = $frm_dailywork;
	}// Add Product 
	public function editAction(){
		$id=$this->getRequest()->getParam('id');
    	$db = new Dailywork_Model_DbTable_DbDailywork();
    	$row= $db->getdailyById($id);
    	$this->view->row_rs=$row;
    	if ($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$data['id']=$id;
    		$product = new Dailywork_Model_DbTable_DbDailywork();
    	 	$product->updatedailyworkById($data);
    		Application_Form_FrmMessage::message("EDIT_SUCESS");
    		Application_Form_FrmMessage::redirectUrl("/dailywork/index");
    	}
    	if(empty($id)){
    		Application_Form_FrmMessage::Sucessfull("NO_RECORD_EDIT", "/dailywork/index");
    	}
    	
    	$fm=new Dailywork_Form_FrmDailywork();
    	$frm_dailywork=$fm->add($row);
    	Application_Model_Decorator::removeAllDecorator($frm_dailywork);
    	$this->view->frm_dailywork= $frm_dailywork;
    	$this->view->form_customer = $frm_dailywork;
   }
}

