<?php
class Report_Model_DbTable_DbLnClient extends Zend_Db_Table_Abstract
{
      
       protected  $db_name='ln_client';

    public function getAllLnClient($search = null){
    	 $db = $this->getAdapter();
    	 
//     	 $db=$this->getAdapter();
    	  
//     	 $from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
//     	 $to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
//     	 $where = " AND ".$from_date." AND ".$to_date;
    	  
//     	 $sql = " SELECT * FROM v_getclientblacklist WHERE 1";
//     	 if(!empty($search['adv_search'])){
//     	 	$s_where = array();
//     	 	$s_search = trim($search['adv_search']);
//     	 	$s_where[] = "branch_name LIKE '%{$s_search}%'";
//     	 	$s_where[] = "client_number LIKE '%{$s_search}%'";
//     	 	$s_where[] = "client_name LIKE '%{$s_search}%'";
//     	 	$s_where[] = "sex LIKE '%{$s_search}%'";
//     	 	$s_where[] = " situation LIKE '%{$s_search}%'";
//     	 	$s_where[] = " doc_name LIKE '%{$s_search}%'";
//     	 	$s_where[] = " id_number LIKE '%{$s_search}%'";
//     	 	$s_where[] = " street LIKE '%{$s_search}%'";
//     	 	$s_where[] = " house LIKE '%{$s_search}%'";
//     	 	$s_where[] = " village_name LIKE '%{$s_search}%'";
//     	 	$s_where[] = " commune_name LIKE '%{$s_search}%'";
//     	 	$s_where[] = " district_name LIKE '%{$s_search}%'";
//     	 	$s_where[] = " province_name LIKE '%{$s_search}%'";
//     	 	$where .=' AND ('.implode(' OR ',$s_where).')';
//     	 }
//     	 if(!empty($search['branch_id'])){
//     	 	$where.= " AND branch_id = ".$search['branch_id'];
//     	 }
//     	 $order = " ORDER BY id DESC ";
//     	 return $db->fetchAll($sql.$where.$order);

    	 $from_date =(empty($search['start_date']))? '1': "create_date >= '".$search['start_date']." 00:00:00'";
    	 $to_date = (empty($search['end_date']))? '1': "create_date <= '".$search['end_date']." 23:59:59'";
    	 $where = " AND ".$from_date." AND ".$to_date;
          $sql=" SELECT * FROM v_getallclient WHERE 1";
          if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = $search['adv_search'];
			$s_where[] = " branch_name LIKE '%{$s_search}%'";
			$s_where[] = " client_number LIKE '%{$s_search}%'";
			$s_where[] = " client_name LIKE '%{$s_search}%'";
			$s_where[] = " situation LIKE '%{$s_search}%'";
			$s_where[] = " doc_name LIKE '%{$s_search}%'";
			$s_where[] = " id_number LIKE '%{$s_search}%'";
			$s_where[] = " job LIKE '%{$s_search}%'";
			
			$s_where[] = " phone LIKE '%{$s_search}%'";
			$s_where[] = " house LIKE '%{$s_search}%'";
			$s_where[] = " street LIKE '%{$s_search}%'";
			$s_where[] = " village_name LIKE '%{$s_search}%'";
			$s_where[] = " com_name LIKE '%{$s_search}%'";
			
			$s_where[] = " pro_name LIKE '%{$s_search}%'";
			$s_where[] = " joint_doc_type LIKE '%{$s_search}%'";
			$s_where[] = " join_nation_id LIKE '%{$s_search}%'";
			
			$where .=' AND ('.implode(' OR ',$s_where).')';
			}
// 			if($search['status']>-1){
// 				$where.= " AND status = ".$search['status'];
// 			}
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
// 			echo $sql.$where.$order;
	          return $db->fetchAll($sql.$where.$order);
    } 
    public function getAllGroup(){
    	$db = $this->getAdapter();
    	$sql="SELECT client_number,name_kh,name_en,sex,status,(SELECT branch_namekh FROM ln_branch WHERE br_id =branch_id limit 1) AS branch_name
    	,pro_id,dis_id,com_id,village_id,spouse_name,phone FROM ln_client ORDER BY client_id";
    	return $db->fetchAll($sql);
    }
    public function getAllCalleteral($search=null){
    	$db = $this->getAdapter();
    	$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
		$sql =" SELECT id ,branch_name ,co_id ,collecteral_code,client_code ,client_id,client_name,name_kh, join_with , relative , 
		collecteral_type,collecteral_owner,number_collecteral,issue_date,collecteral_title_en,date ,note ,status ,is_return FROM 
		`v_getallcallateral` WHERE 1";
		if($search['status_search']>-1){
 			$where.=" AND status=".$search['status_search'];
 		}
		if(!empty($search['branch_id'])){
			$where.=" AND branch_id = ".$search['branch_id'];
		}
		if(!empty($search['adv_search'])){
			$s_where=array();
			$s_search=trim($search['adv_search']);
			
			$s_where[]=" number_collecteral LIKE'%{$s_search}%'";
			$s_where[]=" collecteral_owner LIKE'%{$s_search}%'";
			$s_where[]=" collecteral_title_en LIKE'%{$s_search}%'";
			$s_where[]=" collecteral_type LIKE'%{$s_search}%'";
			$s_where[]=" branch_name LIKE'%{$s_search}%'";
			$s_where[]=" co_id LIKE'%{$s_search}%'";
			$s_where[]=" collecteral_code LIKE'%{$s_search}%'";
			$s_where[]=" client_code LIKE'%{$s_search}%'";
			$s_where[]=" name_kh LIKE'%{$s_search}%'";
			$s_where[]=" client_name LIKE'%{$s_search}%'";
			$s_where[]=" join_with LIKE'%{$s_search}%'";
			$s_where[]=" relative LIKE'%{$s_search}%'";
			$s_where[]=" note LIKE'%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		$order = " ORDER BY client_id ";
    	return $db->fetchAll($sql.$where.$order);
    }
function geteAllcallteral($search=null){
		$db = $this->getAdapter();
		
		$from_date =(empty($search['start_date']))? '1': "date_registration >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': "date_registration <= '".$search['end_date']." 23:59:59'";
		$where = " WHERE ".$from_date." AND ".$to_date;
		
		$sql=" SELECT * FROM ln_client_callecteral ";
		
		if($search['status_search']>-1){
			$where.=" AND status=".$search['status_search'];
		}
		if(!empty($search['branch_id'])){
			$where.=" AND ln_client_callecteral.branch_id = ".$search['branch_id'];
		}
		if(!empty($search['co_name'])){
			$where.=" AND co_id = ".$search['co_name'];
		}
		if(!empty($search['collteral_type'])){
			$where.=" AND callate_type = ".$search['collteral_type'];
		}
		if(!empty($search['client_code'])){
			$where.=" AND client_code = ".$search['client_code'];
		}
		if(!empty($search['adv_search'])){
			$s_where=array();
			$s_search=$search['adv_search'];
			$s_where[]="code_call LIKE '%{$s_search}%'";
			$s_where[]="owner LIKE'%{$s_search}%'";
			$s_where[]="number_collteral LIKE'%{$s_search}%'";
			$s_where[]="note LIKE'%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		$order = " ORDER BY id DESC ";
		//echo $sql.$where.$order;
		return $db->fetchAll($sql.$where.$order);
	}
	function getAllChangeCollteral($search=null){
		$db = $this->getAdapter();
	
		$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
		$where = " WHERE ".$from_date." AND ".$to_date;	
			$sql="SELECT * FROM v_getchangcolleral WHERE 1 ";
				$where='';
				if($search['status_search']>-1){
					$where.=" AND statuss =".$search['status_search'];
				}
				if(!empty($search['branch_id'])){
					$where.=" AND branch_id = ".$search['branch_id'];
				}
// 				if(!empty($search['client_name'])){
// 					$where.=" AND client_id = ".$search['client_name'];
// 				}
				if(!empty($search['adv_search'])){
					$s_where=array();
					$s_search=trim($search['adv_search']);
					$s_where[]="branch_name LIKE '%{$s_search}%'";
					$s_where[]="client_name LIKE '%{$s_search}%'";
					$s_where[]="collecteral_code LIKE '%{$s_search}%'";
					$s_where[]="collecteral_title_old LIKE '%{$s_search}%'";
					$s_where[]="collecteral_owner LIKE '%{$s_search}%'";
					$s_where[]="from_owner_name LIKE '%{$s_search}%'";
					$s_where[]="from_number_collateral LIKE '%{$s_search}%'";
					$s_where[]="collecteral_title_en LIKE '%{$s_search}%'";
					$s_where[]="collecteral_owner LIKE '%{$s_search}%'";
					$s_where[]="number_collateral LIKE '%{$s_search}%'";
					$s_where[]="note LIKE '%{$s_search}%'";
					$where .=' AND ('.implode(' OR ',$s_where).')';
				}
			return $db->fetchAll($sql.$where);
	}
	function getCalleteralByClient($client_id){
			$db = $this->getAdapter();
			$sql =" SELECT id ,branch_name ,co_id ,collecteral_code,client_code ,client_id,client_name,name_kh, join_with , relative ,spouse_name ,owner_name,
			collecteral_type,collecteral_owner,number_collecteral,issue_date,collecteral_title_en,collecteral_title_kh,date ,note ,status ,is_return FROM
			`v_getallcallateral` WHERE client_id = $client_id AND status=1 and is_return=0 ";
			$order = " ORDER BY client_id ";
			return $db->fetchAll($sql.$order);
	}
}

