<?php

class Issue_Model_DbTable_Dbissueplong extends Zend_Db_Table_Abstract
{
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
   function getAllissueplong($search){
   	$from_date =(empty($search['start_date']))? '1': " sp.issue_date >= '".$search['start_date']." 00:00:00'";
   	$to_date = (empty($search['end_date']))? '1': " sp.issue_date <= '".$search['end_date']." 23:59:59'";
   	$where = " AND ".$from_date." AND ".$to_date;
   	$sql="SELECT `sp`.`id` AS `id`,
	      CASE    
		WHEN  (SELECT rec.sale_id FROM `ln_receiveplong` AS rec WHERE rec.status=1 AND rec.sale_id = sp.sale_id ORDER BY rec.id DESC LIMIT 1 ) IS NOT NULL  THEN 'បានប្រគល់'
		WHEN  (SELECT rec.sale_id FROM `ln_receiveplong` AS rec WHERE rec.status=1 AND rec.sale_id = sp.sale_id ORDER BY rec.id DESC LIMIT 1 ) IS NULL THEN 'មិនទាន់ប្រគល់'
		END AS ask_for,
    	(SELECT
		     `ln_project`.`project_name`
		   FROM `ln_project`
		   WHERE (`ln_project`.`br_id` = `s`.`branch_id`)
		   LIMIT 1) AS `branch_name`,
	    `c`.`name_kh`         AS `name_kh`,
	     c.phone,
	    CONCAT(COALESCE(`p`.`land_address`,''),' ',COALESCE(`p`.`street`,'')) AS land_address,
	    
	    s.price_sold,
	    (s.price_sold - (SELECT SUM(cr.total_principal_permonthpaid+cr.extra_payment) FROM `ln_client_receipt_money` AS cr WHERE cr.sale_id = sp.sale_id LIMIT 1) ) AS balance,
	     sp.issue_date,
	     sp.note,
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
   		$s_search = addslashes(trim($search['adv_search']));
   		$s_where[] = " `c`.`name_kh` LIKE '%{$s_search}%'";
   		$s_where[] = " p.`land_address` LIKE '%{$s_search}%'";
   		$s_where[] = " p.`street` LIKE '%{$s_search}%'";
   		$s_where[] = " sp.note LIKE '%{$s_search}%'";
   		$where .=' AND ('.implode(' OR ',$s_where).')';
   		
   	}
   	if($search['status']>-1){
   		$where.= " AND c.status = ".$search['status'];
   	}
   	if(!empty($search['client_name']) AND ($search['client_name'])>0){
   		$where.= " AND `c`.`client_id`=".$search['client_name'];
   	}
   	if(($search['branch_id'])>0){
   		$where.= " AND s.branch_id = ".$search['branch_id'];
   	}
   	if(($search['land_id'])>0){
   		$where.= " AND s.house_id = ".$search['land_id'];
   	}
   	
   	if($search['status_plong']==1){
   		$where.= " AND s.id = (SELECT rec.sale_id FROM `ln_receiveplong` AS rec WHERE rec.status=1 AND rec.sale_id = sp.sale_id ORDER BY rec.id DESC LIMIT 1) ";
   	}
   	if($search['status_plong']==2){
   		$where.= " AND s.id  NOT IN (SELECT rec.sale_id FROM `ln_receiveplong` AS rec WHERE rec.status=1 ) ";
   	}
   	
   	$dbp = new Application_Model_DbTable_DbGlobal();
   	$where.=$dbp->getAccessPermission("`s`.`branch_id`");
   	
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
	    					
	    					$where_proper="id = ".$data['house_id'.$i];
	    					$this->_name="ln_properties";
	    					$arr_proper = array(
	    							'hardtitle'=>$data['hardtitle'.$i],
	    					);
	    					$this->update($arr_proper, $where_proper);
	    					
