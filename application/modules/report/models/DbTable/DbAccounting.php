<?php
class Report_Model_DbTable_DbAccounting extends Zend_Db_Table_Abstract
{
      public function getGaneralJurnal($search = null){
      	$db = $this->getAdapter();
      	 $sql = " SELECT * FROM v_rptJurnal ";
      	 $where ='';
      
	    $from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
	    $to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
	    $where.= " WHERE ".$from_date." AND ".$to_date;

        if($search['branch_id']>0){
    		$where.=" AND branch_id = ".$search['branch_id'];
    	}
    	if($search['currency_type']>0){
    		$where.=" AND currency_type = ".$search['currency_type'];
    	}
      	 if(!empty($search['adv_search'])){
      	 	$s_where = array();
      	 	$s_search = addslashes(trim($search['adv_search']));
      	 	$s_where[] = " branch_name LIKE '%{$s_search}%'";
      	 	$s_where[] = " accountname LIKE '%{$s_search}%'";
      	 	$s_where[] = " account_code LIKE '%{$s_search}%'";
      	 	
      	 	$s_where[] = " journal_code LIKE '%{$s_search}%'";
      	 	$s_where[] = " debit LIKE '%{$s_search}%'";
      	 	$s_where[] = " credit LIKE '%{$s_search}%'";
      	
      	 	$where .=' AND '.implode(' OR ',$s_where).'';
      	 }
        $order=" ORDER BY branch_id,currency_type,id ASC,debit DESC";
      	 return $db->fetchAll($sql.$where.$order);
      }
      function getAllLegerReport($search=null){
      	$db = $this->getAdapter();
      	$sql = " SELECT
	      	`jd`.`id`            AS `id`,
	      	`j`.`date`           AS `date`,
	      	`j`.`journal_code`   AS `journal_code`,
	      	`jd`.`branch_id`     AS `branch_id`,
	      	(SELECT ln_account_name.account_type FROM ln_account_name WHERE id= `jd`.`account_id` LIMIT 1) AS  account_type,
	      	`jd`.`account_id`,
      		(SELECT `ln_branch`.`branch_namekh` FROM `ln_branch`
      			WHERE (`ln_branch`.`br_id` = `jd`.`branch_id`) LIMIT 1) AS `branch_name`,
      		(SELECT
      					`ln_account_name`.`account_name_en`
      					FROM `ln_account_name`
      					WHERE (`ln_account_name`.`id` = `jd`.`account_id`) LIMIT 1) AS `accountname`,
      		(SELECT
      					`ln_account_name`.`account_code`
      					FROM `ln_account_name`
      					WHERE (`ln_account_name`.`id` = `jd`.`account_id`) LIMIT 1) AS `account_code`,
      		`jd`.`jur_id`        AS `jur_id`,
      		`jd`.`currency_type` AS `currency_type`,
      		`jd`.`debit`         AS `debit`,
      		`jd`.`credit`        AS `credit`,
      		`jd`.`note`          AS `note`
      		FROM (`ln_journal` `j`
      		JOIN `ln_journal_detail` `jd`)
      		WHERE ((`jd`.`jur_id` = `j`.`id`)
      		AND (`j`.`status` = 1)
      		AND (`jd`.`status` = 1)) ";
      			$where ='';
      			
//       			$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
//       			$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
//       			$where.= " WHERE ".$from_date." AND ".$to_date;
      			
//       			if($search['branch_id']>0){
//       				$where.=" AND branch_id = ".$search['branch_id'];
//       			}
//       			if($search['currency_type']>0){
//       				$where.=" AND currency_type = ".$search['currency_type'];
//       			}
//       			if(!empty($search['adv_search'])){
//       				$s_where = array();
//       				$s_search = addslashes(trim($search['adv_search']));
//       				$s_where[] = " branch_name LIKE '%{$s_search}%'";
//       				$s_where[] = " accountname LIKE '%{$s_search}%'";
//       				$s_where[] = " account_code LIKE '%{$s_search}%'";
      				 
//       				$s_where[] = " journal_code LIKE '%{$s_search}%'";
//       				$s_where[] = " debit LIKE '%{$s_search}%'";
//       				$s_where[] = " credit LIKE '%{$s_search}%'";
      				 
//       				$where .=' AND '.implode(' OR ',$s_where).'';
//       			}
      			$order=" ORDER BY `jd`.`account_id`,debit DESC,currency_type,id ASC";
      			return $db->fetchAll($sql.$where.$order);
      }
      
 }

