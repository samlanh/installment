<?php

class Budget_Model_DbTable_DbbudgetItem extends Zend_Db_Table_Abstract
{
    protected $_name = 'st_budget_item';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    function getAllDataRows($search){
    	$sql="";
    	
    	
    	$from_date =(empty($search['start_date']))? '1': " send_date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " send_date <= '".$search['end_date']." 23:59:59'";
    	$where='';
    	$where_date = " AND ".$from_date." AND ".$to_date;
    	
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = (trim($search['adv_search']));
    		//$s_where[] = " sms.contance LIKE '%{$s_search}%'";
    		$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	if($search['status']>-1){
    		$where.= " AND s.status = ".$search['status'];
    	}
    	
    	$order.=' ORDER BY id DESC  ';
    	
    	$db = $this->getAdapter();
    	return $db->fetchAll($sql.$where_date.$order);
    }
   
    function addData($data){
    	
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try
    	{
    		$arr = array(
    				''=>''
    				
    				);
    		//$this->_name='';
    		$id = $this->insert($arr);
    		$db->commit();
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    	}
    }
    function updateData($data){
    	 
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try
    	{
    		$arr = array(
    				''=>''
    
    		);
    		
    		//$this->_name='';
    		$where = 'client_id = '.$data['id'];
			$this->update($arr, $where);
    		$db->commit();
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    	}
    }
    function getDataRow($recordId){
    	$db = $this->getAdapter();
    	
    	$sql=" SELECT * FROM $this->_name WHERE id=".$recordId." LIMIT 1";
    	return $db->fetchRow($sql);
    }
    function getAllBudgetItem($option=null){
    	$db = new  Application_Model_DbTable_DbGlobalStock();
    	return $db->getAllBudgetItem($parent = 0, $spacing = '', $cate_tree_array = '',$option);
    	
//     	$sql = "SELECT id,budgetTitle AS name FROM $this->_name WHERE status = 1 Order by id ASC";
//     	$results =  $db->fetchAll($sql);
//     	if(!empty($option)){
//     		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
//     		$optionList= array(
//     			0=>$tr->translate("PLEASE_SELECT_BUDGET"),
//     			-1=>$tr->translate("ADD_NEW"),
//     		);
//     		if(!empty($results)){
//     			foreach ($results as $rs){
//     				$optionList[$rs['id']]=$rs['name'];
//     			}
//     		}
//     		return $optionList;
//     	}
//     	return $results;
    }
    
   
}