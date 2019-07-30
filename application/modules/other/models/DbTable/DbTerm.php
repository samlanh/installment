<?php

class Other_Model_DbTable_DbTerm extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_termcondiction';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
	public function addTermcondiction($_data){
		try {
		$_arr=array(
				'con_khmer'	  	=> trim($_data['con_khmer']),		
				'con_step'	=> trim($_data['con_step']),
				'con_payoff'	=> $_data['con_payoff'],
				'user_id'	 	=> $this->getUserId(),
		);
			$where = 'id = 1';
			return  $this->update($_arr, $where);
		
		}catch (Exception $e){
			echo $e->getMessage();
		}
	}
	public function termCondiction(){
		$db = $this->getAdapter();
		$sql="SELECT * FROM `ln_termcondiction` AS t WHERE t.id=1";
		return $db->fetchRow($sql);
	}
	
}

