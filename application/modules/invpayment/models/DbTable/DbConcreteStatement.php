<?php

class Invpayment_Model_DbTable_DbConcreteStatement extends Zend_Db_Table_Abstract
{
    protected $_name = '';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
  
    function getConcreteStatement($recordId){
    	$db =$this->getAdapter();
    	$sql="SELECT *,
		sd.id,
		(SELECT k.workTitle FROM `st_work_type` k WHERE k.id=sd.workType LIMIT 1) WorkType,
		(SELECT p.proName FROM st_product p WHERE p.proId=sd.proId LIMIT 1) proName,
		(SELECT p.proCode FROM st_product p WHERE p.proId=sd.proId LIMIT 1) proCode,
		(SELECT p.measureLabel FROM st_product p WHERE p.proId=sd.proId LIMIT 1) measureLabel,
		sd.note AS NOTE

		FROM `st_receive_stock_detail` AS sd  JOIN `st_receive_stock` AS st ON sd.receiveId = st.id WHERE
		st.transactionType = 2 AND sd.receiveId = ".$recordId ;
    	return $db->fetchAll($sql);
    }

}