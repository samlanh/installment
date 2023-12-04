<?php

class Group_Model_DbTable_DbProject extends Zend_Db_Table_Abstract
{

    public static function getBranchCode(){
    	$db = new Application_Model_DbTable_DbGlobal();
    	$sql = "SELECT COUNT(br_id) AS amount FROM `ln_project`";
    	$acc_no= $db->getGlobalDbRow($sql);
    	$acc_no=$acc_no['amount'];
    	$new_acc_no= (int)$acc_no+1;
    	$acc_no= strlen((int)$acc_no+1);
    	$pre = "";
    	for($i = $acc_no;$i<3;$i++){
    		$pre.='0';
    	}
    	return $pre.$new_acc_no;
    }
}  
	  

