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
}





