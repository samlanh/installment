<?php

class Budget_Model_DbTable_DbbudgetItem extends Zend_Db_Table_Abstract
{
    protected $_name = 'st_budget_item';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getAllBudgetItems($search){
    	$sql="SELECT bi.id,
    		 bi.budgetTitle,
    		(SELECT p.budgetTitle FROM $this->_name AS p WHERE p.id=bi.parentId LIMIT 1) AS parentTitle,
    		(SELECT b.budgetTitle FROM st_budget_type AS b WHERE b.id=bi.budgetTypeId LIMIT 1) AS budgetType,
	    	bi.createDate,
	    	(SELECT first_name FROM rms_users AS u WHERE u.id = bi.userId LIMIT 1) AS USER ,
    		(SELECT name_en FROM ln_view WHERE TYPE=3 AND key_code = bi.status LIMIT 1) AS STATUS
    	FROM $this->_name AS bi
    		WHERE 1
    	";
    	
    	
    	$from_date =(empty($search['start_date']))? '1': " createDate >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " createDate <= '".$search['end_date']." 23:59:59'";
    	$where='';
    	$where_date = " AND ".$from_date." AND ".$to_date;
    	
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = (trim($search['adv_search']));
    		$s_where[] = " bi.budgetTitle LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if($search['status']>-1){
    		$where.= " AND bi.status = ".$search['status'];
    	}
    	
    	$order=' ORDER BY bi.id DESC  ';
    	
    	$db = $this->getAdapter();
    	return $db->fetchAll($sql.$where_date.$order);
    }
   
    function addBudgetItem($data){
	    try
	    	{
	    		$db = new Application_Model_DbTable_DbGlobalStock();
	    		$result = $db->dataExisting($this->_name,"budgetTitle='".$data['budgetTitle']."'");
	    		
	    		if(empty($result)){
		    		$arr = array(
	    				'budgetTypeId'=>$data['budgetType'],
	    				'parentId'=>$data['budgetItem'],
	    				'budgetTitle'=>$data['budgetTitle'],
	    				'createDate'=>date("Y-m-d"),
	    				'status'=>1,
	    				'userId'=>$this->getUserId(),
		    		);
		    		$this->insert($arr);
	    		}else{
	    			Application_Form_FrmMessage::Sucessfull("DATA_EXISTING", "/budget/item/add");
	    		}
	    	}catch (Exception $e){
	    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	    		Application_Form_FrmMessage::Sucessfull("INSERT_FAIL", "/budget/item/add");
	    	}
    }
    function updateBudgetItem($data){
    	$db = $this->getAdapter();
    	try
    	{
    		$arr = array(
    				'budgetTypeId'=>$data['budgetType'],
    				'parentId'=>$data['budgetItem'],
    				'budgetTitle'=>$data['budgetTitle'],
    				'createDate'=>date("Y-m-d"),
    				'status'=>1,
    				'userId'=>$this->getUserId(),
	    		);
    		$where = 'id = '.$data['id'];
			$this->update($arr, $where);
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		Application_Form_FrmMessage::Sucessfull("UPDATE_FAIL", "/budget/item/index");
    	}
    }
    function getDataRow($recordId){
    	$db = $this->getAdapter();
    	$sql=" SELECT * FROM $this->_name WHERE id=".$recordId." LIMIT 1";
    	return $db->fetchRow($sql);
    }   
}