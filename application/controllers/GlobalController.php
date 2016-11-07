<?php

class GlobalController extends Zend_Controller_Action
{

  public function getMajorAction(){
  	if($this->getRequest()->isPost()){
  		$_data = $this->getRequest()->getPost();
  		$_db = new Application_Model_DbTable_DbGlobal();
  		$major_id=$_db->getMarjorById($_data['dept_id']);
  		print_r(Zend_Json::encode($major_id));
  		exit();
  	}
  }

    public function errorAction()
    {
        // action body
        
    }


}





