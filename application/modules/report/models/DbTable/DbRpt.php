<?php
class Report_Model_DbTable_DbRpt extends Zend_Db_Table_Abstract
{
	
	function getbyid($id){
		$db = $this->getAdapter();
		$sql=" SELECT id,Contract Code,Status,Amount,OLB,Creatin Dat,Start Date	,Close Date,Late Day FROM $this->_name where id=$id ";
		return $db->fetchRow($sql);
	}
	function getAllReturnCollteral($search=null){
		$db=$this->getAdapter();
		$db->beginTransaction();
		try {
			$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
			$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
			$where = " AND ".$from_date." AND ".$to_date;
				
			$sql="SELECT * FROM `v_getreturncalleral` WHERE status = 1";
			if(!empty($search['branch_id'])){
				$where.=" AND branch_id =".$search['branch_id'];
			}
				
			if(!empty($search['adv_search'])){
				$s_where=array();
				$s_search=trim($search['adv_search']);
				
				$s_where[]=" client_code LIKE '%{$s_search}%'";
				$s_where[]=" branch_name LIKE '%{$s_search}%'";
				$s_where[]=" giver_name LIKE '%{$s_search}%'";
				$s_where[]=" receiver_name LIKE '%{$s_search}%'";
				$s_where[]=" collecteral_type LIKE '%{$s_search}%'";
				$s_where[]=" re_owner_type LIKE '%{$s_search}%'";
				$s_where[]=" number_collteral LIKE '%{$s_search}%'";
				$s_where[]=" note LIKE '%{$s_search}%'";
				
				$where .=' AND ('.implode(' OR ',$s_where).')';
			}
	
			$order = " ORDER BY return_id DESC";
			
			return $db->fetchAll($sql.$where.$order);
			$db->commit();
		}catch (Exception $e){
			$db->rollBack();
		}
	}
}