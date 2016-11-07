<?php
class Setting_SettingController extends Zend_Controller_Action {
	
	
public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
	}
	public function indexAction()
	{
		$db = new Setting_Model_DbTable_DbLabel();
		$data = $db->getAllSystemSetting();
		$this->view->data = $data;	
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			try {
				$db = $db->updateKeyCode($post, $data);
				Application_Form_FrmMessage::Sucessfull('ការកែប្រែ​​ជោគ​ជ័យ','/setting/setting');
			} catch (Exception $e) {
				$this->view->msg = 'ការ​បញ្ចូល​មិន​ជោគ​ជ័យ';
			}
		}
	}
	public function addAction(){
		$this->_redirect('/setting/setting');
	}
}

