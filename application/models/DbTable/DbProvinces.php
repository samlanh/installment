<?php

class Application_Model_DbTable_DbProvinces extends Zend_Db_Table_Abstract
{

    protected $_name = 'cs_provinces';
	
	function getProvinceList(){
		$db = $this->getAdapter();
		$sql = "SELECT p.id, p.name, COUNT(a.id) AS num
				FROM `cs_provinces` AS p
					INNER JOIN `cs_agents` AS a ON (p.id = a.province)
				GROUP BY a.province, p.id, p.name
				ORDER BY p.id";		
		return $db->fetchAll($sql);
	}
	
	function getProvinceListAll(){
		$db = $this->getAdapter();
		$sql = "SELECT p.id, p.name
				FROM `cs_provinces` AS p";		
		return $db->fetchAll($sql);
	}

}

