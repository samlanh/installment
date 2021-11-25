<?php
class Report_Model_DbTable_DbLnClient extends Zend_Db_Table_Abstract
{
      
       protected  $db_name='ln_client';

    public function getAllLnClient($search = null){
    	 $db = $this->getAdapter();
    	 $from_date =(empty($search['start_date']))? '1': "create_date >= '".$search['start_date']." 00:00:00'";
    	 $to_date = (empty($search['end_date']))? '1': "create_date <= '".$search['end_date']." 23:59:59'";
    	 $where = " AND ".$from_date." AND ".$to_date;
    	 
         $sql=" SELECT *,
         (SELECT 
			CONCAT(COALESCE(p.land_address,''),',',COALESCE(p.street,''))
			FROM `ln_sale` AS s ,ln_properties AS p WHERE 
			p.id=s.house_id
			AND s.client_id=v_getallclient.client_id LIMIT 1 )  AS house_name 

         FROM v_getallclient WHERE 1";
          if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['adv_search']));
			$s_where[] = " branch_name LIKE '%{$s_search}%'";
			$s_where[] = " client_number LIKE '%{$s_search}%'";
			$s_where[] = " client_name LIKE '%{$s_search}%'";
			$s_where[] = " doc_name LIKE '%{$s_search}%'";
			
			$s_where[] = " phone LIKE '%{$s_search}%'";
			$s_where[] = " house LIKE '%{$s_search}%'";
			$s_where[] = " street LIKE '%{$s_search}%'";
			$s_where[] = " village_name LIKE '%{$s_search}%'";
			$s_where[] = " com_name LIKE '%{$s_search}%'";
			
			$s_where[] = " pro_name LIKE '%{$s_search}%'";
			$s_where[] = " joint_doc_type LIKE '%{$s_search}%'";
			
			$s_where[] = " hname_kh LIKE '%{$s_search}%'";
			$s_where[] = " lphone LIKE '%{$s_search}%'";
			$s_where[] = " joindoc_name LIKE '%{$s_search}%'";
			$s_where[] = " rid_no LIKE '%{$s_search}%'";
			
			$where .=' AND ('.implode(' OR ',$s_where).')';
			}
			if($search['status']>-1){
				$where.= " AND status = ".$search['status'];
			}
			if($search['province']>0){
				$where.=" AND pro_id= ".$search['province'];
			}
			if($search['district']>0){
				$where.=" AND dis_id= ".$search['district'];
			}
			if($search['commune']>0){
				$where.=" AND com_id= ".$search['commune'];
			}
			if($search['village']>0){
				$where.=" AND village_id= ".$search['village'];
			}
			if($search['branch_id']>0){
				$where.=" AND branch_id= ".$search['branch_id'];
			}
			$order=" ORDER BY client_id DESC";
	        return $db->fetchAll($sql.$where.$order);
    } 
}