<?php
class Report_Model_DbTable_DbParamater extends Zend_Db_Table_Abstract
{
      
    public function getALLstaff($search = null){
    	$db = $this->getAdapter();
    	$where="";
    	$sql="SELECT co_id,co_code,co_khname,co_firstname,
    	(SELECT name_kh FROM ln_view WHERE type = 11 AND key_code=sex limit 1 ) AS sex
    	,email,contract_no,shift,workingtime,
    	tel,basic_salary,national_id,address,pob,
    	(SELECT project_name FROM ln_project WHERE br_id = branch_id limit 1) AS branch_name,
    	note,
		(SELECT  first_name FROM rms_users WHERE id = ln_staff.user_id LIMIT 1 ) AS user_name
    	FROM ln_staff WHERE 1 ";
    	$Other =" ORDER BY co_id DESC ";
    	if($search['co_khname']>0){
    		$where.= " AND co_id = ".$search['co_khname'];
    	}
		if($search['co_sex']>-1){
    		$where.= " AND sex = ".$search['co_sex'];
    	}
    	if($search['branch_id']>0){
    		$where.= " AND branch_id = ".$search['branch_id'];
    	}
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = addslashes(trim($search['adv_search']));
    		$s_where[] =" co_code LIKE '%{$s_search}%'";
    		$s_where[]=" co_khname LIKE '%{$s_search}%'";
    		$s_where[]=" co_firstname LIKE '%{$s_search}%'";
    		$s_where[]=" email LIKE '%{$s_search}%'";
    		$s_where[]=" tel LIKE '%{$s_search}%'";
    		$s_where[]=" address LIKE '%{$s_search}%'";
    		$s_where[]=" national_id LIKE '%{$s_search}%'";
    		$where .=' AND '.implode(' OR ',$s_where). '';
    	}
    	return $db->fetchAll($sql.$where.$Other);
    }
   

    function getAllProperties($search=null){
    		$db = $this->getAdapter();
    		$to_enddate = (empty($search['end_date']))? '1': " s.buy_date <= '".$search['end_date']." 23:59:59'";
    		$sql = "SELECT p.`id`,
    		   (SELECT project_name FROM ln_project WHERE br_id = p.`branch_id` limit 1) AS branch_name,
    		    p.`land_code`,p.`land_address`,p.`property_type`,p.`street`,p.note,
				(SELECT t.type_nameen FROM `ln_properties_type` AS t WHERE t.id = p.`property_type` LIMIT 1) AS pro_type,
				p.south,p.north,p.west,p.east,
				p.`width`,p.`height`,p.`land_size`,p.`price`,p.`land_price`,p.`house_price`, ";
    			$sql.="CASE
				WHEN  is_lock =1 THEN (SELECT 1 FROM `ln_sale` AS s WHERE s.house_id =  p.`id`  AND s.status=1 AND s.is_cancel = 0 AND $to_enddate LIMIT 1)
				ELSE  0
				END AS is_lock,
				p.`width`,p.`height`,p.`land_size`,p.`price`,p.`land_price`,p.`house_price`,p.`is_lock`, ";		

    		$sql.="(SELECT first_name FROM `rms_users` WHERE id=p.user_id LIMIT 1) AS user_name,
					(SELECT s.price_sold FROM `ln_sale` AS s WHERE s.house_id =  p.id  AND s.is_cancel = 0  LIMIT 1) AS price_sold
				
			 FROM `ln_properties` AS p WHERE p.`status`=1 ";
    		
    		$from_date =(empty($search['start_date']))? '1': " create_date >= '".$search['start_date']." 00:00:00'";
    		$to_date = (empty($search['end_date']))? '1': " create_date <= '".$search['end_date']." 23:59:59'";
    		$where=" AND ".$from_date." AND ".$to_date;
    		
    		$dbp = new Application_Model_DbTable_DbGlobal();
    		$sql.=$dbp->getAccessPermission("p.`branch_id`");
    		
    		if(!empty($search['property_type'])){
    			$where.= " AND p.`property_type` = ".$search['property_type'];
    		}
    		if($search['type_property_sale']>-1){
    			$where.= " AND p.`is_lock` = ".$search['type_property_sale'];
    		}
    		if($search['branch_id']>0){
    			$where.= " AND p.`branch_id` = ".$search['branch_id'];
    		}
    		if(!empty($search['adv_search'])){
    			$s_where=array();
    			$s_search= addslashes(trim($search['adv_search']));
    			$s_where[]=" p.`land_address` LIKE '%{$s_search}%'";
				$s_where[]=" p.street LIKE '%{$s_search}%'";
    			$where.=' AND ('.implode(' OR ',$s_where).')';
    		}
    		if(!empty($search['streetlist'])){
    			$where.= " AND street ='".$search['streetlist']."'";
    		}

//     		$where.=" ORDER BY p.`property_type`,p.`street` ASC, cast(land_address as unsigned) "; 
// 			$where.=" IF(land_address RLIKE '^[a-z]', 1, 2), land_address ASC";
    		$where.= " ORDER BY p.branch_id,p.`property_type`,p.`street` ASC, LENGTH(land_address), land_address ASC  ";
    		
    		return $db->fetchAll($sql.$where);
    	}
    	function getCancelSale($search=null){
    		$db = $this->getAdapter();
    		$from_date =(empty($search['from_date_search']))? '1': "c.`create_date` >= '".$search['from_date_search']." 00:00:00'";
    		$to_date = (empty($search['to_date_search']))? '1': "c.`create_date` <= '".$search['to_date_search']." 23:59:59'";
    		$where = " AND ".$from_date." AND ".$to_date;
			
			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    		$sql="
				SELECT 
					c.`id`,c.return_back,
					(SELECT project_name FROM `ln_project` WHERE br_id=c.`branch_id` LIMIT 1) AS `project_name`,
					c.paid_amount,c.installment_paid,c.reason,c.`create_date`,
					s.`sale_number`,
					s.price_sold,
					clie.`client_number`,
					(clie.`name_kh`) AS client_name,
					clie.`phone` AS clientPhone,
					pro.`land_code`,
					(SELECT pt.`type_nameen` FROM `ln_properties_type` AS pt WHERE pt.`id` = pro.`property_type` LIMIT 1) AS type_name,
					pro.`property_type`,pro.`land_address`,pro.`street`,
					(SELECT first_name FROM `rms_users` WHERE id=c.user_id LIMIT 1) AS user_name,
					c.cancel_type,
					c.condition_return,
					c.date_for_return,
					CASE
						WHEN  c.cancel_type =1 THEN '".$tr->translate('CANCEL_WITHOUT_RETURN')."'
						WHEN  c.cancel_type =2 THEN '".$tr->translate('CANCEL_WITH_RETURN_AMOUNT')."'
						ELSE  ''
					END AS cancelTypeTitle,
					(SELECT v.name_kh FROM `ln_view` AS v WHERE v.`type` = 32 AND v.key_code = c.condition_return LIMIT 1) AS conditionReturnTitle,
					(SELECT SUM(e.total_amount) FROM ln_expense AS e WHERE e.cancelSale_id = c.id AND e.status=1 LIMIT 1) AS totalReturnPaid
				
				FROM `ln_sale_cancel` AS c, 
					`ln_sale` AS s, 
					`ln_properties` AS pro,
					`ln_client` AS clie
				WHERE s.`id` = c.`sale_id` AND pro.`id` = c.`property_id` AND
					clie.`client_id` = s.`client_id` AND c.status=1";
    		
    		$dbp = new Application_Model_DbTable_DbGlobal();
    		$sql.=$dbp->getAccessPermission("c.branch_id");
    		
    		$order = " ORDER BY c.`branch_id` DESC,c.id DESC ";
    		if($search['branch_id_search']>-1){
    			$where.= " AND c.branch_id = ".$search['branch_id_search'];
    		}
    		if(!empty($search['property_type'])){
    			$where.= " AND pro.`property_type` = ".$search['property_type'];
    		}
    		if($search['client_name']>0){
    			$where.= " AND s.`client_id` = ".$search['client_name'];
    		}
    		if(!empty($search['adv_search'])){
    			$s_where = array();
    			$s_search = addslashes(trim($search['adv_search']));
    			$s_where[] = " clie.`client_number` LIKE '%{$s_search}%'";
    			$s_where[] = " clie.`name_kh` LIKE '%{$s_search}%'";
    			$s_where[] = " s.`sale_number` LIKE '%{$s_search}%'";
    			$s_where[] = " pro.`land_code` LIKE '%{$s_search}%'";
    			$s_where[] = " pro.`land_address` LIKE '%{$s_search}%'";
    			$s_where[] = " pro.`street` LIKE '%{$s_search}%'";
    			$s_where[] = " pro.`reason` LIKE '%{$s_search}%'";
    			$where .=' AND ('.implode(' OR ',$s_where).')';
    		}
    		if(!empty($search['cancelTypeSearch'])){
    			$where.= " AND c.cancel_type = ".$search['cancelTypeSearch'];
    		}
    		return $db->fetchAll($sql.$where.$order);
    		
    	}
    	
    	function getTermCodiction(){
    		$db =$this->getAdapter();
    		$sql="SELECT * FROM `ln_termcondiction` AS t WHERE t.`status`=1 LIMIT 1";
    		return $db->fetchRow($sql);
    	}
    	function getSaleHistory($search=null){
    		$db= $this->getAdapter();
			
			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
			$session_lang=new Zend_Session_Namespace('lang');
			$lang = $session_lang->lang_id;
			
			$str = 'name_en';
			if($lang==1){
				$str = 'name_kh';
			}
		
			$from_date =(empty($search['start_date']))? '1': " s.buy_date >= '".$search['start_date']." 00:00:00'";
			$to_date = (empty($search['end_date']))? '1': " s.buy_date <= '".$search['end_date']." 23:59:59'";
			$where = " AND ".$from_date." AND ".$to_date;
			$sql=" 
				SELECT 
					`s`.`id` AS `id`,
					`s`.`branch_id`,
					`s`.`house_id`,
					(SELECT
						 `ln_project`.`project_name`
					   FROM `ln_project`
					   WHERE (`ln_project`.`br_id` = `s`.`branch_id`)
					   LIMIT 1) AS `branch_name`,
					 c.client_number,
					`c`.`name_kh`         AS `name_kh`,
					`c`.`phone`         AS `phone`,
					`p`.`land_address`    AS `land_address`,
					`p`.`street`          AS `street`,
					(SELECT  pt.type_nameen FROM ln_properties_type AS pt  WHERE pt.`id` = p.property_type LIMIT 1) AS propertyTypeName,
					(SELECT $str FROM `ln_view` WHERE key_code =s.payment_id AND type = 25 limit 1) AS paymenttype,
					`s`.`price_before`    AS `price_before`,
					`s`.`discount_amount` AS `discount_amount`,
					CONCAT(`s`.`discount_percent`,'%') AS `discount_percent`,
			   
					`s`.`price_sold`     AS `price_sold`,
					(SELECT SUM((`cr`.`total_principal_permonthpaid` + `cr`.`extra_payment`)) + ((SELECT COALESCE(SUM(crd.total_amount),0) FROM `ln_credit` AS crd WHERE crd.status=1 AND crd.sale_id = s.id LIMIT 1))
					   FROM `ln_client_receipt_money` `cr`
					   WHERE (`cr`.`sale_id` = `s`.`id`)  LIMIT 1) AS `totalpaid_amount`,   
				   
			   (SELECT
				 (`s`.`price_sold`-SUM(`cr`.`total_principal_permonthpaid` + `cr`.`extra_payment`) - ((SELECT COALESCE(SUM(crd.total_amount),0) FROM `ln_credit` AS crd WHERE crd.status=1 AND crd.sale_id = s.id LIMIT 1)) )
			   FROM `ln_client_receipt_money` `cr`
			   WHERE (`cr`.`sale_id` = `s`.`id`)  LIMIT 1) AS `balance_remain`,   
				`s`.`buy_date`        AS `buy_date`,
				(SELECT  first_name FROM rms_users WHERE id=s.user_id limit 1 ) AS user_name,
				 s.status,
				 CASE    
						WHEN  `s`.`is_cancel` = 0 THEN ' '
						WHEN  `s`.`is_cancel` = 1 THEN '".$tr->translate("CANCELED")."'
						END AS isCancelTitle,
				is_cancel						
				FROM ((`ln_sale` `s`
					JOIN `ln_client` `c`)
				   JOIN `ln_properties` `p`)
				WHERE ((`c`.`client_id` = `s`.`client_id`)
			   AND (`p`.`id` = `s`.`house_id`)) ";
	   
    		$dbp = new Application_Model_DbTable_DbGlobal();
    		$where.=$dbp->getAccessPermission("s.branch_id");
    		
    		if($search['branch_id']>0){
    			$where.= " AND s.branch_id = ".$search['branch_id'];
    		}
    		if (!empty($search['adv_search'])){
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
    		if($search['land_id']>0){
				$where.= " AND (s.house_id = ".$search['land_id']." OR p.old_land_id LIKE '%".$search['land_id']."%')";
            }
    		if(!empty($search['client_name'])){
    			$where.= " AND `s`.`client_id` = ".$search['client_name'];
    		}
    		$order = " ORDER BY s.id DESC";
    		return $db->fetchAll($sql.$where.$order);
    	}
    function getFirstDepositAgreement($sale_id){
    	$db = $this->getAdapter();
    	$sql="SELECT crm.`recieve_amount` AS recieve_amount,
    			crm.date_pay,
    			(SELECT percent_agree FROM `ln_saleschedule` WHERE is_installment = 1 AND sale_id=$sale_id ORDER BY id ASC  LIMIT 1) AS percent_agree,
    			(SELECT percent FROM `ln_saleschedule` WHERE is_installment = 1 AND sale_id=$sale_id ORDER BY id DESC  LIMIT 1) AS percent,
    			(SELECT second_depostit FROM  ln_sale WHERE id = $sale_id ) AS  second_depostit,
    			(SELECT second_depostit FROM  ln_sale WHERE id = $sale_id ) AS  second_depostit,
    			(SELECT validate_date FROM  ln_sale WHERE id = $sale_id ) AS  validate_date
    		FROM `ln_client_receipt_money` AS crm WHERE crm.status=1 AND crm.field3=1
				AND sale_id = $sale_id ORDER BY crm.id ASC";
    	return $db->fetchRow($sql);
    }
   function getAgreementBySaleID($id=null){//bppt,natha,longny,moul mith
    		$db = $this->getAdapter();
			
			$dbp = new Application_Model_DbTable_DbGlobal();
			$currentUserId=$dbp->getUserId();
			$currentUserId = empty($currentUserId)?0:$currentUserId;
		
    		$sql="SELECT
				  `s`.`id`              AS `id`,
				  `s`.`sale_number`     AS `sale_number`,
				  `s`.`payment_id`      AS `payment_id`,
				  `s`.`branch_id`       AS `branch_id`,
				  `s`.`client_id`       AS `client_id`,
				  `s`.`price_before`    AS `price_before`,
				  `s`.`discount_amount` AS `discount_amount`,
				   s.discount_percent,
				  `s`.`price_sold`      AS `price_sold`,
				   s.oversold_price,
				  `s`.`other_fee`       AS `other_fee`,
				  `s`.`admin_fee`       AS `admin_fee`,
				  `s`.`paid_amount`     AS `paid_amount`,
				  `s`.`balance`         AS `balance`,
				  `s`.`amount_collect`  AS `amount_collect`,
				  `s`.`interest_rate`   AS `interest_rate`,
				  `s`.`total_duration`  AS `total_duration`,
				  `s`.`first_payment`   AS `first_payment`,
				   s.lastpayment_amount,
				   s.is_reschedule,
				   s.land_price,
				   s.amount_build,
				   s.build_start,
				   s.buy_date,
				   s.`startcal_date`,
				   s.`end_line`,
    			   s.validate_date,
				   s.agreement_date,
				   s.note_agreement,
				   s.is_verify,
				   s.store_number,
				    s.second_depostit,
				    s.witness_i,
				    s.witness_ii,
				   (SELECT CONCAT(last_name,' ',first_name) FROM rms_users WHERE id = s.user_id LIMIT 1 ) AS user_name,
				   (SELECT CONCAT(last_name,' ',first_name) FROM rms_users WHERE rms_users.id = $currentUserId LIMIT 1 ) AS currentUserName,
				   (SELECT co_khname FROM `ln_staff` WHERE co_id=s.staff_id LIMIT 1) AS staff_name,
				   (SELECT name_kh FROM `ln_view` WHERE type=25 and key_code=s.payment_id limit 1) AS payment_type,
				   
				   (SELECT name_kh FROM `ln_view` WHERE type=25 and key_code=s.pre_schedule_opt limit 1) AS preScheduleOptTitle,
				   s.pre_schedule_opt,
				   s.pre_percent_payment,
				   s.pre_percent_installment,
				   s.pre_amount_month,
				   s.pre_amount_year,
				   s.pre_fix_payment,
				   
				   s.agreement_for,
				   (SELECT name_kh FROM `ln_view` WHERE type=31 and key_code=s.agreement_for limit 1) AS titlePlong,
				  `p`.`project_name`,
				  `p`.`map_url` AS projectNameEng,
				  `p`.`contact_contruction` AS projectLocationEng,
				  `p`.`logo` AS project_logo,
				  `p`.`branch_tel`,
				  `p`.`other` as p_other,
			      `p`.`br_address` AS `project_location`,
				  
				   (SELECT ci.nameKh FROM `ln_contract_issuer` AS ci WHERE ci.id=s.`contract_issuer_id` LIMIT 1) AS project_manager_namekh,
				   (SELECT ci.nationality FROM `ln_contract_issuer` AS ci WHERE ci.id=s.`contract_issuer_id` LIMIT 1) AS project_manager_nationality,
				   (SELECT ci.nationId FROM `ln_contract_issuer` AS ci WHERE ci.id=s.`contract_issuer_id` LIMIT 1) AS project_manager_nation_id,
				   (SELECT ci.nationIdIssueDate FROM `ln_contract_issuer` AS ci WHERE ci.id=s.`contract_issuer_id` LIMIT 1) AS p_nationid_issue,
				   (SELECT ci.currentAddress FROM `ln_contract_issuer` AS ci WHERE ci.id=s.`contract_issuer_id` LIMIT 1) AS project_manager_p_current_address,
				   (SELECT ci.position FROM `ln_contract_issuer` AS ci WHERE ci.id=s.`contract_issuer_id` LIMIT 1) AS position,
				   (SELECT ci.tel FROM `ln_contract_issuer` AS ci WHERE ci.id=s.`contract_issuer_id` LIMIT 1) AS w_manager_tel,
				   (SELECT ci.tel FROM `ln_contract_issuer` AS ci WHERE ci.id=s.`contract_issuer_id` LIMIT 1) AS manager_tel,
				   (SELECT ci.dob FROM `ln_contract_issuer` AS ci WHERE ci.id=s.`contract_issuer_id` LIMIT 1) AS manager_dob,
				   (SELECT ci.sex FROM `ln_contract_issuer` AS ci WHERE ci.id=s.`contract_issuer_id` LIMIT 1) AS p_sex,
				   (SELECT name_kh FROM `ln_view` WHERE type=11 and key_code=(SELECT ci.sex FROM `ln_contract_issuer` AS ci WHERE ci.id=s.`contract_issuer_id` LIMIT 1) limit 1) AS manager_sex ,
				  
				   (SELECT ci.nameKhWith FROM `ln_contract_issuer` AS ci WHERE ci.id=s.`contract_issuer_id` LIMIT 1) AS w_managername1,
				   (SELECT ci.nameKhWith FROM `ln_contract_issuer` AS ci WHERE ci.id=s.`contract_issuer_id` LIMIT 1) AS w_manager_namekh,
				   (SELECT ci.nationalityWith FROM `ln_contract_issuer` AS ci WHERE ci.id=s.`contract_issuer_id` LIMIT 1) AS w_manager_nationality,
				   (SELECT ci.nationIdWith FROM `ln_contract_issuer` AS ci WHERE ci.id=s.`contract_issuer_id` LIMIT 1) AS w_manager_nation_id,
				   (SELECT ci.nationIdIssueDateWith FROM `ln_contract_issuer` AS ci WHERE ci.id=s.`contract_issuer_id` LIMIT 1) AS w_nation_id_issue,
				   (SELECT ci.currentAddressWith FROM `ln_contract_issuer` AS ci WHERE ci.id=s.`contract_issuer_id` LIMIT 1) AS w_current_address,
				   (SELECT ci.positionWith FROM `ln_contract_issuer` AS ci WHERE ci.id=s.`contract_issuer_id` LIMIT 1) AS w_position,
				   (SELECT ci.positionWith FROM `ln_contract_issuer` AS ci WHERE ci.id=s.`contract_issuer_id` LIMIT 1) AS w_manager_position,
				   (SELECT ci.positionWith FROM `ln_contract_issuer` AS ci WHERE ci.id=s.`contract_issuer_id` LIMIT 1) AS w_manager_position1,
				   (SELECT ci.telWith FROM `ln_contract_issuer` AS ci WHERE ci.id=s.`contract_issuer_id` LIMIT 1) AS w_manager_tel1,
				   (SELECT ci.telWith FROM `ln_contract_issuer` AS ci WHERE ci.id=s.`contract_issuer_id` LIMIT 1) AS with_manager_tel,
				   (SELECT ci.dobWith FROM `ln_contract_issuer` AS ci WHERE ci.id=s.`contract_issuer_id` LIMIT 1) AS w_dob,
				   (SELECT ci.sexWith FROM `ln_contract_issuer` AS ci WHERE ci.id=s.`contract_issuer_id` LIMIT 1) AS w_sex,
				   (SELECT name_kh FROM `ln_view` WHERE type=11 and key_code=(SELECT ci.sexWith FROM `ln_contract_issuer` AS ci WHERE ci.id=s.`contract_issuer_id` LIMIT 1) limit 1) AS sc_manager_sex ,
				  

                  `c`.`client_number` AS `client_code`,
     			  `c`.`name_kh` AS `client_namekh`,
     			  `c`.`name_en` AS `client_nameen`,
     			   c.dob,
     			   c.dob AS client_dob ,
     			   c.hname_kh,
     			   c.hname_kh AS with_client_name,
     			   c.sex,
    			   c.ksex,
    			    c.join_type,
    			   (SELECT name_kh FROM `ln_view` WHERE type=11 and key_code=c.ksex limit 1) AS partner_gender,
     			   c.dob_buywith,
     			   c.rid_no,
     			   c.rid_no AS with_client_nation_id,
     			   (SELECT name_kh FROM `ln_view` WHERE TYPE=11 AND key_code=c.`sex` LIMIT 1) AS client_sex,
     			   (SELECT name_kh FROM `ln_view` WHERE type=11 and key_code=c.sex limit 1) AS sexKh,
				   
     			   (SELECT name_kh FROM `ln_view` WHERE type=23 and key_code=c.joint_doc_type limit 1) AS joint_doc_type,
     			   (SELECT name_en FROM `ln_view` WHERE type=23 and key_code=c.joint_doc_type limit 1) AS jointDocTypeEng,
     			   (SELECT name_kh FROM `ln_view` WHERE type=23 and key_code=c.client_d_type limit 1) AS client_d_type,
     			   (SELECT name_en FROM `ln_view` WHERE type=23 and key_code=c.client_d_type limit 1) AS clientDocTypeEng,
     			   
     			   c.p_nationality,
     			   c.p_nationality AS with_client_nationlity ,
  				  `c`.`nationality` AS `client_nationality`,
     			  `c`.`nation_id` AS `client_nation_id`,
     			   c.client_issuedateid,
     			   c.join_issuedateid,
                  `c`.`phone` AS `client_phone`,
  				  `c`.`house` AS `client_house_no`,
                  `c`.`street` AS `client_street`,
                  c.phone,
                  c.lphone as with_phone,
                  c.ghouse as with_house,
                  c.dstreet AS w_street,
                   c.arid_no AS witnesses,
                   c.remark AS clientNote,
				   
				(SELECT `village`.`village_namekh` FROM `ln_village` `village` WHERE (`village`.`vill_id` = `c`.`qvillage`) LIMIT 1) AS `joint_village`,
				(SELECT `village`.`village_name` FROM `ln_village` `village` WHERE (`village`.`vill_id` = `c`.`qvillage`) LIMIT 1) AS `w_client_village_en`,
    			(SELECT `comm`.`commune_namekh` FROM `ln_commune` `comm` WHERE (`comm`.`com_id` = `c`.`dcommune`) LIMIT 1) AS `join_commune`,
    			(SELECT `comm`.`commune_namekh` FROM `ln_commune` `comm` WHERE (`comm`.`com_id` = `c`.`dcommune`) LIMIT 1) AS `w_client_commune_kh`,
    			(SELECT `comm`.`commune_name` FROM `ln_commune` `comm` WHERE (`comm`.`com_id` = `c`.`dcommune`) LIMIT 1) AS `w_client_commune_en`,
    			(SELECT `dist`.`district_namekh` FROM `ln_district` `dist` WHERE (`dist`.`dis_id` = `c`.`adistrict`) LIMIT 1) AS `join_district`,
    			(SELECT `dist`.`district_namekh` FROM `ln_district` `dist` WHERE (`dist`.`dis_id` = `c`.`adistrict`) LIMIT 1) AS `w_client_districtkh`,
    			(SELECT `dist`.`district_name` FROM `ln_district` `dist` WHERE (`dist`.`dis_id` = `c`.`adistrict`) LIMIT 1) AS `w_client_district_en`,
    			(SELECT `provi`.`province_kh_name` FROM `ln_province` `provi` WHERE (`provi`.`province_id` = `c`.`cprovince`) LIMIT 1) AS `join_province`,	
    			(SELECT `provi`.`province_kh_name` FROM `ln_province` `provi` WHERE (`provi`.`province_id` = `c`.`cprovince`) LIMIT 1) AS `w_client_province_kh`,	
    			(SELECT `provi`.`province_en_name` FROM `ln_province` `provi` WHERE (`provi`.`province_id` = `c`.`cprovince`) LIMIT 1) AS `w_client_province_en`,	
    				
				(SELECT `village`.`village_namekh` FROM `ln_village` `village` WHERE (`village`.`vill_id` = `c`.`village_id`) LIMIT 1) AS `client_village_kh`,
				(SELECT `village`.`village_name` FROM `ln_village` `village` WHERE (`village`.`vill_id` = `c`.`village_id`) LIMIT 1) AS `clientVillageEng`,
				(SELECT `comm`.`commune_namekh` FROM `ln_commune` `comm` WHERE (`comm`.`com_id` = `c`.`com_id`) LIMIT 1) AS `client_commune_kh`,
				(SELECT `comm`.`commune_name` FROM `ln_commune` `comm` WHERE (`comm`.`com_id` = `c`.`com_id`) LIMIT 1) AS `clientCommuneEng`,
				(SELECT `dist`.`district_namekh` FROM `ln_district` `dist` WHERE (`dist`.`dis_id` = `c`.`dis_id`) LIMIT 1) AS `client_districtkh`,
				(SELECT `dist`.`district_name` FROM `ln_district` `dist` WHERE (`dist`.`dis_id` = `c`.`dis_id`) LIMIT 1) AS `clientDistrictEng`,
				(SELECT `provi`.`province_kh_name` FROM `ln_province` `provi` WHERE (`provi`.`province_id` = `c`.`pro_id`) LIMIT 1) AS `client_province_kh`,
				(SELECT `provi`.`province_en_name` FROM `ln_province` `provi` WHERE (`provi`.`province_id` = `c`.`pro_id`) LIMIT 1) AS `clientProvinceEng`,
				
				(SELECT name_kh FROM `ln_view` WHERE TYPE=11 AND key_code=c.`ksex` LIMIT 1) AS client_buywith_sex,
				(SELECT name_kh FROM `ln_view` WHERE TYPE=11 AND key_code=c.`ksex` LIMIT 1) AS with_client_sex ,
				c.hname_kh 		AS w_client_namekh,
				c.dob_buywith 	AS w_client_dob_buywith,
				c.p_nationality AS w_client_nationality,
				c.`ghouse` 		AS w_client_house,
				c.lphone 		AS w_client_phone,
				  
				(SELECT prt.`type_nameen` FROM `ln_properties_type` AS prt WHERE prt.`id` =`pp`.`property_type` LIMIT 1) AS `property_type_en`,
				(SELECT prt.`type_namekh` FROM `ln_properties_type` AS prt WHERE prt.`id` =`pp`.`property_type` LIMIT 1) AS `property_type_kh`,
				(SELECT prt.`note` 			FROM `ln_properties_type` AS prt WHERE prt.`id` =`pp`.`property_type` LIMIT 1) AS `propertyTypeNote`,
				(SELECT prt.`serviceFee` 	FROM `ln_properties_type` AS prt WHERE prt.`id` =`pp`.`property_type` LIMIT 1) AS `propertyTypeFee`,
				(SELECT prt.`serviceFeeYear` FROM `ln_properties_type` AS prt WHERE prt.`id` =`pp`.`property_type` LIMIT 1) AS `propertyTypeFeeYearly`,
				
			   
				`pp`.`land_size` AS `property_land_size`,
				
				 pp.`hardtitle`,
				`pp`.`hardtitle` AS `layoutNumber`,
				
				`pp`.`width` AS `property_width`,
				`pp`.`height` AS `property_height`,
				 pp.`land_size`,
				 pp.`land_size` AS `property_size`,
				
				 
				`pp`.`property_type`,
				`pp`.`type_tob`,
				`pp`.`land_code` AS `property_code`,
				`pp`.`land_address` AS `property_title`,
				 pp.`street` AS `property_street`,
				 
				 pp.land_width,
				 pp.land_height,
				 pp.`full_size`,
				 
				 pp.land_width AS houseWidth,
				 pp.land_height AS houseHeight,
				 pp.`full_size` AS houseFullSize,
				 
				 pp.floor,
				 pp.living,
				 pp.`bedroom`,
				 pp.dinnerroom,
				 pp.buidingyear,
				 pp.buidingyear AS periodBuildDescription,
				 pp.`parkingspace`,
				 pp.`note` as `property_note`,
				 pp.`north` AS border_north,
				 pp.`south` AS border_south,
				 pp.`east` AS border_east,
				 pp.`west` AS border_west,
				 pp.`old_land_id`,
				(SELECT `a`.`description` FROM `ln_sale_conditionagreement` `a` WHERE (`a`.`saleId` = `s`.`id`) LIMIT 1) AS `additinalContract`,
				gendertitle,
				gendertitle1 
		FROM 
			`ln_sale` AS `s`,
			ln_project AS p ,
			`ln_client` AS c,
			ln_properties as pp
			
			WHERE 
			`p`.`br_id` = `s`.`branch_id` 
			AND `c`.`client_id` = `s`.`client_id`
			AND `pp`.`id` = `s`.`house_id`
			AND s.id=".$id;
    		
    		$dbp = new Application_Model_DbTable_DbGlobal();
    		$sql.=$dbp->getAccessPermission("`s`.`branch_id`");
    		return $db->fetchRow($sql);
    }
//     function addOversoldPrice($data){
//     	$_db = $this->getAdapter();
//     	$this->_name='ln_sale';
//     	try{
//     		$_arr = array(
//     			'oversold_price' =>$data['oversold_price'],
//     		);
//     		$where="id = ".$data['sale_id'];
//     		$this->update($_arr, $where);
//     	}catch(Exception $e){
//     		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
//     	}
//     }
    function getIssueHouseAgreement($id=null){//bppt,natha,longny,moul mith
    	$db = $this->getAdapter();
    	$sql="
    	SELECT
    	s.buy_date,
    	s.agreement_date,
    	(SELECT CONCAT(last_name,' ',first_name) FROM rms_users WHERE rms_users.id = ih.user_id LIMIT 1 ) AS user_name,
    	(SELECT co_khname FROM `ln_staff` WHERE co_id=s.staff_id LIMIT 1) AS staff_name,
    	 `p`.`logo` AS project_logo,
		`p`.`project_name`,
    	 p.p_sex,
    	`p`.`br_address` AS `project_location`,
    	`p`.`p_manager_namekh` AS `project_manager_namekh`,
    	`p`.`p_manager_nation_id` AS `project_manager_nation_id`,
    	`p`.`p_current_address` AS `project_manager_p_current_address`,
		`p`.`branch_tel`,
		`p`.`other` as p_other,
		`p`.`p_dob` as pManagerDob,
		`p`.`p_nationid_issue` as pManagerNationidIssue,
    	`c`.`name_kh` AS `client_namekh`,
    	c.hname_kh,
    	`c`.`nationality` AS `client_nationality`,
    	`c`.`nation_id` AS `client_nation_id`,
    	`c`.`house` AS `client_house_no`,
    	`c`.`street` AS `client_street`,
    	`c`.`sex` AS `clientSex`,
    	`c`.`ksex` AS `withClientSex`,
    	c.dob,
    	c.dob_buywith,
    	c.phone,
    	c.phone AS client_phone,
		c.lphone AS w_client_phone,
    	c.dstreet AS w_street,
    	c.client_issuedateid AS client_issuedateid,
		
		 (SELECT name_kh FROM `ln_view` WHERE type=23 and key_code=c.client_d_type limit 1) AS client_d_type,
		 
		(SELECT
    	`village`.`village_namekh`
    	FROM `ln_village` `village`
    	WHERE (`village`.`vill_id` = `c`.`village_id`
    	)
    	LIMIT 1) AS `client_village_kh`,
    	(SELECT
    	`comm`.`commune_namekh` FROM `ln_commune` `comm`
    	WHERE (`comm`.`com_id` = `c`.`com_id`)
    	LIMIT 1) AS `client_commune_kh`,
    	(SELECT
    	`provi`.`province_kh_name`
    	FROM `ln_province` `provi`
    	WHERE (`provi`.`province_id` = `c`.`pro_id`)
    	LIMIT 1) AS `client_province_kh`,
    	
    	(SELECT
				     `dist`.`district_namekh`
				   FROM `ln_district` `dist`
				   WHERE (`dist`.`dis_id` = `c`.`dis_id`)
				   LIMIT 1) AS `client_districtkh`,
    	(SELECT
    	`prope_type`.`type_nameen`
    	FROM `ln_properties_type` `prope_type`
    	WHERE (`prope_type`.`id` =`pp`.`property_type`)
    	LIMIT 1) AS `property_type_en`,
    	`pp`.`property_type`,
    	`pp`.`land_address` AS `property_title`,
    	pp.`street` AS `property_street`,
    	ih.payment_id as installment_type,
    	ih.*
    	
    	FROM
	    	`ln_sale` AS `s`,
	    	ln_issue_house as ih,
	    	ln_project AS p ,
	    	`ln_client` AS c,
	    	ln_properties as pp
    	WHERE
	    	`p`.`br_id` = `s`.`branch_id`
	    	AND `c`.`client_id` = `s`.`client_id`
	    	AND `pp`.`id` = `s`.`house_id`
	    	AND ih.sale_id = s.id
	    	AND ih.id=".$id;
    
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission("`s`.`branch_id`");
    
    	return $db->fetchRow($sql);
    }
    function getIssueHouseAgreementEnglish($id=null){//bppt,natha,longny,moul mith
    	$db = $this->getAdapter();
    	$sql="SELECT
	    	`p`.`map_url` AS `project_location_en`,
	    	(SELECT name_en FROM `ln_view` WHERE type=23 and key_code=c.client_d_type limit 1) AS client_doc_type,
	    		
	    	(SELECT
	    	`village`.`village_name`
	    	FROM `ln_village` `village`
	    	WHERE (`village`.`vill_id` = `c`.`village_id`
	    	)
	    	LIMIT 1) AS `village_en`,
	    	
	    	(SELECT
	    	`comm`.`commune_name` FROM `ln_commune` `comm`
	    	WHERE (`comm`.`com_id` = `c`.`com_id`)
	    	LIMIT 1) AS `commune_en`,
	    	
	    	(SELECT
	    	`dist`.`district_name`
	    	FROM `ln_district` `dist`
	    	WHERE (`dist`.`dis_id` = `c`.`dis_id`)
	    	LIMIT 1) AS `district_en`,
	    	
	    	(SELECT
	    	`provi`.`province_en_name`
	    	FROM `ln_province` `provi`
	    	WHERE (`provi`.`province_id` = `c`.`pro_id`)
	    	LIMIT 1) AS `province_en`,
	    	 
	    	
	    	
	    	(SELECT
	    	`prope_type`.`type_nameen`
	    	FROM `ln_properties_type` `prope_type`
	    	WHERE (`prope_type`.`id` =`pp`.`property_type`)
	    	LIMIT 1) AS `property_type_en`
	    	
    	FROM
	    	`ln_sale` AS `s`,
	    	ln_project AS p ,
	    	`ln_client` AS c,
	    	ln_properties as pp
    	WHERE
	    	`p`.`br_id` = `s`.`branch_id`
	    	AND `c`.`client_id` = `s`.`client_id`
	    	AND `pp`.`id` = `s`.`house_id`
	    	AND s.id=".$id;
    
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission("`s`.`branch_id`");
    
    	return $db->fetchRow($sql);
    }

    /*function getAgreementBySaleID($id=null){//for evergreen 100% right
    	$db = $this->getAdapter();
    	$sql="SELECT
    	`s`.`id`              AS `id`,
    	`s`.`sale_number`     AS `sale_number`,
    	`s`.`payment_id`      AS `payment_id`,
    	`s`.`branch_id`       AS `branch_id`,
    	`s`.`client_id`       AS `client_id`,
    	`s`.`price_before`    AS `price_before`,
    	`s`.`discount_amount` AS `discount_amount`,
    	`s`.`price_sold`      AS `price_sold`,
    	`s`.`other_fee`       AS `other_fee`,
    	`s`.`admin_fee`       AS `admin_fee`,
    	`s`.`paid_amount`     AS `paid_amount`,
    	`s`.`balance`         AS `balance`,
    	`s`.`amount_collect`  AS `amount_collect`,
    	`s`.`interest_rate`   AS `interest_rate`,
    	`s`.`total_duration`  AS `total_duration`,
    	s.land_price,
    	s.buy_date,
    	s.agreement_date,
    	`p`.`project_name`,
    	`p`.`br_address` AS `project_location`,
    	`p`.`p_manager_namekh` AS `project_manager_namekh`,
    	`p`.`p_manager_nationality` AS `project_manager_nationality`,
    	`p`.`p_manager_nation_id` AS `project_manager_nation_id`,
    	`p`.`p_current_address` AS `project_manager_p_current_address`,
    	p.w_manager_namekh ,
    	p.w_manager_nation_id,
    	p.w_manager_position,
    	p.w_manager_tel,
    	 
    	p.w_managername1,
    	p.w_manager_position1,
    	p.w_manager_tel1,
    	 
    	`c`.`client_number` AS `client_code`,
    	`c`.`name_kh` AS `client_namekh`,
    	`c`.`name_en` AS `client_nameen`,
    	`c`.`dob_buywith` AS `dob_buywith`,
    	`c`.`rid_no` AS `rid_no`,
    	c.dob as client_dob,
    	(SELECT name_kh FROM `ln_view` WHERE TYPE=11 AND key_code=c.sex) as client_sex,
    	hname_kh AS with_client_name,
    	(SELECT name_kh FROM `ln_view` WHERE TYPE=11 AND key_code=c.ksex) as with_client_sex,
    	c.p_nationality AS with_client_nation_id,
    	c.hname_kh,
    	`c`.`nationality` AS `client_nationality`,
    	`c`.`nation_id` AS `client_nation_id`,
    	`c`.`phone` AS `client_phone`,
    	`c`.`house` AS `client_house_no`,
    	`c`.`street` AS `client_street`,
    	c.p_nationality AS with_client_nationlity,
    	c.phone,
    	(SELECT
    	`village`.`village_name`
    	FROM `ln_village` `village`
    	WHERE (`village`.`vill_id` = `c`.`village_id`)
    	LIMIT 1) AS `client_village_en`,
    	(SELECT
    	`village`.`village_namekh`
    	FROM `ln_village` `village`
    	WHERE (`village`.`vill_id` = `c`.`village_id`
    	)
    	LIMIT 1) AS `client_village_kh`,
    	(SELECT
    	`comm`.`commune_name` FROM `ln_commune` `comm`
    	WHERE (`comm`.`com_id` = `c`.`com_id`)
    	LIMIT 1) AS `client_commune_en`,
    		
    	(SELECT
    	`comm`.`commune_namekh` FROM `ln_commune` `comm`
    	WHERE (`comm`.`com_id` = `c`.`com_id`)
    	LIMIT 1) AS `client_commune_kh`,
    	(SELECT
    	`dist`.`district_name`
    	FROM `ln_district` `dist`
    	WHERE (`dist`.`dis_id` = `c`.`dis_id`) LIMIT 1) AS `client_district`,
    	(SELECT
    	`dist`.`district_namekh`
    	FROM `ln_district` `dist`
    	WHERE (`dist`.`dis_id` = `c`.`dis_id`)
    	LIMIT 1) AS `client_districtkh`,
    	(SELECT
    	`provi`.`province_en_name`
    	FROM `ln_province` `provi`
    	WHERE (`provi`.`province_id` = `c`.`pro_id`) LIMIT 1) AS `client_province_en`,
    	(SELECT
    	`provi`.`province_kh_name`
    	FROM `ln_province` `provi`
    	WHERE (`provi`.`province_id` = `c`.`pro_id`)
    	LIMIT 1) AS `client_province_kh`,
    	(SELECT `property`.`land_code`
    	FROM `ln_properties` `property`
    	WHERE (`property`.`id` = `s`.`house_id`)
    	LIMIT 1) AS `property_code`,
    	(SELECT
    	`property`.`land_address`
    	FROM `ln_properties` `property`
    	WHERE (`property`.`id` = `s`.`house_id`)
    	LIMIT 1) AS `property_title`,
    	(SELECT
    	`prope_type`.`type_nameen`
    	FROM `ln_properties_type` `prope_type`
    	WHERE (`prope_type`.`id` = (SELECT
    	`property`.`property_type`
    	FROM `ln_properties` `property`
    	WHERE (`property`.`id` = `s`.`house_id`)
    	LIMIT 1))
    	LIMIT 1) AS `property_type_en`,
    	(SELECT
    	`prope_type`.`type_namekh`
    	FROM `ln_properties_type` `prope_type`
    	WHERE (`prope_type`.`id` = (SELECT
    	`property`.`property_type`
    	FROM `ln_properties` `property`
    	WHERE (`property`.`id` = `s`.`house_id`)
    	LIMIT 1))
    	LIMIT 1) AS `property_type_kh`,
    	(SELECT
    	`property`.`width`
    	FROM `ln_properties` `property`
    	WHERE (`property`.`id` = `s`.`house_id`)
    	LIMIT 1) AS `property_width`,
    	(SELECT
    	`property`.`height`
    	FROM `ln_properties` `property`
    	WHERE (`property`.`id` = `s`.`house_id`)
    	LIMIT 1) AS `property_height`,
    	(SELECT
    	`property`.`land_size`
    	FROM `ln_properties` `property`
    	WHERE (`property`.`id` = `s`.`house_id`)
    	LIMIT 1) AS `property_size`,
    	(SELECT
    	`property`.`street`
    	FROM `ln_properties` `property`
    	WHERE (`property`.`id` = `s`.`house_id`)
    	LIMIT 1) AS `property_street`
    	FROM `ln_sale` AS `s`,ln_project AS p ,`ln_client` AS c
    	WHERE `p`.`br_id` = `s`.`branch_id`
    	AND `c`.`client_id` = `s`.`client_id`
    	AND s.id=".$id;
    	return $db->fetchRow($sql);
    }*/
    function getFirstDepositBySaleID($id=null,$payment_id){//សម្រាប់លីមហេង ទាយយកថាត្រូវកក់ %?
    	$db = $this->getAdapter();
    	$sql=" SELECT * FROM `ln_saleschedule` AS sc WHERE sc.`sale_id`= ".$id;
    	if($payment_id==4){
    		$sql.=" AND sc.is_installment=1 ";
    	}
    	$order = ' AND is_rescheule=0 ORDER BY sc.no_installment ASC, sc.`date_payment` ASC ';
    	return $db->fetchRow($sql.$order);
    }
    function getScheduleBySaleID($id=null,$payment_id){
    		$db = $this->getAdapter();
    		$sql=" SELECT *,(SELECT name_kh FROM ln_view WHERE type =29 AND key_code = sc.ispay_bank LIMIT 1) AS payment_type FROM `ln_saleschedule` AS sc WHERE sc.`sale_id`= ".$id;
    		if($payment_id==4){
    			$sql.=" AND sc.is_installment=1 ";
    		}
    		$order = ' AND is_rescheule=0 ORDER BY sc.no_installment ASC, sc.`date_payment` ASC ';
    		//ORDER BY no_installment ASC,date_payment ASC, collect_by ASC, status DESC
    		return $db->fetchAll($sql.$order);
    }
    	public function getComissionById($id){
    		$db = $this->getAdapter();
    		$sql= "SELECT c.*,s.`full_commission`,
    		(SELECT p.project_name FROM `ln_project` AS p WHERE p.br_id = c.`branch_id` LIMIT 1) AS brach_name,
    		(SELECT v.name_kh FROM `ln_view` AS v WHERE v.type=13 AND v.key_code = c.`category_id` LIMIT 1) AS cate_title,
			(SELECT cu.name_kh FROM `ln_client` AS cu WHERE cu.client_id = s.`client_id` LIMIT 1) AS cutomer_name,
			(SELECT p.land_code FROM `ln_properties` AS p WHERE p.id = s.`house_id` LIMIT 1) AS land_code,
			(SELECT p.street FROM `ln_properties` AS p WHERE p.id = s.`house_id` LIMIT 1) AS street,
			(SELECT p.land_address FROM `ln_properties` AS p WHERE p.id = s.`house_id` LIMIT 1) AS land_address,
			(SELECT st.co_khname FROM `ln_staff` AS st WHERE st.co_id = c.`staff_id` LIMIT 1) AS co_khname,
			
	    		(SELECT name_kh FROM `ln_view` WHERE type=2 and key_code=c.payment_id limit 1) AS payment_type,
	    		
			(SELECT CONCAT(COALESCE(last_name,''),' ',COALESCE(first_name,'')) FROM rms_users WHERE id = c.user_id LIMIT 1 ) AS user_name
			 FROM 
			`ln_comission` AS c,
			`ln_sale` AS s
			WHERE s.`id` = c.`sale_id` AND c.`id` = ".$id;
    		$dbp = new Application_Model_DbTable_DbGlobal();
    		$sql.=$dbp->getAccessPermission("c.`branch_id`");
    		
    		return $db->fetchRow($sql);
    	}
    	function getCustomerRequirement($search=null){
    		$db = $this->getAdapter();
    		$sql="
				SELECT 
					ct.*
	    			,(SELECT title FROM `rms_know_by` WHERE rms_know_by.id=ct.know_by LIMIT 1) as know_by			
					,(SELECT  first_name FROM rms_users WHERE id = ct.user_id LIMIT 1 ) AS user_name
					,status 
					
					,(SELECT pro.project_name FROM ln_project AS pro WHERE pro.br_id = ct.branchId LIMIT 1 ) AS projectName
					,(SELECT s.sale_number FROM ln_sale AS s WHERE s.id = ct.saleId LIMIT 1 ) AS saleNumber
					,(SELECT s.buy_date FROM ln_sale AS s WHERE s.id = ct.saleId LIMIT 1 ) AS buyDate
					,(SELECT crm.date_payment FROM ln_client_receipt_money AS crm WHERE crm.sale_id = ct.saleId AND crm.total_payment > 0 ORDER BY crm.id DESC LIMIT 1 ) AS lastPaidDate
					
					,(SELECT c.client_number FROM ln_client AS c WHERE c.client_id = ct.clientId LIMIT 1 ) AS clientCode
					,(SELECT c.name_kh FROM ln_client AS c WHERE c.client_id = ct.clientId LIMIT 1 ) AS clientName
					,(SELECT c.phone FROM ln_client AS c WHERE c.client_id = ct.clientId LIMIT 1 ) AS clientPhone
					
					,(SELECT p.land_address FROM ln_properties AS p WHERE p.id = ct.propertyId LIMIT 1 ) AS landAddress
					,(SELECT p.street FROM ln_properties AS p WHERE p.id = ct.propertyId LIMIT 1 ) AS landStreet
			FROM in_customer AS ct 
			WHERE ct.`status`=1 ";
    		$where ="";
    		$from_date =(empty($search['start_date']))? '1': " ct.`date` >= '".$search['start_date']." 00:00:00'";
    		$to_date = (empty($search['end_date']))? '1': " ct.`date` <= '".$search['end_date']." 23:59:59'";
    		$where.= " AND ".$from_date." AND ".$to_date;
    		if(!empty($search['adv_search'])){
    			$s_where = array();
    			$s_search = addslashes(trim($search['adv_search']));
    			$s_where[] =" ct.`name` LIKE '%{$s_search}%'";
    			$s_where[]=" ct.`phone` LIKE '%{$s_search}%'";
    			$s_where[]=" ct.`from_price` LIKE '%{$s_search}%'";
    			$s_where[]=" ct.`to_price` LIKE '%{$s_search}%'";
    			$s_where[]=" ct.`type` LIKE '%{$s_search}%'";
    			$s_where[]=" (SELECT  first_name FROM rms_users WHERE id = ct.user_id LIMIT 1 ) LIKE '%{$s_search}%'";
    			$where .=' AND ( '.implode(' OR ',$s_where).')';
    		}
    		if(!empty($search['user'])){
    			$where.= " AND ct.user_id = ".$search['user'];
    		}
    		if(!empty($search['statusreq'])){
    			$where.= " AND ct.statusreq = '".$search['statusreq']."'";
    		}
    		if($search['know_by']>0){
    			$where.= " AND ct.know_by = ".$search['know_by'];
    		}
    		
    		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
    		$userid = $session_user->user_id;
    		
    		$db_user=new Application_Model_DbTable_DbUsers();
    		$user_info = $db_user->getUserInfo($userid);
    		if (!empty($user_info['staff_id'])){
    			$where.= " AND ct.user_id = ".$userid;
    		}
    		
    		$dbgb = new Application_Model_DbTable_DbGlobal();
    		$userinfo = $dbgb->getUserInfo();
    		if($userinfo['level']!=1 AND $userinfo['level']!=2 AND $userinfo['level']!=11){
    			$where.= " AND ct.user_id = ".$userinfo['user_id'];
    		}
    		$where.=" ORDER BY ct.id DESC ";
    		return $db->fetchAll($sql.$where);
    	}
    	
    	function getNumberInkhmer($number){
    		$khmernumber = array("០","១","២","៣","៤","៥","៦","៧","៨","៩");
    		$spp = str_split($number);
    		$num="";
    		foreach ($spp as $ss){
    	
    			if (!empty($khmernumber[$ss])){
    				$num.=$khmernumber[$ss];
    			}else{
    				$num.=$ss;
    			}
    		}
    		return $num;
    	}
	function verifyAgreement($data){
 		$db = $this->getAdapter();
 		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
 		$user_id = $session_user->user_id;
		$this->_name='ln_sale';
		$arr = array(
				'is_verify'=>1,
				'verify_by'=>$user_id
				);
		$where=' id='.$data['sale_id'];
		$this->update($arr, $where);
	}
	
	function getProject($search=null){
		$db = $this->getAdapter();
		$sql="SELECT p.* FROM `ln_project` as p WHERE 1";
		if ($search['branch_id']>0){
			$sql.=" AND p.br_id=".$search['branch_id'];
		}
		return $db->fetchAll($sql);
	}
	function getExpensByMonth($branch,$date,$monthlytype=1){
		$db = $this->getAdapter();
		if ($monthlytype==1){
			$date = date("Y-m",strtotime($date));
			$sql="SELECT 
				SUM(ex.total_amount) AS totalbymonth
				FROM `ln_expense` AS ex
				WHERE ex.status=1 AND DATE_FORMAT(ex.date,'%Y-%m') ='$date'";
			if (!empty($branch)){
				$sql.=" AND ex.branch_id=".$branch;
			}
			$total_expense = $db->fetchOne($sql);
			if(empty($total_expense)){$total_expense=0;}
			
			
			$sql="SELECT SUM(total_amount) FROM `ln_comission` WHERE status=1 
				AND DATE_FORMAT(for_date,'%Y-%m') ='$date'";
			if (!empty($branch)){
				$sql.=" AND branch_id=".$branch;
			}
			$total_commission = $db->fetchOne($sql);
			if(empty($total_commission)){
				$total_commission=0;
			}
			return $total_expense+$total_commission;
		}else{
			$date = date("Y",strtotime($date));
			$sql="SELECT
			SUM(ex.total_amount) AS totalbymonth
			FROM `ln_expense` AS ex
			WHERE ex.status=1 AND DATE_FORMAT(ex.date,'%Y') ='$date'";
			if (!empty($branch)){
				$sql.=" AND ex.branch_id=".$branch;
			}
			$total_expense = $db->fetchOne($sql);
			if(empty($total_expense)){
				$total_expense=0;
			}
			
			$sql="SELECT SUM(total_amount) FROM `ln_comission` WHERE status=1
			AND DATE_FORMAT(for_date,'%Y') ='$date'";
			if (!empty($branch)){
				$sql.=" AND branch_id=".$branch;
			}
			$total_commission = $db->fetchOne($sql);
			if(empty($total_commission)){
				$total_commission=0;
			}
			return $total_expense+$total_commission;
		}
		
	}
	
	function getTotalPrinciplePaidById($id){
		$db = $this->getAdapter();
		$sql="SELECT SUM(c.total_principal_permonthpaid) FROM `ln_client_receipt_money` AS c
			WHERE c.sale_id = $id AND c.status=1";
		return $db->fetchOne($sql);
	}
	function getLastDatePaidById($id){
		$db = $this->getAdapter();
		$sql="SELECT c.date_pay FROM `ln_client_receipt_money` AS c
			WHERE c.sale_id = $id AND c.status=1 AND c.recieve_amount>0 ORDER BY c.id DESC LIMIT 1";
		return $db->fetchOne($sql);
	}
	
	
	
	function checkLandSaleNotUpdateLock($pro_id){
		$db = $this->getAdapter();
		$sql="SELECT p.land_address FROM `ln_properties` AS p WHERE p.id =$pro_id AND p.is_lock =0 LIMIT 1";
		return $db->fetchOne($sql);
	}
	
	
	
	
	public function getCustomerRequirmentById($id){
		$db = $this->getAdapter();
		$sql = "SELECT *,(SELECT kn.title FROM rms_know_by as kn WHERE kn.id = know_by LIMIT 1) as know_bytitle FROM in_customer WHERE id = ".$db->quote($id);
		$sql.=" LIMIT 1 ";
		$row=$db->fetchRow($sql);
		return $row;
	}
	public function AllHistoryContact($crm_id){
		$db = $this->getAdapter();
		$sql="
		SELECT 
			hc.*
			,(SELECT CONCAT(last_name,' ',first_name) FROM rms_users WHERE hc.user_contact=id LIMIT 1 ) AS user_contact_name
			,s.sale_number AS saleNumber
			,s.buy_date AS buyDate
			,(SELECT p.land_address FROM ln_properties AS p WHERE p.id = s.house_id LIMIT 1 ) AS landAddress
			,(SELECT p.street FROM ln_properties AS p WHERE p.id = s.house_id LIMIT 1 ) AS landStreet
		FROM 
			`ln_history_contact` AS hc 
			LEFT JOIN ln_sale AS s ON s.id = hc.saleId
		WHERE customer_id = $crm_id 
		ORDER BY hc.id DESC";
		return $db->fetchAll($sql);
	}
	public function AllHistoryContactList($search){
		$db = $this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$sql="SELECT 
				c.*
				,(SELECT CONCAT(last_name,' ',first_name) FROM rms_users WHERE c.user_contact=id LIMIT 1 ) AS user_contact_name
				,name, phone
				,(SELECT title FROM `rms_know_by` WHERE rms_know_by.id=know_by LIMIT 1) as know_by
				, from_price
				,to_price
				,requirement
				,type
				,description	
				,statusreq
				
				,(SELECT pro.project_name FROM ln_project AS pro WHERE pro.br_id = ct.branchId LIMIT 1 ) AS projectName
				,(SELECT s.sale_number FROM ln_sale AS s WHERE s.id = ct.saleId LIMIT 1 ) AS saleNumber
				,(SELECT s.buy_date FROM ln_sale AS s WHERE s.id = ct.saleId LIMIT 1 ) AS buyDate
				,(SELECT crm.date_payment FROM ln_client_receipt_money AS crm WHERE crm.sale_id = ct.saleId AND crm.total_payment > 0 ORDER BY crm.id DESC LIMIT 1 ) AS lastPaidDate
				
				,(SELECT c.client_number FROM ln_client AS c WHERE c.client_id = ct.clientId LIMIT 1 ) AS clientCode
				,(SELECT c.name_kh FROM ln_client AS c WHERE c.client_id = ct.clientId LIMIT 1 ) AS clientName
				,(SELECT c.phone FROM ln_client AS c WHERE c.client_id = ct.clientId LIMIT 1 ) AS clientPhone
				
				,(SELECT p.land_address FROM ln_properties AS p WHERE p.id = ct.propertyId LIMIT 1 ) AS landAddress
				,(SELECT p.street FROM ln_properties AS p WHERE p.id = ct.propertyId LIMIT 1 ) AS landStreet
			
		";
		$sql.=", CASE
		WHEN  c.proccess = 0 THEN 'បោះបង់ការទំនាក់ទំនង'
		WHEN c.proccess = 1 THEN 'កំពុងដំណើរការណ៍'
		WHEN c.proccess = 2 THEN 'បន្តការទំនាក់ទំនង'
		WHEN c.proccess = 3 THEN 'រង់ចាំណាត់ជួប'
		WHEN c.proccess = 4 THEN 'បានណាត់ជួប'
		WHEN c.proccess = 5 THEN 'បិទការលក់'
		WHEN c.proccess = 6 THEN 'ការកក់ប្រាក់'
		WHEN c.proccess = 7 THEN 'ចុះកុងត្រា'
		
		END AS proccess ";
		$sql.=" FROM `ln_history_contact` AS c,
				in_customer AS ct ";
		
		$from_date =(empty($search['start_date']))? '1': " c.contact_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " c.contact_date <= '".$search['end_date']." 23:59:59'";
		$where = " WHERE ".$from_date." AND ".$to_date;		
		$where.=" AND ct.id = c.customer_id ";
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[] = " ct.name LIKE '%{$s_search}%'";
			$s_where[] = " c.feedback LIKE '%{$s_search}%'";
			$s_where[] = " ct.phone LIKE '%{$s_search}%'";
			
			
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['proccessSearch']>-1){
			$where.= " AND c.proccess = '".$search['proccessSearch']."'";
		}
		if($search['know_by']>0){
			$where.= " AND ct.know_by = ".$search['know_by'];
		}	
		if(!empty($search['user'])){
			$where.= " AND c.user_contact = ".$search['user'];
		}
		$dbp = new Application_Model_DbTable_DbGlobal();
		$userInfo = $dbp->getUserInfo();
		if(!empty($userInfo)){
			$level = empty($userInfo["level"]) ? 0 : $userInfo["level"];
			$userId = empty($userInfo["user_id"]) ? 0 : $userInfo["user_id"];
			if($level!=1 AND $level!=11){
    			$where.= " AND c.user_contact = ".$userId;
    		}
		}
		
		$order=" ORDER BY c.contact_date DESC,c.id DESC ";
		return $db->fetchAll($sql.$where.$order);
	}
	function getAgreementByChangeOwnerSaleID($id=null){
    		$db = $this->getAdapter();
    		$sql="
    		SELECT
				  `s`.`id`              AS `id`,
				  `s`.`sale_number`     AS `sale_number`,
				  `s`.`payment_id`      AS `payment_id`,
				  `s`.`branch_id`       AS `branch_id`,
				  `s`.`client_id`       AS `client_id`,
				  `s`.`price_before`    AS `price_before`,
				  `s`.`discount_amount` AS `discount_amount`,
				   s.discount_percent,
				  `s`.`price_sold`      AS `price_sold`,
				  `s`.`other_fee`       AS `other_fee`,
				  `s`.`admin_fee`       AS `admin_fee`,
				  `s`.`paid_amount`     AS `paid_amount`,
				  `s`.`balance`         AS `balance`,
				  `s`.`amount_collect`  AS `amount_collect`,
				  `s`.`interest_rate`   AS `interest_rate`,
				  `s`.`total_duration`  AS `total_duration`,
				  `s`.`first_payment`  AS `first_payment`,
				   s.lastpayment_amount,
				   s.is_reschedule,
				   s.land_price,
				   s.amount_build,
				   s.build_start,
				   s.buy_date,
				   s.`end_line`,
    			   s.validate_date,
				   s.agreement_date,
				   s.note_agreement,
				   s.is_verify,
				   s.store_number,
				    s.second_depostit,
				    s.witness_i,
				    s.witness_ii,
				   (SELECT CONCAT(last_name,' ',first_name) FROM rms_users WHERE id = s.user_id LIMIT 1 ) AS user_name,
				   (SELECT co_khname FROM `ln_staff` WHERE co_id=s.staff_id LIMIT 1) AS staff_name,
				   (SELECT name_kh FROM `ln_view` WHERE type=25 and key_code=s.payment_id limit 1) AS payment_type,
				  `p`.`project_name`,
				   `p`.`logo` AS project_logo,
				  `p`.`branch_tel`,
				  `p`.`other` as p_other,
				   p.p_sex,
			      `p`.`br_address` AS `project_location`,
			      `p`.`p_manager_namekh` AS `project_manager_namekh`,
			      `p`.`p_manager_nationality` AS `project_manager_nationality`,
				  `p`.`p_manager_nation_id` AS `project_manager_nation_id`,
                  `p`.`p_current_address` AS `project_manager_p_current_address`,
                   p.w_manager_namekh ,
                   p.`position`,
                   p.w_manager_nation_id,
                   p.p_dob as manager_dob,
                   p.`p_nationid_issue`,
                    p.w_manager_namekh ,
                   p.w_manager_nation_id,
                   p.`w_manager_nationality`,
                   p.`w_sex`,
                   
                   p.w_manager_nation_id,
			    	p.w_manager_position,
			    	p.w_manager_tel,
			    	p.w_manager_tel AS manager_tel,
    	
                   p.w_managername1,
			    	p.w_manager_position1,
			    	p.w_position,
			    	p.w_manager_tel1,
			    	p.w_manager_tel1 AS with_manager_tel,
			    	cho.change_date AS changeDate,
    	
                   (SELECT name_kh FROM `ln_view` WHERE TYPE=11 AND key_code=p.`w_sex` LIMIT 1) AS sc_manager_sex,
	                  p.`w_dob`,
	                  p.`w_current_address`,
	                  p.`w_nation_id_issue`,
                   (SELECT name_kh FROM `ln_view` WHERE type=11 and key_code=p.p_sex limit 1) AS manager_sex ,
				   
                  `c`.`client_number` AS `client_code`,
     			  `c`.`name_kh` AS `client_namekh`,
     			  `c`.`name_en` AS `client_nameen`,
     			   c.dob,
     			   c.dob AS client_dob ,
     			   c.hname_kh,
     			   c.hname_kh AS with_client_name,
     			   c.sex,
    			   c.ksex,
    			    c.join_type,
    			   (SELECT name_kh FROM `ln_view` WHERE type=11 and key_code=c.ksex limit 1) AS partner_gender,
     			   c.dob_buywith,
     			   c.rid_no,
     			   c.rid_no AS with_client_nation_id,
     			   (SELECT name_kh FROM `ln_view` WHERE TYPE=11 AND key_code=c.`sex` LIMIT 1) AS client_sex,
     			   (SELECT name_kh FROM `ln_view` WHERE type=11 and key_code=c.sex limit 1) AS sexKh,
     			   (SELECT name_kh FROM `ln_view` WHERE type=23 and key_code=c.joint_doc_type limit 1) AS joint_doc_type,
     			   (SELECT name_kh FROM `ln_view` WHERE type=23 and key_code=c.client_d_type limit 1) AS client_d_type,
     			   
     			   c.p_nationality,
     			   c.p_nationality AS with_client_nationlity ,
  				  `c`.`nationality` AS `client_nationality`,
     			  `c`.`nation_id` AS `client_nation_id`,
     			   c.client_issuedateid,
     			   c.join_issuedateid,
                  `c`.`phone` AS `client_phone`,
  				  `c`.`house` AS `client_house_no`,
                  `c`.`street` AS `client_street`,
                  c.phone,
                  c.lphone as with_phone,
                  c.ghouse as with_house,
                  c.dstreet AS w_street,
                   c.arid_no AS witnesses,
				  (SELECT
				     `village`.`village_namekh`
				   FROM `ln_village` `village`
				   WHERE (`village`.`vill_id` = `c`.`qvillage`)
				   LIMIT 1) AS `joint_village`,
    			  (SELECT
				     `comm`.`commune_namekh` FROM `ln_commune` `comm`
				   WHERE (`comm`.`com_id` = `c`.`dcommune`)
				   LIMIT 1) AS `join_commune`,
    			 (SELECT
				     `dist`.`district_namekh`
				   FROM `ln_district` `dist`
				   WHERE (`dist`.`dis_id` = `c`.`adistrict`) LIMIT 1) AS `join_district`,
    			(SELECT
				     `provi`.`province_kh_name`
				   FROM `ln_province` `provi`
				   WHERE (`provi`.`province_id` = `c`.`cprovince`) LIMIT 1) AS `join_province`,	
    				
				  (SELECT
					     `village`.`village_namekh`
					   FROM `ln_village` `village`
					   WHERE (`village`.`vill_id` = `c`.`village_id`
					                                 )
					   LIMIT 1) AS `client_village_kh`,
				   (SELECT
				     `comm`.`commune_namekh` FROM `ln_commune` `comm`
				   WHERE (`comm`.`com_id` = `c`.`com_id`)
				   LIMIT 1) AS `client_commune_kh`,
				  (SELECT
				     `dist`.`district_namekh`
				   FROM `ln_district` `dist`
				   WHERE (`dist`.`dis_id` = `c`.`dis_id`)
				   LIMIT 1) AS `client_districtkh`,
				  (SELECT
				     `provi`.`province_kh_name`
				   FROM `ln_province` `provi`
				   WHERE (`provi`.`province_id` = `c`.`pro_id`)
				   LIMIT 1) AS `client_province_kh`,
				   (SELECT name_kh FROM `ln_view` WHERE TYPE=11 AND key_code=c.`ksex` LIMIT 1) AS client_buywith_sex,
				    (SELECT name_kh FROM `ln_view` WHERE TYPE=11 AND key_code=c.`ksex` LIMIT 1) AS with_client_sex ,
				   c.hname_kh AS w_client_namekh,
				   c.dob_buywith AS w_client_dob_buywith,
				   c.p_nationality AS w_client_nationality,
				   c.`ghouse` AS w_client_house,
					c.lphone AS w_client_phone,
				  (SELECT
				     `village`.`village_name`
				   FROM `ln_village` `village`
				   WHERE (`village`.`vill_id` = `c`.`qvillage`)
				   LIMIT 1) AS `w_client_village_en`,
					  (SELECT
					     `village`.`village_namekh`
					   FROM `ln_village` `village`
					   WHERE (`village`.`vill_id` = `c`.`qvillage`
					                                 )
					   LIMIT 1) AS `w_client_village_kh`,
				  (SELECT
				     `comm`.`commune_name` FROM `ln_commune` `comm`
				   WHERE (`comm`.`com_id` = `c`.`dcommune`)
				   LIMIT 1) AS `w_client_commune_en`,
				   
				   (SELECT
				     `comm`.`commune_namekh` FROM `ln_commune` `comm`
				   WHERE (`comm`.`com_id` = `c`.`dcommune`)
				   LIMIT 1) AS `w_client_commune_kh`,
				  (SELECT
				     `dist`.`district_name`
				   FROM `ln_district` `dist`
				   WHERE (`dist`.`dis_id` = `c`.`adistrict`) LIMIT 1) AS `w_client_district`,
				  (SELECT
				     `dist`.`district_namekh`
				   FROM `ln_district` `dist`
				   WHERE (`dist`.`dis_id` = `c`.`adistrict`)
				   LIMIT 1) AS `w_client_districtkh`,
				  (SELECT
				     `provi`.`province_en_name`
				   FROM `ln_province` `provi`
				   WHERE (`provi`.`province_id` = `c`.`cprovince`) LIMIT 1) AS `w_client_province_en`,
				  (SELECT
				     `provi`.`province_kh_name`
				   FROM `ln_province` `provi`
				   WHERE (`provi`.`province_id` = `c`.`cprovince`)
				   LIMIT 1) AS `w_client_province_kh`,
				   
				   toc.`client_number` 			AS `to_client_code`,
				   toc.`name_kh` 				AS `to_client_namekh`,
				   toc.`name_en` 				AS `to_client_nameen`,
     			   toc.dob						AS `to_dob`,
     			   toc.dob 						AS 	to_client_dob ,
     			   toc.hname_kh					AS 	to_hname_kh,
     			   toc.hname_kh 				AS 	to_with_client_name,
     			   toc.sex						AS 	to_sex,
    			   toc.ksex						AS 	to_ksex,
    			   toc.join_type				AS 	to_join_type,
    			   (SELECT name_kh FROM `ln_view` WHERE type=11 and key_code=toc.ksex limit 1) AS to_partner_gender,
     			   toc.dob_buywith				AS to_dob_buywith,
     			   toc.rid_no					AS to_rid_no,
     			   toc.rid_no 					AS to_with_client_nation_id,
     			   (SELECT name_kh FROM `ln_view` WHERE TYPE=11 AND key_code=toc.`sex` LIMIT 1) 			AS to_client_sex,
     			   (SELECT name_kh FROM `ln_view` WHERE type=11 and key_code=toc.sex limit 1) 				AS to_sexKh,
     			   (SELECT name_kh FROM `ln_view` WHERE type=23 and key_code=toc.joint_doc_type limit 1) 	AS to_joint_doc_type,
     			   (SELECT name_kh FROM `ln_view` WHERE type=23 and key_code=toc.client_d_type limit 1) 	AS to_client_d_type,
				   
				   toc.p_nationality  		AS to_p_nationality,
     			   toc.p_nationality 		AS to_with_client_nationlity ,
  				   toc.`nationality` 		AS to_client_nationality,
     			   toc.`nation_id` 			AS to_client_nation_id,
     			   toc.client_issuedateid	AS to_client_issuedateid,
     			   toc.join_issuedateid		AS to_join_issuedateid,
                   toc.phone 				AS to_client_phone,
  				   toc.house 				AS to_client_house_no,
                   toc.street 				AS to_client_street,
                   toc.lphone 				AS to_with_phone,
                   toc.ghouse 				AS to_with_house,
                   toc.dstreet 				AS to_w_street,
                   toc.arid_no 				AS to_witnesses,
				   
				  (SELECT `village`.`village_namekh` FROM `ln_village` `village` WHERE (`village`.`vill_id` = toc.qvillage)  LIMIT 1) 		AS to_joint_village,
    			  (SELECT `comm`.`commune_namekh`    FROM `ln_commune` `comm`    WHERE (`comm`.`com_id` = toc.dcommune) LIMIT 1) 			AS to_join_commune,
    			  (SELECT `dist`.`district_namekh`   FROM `ln_district` `dist`   WHERE (`dist`.`dis_id` = toc.adistrict) LIMIT 1) 			AS to_join_district,
    			  (SELECT `provi`.`province_kh_name` FROM `ln_province` `provi`  WHERE (`provi`.`province_id` = toc.cprovince) LIMIT 1) 	AS to_join_province,	
    				
				  (SELECT `village`.`village_namekh` FROM `ln_village` `village` WHERE (`village`.`vill_id` = toc.village_id) LIMIT 1) 	AS to_client_village_kh,
				  (SELECT `comm`.`commune_namekh` FROM `ln_commune` `comm` WHERE (`comm`.`com_id` = toc.com_id) LIMIT 1) 				AS to_client_commune_kh,
				  (SELECT `dist`.`district_namekh` FROM `ln_district` `dist` WHERE (`dist`.`dis_id` = toc.dis_id) LIMIT 1) 				AS to_client_districtkh,
				  (SELECT `provi`.`province_kh_name` FROM `ln_province` `provi` WHERE (`provi`.`province_id` = toc.pro_id) LIMIT 1) 	AS to_client_province_kh,
				  
				  (SELECT name_kh FROM `ln_view` WHERE TYPE=11 AND key_code=toc.`ksex` LIMIT 1) AS to_client_buywith_sex,
				  (SELECT name_kh FROM `ln_view` WHERE TYPE=11 AND key_code=toc.`ksex` LIMIT 1) AS to_with_client_sex ,
				  
				   toc.hname_kh 			AS to_w_client_namekh,
				   toc.dob_buywith 			AS to_w_client_dob_buywith,
				   toc.p_nationality 		AS to_w_client_nationality,
				   toc.`ghouse` 			AS to_w_client_house,
				   toc.lphone 				AS to_w_client_phone,
				   
				  (SELECT `village`.`village_name` FROM `ln_village` `village` WHERE (`village`.`vill_id` = toc.qvillage)  LIMIT 1) 	AS `to_w_client_village_en`,
				  (SELECT `village`.`village_namekh` FROM `ln_village` `village` WHERE (`village`.`vill_id` = toc.qvillage)  LIMIT 1) 	AS `to_w_client_village_kh`,
				  (SELECT `comm`.`commune_name` FROM `ln_commune` `comm` WHERE (`comm`.`com_id` = toc.`dcommune`)  LIMIT 1) 			AS `to_w_client_commune_en`,
				   
				  (SELECT `comm`.`commune_namekh` FROM `ln_commune` `comm` WHERE (`comm`.`com_id` = toc.`dcommune`) LIMIT 1) 			 				AS `to_w_client_commune_kh`,
				  (SELECT `dist`.`district_name` FROM `ln_district` `dist` WHERE (`dist`.`dis_id` = toc.`adistrict`) LIMIT 1) 			 				AS `to_w_client_district`,
				  (SELECT `dist`.`district_namekh` FROM `ln_district` `dist` WHERE (`dist`.`dis_id` = toc.`adistrict`) LIMIT 1) 		 				AS `to_w_client_districtkh`,
				  (SELECT `provi`.`province_en_name` FROM `ln_province` `provi` WHERE (`provi`.`province_id` = toc.`cprovince`) LIMIT 1) 				AS `to_w_client_province_en`,
				  (SELECT `provi`.`province_kh_name` FROM `ln_province` `provi` WHERE (`provi`.`province_id` = toc.`cprovince`) LIMIT 1) 				AS `to_w_client_province_kh`,
				  
				  (SELECT `prope_type`.`type_nameen` FROM `ln_properties_type` `prope_type` WHERE (`prope_type`.`id` =`pp`.`property_type`) LIMIT 1) 	AS `property_type_en`,
				  (SELECT `prope_type`.`type_namekh` FROM `ln_properties_type` `prope_type` WHERE `prope_type`.`id` = `pp`.`property_type` LIMIT 1) AS `property_type_kh`,
				
				`pp`.`land_size` AS `property_land_size`,
				
				`pp`.`width` AS `property_width`,
				`pp`.`height` AS `property_height`,
				 pp.`land_size`,
				 
				`pp`.`property_type`,
				`pp`.`type_tob`,
				`pp`.`land_code` AS `property_code`,
				`pp`.`land_address` AS `property_title`,
				 pp.`street` AS `property_street`,
				 
				 pp.land_width,
				 pp.land_height,
				 pp.`full_size`,
				 
				 pp.floor,
				 pp.living,
				 pp.`bedroom`,
				 pp.dinnerroom,
				 pp.buidingyear,
				 pp.`parkingspace`,
				 pp.`note` as `property_note`,
				(SELECT `property`.`land_size` FROM `ln_properties` `property` WHERE (`property`.`id` = `s`.`house_id`) LIMIT 1) AS `property_size`,
				 pp.`north` AS border_north,
				 pp.`south` AS border_south,
				 pp.`east` AS border_east,
				 pp.`west` AS border_west,
				 pp.`old_land_id`
			FROM 
				ln_change_owner 	AS cho,
				ln_sale 			AS `s`,
				ln_project 			AS p ,
				ln_client 			AS c,
				ln_client 			AS toc,
				ln_properties 		AS pp
			WHERE 
				cho.sale_id = s.id
				AND `p`.`br_id` = cho.branch_id
				AND `c`.`client_id` = cho.from_customer
				AND  toc.`client_id` = cho.to_customer
				AND `pp`.`id` = `s`.`house_id`
				AND cho.id=".$id;
    		
    		$dbp = new Application_Model_DbTable_DbGlobal();
    		$sql.=$dbp->getAccessPermission("cho.branch_id");
    		
    		return $db->fetchRow($sql);
    }
	
	function getUserActivity($search=null){
		$db = $this->getAdapter();
		$sql="
		
		SELECT 
			uac.* 
			,CONCAT(COALESCE(u.first_name,''),' ',COALESCE(u.last_name,'')) AS userName
		FROM 
			`rns_user_activity` AS uac,
			`rms_users` AS u
		WHERE u.`id` = uac.`user_id`
			AND u.`user_name` !='system'
		
		";
		$where ="";
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$where.=$dbp->getAccessPermission("s.`branch_id`");
		
		$from_date =(empty($search['start_date']))? '1': " uac.`date_time` >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " uac.`date_time` <= '".$search['end_date']." 23:59:59'";
		$where.= " AND ".$from_date." AND ".$to_date;
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
			$s_where[] =" REPLACE(uac.`description`,' ','') LIKE '%{$s_search}%'";
			$s_where[] =" REPLACE(uac.`user_name`,' ','') LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(u.`user_name`,' ','') LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(u.`first_name`,' ','') LIKE '%{$s_search}%'";
			$s_where[]=" REPLACE(u.`last_name`,' ','') LIKE '%{$s_search}%'";
		
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if(!empty($search['keyword'])){
			$s_where = array();
			$s_search = str_replace(' ', '', addslashes(trim($search['keyword'])));
			$s_where[] =" REPLACE(uac.`description`,' ','') LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if($search['user_id']>0){
			$where.= " AND uac.`user_id` = ".$search['user_id'];
		}
		$groupby =" GROUP BY uac.`id` DESC";
		return $db->fetchAll($sql.$where.$groupby);
	}
	
	function getPurchasePaymentDetail($payment_id){
    	$db = $this->getAdapter();
    	$sql="SELECT pd.*,
    	(SELECT p.invoice FROM `ln_expense` AS p WHERE p.id = pd.purchase_id LIMIT 1) AS supplier_no,
    	(SELECT p.other_invoice FROM `ln_expense` AS p WHERE p.id = pd.purchase_id LIMIT 1) AS other_invoice
    	FROM `rms_expense_payment_detail` AS pd WHERE pd.payment_id =$payment_id ";
    	return $db->fetchAll($sql);
    }
	
	
	
	function getRefundLetterByID($id=null){
    		$db = $this->getAdapter();
    		$sql="SELECT
				  `s`.`id`              AS `id`,
				  `s`.`sale_number`     AS `sale_number`,
				  `s`.`payment_id`      AS `payment_id`,
				  `s`.`branch_id`       AS `branch_id`,
				  `s`.`client_id`       AS `client_id`,
				  `s`.`price_before`    AS `price_before`,
				  `s`.`discount_amount` AS `discount_amount`,
				   s.discount_percent,
				  `s`.`price_sold`      AS `price_sold`,
				   s.oversold_price,
				  `s`.`other_fee`       AS `other_fee`,
				  `s`.`admin_fee`       AS `admin_fee`,
				  `s`.`paid_amount`     AS `paid_amount`,
				  `s`.`balance`         AS `balance`,
				  `s`.`amount_collect`  AS `amount_collect`,
				  `s`.`interest_rate`   AS `interest_rate`,
				  `s`.`total_duration`  AS `total_duration`,
				  `s`.`first_payment`   AS `first_payment`,
				   s.lastpayment_amount,
				   s.is_reschedule,
				   s.land_price,
				   s.amount_build,
				   s.build_start,
				   s.buy_date,
				   s.`end_line`,
    			   s.validate_date,
				   s.agreement_date,
				   s.note_agreement,
				   s.is_verify,
				   s.store_number,
				    s.second_depostit,
				    s.witness_i,
				    s.witness_ii,
				   (SELECT CONCAT(last_name,' ',first_name) FROM rms_users WHERE id = s.user_id LIMIT 1 ) AS user_name,
				   (SELECT co_khname FROM `ln_staff` WHERE co_id=s.staff_id LIMIT 1) AS staff_name,
				   (SELECT name_kh FROM `ln_view` WHERE type=25 and key_code=s.payment_id limit 1) AS payment_type,
				   
				   (SELECT name_kh FROM `ln_view` WHERE type=25 and key_code=s.pre_schedule_opt limit 1) AS preScheduleOptTitle,
				   s.pre_schedule_opt,
				   s.pre_percent_payment,
				   s.pre_percent_installment,
				   s.pre_amount_month,
				   s.pre_amount_year,
				   s.pre_fix_payment,
				   
				   s.agreement_for,
				   (SELECT name_kh FROM `ln_view` WHERE type=31 and key_code=s.agreement_for limit 1) AS titlePlong,
				  `p`.`project_name`,
				   `p`.`logo` AS project_logo,
				  `p`.`branch_tel`,
				  `p`.`other` as p_other,
				   p.p_sex,
			      `p`.`br_address` AS `project_location`,
			      `p`.`p_manager_namekh` AS `project_manager_namekh`,
			      `p`.`p_manager_nationality` AS `project_manager_nationality`,
				  `p`.`p_manager_nation_id` AS `project_manager_nation_id`,
                  `p`.`p_current_address` AS `project_manager_p_current_address`,
                   p.w_manager_namekh ,
                   p.`position`,
                   p.w_manager_nation_id,
                   p.p_dob as manager_dob,
                   p.`p_nationid_issue`,
                    p.w_manager_namekh ,
                   p.w_manager_nation_id,
                   p.`w_manager_nationality`,
                   p.`w_sex`,
                   
                   p.w_manager_nation_id,
			    	p.w_manager_position,
			    	p.w_manager_tel,
			    	p.w_manager_tel AS manager_tel,
    	
                   p.w_managername1,
			    	p.w_manager_position1,
			    	p.w_position,
			    	p.w_manager_tel1,
			    	p.w_manager_tel1 AS with_manager_tel,
    	
                   (SELECT name_kh FROM `ln_view` WHERE TYPE=11 AND key_code=p.`w_sex` LIMIT 1) AS sc_manager_sex,
	                  p.`w_dob`,
	                  p.`w_current_address`,
	                  p.`w_nation_id_issue`,
                   (SELECT name_kh FROM `ln_view` WHERE type=11 and key_code=p.p_sex limit 1) AS manager_sex ,
                  `c`.`client_number` AS `client_code`,
     			  `c`.`name_kh` AS `client_namekh`,
     			  `c`.`name_en` AS `client_nameen`,
     			   c.dob,
     			   c.dob AS client_dob ,
     			   c.hname_kh,
     			   c.hname_kh AS with_client_name,
     			   c.sex,
    			   c.ksex,
    			    c.join_type,
    			   (SELECT name_kh FROM `ln_view` WHERE type=11 and key_code=c.ksex limit 1) AS partner_gender,
     			   c.dob_buywith,
     			   c.rid_no,
     			   c.rid_no AS with_client_nation_id,
     			   (SELECT name_kh FROM `ln_view` WHERE TYPE=11 AND key_code=c.`sex` LIMIT 1) AS client_sex,
     			   (SELECT name_kh FROM `ln_view` WHERE type=11 and key_code=c.sex limit 1) AS sexKh,
     			   (SELECT name_kh FROM `ln_view` WHERE type=23 and key_code=c.joint_doc_type limit 1) AS joint_doc_type,
     			   (SELECT name_kh FROM `ln_view` WHERE type=23 and key_code=c.client_d_type limit 1) AS client_d_type,
     			   
     			   c.p_nationality,
     			   c.p_nationality AS with_client_nationlity ,
  				  `c`.`nationality` AS `client_nationality`,
     			  `c`.`nation_id` AS `client_nation_id`,
     			   c.client_issuedateid,
     			   c.join_issuedateid,
                  `c`.`phone` AS `client_phone`,
  				  `c`.`house` AS `client_house_no`,
                  `c`.`street` AS `client_street`,
                  c.phone,
                  c.lphone as with_phone,
                  c.ghouse as with_house,
                  c.dstreet AS w_street,
                   c.arid_no AS witnesses,
                   c.remark AS clientNote,
				  (SELECT
				     `village`.`village_namekh`
				   FROM `ln_village` `village`
				   WHERE (`village`.`vill_id` = `c`.`qvillage`)
				   LIMIT 1) AS `joint_village`,
    			  (SELECT
				     `comm`.`commune_namekh` FROM `ln_commune` `comm`
				   WHERE (`comm`.`com_id` = `c`.`dcommune`)
				   LIMIT 1) AS `join_commune`,
    			 (SELECT
				     `dist`.`district_namekh`
				   FROM `ln_district` `dist`
				   WHERE (`dist`.`dis_id` = `c`.`adistrict`) LIMIT 1) AS `join_district`,
    			(SELECT
				     `provi`.`province_kh_name`
				   FROM `ln_province` `provi`
				   WHERE (`provi`.`province_id` = `c`.`cprovince`) LIMIT 1) AS `join_province`,	
    				
				  (SELECT
					     `village`.`village_namekh`
					   FROM `ln_village` `village`
					   WHERE (`village`.`vill_id` = `c`.`village_id`
					                                 )
					   LIMIT 1) AS `client_village_kh`,
				   (SELECT
				     `comm`.`commune_namekh` FROM `ln_commune` `comm`
				   WHERE (`comm`.`com_id` = `c`.`com_id`)
				   LIMIT 1) AS `client_commune_kh`,
				  (SELECT
				     `dist`.`district_namekh`
				   FROM `ln_district` `dist`
				   WHERE (`dist`.`dis_id` = `c`.`dis_id`)
				   LIMIT 1) AS `client_districtkh`,
				  (SELECT
				     `provi`.`province_kh_name`
				   FROM `ln_province` `provi`
				   WHERE (`provi`.`province_id` = `c`.`pro_id`)
				   LIMIT 1) AS `client_province_kh`,
				   (SELECT name_kh FROM `ln_view` WHERE TYPE=11 AND key_code=c.`ksex` LIMIT 1) AS client_buywith_sex,
				    (SELECT name_kh FROM `ln_view` WHERE TYPE=11 AND key_code=c.`ksex` LIMIT 1) AS with_client_sex ,
				   c.hname_kh AS w_client_namekh,
				   c.dob_buywith AS w_client_dob_buywith,
				   c.p_nationality AS w_client_nationality,
				   c.`ghouse` AS w_client_house,
					c.lphone AS w_client_phone,
				  (SELECT
				     `village`.`village_name`
				   FROM `ln_village` `village`
				   WHERE (`village`.`vill_id` = `c`.`qvillage`)
				   LIMIT 1) AS `w_client_village_en`,
					  (SELECT
					     `village`.`village_namekh`
					   FROM `ln_village` `village`
					   WHERE (`village`.`vill_id` = `c`.`qvillage`
					                                 )
					   LIMIT 1) AS `w_client_village_kh`,
				  (SELECT
				     `comm`.`commune_name` FROM `ln_commune` `comm`
				   WHERE (`comm`.`com_id` = `c`.`dcommune`)
				   LIMIT 1) AS `w_client_commune_en`,
				   
				   (SELECT
				     `comm`.`commune_namekh` FROM `ln_commune` `comm`
				   WHERE (`comm`.`com_id` = `c`.`dcommune`)
				   LIMIT 1) AS `w_client_commune_kh`,
				  (SELECT
				     `dist`.`district_name`
				   FROM `ln_district` `dist`
				   WHERE (`dist`.`dis_id` = `c`.`adistrict`) LIMIT 1) AS `w_client_district`,
				  (SELECT
				     `dist`.`district_namekh`
				   FROM `ln_district` `dist`
				   WHERE (`dist`.`dis_id` = `c`.`adistrict`)
				   LIMIT 1) AS `w_client_districtkh`,
				  (SELECT
				     `provi`.`province_en_name`
				   FROM `ln_province` `provi`
				   WHERE (`provi`.`province_id` = `c`.`cprovince`) LIMIT 1) AS `w_client_province_en`,
				  (SELECT
				     `provi`.`province_kh_name`
				   FROM `ln_province` `provi`
				   WHERE (`provi`.`province_id` = `c`.`cprovince`)
				   LIMIT 1) AS `w_client_province_kh`,
				  
			(SELECT
				     `prope_type`.`type_nameen`
				   FROM `ln_properties_type` `prope_type`
				   WHERE (`prope_type`.`id` =`pp`.`property_type`)
				   LIMIT 1) AS `property_type_en`,
			(SELECT
				     `prope_type`.`type_namekh`
				   FROM `ln_properties_type` `prope_type`
				   WHERE `prope_type`.`id` = `pp`.`property_type` LIMIT 1) AS `property_type_kh`,
		   (SELECT
			 `prope_type`.`note`
		   FROM `ln_properties_type` `prope_type`
		   WHERE `prope_type`.`id` = `pp`.`property_type` LIMIT 1) AS `propertyTypeNote`,
			`pp`.`land_size` AS `property_land_size`,
			
			`pp`.`width` AS `property_width`,
		    `pp`.`height` AS `property_height`,
		     pp.`land_size`,
		     
		    `pp`.`property_type`,
		    `pp`.`type_tob`,
		    `pp`.`land_code` AS `property_code`,
		    `pp`.`land_address` AS `property_title`,
 			 pp.`street` AS `property_street`,
 			 
 			 pp.land_width,
 			 pp.land_height,
 			 pp.`full_size`,
			 
			 pp.land_width AS houseWidth,
 			 pp.land_height AS houseHeight,
 			 pp.`full_size` AS houseFullSize,
 			 
 			 pp.floor,
 			 pp.living,
 			 pp.`bedroom`,
 			 pp.dinnerroom,
 			 pp.buidingyear,
 			 pp.`parkingspace`,
 			 pp.`note` as `property_note`,
			 
 			 sC.`cancel_code` as `cancelCode`,
 			 sC.`reason` as `cancelReason`,
 			 sC.`paid_amount` as `paidAmount`,
 			 sC.`return_back` as `amountReturnBack`,
 			 sC.`create_date` as `create_date`,
			 
	(SELECT
    	`property`.`land_size`
    	FROM `ln_properties` `property`
    	WHERE (`property`.`id` = `s`.`house_id`)
    	LIMIT 1) AS `property_size`,
 			 pp.`north` AS border_north,
 			 pp.`south` AS border_south,
 			 pp.`east` AS border_east,
 			 pp.`west` AS border_west,
 			 pp.`old_land_id`,
 	(SELECT
	    	`a`.`description`
	    	FROM `ln_sale_conditionagreement` `a`
	    		WHERE (`a`.`saleId` = `s`.`id`)
	    	LIMIT 1) AS `additinalContract`,
	    	gendertitle,
	    	gendertitle1 
		FROM 
			`ln_sale` AS `s`,
			`ln_sale_cancel` AS `sC`,
			ln_project AS p ,
			`ln_client` AS c,
			ln_properties as pp
			
			WHERE 
			`p`.`br_id` = `s`.`branch_id` 
			AND `c`.`client_id` = `s`.`client_id`
			AND `sC`.`sale_id` = `s`.`id`
			AND `pp`.`id` = `s`.`house_id` 
			AND `sC`.`status` = 1 
			AND sC.id=".$id;
    		
    		$dbp = new Application_Model_DbTable_DbGlobal();
    		$sql.=$dbp->getAccessPermission("sC.`branch_id`");
    		
    		return $db->fetchRow($sql);
    }
	
	function getLastRecordPaymentSchdule($saleId){
		$db=$this->getAdapter();
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$userId=$dbp->getUserId();
		$sql = "SELECT 
			sch.*,
			(SELECT v.name_kh FROM ln_view AS v WHERE v.type =29 AND v.key_code = sch.ispay_bank LIMIT 1) AS lastPaymentTitle,
			(SELECT CONCAT(last_name,' ',first_name) FROM rms_users WHERE rms_users.id = $userId LIMIT 1 ) AS currentUserName
		FROM `ln_saleschedule` AS sch 
		WHERE 
			sch.sale_id = $saleId 
			AND sch.status=1
		ORDER BY sch.no_installment DESC 
		LIMIT 1";
		return $db->fetchRow($sql);
	}
	
	
	public function updateNotePropertyLayoutNote($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
	
			$arr = array(
					'noteForLayout'=>$data['noted'],
			);
			$where=" id = ".$data['id'];
			$this->_name="ln_properties";
			$this->update($arr, $where);
	
			$db->commit();
			return 1;
		}catch (Exception $e){
			$err =$e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
			$db->rollBack();
		}
	}
}