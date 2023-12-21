<?php

class Loan_Model_DbTable_DdVerifySale extends Zend_Db_Table_Abstract
{	protected $_name = 'ln_sale';
	
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
    	
    	$from_date =(empty($search['start_date']))? '1': " s.verifyDate >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " s.verifyDate <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	$sql=" 
    	SELECT `s`.`id` AS `id`,
    	(SELECT
		     `ln_project`.`project_name`
		   FROM `ln_project`
		   WHERE (`ln_project`.`br_id` = `s`.`branch_id`)
		   LIMIT 1) AS `branch_name`,
	    `c`.`name_kh`         AS `name_kh`,
	    `c`.`phone`         AS `phone`,
	    `p`.`land_address`    AS `land_address`,
	    `p`.`street`          AS `street`,
	    (SELECT $str FROM `ln_view` WHERE key_code =s.payment_id AND type = 25 limit 1) AS paymenttype,
  		`s`.`price_before`    AS `price_before`,
  		 `s`.`discount_amount` AS `discount_amount`,
 		CONCAT(`s`.`discount_percent`,'%') AS `discount_percent`,
       
 		`s`.`price_sold`     AS `price_sold`,
 		(SELECT
	     SUM((`cr`.`total_principal_permonthpaid` + `cr`.`extra_payment`)) + ((SELECT COALESCE(SUM(crd.total_amount),0) FROM `ln_credit` AS crd WHERE crd.status=1 AND crd.sale_id = s.id LIMIT 1))
	   FROM `ln_client_receipt_money` `cr`
	   WHERE (`cr`.`sale_id` = `s`.`id`)  LIMIT 1) AS `totalpaid_amount`,   
	   
	   (SELECT
	     (`s`.`price_sold`-SUM(`cr`.`total_principal_permonthpaid` + `cr`.`extra_payment`) - ((SELECT COALESCE(SUM(crd.total_amount),0) FROM `ln_credit` AS crd WHERE crd.status=1 AND crd.sale_id = s.id LIMIT 1)) )
	   FROM `ln_client_receipt_money` `cr`
	   WHERE (`cr`.`sale_id` = `s`.`id`)  LIMIT 1) AS `balance_remain`,   
        `s`.`verifyDate`        AS `verifyDate`,
        (SELECT  first_name FROM rms_users WHERE id=s.verify_by limit 1 ) AS user_name,
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
			AND s.is_verify = 1
	   
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
	
	public function addVerifySale($_data){
		try{
			$db= $this->getAdapter();
			$arr1 = array(
					'is_verify'	  		=> 1,
					'price_before'	  	=> $_data['sold_price'],
					'discount_amount'	  	=> 0,
					'discount_percent'	  	=> 0,
					'price_sold'	  	=> $_data['sold_price'],
					'paid_amount'	  	=> $_data['totalPrincipalpaid'],
					'balance'	  => $_data['totalBalance'],
					'verify_by'	  => $this->getUserId(),
					'verifyDate'	  => date('Y-m-d'),
				);
			$saleId = empty($_data['saleId']) ? 0 : $_data['saleId'];
			$where="id = ".$saleId;
			$this->_name="ln_sale";
			$this->update($arr1, $where);
			
			$isCompleated = 0;
			if($_data['totalBalance']<=0){
				$isCompleated=1;
			}
			$arrClientReceipt = array(
				
				'outstanding'                   =>	$_data['sold_price'],//ប្រាក់ដើមមុនបង់
    			'selling_price'    				=>  $_data['sold_price'],
    			'total_principal_permonth'		=>	$_data["totalPrincipalpaid"],//ប្រាក់ដើមត្រូវបង់
    			'total_principal_permonthpaid'	=>	$_data["totalPrincipalpaid"],//ប្រាក់ដើមត្រូវបង់
    			
    			'total_payment'					=>	$_data["totalPrincipalpaid"],//ប្រាក់ត្រូវបង់ok
    			'principal_amount'				=>	$_data['totalPrincipalpaid'],//ប្រាក់ដើមនៅសល់បន្ទប់ពីបង់
    			'balance'						=>	$_data["totalBalance"],
				
    			'recieve_amount'				=>	$_data["totalPrincipalpaid"],//ok
    			'amount_payment'				=>	$_data["totalPrincipalpaid"],//brak ban borng
    			'return_amount'					=>	0,//ok
				
    			'user_id'						=>	$this->getUserId(),
    			'status'						=>	1,
    			'is_completed'					=>	$isCompleated,
    		    'field3'						=>  3,
				);
			$whereClientReceipt=" sale_id = ".$saleId;
			$this->_name="ln_client_receipt_money";
			$this->update($arrClientReceipt, $whereClientReceipt);
			
			$dbc = new Application_Model_DbTable_DbGlobal();
			if (!empty($_data['identity1'])){
				$part= PUBLIC_PATH.'/images/document/expense/';
				if (!file_exists($part)) {
					mkdir($part, 0777, true);
				}
				
				$identity = $_data['identity1'];
				$ids = explode(',', $identity);
				$image_name="";
				$photo="";
				foreach ($ids as $i){
					if(!empty($_FILES['attachment'.$i])){
						$name = $_FILES['attachment'.$i]['name'];
						if (!empty($name)){
							$ss = 	explode(".", $name);
							$newDocName = "document_payment_".date("Y").date("m").date("d").time().$i.".".end($ss);
							$newDocConvert = $dbc->resizeImase($_FILES['attachment'.$i], $part,$newDocName);
							if(!empty($newDocConvert)){
								$photo = $newDocConvert;
								$arr = array(
										'exspense_id'		=>$saleId,
										'document_name'		=>$photo,
										'title'				=>$_data['title_'.$i],
										'date'   			=> date('Y-m-d H:i:s'),
										'documentforType'   =>4, //documentforType = 4 is document of verify sale
								);
								$this->_name = "ln_expense_document";
								$this->insert($arr);
							}
						}
					}
				}
			}
			
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	
}

