<?php

class Loan_Model_DbTable_Dbissueplong extends Zend_Db_Table_Abstract
{

    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authinstall');
    	return $session_user->user_id;
    	 
    }
   function getAllissueplong($search){
   	$from_date =(empty($search['start_date']))? '1': " sp.issue_date >= '".$search['start_date']." 00:00:00'";
   	$to_date = (empty($search['end_date']))? '1': " sp.issue_date <= '".$search['end_date']." 23:59:59'";
   	$where = " AND ".$from_date." AND ".$to_date;
   	$sql="SELECT `s`.`id` AS `id`,
    	(SELECT
		     `ln_project`.`project_name`
		   FROM `ln_project`
		   WHERE (`ln_project`.`br_id` = `s`.`branch_id`)
		   LIMIT 1) AS `branch_name`,
	    `c`.`name_kh`         AS `name_kh`,
	    `c`.`phone`         AS `phone`,
	    `p`.`land_address`    AS `land_address`,
	    `p`.`street`          AS `street`,
	    sp.issue_date,
         s.status
		FROM (`ln_sale` `s`,
			ln_issueplong AS sp,
		     `ln_client` `c`
		   JOIN `ln_properties` `p`)
		WHERE (
		s.id=sp.sale_id
		AND (`c`.`client_id` = `s`.`client_id`)
       AND (`p`.`id` = `s`.`house_id`)) ";
   	
   	if(!empty($search['adv_search'])){
   		$s_where = array();
   	}
   	if($search['status']>-1){
   		$where.= " AND cp.status = ".$search['status'];
   	}
   	if(($search['client_name'])>0){
   		$where.= " AND `cp`.`client_id`=".$search['client_name'];
   	}
   	if(($search['branch_id'])>0){
   		$where.= " AND ( cp.from_branchid = ".$search['branch_id']." OR cp.to_branchid = ".$search['branch_id']." )";
   	}
   	
   	$order = " ORDER BY sp.id DESC";
   	$db = $this->getAdapter();
   	return $db->fetchAll($sql.$where.$order);
   }
    
    public function addIssuePlong($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		
    			$ids = explode(',', $data['identity']);
	    		if(!empty($data['identity'])){
	    			foreach ($ids as $i){
	    					$arr = array(
	    						'is_issueplong'=>1,
	    						'issueplong_date'=>date("Y-m-d")
	    						);
	    					
	    					$where="id = ".$data['sale_id'.$i];
	    					$this->_name="ln_sale";
	    					$this->update($arr, $where);
	    					
	    					$sql="SELECT 
	    							s.id ,s.amount_daydelay,
	    							s.is_issueplong,s.is_receivedplong,
	    							s.issueplong_date,s.payment_id,
	    							ss.date_payment,
	    							ss.id AS id_detail
	    						FROM `ln_sale` AS s,
	    							ln_saleschedule as ss
	    						WHERE 
	    						s.id=ss.sale_id
	    					AND s.id=".$data['sale_id'.$i]."  ORDER BY ss.id DESC LIMIT 1";
	    					$rs = $db->fetchRow($sql);
	    					
	    					
	    					if($rs['payment_id']==6 OR $rs['payment_id']==3){
	    						if($rs['amount_daydelay']>0 AND $rs['is_issueplong']==0){
	    							$str_next = '+'.$rs['amount_daydelay'].' day';
	    							$next_payment = $rs['date_payment'];
	    							$next_payment = date("Y-m-d", strtotime("$next_payment $str_next"));//code here have problem
	    							
	    							$arr = array(
	    										'date_payment'=>$next_payment
	    									);
	    							$where="id = ".$rs['id_detail'];
	    							$this->_name="ln_saleschedule";
	    							$this->update($arr, $where);
	    							
	    						}
	    					}
	    					
	    					$where="id = ".$data['sale_id'.$i];
	    					$this->_name="ln_issueplong";
    						$arr = array(
    							'sale_id'=>$data['sale_id'.$i],
    							'issue_date'=>date('Y-m-d'),
    							'note'=>$data['note'.$i],
    							'is_receivedplong'=>0,
    						);
    						$this->insert($arr);
	    			}
	    		}
    			$db->commit();
    			return 1;
    		}catch (Exception $e){
    			$db->rollBack();
    			$err =$e->getMessage();
//     			echo $err;exit();
    			Application_Model_DbTable_DbUserLog::writeMessageError($err);
    		}
    }
//     function getTransferProject($id){
//     	$sql=" select * from ln_change_project where id= $id limit 1";
//     	$db = $this->getAdapter();
//     	return $db->fetchRow($sql);
//     }
}