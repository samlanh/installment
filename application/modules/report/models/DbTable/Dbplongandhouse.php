<?php
class Report_Model_DbTable_Dbplongandhouse extends Zend_Db_Table_Abstract
{
	function getAllHeadproperties($search=null){
		$db = $this->getAdapter();
		$to_enddate = (empty($search['end_date']))? '1': " s.buy_date <= '".$search['end_date']." 23:59:59'";
		$sql = "SELECT p.`id`,
			(SELECT project_name FROM ln_project WHERE br_id = p.`branch_id` limit 1) AS branch_name,
			p.`land_code`,
			p.`land_address`,
			p.`property_type`,
			p.`street`,
			p.hardtitle ,
			p.noteForLayout,
			(SELECT ps.title FROM `ln_plongstep_option` AS ps,ln_processing_plong AS pr WHERE ps.id = pr.process_status AND `p`.`id` = `pr`.`property_id` ORDER BY pr.id DESC limit 1 ) AS processing,
			(SELECT t.type_nameen FROM `ln_properties_type` AS t WHERE t.id = p.`property_type` LIMIT 1) AS pro_type,
			rp.layout_type,rp.date AS received_date,
			(SELECT cl.name_kh FROM ln_client AS cl WHERE cl.`client_id` = rp.`customer_id` LIMIT 1) AS client_name,
			(SELECT cl.phone FROM ln_client AS cl WHERE cl.`client_id` = rp.`customer_id` LIMIT 1) AS tel,
			s.price_sold,
			s.id as saleId,
		(SELECT SUM(crm.total_principal_permonthpaid+crm.extra_payment) FROM `ln_client_receipt_money` AS crm WHERE crm.sale_id=s.id LIMIT 1) AS totalPaid,
		";
		$sql.=" (SELECT first_name FROM `rms_users` WHERE id=p.user_id LIMIT 1) AS user_name
			FROM `ln_properties` AS p
				LEFT JOIN ln_receiveplong AS rp ON p.`id` = rp.`house_id` AND rp.`status`=1
				LEFT JOIN ln_sale AS s ON s.`house_id` = p.`id` AND s.`is_cancel`=0 AND s.`status`=1
			WHERE p.`status`=1 ";
	
		$from_date =(empty($search['start_date']))? '1': " p.create_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " p.create_date <= '".$search['end_date']." 23:59:59'";
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
		if(!empty($search['plong_type'])){
			$where.= " AND rp.`layout_type` = '".$search['plong_type']."'";
		}
		if($search['process_status']>0){
			$where.=" AND (SELECT pr.process_status FROM ln_processing_plong AS pr WHERE `p`.`id` = `pr`.`property_id` AND pr.process_status LIMIT 1) = ".$search['process_status'];
		}
		if($search['plong_processtype']>0){
			if($search['plong_processtype']==1){
				//AND p.`hardtitle` !=''
				$where.="  AND p.id NOT IN (SELECT pr.property_id FROM ln_processing_plong AS pr WHERE `p`.`id` = `pr`.`property_id`)";
				$where.="  AND p.id NOT IN (SELECT rps.house_id FROM ln_receiveplong AS rps WHERE `p`.`id` = rps.`house_id` AND rps.status=1 ) " ;
	
			}elseif($search['plong_processtype']==2){
				$where.=" AND p.id IN (SELECT rp.house_id FROM ln_receiveplong AS pr WHERE `p`.`id` = rp.`house_id` ) ";
				$where.=" AND p.id NOT IN (SELECT pr.property_id FROM ln_processing_plong AS pr WHERE `p`.`id` = `pr`.`property_id`) ";
			}else{
				$where.=" AND p.id IN (SELECT rps.house_id FROM ln_receiveplong AS rps WHERE `p`.`id` = rps.`house_id` AND rps.status=1 ) ";
				$where.=" AND p.id IN (SELECT pr.property_id FROM ln_processing_plong AS pr WHERE `p`.`id` = `pr`.`property_id`) ";
				//AND p.`hardtitle` !=''
			}
		}
	
		$search['sale_status'] = empty($search['sale_status'])?0:$search['sale_status'];
		if($search['sale_status']>0){
			if($search['sale_status']==1){//full paid
				$where.=" AND s.price_sold <= ((SELECT COALESCE(SUM(rm.total_principal_permonthpaid+rm.extra_payment),0) FROM `ln_client_receipt_money` as rm WHERE rm.status=1 AND rm.sale_id=s.id LIMIT 1)) ";
			}else if($search['sale_status']==2){
				$where.=" AND s.price_sold > ((SELECT COALESCE(SUM(rm.total_principal_permonthpaid+rm.extra_payment),0) FROM `ln_client_receipt_money` as rm WHERE rm.status=1 AND rm.sale_id=s.id LIMIT 1) ) ";
			}else if($search['sale_status']==3){
				$where.=" AND s.is_cancel = 0 ";
			}else if($search['sale_status']==4){
				$where.=" AND s.is_cancel = 1 ";
			}else{
			}
		}
	
		$where.= " ORDER BY p.branch_id,p.`property_type`,p.`street` ASC, LENGTH(land_address), land_address ASC  ";
		return $db->fetchAll($sql.$where);
	}
	function getAllPlongStep($search){
			
		$dbp = new Application_Model_DbTable_DbGlobal();
		$from_date =(empty($search['start_date']))? '1': " pr.date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " pr.date <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		$sql="SELECT `pr`.*,
		(SELECT `ln_project`.`project_name`   	FROM `ln_project`  	WHERE (`ln_project`.`br_id` = `pr`.`branch_id`)	LIMIT 1) AS `branch_name`,
		`c`.`name_kh`         AS `name_kh`,
		`p`.`land_address`    AS `land_address`,
		`p`.`street`          AS `street`,
		p.hardtitle,
		c.phone,
		CASE
		WHEN  pr.process_status = 1 THEN '1.HQ-P'
		WHEN  pr.process_status = 2 THEN '2.P-HQ'
		WHEN  pr.process_status = 3 THEN '3.HQ-T'
		WHEN  pr.process_status = 4 THEN '4.HQ-P'
		WHEN  pr.process_status = 5 THEN '5.HQ-C'
		END AS processing
		";
		$sql.=$dbp->caseStatusShowImage("pr.status");
		$sql.="
		FROM (`ln_processing_plong` `pr`,
		`ln_client` `c`
		JOIN `ln_properties` `p`)
		WHERE (`c`.`client_id` = `pr`.`customer_id`)
		AND (`p`.`id` = `pr`.`property_id`)
		";
		if(!empty($search['adv_search'])){
			$s_where = array();
		}
		if(!empty($search['client_name']) AND ($search['client_name'])>0){
			$where.= " AND `c`.`client_id`=".$search['client_name'];
		}
		if(($search['branch_id'])>0){
			$where.= " AND pr.branch_id = ".$search['branch_id'];
		}
		if($search['land_id']>0){
			$where.= " AND pr.property_id = ".$search['land_id'];
		}
		if (!empty($search['process_status'])){
			$where.= " AND pr.process_status = ".$search['process_status'];
		}
		$where.=$dbp->getAccessPermission("`pr`.`branch_id`");
	
		$order = " ORDER BY pr.id DESC";
		$db = $this->getAdapter();
		return $db->fetchAll($sql.$where.$order);
	}
	function getPlongStepDetailByID($id,$step=1){
		$db = $this->getAdapter();
		$sql="SELECT * FROM ln_processing_plong_detail WHERE processplong_id =$id AND process_status = $step ORDER BY id DESC LIMIT 1";
		return $db->fetchRow($sql);
	}
	function getRecivePlongInfo($id){
		$db = $this->getAdapter();
		$sql="SELECT
		(SELECT project_name FROM `ln_project` WHERE ln_project.br_id =c.`branch_id` LIMIT 1) AS branch_name,
		(SELECT logo FROM `ln_project` WHERE ln_project.br_id =c.`branch_id` LIMIT 1) AS project_logo,
		cl.name_kh AS client_namekh,
		cl.name_en AS client_nameen,
		(SELECT v.name_kh FROM `ln_view` AS v WHERE v.key_code = cl.`sex` AND v.`type`=11 LIMIT 1 ) AS sexKh,
		(SELECT v.name_kh FROM `ln_view` AS v WHERE v.key_code = cl.`client_d_type` AND v.`type`=23 LIMIT 1 ) AS client_d_typekh,
		cl.`nation_id`,
		cl.`house`,
		cl.`street`,
		(SELECT p.province_kh_name FROM `ln_province` AS p WHERE p.province_id = cl.`pro_id` LIMIT 1) AS province_kh_name,
		(SELECT d.district_namekh FROM `ln_district` AS d WHERE d.dis_id = cl.`dis_id` LIMIT 1) AS district_namekh,
		(SELECT com.commune_namekh FROM `ln_commune` AS com WHERE com.com_id = cl.`com_id` LIMIT 1) AS commune_namekh,
		(SELECT vil.village_namekh FROM `ln_village` AS vil WHERE vil.vill_id = cl.`village_id` LIMIT 1) AS village_namekh,
		(SELECT CONCAT(pro.land_address,'-',pro.street) FROM `ln_properties` AS pro WHERE pro.id = s.`house_id` LIMIT 1) AS propertyinfo,
		(SELECT pro.hardtitle FROM `ln_properties` AS pro WHERE pro.id = s.`house_id` LIMIT 1) AS hardtitle,
		(SELECT  CONCAT(last_name,' ',first_name) FROM rms_users WHERE rms_users.id=c.user_id LIMIT 1) AS userNameInput,
		cl.arid_no AS witnesses,
		c.*
		FROM `ln_receiveplong` AS c,
		`ln_sale` AS s,
		`ln_client` AS cl
		WHERE
		s.`id` = c.`sale_id`
		AND cl.`client_id` = s.`client_id`
		AND c.`id`=$id LIMIT 1";
		return $db->fetchRow($sql);
	}
	function getReportAllIssueHouse($search = null){
		$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
			
		$from_date =(empty($search['start_date']))? '1': " rs.issue_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " rs.issue_date <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		$sql = "SELECT rs.id,
		(SELECT ln_project.project_name FROM `ln_project` WHERE ln_project.br_id = rs.branch_id LIMIT 1) AS branch_name,
		(SELECT name_kh FROM ln_client AS c WHERE `c`.`client_id` = `s`.`client_id` LIMIT 1) customer_name,
		(SELECT phone FROM ln_client AS c WHERE `c`.`client_id` = `s`.`client_id` LIMIT 1) AS tel,
		p.land_address ,
		p.street  AS street,
		CASE
		WHEN  `rs`.`payment_id` = 1 THEN '".$tr->translate("IS_PAYOFF")."'
		WHEN  `rs`.`payment_id` = 2 THEN '".$tr->translate("PAY_INSTALLMENT")."'
		END AS payment_id,
		rs.electric_start,rs.water_start,rs.issue_date,
		(SELECT  first_name FROM rms_users WHERE rms_users.id=rs.user_id LIMIT 1) AS user_name,
		rs.note
		FROM
		ln_sale AS s,
		`ln_properties` `p`,
		ln_issue_house AS rs
		WHERE s.id = rs.sale_id AND p.id = s.house_id AND rs.status=1 ";
			
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[] = " p.land_address LIKE '%{$s_search}%'";
			$s_where[] = " p.street LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if(!empty($search['streetlist'])){
			$where.= " AND street ='".$search['streetlist']."'";
		}
		if(!empty($search['land_id']) AND $search['land_id']>-1){
			$where.= " AND (s.house_id = ".$search['land_id']." OR p.old_land_id LIKE '%".$search['land_id']."%')";
		}
		if($search['branch_id']>0){
			$where.= " AND rs.branch_id = ".$search['branch_id'];
		}
		if($search['payment_id']>0){
			$where.= " AND rs.payment_id = ".$search['payment_id'];
		}
		if(!empty($search['client_name'])){
			$where.= " AND `s`.`client_id` = ".$search['client_name'];
		}
	
