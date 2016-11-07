<?php
class Setting_ProfileController extends Zend_Controller_Action {
	
	
public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
	}
	public function indexAction()
	{
// 		$key = new Application_Model_DbTable_DbKeycode();
// 		$data=$key->getKeyCode();
// 		$this->view->data= $data;
		 
// 		if($this->getRequest()->isPost()){
// 			$post=$this->getRequest()->getPost();
			 
// 			try {
// 				$db = $key->updateKeyCode($post, $data);
// 				Application_Form_FrmMessage::Sucessfull('ការ​បញ្ចូល​​ជោគ​ជ័យ', self::REDIRECT_URL);
// 			} catch (Exception $e) {
// 				$this->view->msg = 'ការ​បញ្ចូល​មិន​ជោគ​ជ័យ';
// 			}
// 		}
	}
	
}

