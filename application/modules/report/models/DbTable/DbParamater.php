<?php
class Report_Model_DbTable_DbParamater extends Zend_Db_Table_Abstract
{
      public function getAllHoliday($search=null){
    	$db = $this->getAdapter();		
          $sql="SELECT id,holiday_name,amount_day,start_date,end_date,status,modify_date,note FROM ln_holiday ";
//           $where = '';
          $from_date =(empty($search['start_date']))? '1': "start_date >= '".$search['start_date']." 00:00:00'";
          $to_date = (empty($search['end_date']))? '1': "end_date <= '".$search['end_date']." 23:59:59'";
          $where = " WHERE ".$from_date." AND ".$to_date;
          if($search['search_status']>-1){
          	$where.= " AND status = ".$search['search_status'];
          }
          elseif(!empty($search['adv_search'])){
          	$s_where = array();
          	$s_search = $search['adv_search'];
          	$s_where[] = " holiday_name LIKE '%{$s_search}%'";
          	$s_where[]=" start_date LIKE '%{$s_search}%'";
          	$s_where[]=" end_date LIKE '%{$s_search}%'";
          	$s_where[]=" amount_day LIKE '%{$s_search}%'";
          	$s_where[]=" note LIKE '%{$s_search}%'";
          	$where .=' AND '.implode(' OR ',$s_where).'';
          }      
          return $db->fetchAll($sql.$where);
    }
    public function getALLzone($search = null){
    	$db = $this->getAdapter();
    	$sql="SELECT zone_id,zone_name,zone_num,modify_date,status FROM ln_zone WHERE 1";
    	$Other =" ORDER BY zone_id DESC ";
    	$where = '';
    	if($search['search_status']>-1){
    		$where.= " AND status = ".$search['search_status'];
    	}
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = $search['adv_search'];
    		$s_where[] = " zone_name LIKE '%{$s_search}%'";
    		$s_where[]=" zone_num LIKE '%{$s_search}%'";
    		$s_where[]=" modify_date LIKE '%{$s_search}%'";
    		$where .=' AND '.implode(' OR ',$s_where).'';
    	}
    	//echo $sql.$where.$Other;
    	return $db->fetchAll($sql.$where.$Other);
    }
    public function getALLstaff($search = null){
    	$db = $this->getAdapter();
    	$from_date =(empty($search['from_date']))? '1': "create_date >= '".$search['from_date']." 00:00:00'";
    	$to_date = (empty($search['to_date']))? '1': "create_date <= '".$search['to_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;	
    	$sql="SELECT co_id,co_code,co_khname,co_firstname,(SELECT name_kh FROM ln_view WHERE TYPE = 11 AND key_code=sex ) AS sex
    	,email,basic_salary,start_date,end_date,contract_no,shift,workingtime,(SELECT position_kh FROM ln_position WHERE id=position_id) As position,
    	tel,basic_salary,national_id,address,degree,
    	(SELECT project_name FROM ln_project WHERE br_id = branch_id limit 1) AS branch_name,note FROM ln_staff WHERE 1";
    	$Other =" ORDER BY co_id DESC ";
    	//$where = '';
    	//echo $search['txtsearch'];
    	if(!empty($search['co_khname'])){
    		$where.= " AND co_id = ".$search['co_khname'];
    	}
    	if(!empty($search['adv_search'])){
    		$s_where = array();
    		$s_search = $search['adv_search'];
    		$s_where[] =" co_code LIKE '%{$s_search}%'";
    		$s_where[]=" co_khname LIKE '%{$s_search}%'";
    		$s_where[]=" co_firstname LIKE '%{$s_search}%'";
    		$s_where[]=" email LIKE '%{$s_search}%'";
    		$s_where[]=" tel LIKE '%{$s_search}%'";
    		$s_where[]=" address LIKE '%{$s_search}%'";
    		$s_where[]=" national_id LIKE '%{$s_search}%'";
    		$where .=' AND '.implode(' OR ',$s_where). '';
    	}
    	//echo  $sql.$where.$Other;
    	return $db->fetchAll($sql.$where.$Other);
    }
    public function getAllVillage($search= null){
    	$db = $this->getAdapter();
    	$from_date =(empty($search['from_date']))? '1': "modify_date >= '".$search['from_date']." 00:00:00'";
    	$to_date = (empty($search['to_date']))? '1': "modify_date <= '".$search['to_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	$sql = "SELECT
				v.vill_id,v.village_namekh,v.village_name,v.displayby,
				(SELECT commune_name FROM ln_commune WHERE v.commune_id=com_id LIMIT 1) AS commune_name,
				d.district_name,p.province_en_name
				,v.modify_date,v.status,
				(SELECT first_name FROM rms_users WHERE id=v.user_id LIMIT 1) AS user_name
				FROM ln_village AS v,`ln_commune` AS c, `ln_district` AS d , `ln_province` AS p
				WHERE v.commune_id = c.com_id AND c.district_id = d.dis_id AND d.pro_id = p.province_id ";
    	
        if($search['province_name']>0){
        	$where.= " AND p.province_id = ".$search['province_name'];
        }
        if(!empty($search['district_name'])){
        	$where.= " AND d.dis_id = ".$search['district_name'];
        }        
		if($search['search_status']>-1){
			$where.= " AND v.status = ".$search['search_status'];
		}
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = $search['adv_search'];
			$s_where[] = " v.village_name LIKE '%{$s_search}%'";
			$s_where[]=" v.village_namekh LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		$order= ' ORDER BY v.vill_id DESC ';
		return $db->fetchAll($sql.$where.$order);
    }
function getAllBranch($search=null){
    		$db = $this->getAdapter();
    	$sql = "SELECT b.br_id,b.branch_namekh,b.branch_nameen,b.br_address,b.branch_code,b.branch_tel,b.fax,
(SELECT v.name_en FROM `ln_view` AS v WHERE v.`type` = 4 AND v.key_code = b.displayby)AS displayby,b.other,b.`status` FROM ln_branch AS b  ";
    	$where = ' WHERE b.branch_namekh!="" AND b.branch_nameen !="" ';
    	if($search['select_branch_nameen']>0){
    		$where.= " AND b.br_id = ".$search['select_branch_nameen'];
    	}
    	if($search['status_search']>-1){
    		$where.= " AND b.status = ".$search['status_search'];
    	}
    	
    	if(!empty($search['adv_search'])){
    		$s_where=array();
    		$s_search=$search['adv_search'];
    		$s_where[]=" b.branch_namekh LIKE '%{$s_search}%'";
    		$s_where[]=" b.branch_nameen LIKE '%{$s_search}%'";
    		$s_where[]=" b.br_address LIKE '%{$s_search}%'";
    		$s_where[]=" b.branch_code LIKE '%{$s_search}%'";
    		$s_where[]=" b.branch_tel LIKE '%{$s_search}%'";
    		$s_where[]=" b.fax LIKE '%{$s_search}%'";
    		$s_where[]=" b.other LIKE '%{$s_search}%'";
    		$s_where[]=" b.displayby LIKE '%{$s_search}%'";
    		$where.=' AND ('.implode(' OR ',$s_where).')';
    	}
    	$order=' ORDER BY b.br_id DESC';
   //echo $sql.$where;
   return $db->fetchAll($sql.$where.$order);
    	}
    function getAllProperties($search=null){
    		$db = $this->getAdapter();
    		$from_date =(empty($search['start_date']))? '1': " create_date >= '".$search['start_date']." 00:00:00'";
    		$to_date = (empty($search['end_date']))? '1': " create_date <= '".$search['end_date']." 23:59:59'";
    		$where = " AND ".$from_date." AND ".$to_date;
    		$sql = "SELECT p.`id`,p.`branch_id`,p.`land_code`,p.`land_address`,p.`property_type`,p.`street`,
				(SELECT t.type_nameen FROM `ln_properties_type` AS t WHERE t.id = p.`property_type`) AS pro_type,
				p.`width`,p.`height`,p.`land_size`,p.`price`,p.`land_price`,p.`house_price`
				 FROM `ln_properties` AS p WHERE p.`status`=1";
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
    			$s_search=$search['adv_search'];
    			$s_where[]=" p.`land_code` LIKE '%{$s_search}%'";
    			$s_where[]=" p.`land_address` LIKE '%{$s_search}%'";
    			$s_where[]=" p.`land_size` LIKE '%{$s_search}%'";
    			$s_where[]=" p.`height` LIKE '%{$s_search}%'";
    			$s_where[]=" p.width LIKE '%{$s_search}%'";
    			$s_where[]=" p.`price` LIKE '%{$s_search}%'";
    			$s_where[]=" p.`land_price` LIKE '%{$s_search}%'";
    			$s_where[]=" p.`house_price` LIKE '%{$s_search}%'";
    			$where.=' AND ('.implode(' OR ',$s_where).')';
    		}
    		//echo $sql.$where;
    		return $db->fetchAll($sql.$where);
    	}
    	function getCancelSale($search=null){
    		$db = $this->getAdapter();
    		$from_date =(empty($search['from_date_search']))? '1': "c.`create_date` >= '".$search['from_date_search']." 00:00:00'";
    		$to_date = (empty($search['to_date_search']))? '1': "c.`create_date` <= '".$search['to_date_search']." 23:59:59'";
    		$where = " AND ".$from_date." AND ".$to_date;
    		$sql='SELECT c.`id`,
				s.`sale_number`,
				clie.`client_number`,
				CONCAT(clie.`name_kh`," ",clie.`name_en`) AS client_name,
				p.`project_name`,pro.`land_code`,c.`create_date`,
				(SELECT pt.`type_nameen` FROM `ln_properties_type` AS pt WHERE pt.`id` = pro.`property_type`) AS type_name,
				pro.`property_type`,pro.`land_address`,pro.`street`
				FROM `ln_sale_cancel` AS c , `ln_sale` AS s, `ln_project` AS p,`ln_properties` AS pro,
				`ln_client` AS clie
				WHERE s.`id` = c.`sale_id` AND p.`br_id` = c.`branch_id` AND pro.`id` = c.`property_id` AND
				clie.`client_id` = s.`client_id`';
    		$order = " ORDER BY c.`branch_id` DESC";
    		if($search['branch_id_search']>-1){
    			$where.= " AND c.branch_id = ".$search['branch_id_search'];
    		}
    		if(!empty($search['property_type'])){
    			$where.= " AND pro.`property_type` = ".$search['property_type'];
    		}
    		if(!empty($search['adv_search'])){
    			$s_where = array();
    			$s_search = addslashes(trim($search['adv_search']));
    			$s_where[] = " clie.`client_number` LIKE '%{$s_search}%'";
    			$s_where[] = " s.`sale_number` LIKE '%{$s_search}%'";
    			$s_where[] = " p.`project_name` LIKE '%{$s_search}%'";
    			$s_where[] = " pro.`land_code` LIKE '%{$s_search}%'";
    			$where .=' AND ('.implode(' OR ',$s_where).')';
    		}
    		return $db->fetchAll($sql.$where.$order);
    		
    	}
    	function getAllIncome($search=null){
    		$db = $this->getAdapter();
    		$session_user=new Zend_Session_Namespace('auth');
    		$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
    		$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
    		$where = " WHERE ".$from_date." AND ".$to_date;
    	
    		$sql=" SELECT id,
    		(SELECT project_name FROM `ln_project` WHERE ln_project.br_id =branch_id LIMIT 1) AS branch_name,
    		title, invoice,branch_id,
    		(SELECT name_en FROM `ln_view` WHERE type=12 and key_code=category_id limit 1) AS category_name,
    		total_amount,description,date,status FROM ln_income ";
    	
    		if (!empty($search['adv_search'])){
    			$s_where = array();
    			$s_search = trim(addslashes($search['adv_search']));
    			$s_where[] = " description LIKE '%{$s_search}%'";
    			$s_where[] = " title LIKE '%{$s_search}%'";
    			$s_where[] = " total_amount LIKE '%{$s_search}%'";
    			$s_where[] = " invoice LIKE '%{$s_search}%'";
    			$where .=' AND ('.implode(' OR ',$s_where).')';
    		}
    		if($search['branch_id']>0){
    			$where.= " AND branch_id = ".$search['branch_id'];
    		}
    		if($search['category_id']>-1 || !empty($search['category_id'])){
    			$where.= " AND category_id = ".$search['category_id'];
    		}
    		$order=" order by id desc ";
    		return $db->fetchAll($sql.$where.$order);
    	}
    	function getAllExpense($search=null){
    		$db = $this->getAdapter();
    		$session_user=new Zend_Session_Namespace('auth');
    		$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
    		$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
    		$where = " WHERE ".$from_date." AND ".$to_date;
    	
    		$sql=" SELECT id,
    		(SELECT project_name FROM `ln_project` WHERE ln_project.br_id =branch_id LIMIT 1) AS branch_name,
    		title,invoice,
    	
    		(SELECT name_en FROM `ln_view` WHERE type=13 and key_code=category_id limit 1) AS category_name,
    		total_amount,description,date,status FROM ln_expense ";
    	
    		if (!empty($search['adv_search'])){
    			$s_where = array();
    			$s_search = trim(addslashes($search['adv_search']));
    			$s_where[] = " description LIKE '%{$s_search}%'";
    			$s_where[] = " title LIKE '%{$s_search}%'";
    			$s_where[] = " total_amount LIKE '%{$s_search}%'";
    			$s_where[] = " invoice LIKE '%{$s_search}%'";
    			$where .=' AND ('.implode(' OR ',$s_where).')';
    		}
    		if($search['category_id_expense']>-1 || !empty($search['category_id_expense'])){
    			$where.= " AND category_id = ".$search['category_id_expense'];
    		}
    		if($search['branch_id']>0){
    			$where.= " AND branch_id = ".$search['branch_id'];
    		}
    		$order=" order by id desc ";
    		return $db->fetchAll($sql.$where.$order);
    	}
    	function getSoldIncome($search=null){
    		$db= $this->getAdapter();
    		$where='';
    		$sql = "SELECT * FROM v_soldreport WHERE 1";
    		$from_date =(empty($search['start_date']))? '1': " buy_date >= '".$search['start_date']." 00:00:00'";
    		$to_date = (empty($search['end_date']))? '1': " buy_date <= '".$search['end_date']." 23:59:59'";
    		$where.= " AND ".$from_date." AND ".$to_date;
    		if($search['branch_id']>0){
    			$where.= " AND branch_id = ".$search['branch_id'];
    		}
    		if (!empty($search['adv_search'])){
    			$s_where = array();
    			$s_search = trim(addslashes($search['adv_search']));
    			$s_where[] = " sale_number LIKE '%{$s_search}%'";
    			$s_where[] = " price_before LIKE '%{$s_search}%'";
    			$s_where[] = " name_kh LIKE '%{$s_search}%'";
    			$s_where[] = " name_en LIKE '%{$s_search}%'";
    			$s_where[] = " client_number LIKE '%{$s_search}%'";
    			$where .=' AND ('.implode(' OR ',$s_where).')';
    		}
    		return $db->fetchAll($sql.$where);
    	}
    	function getCollectPayment($search=null){
    		$db= $this->getAdapter();
    		//$where='';
    		$sql = "SELECT * FROM v_getcollectmoney WHERE 1";
    		$from_date =(empty($search['start_date']))? '1': " date_pay >= '".$search['start_date']." 00:00:00'";
	      	$to_date = (empty($search['end_date']))? '1': " date_pay <= '".$search['end_date']." 23:59:59'";
	      	$where = " AND ".$from_date." AND ".$to_date;
	      	if($search['branch_id']>0){
	      		$where.= " AND branch_id = ".$search['branch_id'];
	      	}
	      	if (!empty($search['adv_search'])){
	      		$s_where = array();
	      		$s_search = trim(addslashes($search['adv_search']));
	      		$s_where[] = " client_number LIKE '%{$s_search}%'";
	      		$s_where[] = " name_kh LIKE '%{$s_search}%'";
	      		$s_where[] = " client_name LIKE '%{$s_search}%'";
	      		$s_where[] = " receipt_no LIKE '%{$s_search}%'";
	      		$where .=' AND ('.implode(' OR ',$s_where).')';
	      	}
    		return $db->fetchAll($sql.$where);
    	}
    	function getTermCodiction(){
    		$db =$this->getAdapter();
    		$sql="SELECT * FROM `ln_termcondiction` AS t WHERE t.`status`=1 LIMIT 1";
    		return $db->fetchRow($sql);
    	}
    	function getSaleHistory($search=null){
    		$db= $this->getAdapter();
    		$sql="SELECT * FROM `v_getsalehistory` WHERE 1 ";
    		$order =' ORDER BY house_id DESC';
    		$from_date =(empty($search['start_date']))? '1': " create_date >= '".$search['start_date']." 00:00:00'";
    		$to_date = (empty($search['end_date']))? '1': " create_date <= '".$search['end_date']." 23:59:59'";
    		$where = " AND ".$from_date." AND ".$to_date;
    		
    		if($search['branch_id']>0){
    			$where.= " AND branch_id = ".$search['branch_id'];
    		}
    		if (!empty($search['adv_search'])){
    			$s_where = array();
    			$s_search = trim(addslashes($search['adv_search']));
    			$s_where[] = " sale_number LIKE '%{$s_search}%'";
    			$s_where[] = " project_name LIKE '%{$s_search}%'";
    			$s_where[] = " client_code LIKE '%{$s_search}%'";
    			$s_where[] = " client_name_kh LIKE '%{$s_search}%'";
    			$s_where[] = " client_name_en LIKE '%{$s_search}%'";
    			$where .=' AND ('.implode(' OR ',$s_where).')';
    		}
    		if(!empty($search['property_type'])){
    			$where.= " AND property_type_id = ".$search['property_type'];
    		}
    		return $db->fetchAll($sql.$where.$order);
    	}
    	function getAgreementBySaleID($id=null){
    		$db = $this->getAdapter();
    		$sql="SELECT * FROM `v_agreement` WHERE id = ".$id;
    		return $db->fetchRow($sql);
    	}
    	function getScheduleBySaleID($id=null){
    		$db = $this->getAdapter();
    		$sql=" SELECT * FROM `ln_saleschedule` AS sc WHERE sc.`sale_id`= ".$id;
    		$order = ' ORDER BY sc.`date_payment` ASC';
    		return $db->fetchAll($sql.$order);
    	}
    	
}

