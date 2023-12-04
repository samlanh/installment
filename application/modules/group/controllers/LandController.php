<?php
class Group_LandController extends Zend_Controller_Action {
	const REDIRECT_URL = '/group/index';
	public function init()
	{
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	
	function getlandinfoAction(){
		if($this->getRequest()->isPost()){
			$db = new Group_Model_DbTable_DbClient();
			$data = $this->getRequest()->getPost();
			$code = $db->getGroupCodeBYId($data);
			print_r(Zend_Json::encode($code));
			exit();
		}
	}
	
	function getPropertyNoAction(){// by vandy get property code
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobal();
			$dataclient=$db->getNewLandByBranch($data['branch_id']);
			print_r(Zend_Json::encode($dataclient));
			exit();
		}
	}
	function checkTitleAction(){// by vandy check tilte property 
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Group_Model_DbTable_DbLand();
			$dataclient=$db->CheckTitle($data);
			print_r(Zend_Json::encode($dataclient));
			exit();
		}
	}
}