	    					$where="id = ".$data['sale_id'.$i];
	    					$this->_name="ln_issueplong";
    						$arr = array(
    							'sale_id'=>$data['sale_id'.$i],
    							'issue_date'=>date('Y-m-d'),
    							'layout_number'=>$data['hardtitle'.$i],
    							'note'=>$data['note'.$i],
    							'is_receivedplong'=>0,
    						);
    						$this->insert($arr);
	    			}
	    		}
    			$db->commit();
    			return 1;
    		}catch (Exception $e){
    			$err =$e->getMessage();
    			Application_Model_DbTable_DbUserLog::writeMessageError($err);
    			$db->rollBack();
    		
    			
    		}
    }
    function getPlongbyId($record_id){
    	$db = $this->getAdapter();
    	$sql="SELECT *,
			(SELECT branch_id FROM ln_sale WHERE ln_sale.id=ln_issueplong.sale_id LIMIT 1) AS branch_id
    	 FROM 
    		ln_issueplong WHERE id=$record_id ";
    	
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission("(SELECT branch_id FROM ln_sale WHERE ln_sale.id=ln_issueplong.sale_id LIMIT 1)");
    	$sql.=" LIMIT 1 ";
    	
    	return $db->fetchRow($sql);
    }
    public function EditIssuePlong($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    
    		$ids = explode(',', $data['identity']);
    		if(!empty($data['identity'])){
    			foreach ($ids as $i){
    				$where_proper="id = ".$data['house_id'.$i];
    				$this->_name="ln_properties";
    				$arr_proper = array(
    					'hardtitle'=>$data['hardtitle'.$i],
    				);
    				$this->update($arr_proper, $where_proper);
    
    				$where="id = ".$data['sale_id'.$i];
    				$this->_name="ln_issueplong";
    				$arr = array(
    						'layout_number'=>$data['hardtitle'.$i],
    						'note'=>$data['note'.$i],
    						'is_receivedplong'=>0,
    				);
    				$where=" sale_id = ".$data['sale_id'.$i];
    				$this->update($arr, $where);
    			}
    		}
    		$db->commit();
    		return 1;
    	}catch (Exception $e){
    		$db->rollBack();
    		$err =$e->getMessage();
    		Application_Model_DbTable_DbUserLog::writeMessageError($err);
    	}
    }
    
    
    public function updateNote($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    
    		$arr = array(
    					'note'=>$data['noted'],
    				);
    		$where=" id = ".$data['id'];
    		$this->_name="ln_issueplong";
    		$this->update($arr, $where);
    				
    		$rs = $this->getPlongbyId($data['id']);
    		if (!empty($rs)){
    			$arr = array(
    					'note'=>$data['noted'],
    			);
    			$where=" sale_id = ".$rs['sale_id'];
    			$this->_name="ln_processing_plong";
    			$this->update($arr, $where);
    		}
    		$db->commit();
    		return 1;
    	}catch (Exception $e){
    		$err =$e->getMessage();
    		Application_Model_DbTable_DbUserLog::writeMessageError($err);
    		$db->rollBack();
    	}
    }
    function getAllissueCompleted($search){
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$from_date =(empty($search['start_date']))? '1': " pr.date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " pr.date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	$sql="SELECT `pr`.`id` AS `id`,
    	
    	(SELECT `ln_project`.`project_name`   	FROM `ln_project`  	WHERE (`ln_project`.`br_id` = `pr`.`branch_id`)	LIMIT 1) AS `branch_name`,
    	CASE    
		WHEN  (SELECT rec.sale_id FROM `ln_receiveplong` AS rec WHERE rec.status=1 AND rec.sale_id = pr.sale_id ORDER BY rec.id DESC LIMIT 1 ) IS NOT NULL  THEN 'បានប្រគល់'
		WHEN  (SELECT rec.sale_id FROM `ln_receiveplong` AS rec WHERE rec.status=1 AND rec.sale_id = pr.sale_id ORDER BY rec.id DESC LIMIT 1 ) IS NULL THEN 'មិនទាន់ប្រគល់'
		END AS ask_for,
    	`c`.`name_kh`         AS `name_kh`,
    	c.phone,
    	`p`.`land_address`    AS `land_address`,
    	`p`.`street`          AS `street`,
    	 p.hardtitle,
    	 s.price_sold,
	    (s.price_sold - (SELECT SUM(cr.total_principal_permonthpaid+cr.extra_payment) FROM `ln_client_receipt_money` AS cr WHERE cr.sale_id = s.id LIMIT 1) ) AS balance,
    	pr.note_payment, 
    	p.noteForLayout 
		
		";
    	$sql.="
    		FROM (
    		`ln_processing_plong` `pr`,
    		`ln_sale` `s`,
		    `ln_client` `c`,
		   	 `ln_properties` `p`)
    	    WHERE 
    	    	pr.sale_id = s.id
    	      AND `c`.`client_id` = `pr`.`customer_id`
              AND `p`.`id` = `pr`.`property_id`
    		  AND pr.process_status=5 ";
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['adv_search']));
    		$s_where[] = " `c`.`name_kh` LIKE '%{$s_search}%'";
    		$s_where[] = " p.`hardtitle` LIKE '%{$s_search}%'";
    		$s_where[] = " p.`land_address` LIKE '%{$s_search}%'";
    		$s_where[] = " p.`street` LIKE '%{$s_search}%'";
    		$where .=' AND ('.implode(' OR ',$s_where).')';
    	}
    	
    	if($search['status_plong']==1){
    		$where.= " AND s.id = (SELECT rec.sale_id FROM `ln_receiveplong` AS rec WHERE rec.status=1 AND rec.sale_id = s.id ORDER BY rec.id DESC LIMIT 1) ";
    	}
    	if($search['status_plong']==2){
    		$where.= " AND s.id  NOT IN (SELECT rec.sale_id FROM `ln_receiveplong` AS rec WHERE rec.status=1 ) ";
    	}
    	
    	if(!empty($search['client_name']) AND ($search['client_name'])>0){
    		$where.= " AND `c`.`client_id`=".$search['client_name'];
    	}
    	if(($search['branch_id'])>0){
    		$where.= " AND pr.branch_id = ".$search['branch_id'];
    	}
    	if(($search['land_id'])>0){
    		$where.= " AND pr.property_id = ".$search['land_id'];
    	}
    	$where.=$dbp->getAccessPermission("`pr`.`branch_id`");
    
    	$order = " ORDER BY pr.id DESC";
    	$db = $this->getAdapter();
    	return $db->fetchAll($sql.$where.$order);
    }
    public function updateNotePaymentforPlongStep($data){
    	$db = $this->getAdapter();
    	try{
    			$arr = array(
    				'note_payment'=>$data['noted'],
    			);
    			$where=" id = ".$data['id'];
    			$this->_name="ln_processing_plong";
    			$this->update($arr, $where);
    		    return 1;
    	}catch (Exception $e){
    		$err =$e->getMessage();
    		Application_Model_DbTable_DbUserLog::writeMessageError($err);
    	}
    }
}