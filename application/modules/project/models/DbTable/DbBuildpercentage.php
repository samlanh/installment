<?php

class Project_Model_DbTable_DbBuildpercentage extends Zend_Db_Table_Abstract
{
    protected $_name = 'ln_properties';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function updateBuildPercentage($data){
    	
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		if(!empty($data['identity'])){
    			
    			$ids = explode(',', $data['identity']);
    			foreach ($ids as $i){
	    			$arr = array(
	    				'buildPercentage'=>$data['percentage'.$i],
	    				'buildPercentageNote'=>$data['note'.$i],
	    			);
	    			$where ='id='.$data['land_id'.$i];
	    			$this->update($arr, $where);
    			}
    		}
	    	$db->commit();
    	}catch(exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
    	}
	}
	
}