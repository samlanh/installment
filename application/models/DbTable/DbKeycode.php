<?php

class Application_Model_DbTable_DbKeycode extends Zend_Db_Table_Abstract
{

    protected $_name = 'rms_setting';
    
	public function getSystemSetting($id){
		$db = $this->getAdapter();
		$sql = "SELECT keycode,value FROM ln_system_setting WHERE id=$id";
		return $db->fetchRow($sql);
	}
	function getKeyCodeMiniInv($loginonly = FALSE){
		$db = $this->getAdapter();
		$sql = 'SELECT `keyName`,`keyValue` FROM `rms_setting`';
		if($loginonly){
			//$sql .= " WHERE `code` > 10";
		}
		$_result = $db->fetchAll($sql);
		$_k = array(); 
		foreach ($_result as $key => $k) {
			$_k[$k['keyName']] = $k['keyValue'];
		}
		return $_k;
	}
	
	static function getHieghtRow(){
		return 29;		
	}
	public function getLabelVaueById($id){
		$db = $this->getAdapter();
		$sql = "SELECT code,keyName,keyValue FROM `rms_setting` WHERE code = $id AND access_type=0";
		return $db->fetchRow($sql);
	}
	
	function getMainBranch(){
		$db = $this->getAdapter();
		$sql = 'SELECT `keyValue` FROM `rms_setting` WHERE `keyName`="mainbranch"';
		$_result = $db->fetchRow($sql);		
		return $_result['keyValue'];
	}
	
	static function getMonthNameKh($month){
		$_mkh = array(
					1=>'មករា',
					2=>'កុម្ភៈ',
					3=>'មីនា',
					4=>'មេសា',
					5=>'ឧសភា',
					6=>'មិថុនា',
					7=>'កក្កដា',
					8=>'សីហា',
					9=>'កញ្ញា',
					10=>'តុលា',
					11=>'វិច្ឆិកា',
					12=>'ធ្នូ'
					);
		return $_mkh[$month];
	}
}

