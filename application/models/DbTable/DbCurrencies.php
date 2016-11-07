<?php

class Application_Model_DbTable_DbCurrencies extends Zend_Db_Table_Abstract
{

    protected $_name = 'cs_currencies';

	function getCurrencyList(){
		$db = $this->getAdapter();
		$sql = "SELECT id, curr_nameen as name, symbol
				FROM `ln_currency`";		
		return $db->fetchAll($sql);
	}
}

