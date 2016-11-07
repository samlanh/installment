<?php

class Other_Model_DbTable_DbStudent extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_holiday';
    
    function addHoliday($data){
    	$this->insert($data);
    	
    }
	
}

