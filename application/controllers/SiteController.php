<?php

class SiteController extends Zend_Controller_Action
{
    public function indexAction()
    {
       $this->_helper->layout()->disableLayout();
        
    }
	public  function errorAction(){
		$this->_helper->layout()->disableLayout();
	}
	public  function homeAction(){
		$this->_helper->layout()->disableLayout();
		
		$db = new Home_Model_DbTable_DbDashboard();
		$lastest = $db->getAllNews(9);
		$this->view->lastestnews = $lastest;
		
		
		
		$db = new Application_Model_DbTable_DbSiteFront();
		
		$allProject = $db->getAllBranchName();
		$this->view->allProject =$allProject;
		
		$search = array(
			'branch_id'=>1
		);
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
		}
			
		$allProperty = $db->getCountPropertyByType($search);
		$this->view->allProperty =$allProperty;
		$this->view->search =$search;
		
		//$search['propertyStatus']=1;
		//$remainProperty = $db->getCountPropertyByType($search);
		//$this->view->remainProperty =$remainProperty;
	}
	
	function propertyAction(){
		$this->_helper->layout()->disableLayout();
		
		$db = new Application_Model_DbTable_DbSiteFront();
		
		$allProject = $db->getAllBranchName();
		$this->view->allProject =$allProject;
		$this->view->allPropertyType = $db->getAllPropertyType();
		
		$search = array(
			'branch_id'=>1,
			'propertyStatus'=>'',
			'property_type'=>'',
			'adv_search'=>'',
		);
		//if($this->getRequest()->isPost()){
			//$search=$this->getRequest()->getPost();
		//}
		
		$param = $this->getRequest()->getParams();
		if(isset($param['search'])){
			$search=$param;
		}
			
		$allProperty = $db->getAllProperty($search);
		//$this->view->allProperty =$allProperty;
		
		$paginator = Zend_Paginator::factory($allProperty);
		$paginator->setDefaultItemCountPerPage(30);
		$allItems = $paginator->getTotalItemCount();
		$countPages= $paginator->count();
		$p = Zend_Controller_Front::getInstance()->getRequest()->getParam('pages');
		
		
		if(isset($p))
		{
			$paginator->setCurrentPageNumber($p);
		} else $paginator->setCurrentPageNumber(1);
		
		$currentPage = $paginator->getCurrentPageNumber();
		 
		$this->view->allProperty  = $paginator;
		$this->view->countItems = $allItems;
		$this->view->countPages = $countPages;
		$this->view->currentPage = $currentPage;
		
		if($currentPage == $countPages)
		{
			$this->view->nextPage = $countPages;
			$this->view->previousPage = $currentPage-1;
		}
		else if($currentPage == 1)
		{
			$this->view->nextPage = $currentPage+1;
			$this->view->previousPage = 1;
		}
		else {
			$this->view->nextPage = $currentPage+1;
			$this->view->previousPage = $currentPage-1;
		}
			
			
		
		
		$this->view->search =$search;
	}
	function newsAction(){
		$this->_helper->layout()->disableLayout();
		
		$param = $this->getRequest()->getParams();
		if(isset($param['search'])){
			$search=$param;
		}
		
		$db = new Home_Model_DbTable_DbDashboard();
		$allnews = $db->getAllNews();
		
		$paginator = Zend_Paginator::factory($allnews);
		$paginator->setDefaultItemCountPerPage(15);
		$allItems = $paginator->getTotalItemCount();
		$countPages= $paginator->count();
		$p = Zend_Controller_Front::getInstance()->getRequest()->getParam('pages');
		
		
		if(isset($p))
		{
			$paginator->setCurrentPageNumber($p);
		} else $paginator->setCurrentPageNumber(1);
		
		$currentPage = $paginator->getCurrentPageNumber();
		 
		$this->view->allnews  = $paginator;
		$this->view->countItems = $allItems;
		$this->view->countPages = $countPages;
		$this->view->currentPage = $currentPage;
		
		if($currentPage == $countPages)
		{
			$this->view->nextPage = $countPages;
			$this->view->previousPage = $currentPage-1;
		}
		else if($currentPage == 1)
		{
			$this->view->nextPage = $currentPage+1;
			$this->view->previousPage = 1;
		}
		else {
			$this->view->nextPage = $currentPage+1;
			$this->view->previousPage = $currentPage-1;
		}
		
	}
	function articleAction(){
		$this->_helper->layout()->disableLayout();
		
		$id = $this->getRequest()->getParam("detail");
		$db = new Home_Model_DbTable_DbDashboard();
		if (!empty($id)) {
			$detail =	$db->getNewsDetail($id);
			$this->view->detail = $detail;
		}
	}
	function myreportAction(){
		$this->_helper->layout()->disableLayout();
		
		$session_user=new Zend_Session_Namespace(FRONT_SES);
		$username = $session_user->first_name;
		$user_id = $session_user->user_id;
		if(empty($user_id)){
			$this->_redirect("/site/home");
		}
		
		$db = new Application_Model_DbTable_DbSiteFront();
		
		$allProject = $db->getAllBranchName();
		$this->view->allProject =$allProject;
		
		$search = array(
			'branch_id'=>1,
			'adv_search'=>'',
			'start_date'=> date('Y-m-d'),
			'end_date'=>date('Y-m-d'),
		);
		if($this->getRequest()->isPost()){
			$search=$this->getRequest()->getPost();
		}
		$this->view->search =$search;
		$this->view->saleRow = $db->getAllSaleByAgent($search);
		
		
		
		$this->view->totalFullCommission = $db->getTotalFullCommission();
		$this->view->commissionpaid = $db->getCommissionPiadByAgent();
		$this->view->commissionPayment = $db->getCommissionPaymentPaidByAgent();
		$this->view->totalSale = $db->getTotalSaleByAgent();
	}
	function changepasswordAction(){
		$this->_helper->layout()->disableLayout();
		
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$message = $this->getRequest()->getParam("message");
		if(!empty($message)){
			if($message==1){
				$this->view->message = $tr->translate('OLD_PASSWORD_NOT_MATCH');
			}else if($message==2){
				$this->view->message = $tr->translate('PASSWORD_AND_COMFIRM_PASSWORD_NOT_MATCH');
			}
		}
		
		$session_user=new Zend_Session_Namespace(FRONT_SES);
		$username = $session_user->first_name;
		$user_id = $session_user->user_id;
		$password = $session_user->pwd;
		if(empty($user_id)){
			$this->_redirect("/site/home");
		}
		
		if($this->getRequest()->isPost())		
		{
			$formdata=$this->getRequest()->getPost();
			$db_user=new Application_Model_DbTable_DbUsers();
			$old_password=$formdata['old_password'];
			$new_password =$formdata['new_password'];
			$comfirm_password =$formdata['comfirm_password'];
			
			if($old_password!=$password){
				Application_Form_FrmMessage::redirectUrl("/site/changepassword?message=1");	
				exit();
			}
			
			if($new_password!=$comfirm_password){	
				Application_Form_FrmMessage::redirectUrl("/site/changepassword?message=2");	
				exit();
			}
			try {
				$db_user->changePassword($new_password, $user_id);
				$session_user->unlock();	
				$session_user->pwd=$new_password;
				$session_user->lock();
				
			} catch (Exception $e) {
				Application_Form_FrmMessage::message('ការផ្លាស់ប្តូរត្រូវបរាជ័យ');
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}		
				
			Application_Form_FrmMessage::redirectUrl("/site/home?message=1");	
			exit();
		}
	}
	
	public function logoutAction()
    {
        if($this->getRequest()->getParam('value')==1){        	
        	$aut=Zend_Auth::getInstance();
        	$aut->clearIdentity();  
        	
        	$session_user=new Zend_Session_Namespace(FRONT_SES);
			
        	$session_user->unsetAll();       	
        	Application_Form_FrmMessage::redirectUrl("/site/home");
        	exit();
        } 
    }
	public function signInAction()
    {
		$this->_helper->layout()->disableLayout();
        $session_user=new Zend_Session_Namespace(FRONT_SES);
		$username = $session_user->first_name;
		$user_id = $session_user->user_id;
		if(!empty($user_id)){
			$this->_redirect("/site/home");
		}
		
		if($this->getRequest()->isPost())		
		{
			$formdata=$this->getRequest()->getPost();
			$db_user=new Application_Model_DbTable_DbUsers();
			$user_name=$formdata['user_name'];
			$password =$formdata['password'];
			if($db_user->userAuthenticate($user_name,$password)){
				
				$session_user=new Zend_Session_Namespace(FRONT_SES);
				$user_id=$db_user->getUserID($user_name);
				$user_info = $db_user->getUserInfo($user_id);
				
				$session_user->user_id=$user_id;
				$session_user->user_name=$user_name;
				$session_user->pwd=$password;		
				$session_user->level= $user_info['user_type'];
				$session_user->last_name= $user_info['last_name'];
				$session_user->first_name= $user_info['first_name'];
				$session_user->branch_list= $user_info['branch_list'];
				$session_user->staff_id= $user_info['staff_id'];
				$session_user->lock();
				
				Application_Form_FrmMessage::redirectUrl("/site/home");	
				exit();
					
			}
		}
    }
}





