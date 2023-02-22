<?php

class  Budget_Model_DbTable_DbImport extends Zend_Db_Table_Abstract
{

    protected $_name = 'st_budget_item';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    
    }
    
    public function BudgetByImport($data){
    	$db = $this->getAdapter();
    	$count = count($data);
    	$dbg = new Application_Model_DbTable_DbGlobal();

    	for($i=1; $i<=$count; $i++){

			if(empty($data[$i]['B']) OR empty($data[$i]['C'])){
				continue;
			}
			$sql=" SELECT id FROM `st_budget_item` WHERE budgetTitle = '".$data[$i]['B']." ' ";
			$parentId =  $db->fetchOne($sql);

			$this->_name = "st_budget_item";
			$_arr=array(

				//	'budgetTypeId'=>$data['budgetType'],
					'createDate'=>date("Y-m-d"),
					'status'=>1,
					'userId'=>$this->getUserId(),
			);

			if(empty($parentId)){
			
				$_arr['budgetTitle']=$data[$i]['B'];
				$_arr['parentId']=0;
				$parentId= $this->insert($_arr);

			}
			$sql=" SELECT id FROM `st_budget_item` WHERE budgetTitle = '".$data[$i]['C']." ' ";
			$budgetId =  $db->fetchOne($sql);

			if(empty($budgetId)){
			
				$_arr['budgetTitle']=$data[$i]['C'];
				$_arr['parentId']= $parentId;
				$this->insert($_arr);
			}

    	}
    }
}   

