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
		$btpype='';
    	for($i=1; $i<=$count; $i++){

			if(empty($data[$i]['B'])){
				continue;
			}
			$sql=" SELECT id FROM `st_budget_item` WHERE budgetTitle = '".$data[$i]['B']." ' ";
			$parentId =  $db->fetchOne($sql);
			$this->_name = "st_budget_item";
			
			if(empty($parentId)){
				$btpype++;
				
				$_arr=array(
					'budgetTypeId'=>$btpype,
					'budgetTitle' =>$data[$i]['B'],
					'parentId'    =>0,
					'createDate'  =>date("Y-m-d"),
					'status'      =>1,
					'userId'      =>$this->getUserId(),
				);
				$this->insert($_arr);	
			}
    	}
		for($j=1; $j<=$count; $j++){

			if(empty($data[$j]['B']) OR empty($data[$j]['C'])){
				continue;
			}
			$sql=" SELECT id FROM `st_budget_item` WHERE budgetTitle = '".$data[$j]['B']." ' ";
			$parentId =  $db->fetchOne($sql);

			
			$sql1=" SELECT id FROM `st_budget_item` WHERE budgetTitle = '".$data[$j]['C']." ' ";
			$budgetId =  $db->fetchOne($sql1);

			if(empty($budgetId)){
				$_arr=array(
					'budgetTypeId'=>$parentId,
					'budgetTitle' =>$data[$j]['C'],
					'parentId'    =>$parentId,
					'createDate'  =>date("Y-m-d"),
					'status'      =>1,
					'userId'      =>$this->getUserId(),
				);
				$this->insert($_arr);
			
			}

    	}
    }
}   

