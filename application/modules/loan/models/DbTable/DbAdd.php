<?php

class Loan_Model_DbTable_DbAdd extends Zend_Db_Table_Abstract
{	protected $_name = 'ln_zone';
	public function Addzone($_data){
		$_zone=array(
				'zone_name'  => $_data['zone_name'],
				'zone_num'  => $_data['zone_number'],
				'modify_date' => Zend_Date::now(),
				'user_id'	  => $this->getUserId()
		);
		
		return  $this->insert($_zone);
	} 
}

