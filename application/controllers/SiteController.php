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
		$this->view->allnews = $db->getAllNews();
		
		
		$db = new Application_Model_DbTable_DbSiteFront();
		$search = array(
			'branch_id'=>1
		);
		$allProperty = $db->getCountPropertyByType($search);
		$this->view->allProperty =$allProperty;
		$this->view->search =$search;
		
		//$search['propertyStatus']=1;
		//$remainProperty = $db->getCountPropertyByType($search);
		//$this->view->remainProperty =$remainProperty;
	}
}