		$where.=$dbp->getAccessPermission("rs.branch_id");
		$order=" ORDER BY s.id DESC ";
		return $db->fetchAll($sql.$where.$order);
	}
	public function getCustomerReceivedPlong($search=null){
		$db = $this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$plogtitle = $tr->translate('PLONG_TITLE');
	
		$from_date =(empty($search['from_date_search']))? '1': "c.`create_date` >= '".$search['from_date_search']." 00:00:00'";
		$to_date = (empty($search['to_date_search']))? '1': "c.`create_date` <= '".$search['to_date_search']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		$sql ='SELECT c.`id`,
		p.`project_name` as branch_name,
		clie.`name_kh` AS client_name,
		(SELECT protype.type_nameen FROM `ln_properties_type` AS protype WHERE protype.id = pro.`property_type` LIMIT 1) AS property_type,
		pro.`land_address`,pro.`street`,`layout_type`,c.date,
		c.create_date,c.note,c.`status`,"'.$plogtitle.'",
		pro.`hardtitle`,
		(SELECT first_name FROM `rms_users` WHERE  id=c.user_id LIMIT 1) AS user_name
		FROM `ln_receiveplong` AS c ,`ln_project` AS p,`ln_properties` AS pro,
		`ln_client` AS clie
		WHERE p.`br_id` = c.`branch_id` AND pro.`id` = c.`house_id` AND
		clie.`client_id` = c.`customer_id` AND c.`status`=1';
	
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission("c.`branch_id`");
	
		if($search['branch_id']>0){
			$where.= " AND c.branch_id = ".$search['branch_id'];
		}
		if($search['land_id']>0){
			$where.= " AND c.house_id = ".$search['land_id'];
		}
		if($search['client_name']>0){
			$where.= " AND c.customer_id = ".$search['client_name'];
		}
		if(!empty($search['plong_type'])){
			$where.= " AND c.layout_type = '".$search['plong_type']."'";
		}
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[] = " clie.`name_kh` LIKE '%{$s_search}%'";
			$s_where[] = " pro.`land_address` LIKE '%{$s_search}%'";
			$s_where[] = " pro.`street` LIKE '%{$s_search}%'";
			$s_where[] = " c.`note` LIKE '%{$s_search}%'";
			$s_where[] = " pro.`hardtitle` LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		$dbp = new Application_Model_DbTable_DbGlobal();
		$where.=$dbp->getAccessPermission("c.`branch_id`");
	
		$where.=" ORDER BY c.`id` DESC ";
		return $db->fetchAll($sql.$where);
	}
 }