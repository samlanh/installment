<?php

class Loan_Model_DbTable_DbSetCommission extends Zend_Db_Table_Abstract
{

    protected $_name = 'ln_sale';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    	return $session_user->user_id;
    }
    public function getAllIndividuleLoan($search,$reschedule =null){
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$edit_sale = $tr->translate("EDITSALEONLY");
    	$session_lang=new Zend_Session_Namespace('lang');
    	$lang = $session_lang->lang_id;
    	
    	$str = 'name_en';
    	if($lang==1){
    		$str = 'name_kh';
    	}
    	
    	$from_date =(empty($search['start_date']))? '1': " s.date_setcommission >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " s.date_setcommission <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	$sql=" 
    	SELECT `s`.`id` AS `id`,
    	(SELECT
		     `ln_project`.`project_name`
		   FROM `ln_project`
		   WHERE (`ln_project`.`br_id` = `s`.`branch_id`)
		   LIMIT 1) AS `branch_name`,
		   (SELECT co_khname FROM `ln_staff` WHERE co_id=s.staff_id LIMIT 1) AS staff_name,
		   s.full_commission,
		   ((SELECT COALESCE(SUM(c.`total_amount`),0) FROM `ln_comission` AS c WHERE s.`id` = c.`sale_id` AND c.status=1 LIMIT 1)+(SELECT COALESCE(SUM(cpd.payment_amount),0) FROM `rms_commission_payment_detail` as cpd, rms_commission_payment AS cp WHERE cp.id = cpd.payment_id AND cpd.sale_id=s.id AND cp.status=1 LIMIT 1)) AS totoalCmminssionPaid,
		   CONCAT(COALESCE(`c`.`name_kh`,''),'-', COALESCE(`p`.`land_address`,''),',',COALESCE(`p`.`street`,'')) AS saleProperty,
	   
	    (SELECT $str FROM `ln_view` WHERE key_code =s.payment_id AND type = 25 limit 1) AS paymenttype,
 		`s`.`price_sold`     AS `price_sold`,
 		(SELECT
	     SUM((`cr`.`total_principal_permonthpaid` + `cr`.`extra_payment`)) + ((SELECT COALESCE(SUM(crd.total_amount),0) FROM `ln_credit` AS crd WHERE crd.status=1 AND crd.sale_id = s.id LIMIT 1))
	   FROM `ln_client_receipt_money` `cr`
	   WHERE (`cr`.`sale_id` = `s`.`id`)  LIMIT 1) AS `totalpaid_amount`,   
	   
	   (SELECT
	     (`s`.`price_sold`-SUM(`cr`.`total_principal_permonthpaid` + `cr`.`extra_payment`) - ((SELECT COALESCE(SUM(crd.total_amount),0) FROM `ln_credit` AS crd WHERE crd.status=1 AND crd.sale_id = s.id LIMIT 1)) )
	   FROM `ln_client_receipt_money` `cr`
	   WHERE (`cr`.`sale_id` = `s`.`id`)  LIMIT 1) AS `balance_remain`,   
        `s`.`date_setcommission`        AS `date_setcommission`,
        (SELECT  first_name FROM rms_users WHERE id=s.user_id limit 1 ) AS user_name,
         s.status,
         CASE    
				WHEN  `s`.`is_cancel` = 0 THEN ' '
				WHEN  `s`.`is_cancel` = 1 THEN '".$tr->translate("CANCELED")."'
				END AS modify_date
		FROM ((`ln_sale` `s`
		    JOIN `ln_client` `c`)
		   JOIN `ln_properties` `p`)
		WHERE ((`c`.`client_id` = `s`.`client_id`)
       AND (`p`.`id` = `s`.`house_id`)) 
	   AND s.staff_id>0 AND s.full_commission >0
	   ";
    	
    	$db = $this->getAdapter();
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['adv_search']));
      	 	$s_where[] = " s.receipt_no LIKE '%{$s_search}%'";
      	 	$s_where[] = " s.sale_number LIKE '%{$s_search}%'";
      	 	$s_where[] = " p.land_code LIKE '%{$s_search}%'";
      	 	$s_where[] = " p.land_address LIKE '%{$s_search}%'";
      	 	$s_where[] = " c.client_number LIKE '%{$s_search}%'";
      	 	$s_where[] = " c.name_en LIKE '%{$s_search}%'";
      	 	$s_where[] = " c.name_kh LIKE '%{$s_search}%'";
      	 	$s_where[] = " c.phone LIKE '%{$s_search}%'";
      	 	$s_where[] = " s.price_sold LIKE '%{$s_search}%'";
      	 	$s_where[] = " s.comission LIKE '%{$s_search}%'";
      	 	$s_where[] = " s.total_duration LIKE '%{$s_search}%'";
      	 	$where .=' AND ( '.implode(' OR ',$s_where).')';
    	}
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	if($search['co_id']>0){
//     		$where.= " AND s.staff_id = ".$search['co_id'];
    		$condiction = $dbp->getChildAgency($search['co_id']);
    		if (!empty($condiction)){
    			$where.=" AND s.staff_id IN ($condiction)";
    		}else{
    			$where.=" AND s.staff_id=".$search['co_id'];
    		}
    	}
    	if($search['status']>-1){
    		$where.= " AND s.status = ".$search['status'];
    	}
    	if(!empty($search['land_id']) AND $search['land_id']>-1){
    		$where.= " AND (s.house_id = ".$search['land_id']." OR p.old_land_id LIKE '%".$search['land_id']."%')";
    	}
    	if(($search['client_name'])>0){
    		$where.= " AND `s`.`client_id`=".$search['client_name'];
    	}
    	if(($search['branch_id'])>0){
    		$where.= " AND s.branch_id = ".$search['branch_id'];
    	}
    	if(($search['schedule_opt'])>0){
    		$where.= " AND s.payment_id = ".$search['schedule_opt'];
    	}
    	if(!empty($search['streetlist'])){
    		$where.= " AND p.street = '".$search['streetlist']."'";
    	}	
    	$order = " ORDER BY s.id DESC";
    	
    	$where.=$dbp->getAccessPermission("`s`.`branch_id`");
    	return $db->fetchAll($sql.$where.$order);
    }
    
	public function getSaleNotSetCommission($_data=array()){
		$branch_id = empty($_data['branch_id'])?0:$_data['branch_id'];
		$db = $this->getAdapter();
		$sql= "
				SELECT s.id,
				  CONCAT((SELECT name_kh FROM ln_client WHERE ln_client.client_id=s.`client_id` LIMIT 1),'-',
				  (SELECT CONCAT(p.land_address,',',p.street) FROM ln_properties AS p WHERE p.id = s.house_id LIMIT 1)) AS name
				FROM
				  ln_sale AS s
				WHERE s.status=1 
				    AND s.branch_id=$branch_id 
					AND s.staff_id<=0 AND s.full_commission <=0
				";
		$sql.=" AND is_cancel=0";
		return $db->fetchAll($sql);
	}
	
	public function setCommissionAgency($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
				$ids = explode(',', $data['identity']);
	    		if(!empty($data['identity'])){
	    			foreach ($ids as $i){
						$arr = array(
							'staff_id'=>$data['staff_id'],
							'full_commission'=>$data['commission_amount'.$i],
							'date_setcommission'=>date("Y-m-d")//date set commission
							);
						$where="id = ".$data['sale_id'.$i];
						$this->_name="ln_sale";
						$this->update($arr, $where);
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
	
	public function getSaleSetCommissionBySaleId($SaleId){
		$db = $this->getAdapter();
		$sql= "
				SELECT s.*,
				  CONCAT((SELECT name_kh FROM ln_client WHERE ln_client.client_id=s.`client_id` LIMIT 1),'-',
				  (SELECT CONCAT(p.land_address,',',p.street) FROM ln_properties AS p WHERE p.id = s.house_id LIMIT 1)) AS name,
				  (SELECT
	     SUM((`cr`.`total_principal_permonthpaid` + `cr`.`extra_payment`)) + ((SELECT COALESCE(SUM(crd.total_amount),0) FROM `ln_credit` AS crd WHERE crd.status=1 AND crd.sale_id = s.id LIMIT 1))
	   FROM `ln_client_receipt_money` `cr`
	   WHERE (`cr`.`sale_id` = `s`.`id`)  LIMIT 1) AS `totalpaid_amount`,   
	   
	   (SELECT
	     (`s`.`price_sold`-SUM(`cr`.`total_principal_permonthpaid` + `cr`.`extra_payment`) - ((SELECT COALESCE(SUM(crd.total_amount),0) FROM `ln_credit` AS crd WHERE crd.status=1 AND crd.sale_id = s.id LIMIT 1)) )
	   FROM `ln_client_receipt_money` `cr`
	   WHERE (`cr`.`sale_id` = `s`.`id`)  LIMIT 1) AS `balance_remain`,
	   
	   ((SELECT COALESCE(SUM(c.`total_amount`),0) FROM `ln_comission` AS c WHERE s.`id` = c.`sale_id` AND c.status=1 LIMIT 1)+(SELECT COALESCE(SUM(cpd.payment_amount),0) FROM `rms_commission_payment_detail` as cpd, rms_commission_payment AS cp WHERE cp.id = cpd.payment_id AND cpd.sale_id=s.id AND cp.status=1 LIMIT 1)) AS totoalCmminssionPaid
				FROM
				  ln_sale AS s
				WHERE s.status=1 
				    AND s.id=$SaleId
					 AND s.staff_id>0 AND s.full_commission >0 
					
				";
			$sql.=" LIMIT 1";
		return $db->fetchRow($sql);
	}
	
	public function editSetCommissionAgency($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    		
				if(!empty($data['id'])){
					$arr = array(
						'staff_id'=>$data['staff_id'],
						'full_commission'=>$data['full_commission'],
						);
					$where="id = ".$data['id'];
					$this->_name="ln_sale";
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
}



