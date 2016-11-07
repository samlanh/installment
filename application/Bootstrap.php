<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	function _initViewHelpers(){
		date_default_timezone_set('Asia/Phnom_Penh');
		// Config view Zend_Dojo
		$view = new Zend_View();
	    $view->addHelperPath('Zend/Dojo/View/Helper/', 'Zend_Dojo_View_Helper');	
	    $viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer();	
	    $viewRenderer->setView($view);	
	    Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);    	      	
		// End Config view Zend_Dojo
	}	
	protected function _initAuthPlugin()
	{		
		//Zend_Controller_Front::getInstance()->registerPlugin(
 		//new Application_Model_CustomAuth(Zend_Auth::getInstance()));	

		date_default_timezone_set('Asia/Phnom_Penh');
		$view = new Zend_View();
		$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
		$viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer();
		$viewRenderer->setView($view);
		Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);
		$this ->bootstrap('layout');
		$layout = $this->getResource('layout');
		$view = $layout->getView();
		Zend_Layout::StartMvc();
	}	
	protected function _initNavigationXml()
	{
// 		$this->bootstrap('layout');
// 		$layout = $this->getResource('layout');
// 		$view = $layout->getView();
// 		$config = new Zend_Config_Xml(APPLICATION_PATH.'/configs/navigation.xml');
// 		$navigation = new Zend_Navigation($config);
// 		$view->navigation($navigation);
	}
}