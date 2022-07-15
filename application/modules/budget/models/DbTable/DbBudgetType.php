<?php

class Budget_Model_DbTable_DbBudgetType extends Zend_Db_Table_Abstract
{
    protected $_name = 'st_budget_type';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getAllBudgetType($search){
    	$sql="SELECT b.id,
    		b.budgetTitle,
    		(SELECT p.budgetTitle FROM $this->_name As p WHERE p.id=b.parentId LIMIT 1) AS parentTitle,
	    	b.createDate,
	    	(SELECT first_name from rms_users as u where u.id = b.userId LIMIT 1) as user ,
    		(SELECT name_en from ln_view where type=3 and key_code = b.status LIMIT 1) AS status
    	FROM $this->_name As b
    		WHERE 1
    	";
    	
    	$from_date =(empty($search['start_date']))? '1': "b.createDate >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " b.createDate <= '".$search['end_date']." 23:59:59'";
    	
    	$where_date = " AND ".$from_date." AND ".$to_date;
    	$where='';
    	
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = (trim($search['adv_search']));
    		$s_where[] = " b.budgetTitle LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if($search['status']>-1){
    		$where.= " AND b.status = ".$search['status'];
    	}
    	
    	$order=' ORDER BY b.id DESC  ';
    	
    	$db = $this->getAdapter();
    	return $db->fetchAll($sql.$where_date.$where.$order);
    }
   
    function addBudgetType($data){
    	try
    	{
    		$db = new Application_Model_DbTable_DbGlobalStock();
    		$result = $db->dataExisting($this->_name,"budgetTitle='".$data['budgetTitle']."'");
    		
    		if(empty($result)){
	    		$arr = array(
	    				'parentId'=>$data['parent_id'],
	    				'budgetTitle'=>$data['budgetTitle'],
	    				'createDate'=>date("Y-m-d"),
	    				'status'=>1,
	    				'userId'=>$this->getUserId(),
	    			);
	    		$this->insert($arr);
    		}else{
    			Application_Form_FrmMessage::Sucessfull("DATA_EXISTING", "/budget/type/add");
    		}
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		Application_Form_FrmMessage::Sucessfull("INSERT_FAIL", "/budget/type/add");
    	}
    }
    function updateBudgetType($data){
    	try
    	{
    		$arr = array(
    			'parentId'=>$data['parent_id'],
	    		'budgetTitle'=>$data['budgetTitle'],
    			'status'=>$data['status'],
    			'userId'=>$this->getUserId(),
    		);
    		
    		$where = 'id = '.$data['id'];
			$this->update($arr, $where);
			
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		Application_Form_FrmMessage::Sucessfull("UPDATE_FAIL", "/budget/type/index");
    	}
    }
    function getDataRow($recordId){
    	$db = $this->getAdapter();
    	$sql=" SELECT * FROM $this->_name WHERE id=".$recordId." LIMIT 1";
    	return $db->fetchRow($sql);
    }
   
}