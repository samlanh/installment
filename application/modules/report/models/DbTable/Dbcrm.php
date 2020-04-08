<?php
class Report_Model_DbTable_Dbcrm extends Zend_Db_Table_Abstract
{

	public  function getStudentInfo($search){
		$_db = $this->getAdapter();
		$sql = "SELECT stu_id,stu_enname,stu_khname,
		(SELECT name_kh FROM `rms_view` WHERE type=2 AND key_code = sex) as gender
		,stu_code,dob,remark,tel,(SELECT province_kh_name FROM `rms_province` WHERE `province_id`= rms_student.province_id) as pro,
		father_phone,mother_phone,email,father_khname,father_enname,father_nation,(SELECT `occu_name` FROM `rms_occupation` WHERE `occupation_id` = father_job)as far_job,(SELECT `occu_name` FROM `rms_occupation` WHERE `occupation_id` = mother_job)as mom_job,
		mother_enname,mother_khname,mother_nation,guardian_khname,guardian_nation,guardian_document,guardian_enname,address,home_num,street_num,village_name,commune_name,district_name,
		(SELECT `occu_name` FROM `rms_occupation` WHERE `occupation_id` = guardian_job)as guar_job,guardian_tel,guardian_email,remark,
		(SELECT name_kh FROM `rms_view` WHERE type=1 AND key_code = status) as status,nationality
		FROM rms_student where status = 1";
		
		$sql.='';
		if(empty($search)){
			$_db->fetchAll($sql);
		}
		if(!empty($search['txtsearch']))
		{
			$s_where = array();
			$s_search = trim($search['txtsearch']);
			$s_where[] = " stu_enname LIKE '%{$s_search}%'";
			$s_where[] = " stu_khname LIKE '%{$s_search}%'";
			$s_where[] = " stu_code LIKE '%{$s_search}%'";
			$s_where[] = " nationality LIKE '%{$s_search}%'";
			// 			$s_where[] = " en_name LIKE '%{$s_search}%'";
			// 			$s_where[] = " sex LIKE '%{$s_search}%'";
			//			$s_where[] = " nationality LIKE '%{$s_search}%'";
			$sql .=' AND ( '.implode(' OR ',$s_where).')';
		}
		return $_db->fetchAll($sql);
	}
	function getAllCRM($search = ''){
		$db = $this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$sql="SELECT c.*,
			(SELECT b.branch_nameen FROM `rms_branch` AS b  WHERE b.br_id = c.branch_id LIMIT 1) AS branch_name,
			CASE
			WHEN  c.sex = 1 THEN '".$tr->translate("MALE")."'
			WHEN  c.sex = 2 THEN '".$tr->translate("FEMALE")."'
			END AS sexTitle,
			CASE
			WHEN  c.ask_for = 1 THEN '".$tr->translate("KHMER_KNOWLEDGE")."'
			WHEN  c.ask_for = 2 THEN '".$tr->translate("ENGLISH")."'
			WHEN  c.ask_for = 3 THEN '".$tr->translate("UNIVERSITY")."'
			WHEN  c.ask_for = 4 THEN '".$tr->translate("OTHER")."'
			END AS ask_for_title,
			CASE
			WHEN  c.crm_status = 0 THEN '".$tr->translate("DROPPED")."'
			WHEN  c.crm_status = 1 THEN '".$tr->translate("PROCCESSING")."'
			WHEN  c.crm_status = 2 THEN '".$tr->translate("WAITING_TEST")."'
			WHEN  c.crm_status = 3 THEN '".$tr->translate("COMPLETED")."'
			END AS crm_status_title,
			(SELECT k.title FROM `rms_know_by` AS k WHERE k.id = c.know_by LIMIT 1 ) AS know_by_title,
			(SELECT COUNT(cr.id) FROM `rms_crm_history_contact` AS cr WHERE cr.crm_id = c.id LIMIT 1) AS amountContact,
			(SELECT CONCAT(first_name) FROM rms_users WHERE c.user_id=id LIMIT 1 ) AS userby
			FROM `rms_crm` AS c
			WHERE 1
		";
		$where = ' ';
		$from_date =(empty($search['start_date']))? '1': " c.create_date >= '".date("Y-m-d",strtotime($search['start_date']))." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " c.create_date <= '".date("Y-m-d",strtotime($search['end_date']))." 23:59:59'";
		$where.= " AND  ".$from_date." AND ".$to_date;
		if(!empty($search['advance_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['advance_search']));
			$s_where[] = " c.kh_name LIKE '%{$s_search}%'";
			$s_where[] = " c.first_name LIKE '%{$s_search}%'";
			$s_where[] = " c.last_name LIKE '%{$s_search}%'";
			$s_where[] = " c.tel LIKE '%{$s_search}%'";
			$where .=' AND ( '.implode(' OR ',$s_where).')';
		}
		if(!empty($search['branch_search'])){
			$where.= " AND c.branch_id = ".$db->quote($search['branch_search']);
		}
		if(!empty($search['ask_for_search'])){
			$where.= " AND c.ask_for = ".$db->quote($search['ask_for_search']);
		}
		if(!empty($search['know_by_search'])){
			$where.= " AND c.know_by = ".$db->quote($search['know_by_search']);
		}
		if($search['status_search']>-1){
			$where.= " AND c.crm_status = ".$db->quote($search['status_search']);
		}
		$dbp = new Application_Model_DbTable_DbGlobal();
		$where.=$dbp->getAccessPermission('c.branch_id');
		$where.=" ORDER BY c.id DESC";
		$row = $db->fetchAll($sql.$where);
		$resutl = $row;
		if (!empty($search['prev_concern'])){
			$resutl = array();
			$epl = explode(",", $search['prev_concern']);
			$array = array();
			foreach ($epl as $ss){
				$key = $this->checkPrevConcern($ss);
				$array[$key] = $key;
			}
			
			if (!empty($row)) foreach ($row as $key => $rs){
				$exp = explode(",", $rs['prev_concern']);
				foreach ($exp as $ss){
					if (in_array($ss, $array)) {
						$resutl[$key] = $rs;
						break;
					}
				}
			}
		}
		return $resutl;
	}
	function checkPrevConcern($value){
		$db = $this->getAdapter();
		$sql="SELECT v.key_code FROM `rms_view` AS v WHERE v.name_kh = '$value' AND v.type=22  LIMIT 1";
		return $db->fetchOne($sql);
	}
	function getAllCRMDailyContact($search = ''){
		$db = $this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$sql="
			SELECT cc.id, cc.customer_id, cc.contact_date, cc.feedback,cc.next_contact,
			(SELECT CONCAT(first_name) FROM rms_users WHERE cc.user_contact=id LIMIT 1 ) AS user_contact,
			(SELECT CONCAT(first_name) FROM rms_users WHERE c.user_id=id LIMIT 1 ) AS userby, 
			 CASE WHEN cc.proccess = 0 THEN 'បោះបង់ការទំនាក់ទំនង' WHEN cc.proccess = 1 THEN 'កំពុងដំណើរការណ៍' WHEN cc.proccess = 2 THEN 'WAITING_TEST' WHEN cc.proccess = 3 THEN 'បង់រួច' END AS crm_status_title, 
			 c.name,c.phone 
			FROM `in_customer` AS c,
			     `ln_history_contact` AS cc 
			WHERE cc.customer_id = c.id ";
		
		$where = ' ';
		$from_date =(empty($search['start_date']))? '1': " cc.contact_date >= '".date("Y-m-d",strtotime($search['start_date']))." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " cc.contact_date <= '".date("Y-m-d",strtotime($search['end_date']))." 23:59:59'";
		$where.= " AND  ".$from_date." AND ".$to_date;
// 		if(!empty($search['advance_search'])){
// 			$s_where = array();
// 			$s_search = addslashes(trim($search['advance_search']));
// 			$s_where[] = " c.name LIKE '%{$s_search}%'";
// 			$s_where[] = " c.phone LIKE '%{$s_search}%'";
// 			$where .=' AND ( '.implode(' OR ',$s_where).')';
// 		}
// 		if(!empty($search['ask_for_search'])){
// 			$where.= " AND c.ask_for = ".$db->quote($search['ask_for_search']);
// 		}
// 		if(!empty($search['crm_list'])){
// 			$where.= " AND cc.crm_id = ".$db->quote($search['crm_list']);
// 		}
		
// 		if($search['status_search']>-1){
// 			$where.= " AND cc.proccess = ".$db->quote($search['status_search']);
// 		}
		$where.=" ORDER BY cc.id DESC";
		return $db->fetchAll($sql.$where);
	}
	
	function getAllStudentTest($search=null){
		try{
			$_db = new Application_Model_DbTable_DbGlobal();
			$branch_id = $_db->getAccessPermission('st.branch_id');
	
			$db=$this->getAdapter();
	
			$from_date =(empty($search['start_date']))? '1': "str.create_date >= '".$search['start_date']." 00:00:00'";
			$to_date = (empty($search['end_date']))? '1': "str.create_date <= '".$search['end_date']." 23:59:59'";
	
			$sql=" SELECT st.*,
					(SELECT name_en FROM rms_view WHERE TYPE=2 AND key_code=st.sex LIMIT 1) AS sex,
					(SELECT i.title FROM `rms_product_cate` AS i WHERE i.id = str.degree AND i.type=1 LIMIT 1) AS degree_title,
					(SELECT idd.title FROM `rms_product_catedetail` AS idd WHERE idd.id = str.grade AND idd.items_type=1 LIMIT 1) AS grade_title,
					(SELECT i.title FROM `rms_product_cate` AS i WHERE i.id = str.degree_result AND i.type=1 LIMIT 1) AS degree_result_title,
					(SELECT idd.title FROM `rms_product_catedetail` AS idd WHERE idd.id = str.grade_result AND idd.items_type=1 LIMIT 1) AS grade_result_title,
					(SELECT first_name FROM rms_users WHERE rms_users.id = str.user_id LIMIT 1) AS user_id,
					(SELECT name_kh FROM rms_view WHERE TYPE=15 AND key_code = str.updated_result LIMIT 1) AS result_status,
					(SELECT first_name FROM rms_users WHERE rms_users.id = str.result_by LIMIT 1) AS result_by,
					str.create_date AS create_date_exam,
					str.result_date,
					str.test_date AS test_date_exam,
					str.updated_result AS updated_result_de,
					str.note AS note_result,
					str.is_registered
					FROM `rms_student` AS st,
					`rms_student_test_result` AS str
					WHERE st.is_studenttest =1
					AND str.stu_test_id = st.stu_id
					AND
					STATUS=1
					AND st.stu_khname!=''
			$branch_id
			";
	
			$where = " AND ".$from_date." AND ".$to_date;
	
			if(!empty($search['advance_search'])){
				$s_where=array();
				$s_search= addslashes(trim($search['advance_search']));
				$s_where[]= " st.serial LIKE '%{$s_search}%'";
				$s_where[]= " st.stu_code LIKE '%{$s_search}%'";
				$s_where[]= " st.stu_khname LIKE '%{$s_search}%'";
				$s_where[]= " st.stu_enname LIKE '%{$s_search}%'";
				$s_where[]= " st.last_name LIKE '%{$s_search}%'";
				$s_where[]= " st.tel LIKE '%{$s_search}%'";
				$where.=' AND ('.implode(' OR ', $s_where).')';
			}
			if(($search['branch_search']>0)){
				$where.= " AND st.branch_id = ".$search['branch_search'];
			}
		if(!empty($search['user'])){
			$where.= " AND str.user_id = ".$search['user'];
		}
		if(!empty($search['degree_search'])){
			$where .= " and str.degree_result = ".$search['degree_search'];
		}
		if(!empty($search['student_option_search'])){
			$where .= " and st.student_option = ".$search['student_option_search'];
		}
		if(!empty($search['province_search'])){
			$where .= " and st.province_id = ".$search['province_search'];
		}
// 		if($search['register_status']!=''){
// 		$where .= " and st.register = ".$search['register_status'];
// 		}
		if($search['result_status']!=''){
		$where .= " and str.updated_result = ".$search['result_status'];
		}
		$order=" ORDER By str.updated_result DESC,str.degree_result ASC,str.grade_result ASC ";
		return $db->fetchAll($sql.$where.$order);
		}catch(Exception $e){
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				echo $e->getMessage();
		}
		}
}



