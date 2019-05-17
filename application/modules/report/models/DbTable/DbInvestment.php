<?php
class Report_Model_DbTable_DbInvestment extends Zend_Db_Table_Abstract
{
	
	public function getInvestmentById($id){
		$db=$this->getAdapter();
		$sql = "SELECT i.*,
				iv.name,
				iv.sex,
				iv.phone,
				iv.email,
				iv.current_address,
				(SELECT CONCAT(last_name ,' ',first_name)  FROM `rms_users` WHERE id = i.user_id LIMIT 1) AS user_name
				FROM `rms_investment` AS i,
				`rms_investor` AS iv
				WHERE 
				i.investor_id = iv.id
				AND
			i.id= $id LIMIT 1";
		return $db->fetchRow($sql);
	}
	public function getInvestorSchedule($id){
		$db=$this->getAdapter();
		$sql = "SELECT ivd.* FROM `rms_investment_detail` AS ivd WHERE ivd.investment_id= $id";
		$sql.=" ORDER BY ivd.time ASC,ivd.date ASC";
		return $db->fetchAll($sql);
	}
	
	public function getInvestmentBrokerById($id){
		$db=$this->getAdapter();
		$sql = "SELECT i.*,
		iv.name,
		iv.sex,
		iv.phone,
		iv.email,
		iv.current_address,
		(SELECT CONCAT(last_name ,' ',first_name)  FROM `rms_users` WHERE id = i.user_id LIMIT 1) AS user_name
		FROM `rms_investment` AS i,
		`rms_broker` AS iv
		WHERE
		i.broker_id = iv.id
		AND
		i.id= $id LIMIT 1";
		return $db->fetchRow($sql);
	}
	public function getBrokerSchedule($id){
		$db=$this->getAdapter();
		$sql = "SELECT ivd.* FROM `rms_investment_detail_broker` AS ivd WHERE ivd.investment_id= $id";
		$sql.=" ORDER BY ivd.time ASC,ivd.date ASC";
		return $db->fetchAll($sql);
	}
	public function getInvestorPaymentHistory($investment_id){
		$db=$this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$label1 = $tr->translate("NORMAL_WITHDRAW");
		$label2 = $tr->translate("PRINCIPAL_WITHDRAW");
		$label3 = $tr->translate("PAYOFF_WITHDRAW");
		$sql = "SELECT
					crm.*,
					(SELECT i.invest_no FROM `rms_investment` AS i WHERE i.id = crm.investment_id LIMIT 1) AS invest_no,
					DATE_FORMAT(crm.paid_date, '%d-%m-%Y') AS `date_input`,
					(SELECT ve.`name_kh` FROM `ln_view` AS ve WHERE ve.`key_code` = `crm`.`payment_method` AND ve.`type` = 2 LIMIT 1) AS payment_method_title,
					v.name AS investor_name,
					CASE
					WHEN  crm.`option_pay` = 1 THEN '$label1'
					WHEN  crm.`option_pay` = 2 THEN '$label2'
					WHEN  crm.`option_pay` = 3 THEN '$label3'
				END AS option_pay_title,
				(SELECT  u.first_name FROM rms_users AS u WHERE u.id=crm.user_id LIMIT 1 ) AS user_name
				FROM
					`rms_investor_withdraw` AS crm,
					`rms_investor` AS v
				WHERE
					crm.investor_id = v.id
					AND crm.status=1
					AND crm.`investment_id` =$investment_id ORDER BY crm.`id` DESC ";
		return $db->fetchAll($sql);
	}
	
	public function getBrokerPaymentHistory($investment_id){
		$db=$this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$label1 = $tr->translate("NORMAL_WITHDRAW");
		$label2 = $tr->translate("PRINCIPAL_WITHDRAW");
		$label3 = $tr->translate("PAYOFF_BROKER_WITHDRAW");
		$sql = "SELECT
					crm.*,
					(SELECT i.invest_no FROM `rms_investment` AS i WHERE i.id = crm.investment_id LIMIT 1) AS invest_no,
					DATE_FORMAT(crm.paid_date, '%d-%m-%Y') AS `date_input`,
					(SELECT ve.`name_kh` FROM `ln_view` AS ve WHERE ve.`key_code` = `crm`.`payment_method` AND ve.`type` = 2 LIMIT 1) AS payment_method_title,
					v.name AS broker_name,
					CASE
					WHEN  crm.`option_pay` = 1 THEN '$label1'
					WHEN  crm.`option_pay` = 2 THEN '$label2'
					WHEN  crm.`option_pay` = 3 THEN '$label3'
					END AS option_pay_title,
					(SELECT  u.first_name FROM rms_users AS u WHERE u.id=crm.user_id LIMIT 1 ) AS user_name
				FROM
					`rms_investor_withdraw_broker` AS crm,
					`rms_broker` AS v
				WHERE
					crm.broker_id = v.id
					AND crm.status=1
					AND crm.`investment_id` =$investment_id ORDER BY crm.`id` DESC ";
		return $db->fetchAll($sql);
	}
	
	public function getAllBrokerBalance($search=null){
		$db=$this->getAdapter();
		$sql = "SELECT 
					b.name AS broker_name,
					b.phone AS broker_phone,
					b.email AS broker_email,
					iv.name AS investor_name,
					iv.phone AS investor_phone,
					iv.email AS investor_email,
					(SELECT SUM(wb.recieve_amount) FROM `rms_investor_withdraw_broker` AS wb WHERE wb.investment_id = i.id LIMIT 1) AS total_paidready,
					(SELECT COUNT(ib.id) FROM `rms_investment_detail_broker` AS ib WHERE ib.investment_id = i.id AND ib.is_complete =1 LIMIT 1) AS completed_time,
					(SELECT COUNT(ib.id) FROM `rms_investment_detail_broker` AS ib WHERE ib.investment_id = i.id AND ib.is_complete =0 LIMIT 1) AS not_completed_time,
					(SELECT SUM(ib.total_payment) FROM `rms_investment_detail_broker` AS ib WHERE ib.investment_id = i.id AND ib.is_complete =0 LIMIT 1) AS total_remain,
					i.* 
				FROM `rms_investment` AS i,
					`rms_broker` AS b,
					`rms_investor` AS iv
				WHERE i.broker_id !=''
					AND b.id = i.broker_id
					AND iv.id = i.investor_id AND i.status=1 ";
		$from_date =(empty($search['start_date']))? '1': "i.date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': "i.date <= '".$search['end_date']." 23:59:59'";
		$sql.= " AND  ".$from_date." AND ".$to_date;
		$where = "";
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
			$s_where[] = " i.invest_no LIKE '%{$s_search}%'";
			$s_where[] = " i.amount LIKE '%{$s_search}%'";
			$s_where[] = " i.duration LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if(!empty($search['investor_id'])){
			$where.=" AND i.investor_id = ".$search['investor_id'];
		}
		if(!empty($search['broker_id'])){
			$where.=" AND i.broker_id = ".$search['broker_id'];
		}
		$order=" ORDER BY i.id DESC ";
		
		return $db->fetchAll($sql.$where.$order);
	}
	public function getAllInvestment($search=null){
		$db=$this->getAdapter();
		$sql = "SELECT
		iv.name AS investor_name,
		iv.phone AS investor_phone,
		iv.email AS investor_email,
		(SELECT SUM(wb.recieve_amount) FROM `rms_investor_withdraw` AS wb WHERE wb.investment_id = i.id LIMIT 1) AS total_paidready,
		(SELECT COUNT(ib.id) FROM `rms_investment_detail` AS ib WHERE ib.investment_id = i.id AND ib.is_complete =1 LIMIT 1) AS completed_time,
		(SELECT COUNT(ib.id) FROM `rms_investment_detail` AS ib WHERE ib.investment_id = i.id AND ib.is_complete =0 LIMIT 1) AS not_completed_time,
		(SELECT SUM(ib.principle_after) FROM `rms_investment_detail` AS ib WHERE ib.investment_id = i.id AND ib.is_complete =0 LIMIT 1) AS total_principle_remain,
		(SELECT SUM(ib.interest_amountafter) FROM `rms_investment_detail` AS ib WHERE ib.investment_id = i.id AND ib.is_complete =0 LIMIT 1) AS total_interest_remain,
		i.*
		FROM `rms_investment` AS i,
		`rms_investor` AS iv
		WHERE iv.id = i.investor_id AND i.status=1 ";
		$from_date =(empty($search['start_date']))? '1': "i.date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': "i.date <= '".$search['end_date']." 23:59:59'";
		$sql.= " AND  ".$from_date." AND ".$to_date;
		$where = "";
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
			$s_where[] = " i.invest_no LIKE '%{$s_search}%'";
			$s_where[] = " i.amount LIKE '%{$s_search}%'";
			$s_where[] = " i.duration LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if(!empty($search['investor_id'])){
			$where.=" AND i.investor_id = ".$search['investor_id'];
		}
		$order=" ORDER BY i.id DESC ";
	
		return $db->fetchAll($sql.$where.$order);
	}
	function getInvestmentReceiptById($id){
		try{
			$db = $this->getAdapter();
			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
			$label1 = $tr->translate("NORMAL_WITHDRAW");
			$label2 = $tr->translate("PRINCIPAL_WITHDRAW");
			$label3 = $tr->translate("PAYOFF_WITHDRAW");
	
			$sql = "
				SELECT
					w.*,
					v.name,
					(SELECT i.invest_no FROM `rms_investment` AS i WHERE i.id = w.investment_id LIMIT 1) AS invest_no,
					w.principle_paid,
					w.interest_paid,
					w.total_payment,
					w.recieve_amount,
					w.payment_date,
					w.paid_date,
					(SELECT ve.`name_kh` FROM `ln_view` AS ve WHERE ve.`key_code` = `w`.`payment_method` AND ve.`type` = 2 LIMIT 1) AS payment_method_title,
					CASE
					WHEN  w.`option_pay` = 1 THEN '$label1'
					WHEN  w.`option_pay` = 2 THEN '$label2'
					WHEN  w.`option_pay` = 3 THEN '$label3'
					END AS option_pay,
					(SELECT  first_name FROM rms_users WHERE id=w.user_id LIMIT 1 ) AS by_user
					FROM `rms_investor_withdraw` AS w,	
						`rms_investor` AS v 
					WHERE v.id = w.investor_id AND w.status=1 AND w.id=$id
			";
			return $db->fetchRow($sql);
			 
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	function getBrokerReceiptById($id){
		try{
			$db = $this->getAdapter();
			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
			$label1 = $tr->translate("NORMAL_WITHDRAW");
			$label2 = $tr->translate("PRINCIPAL_WITHDRAW");
			$label3 = $tr->translate("PAYOFF_BROKER_WITHDRAW");
	
			$sql = "
			SELECT
			w.*,
			v.name,
			(SELECT i.invest_no FROM `rms_investment` AS i WHERE i.id = w.investment_id LIMIT 1) AS invest_no,
			(SELECT ve.`name_kh` FROM `ln_view` AS ve WHERE ve.`key_code` = `w`.`payment_method` AND ve.`type` = 2 LIMIT 1) AS payment_method_title,
			CASE
			WHEN  w.`option_pay` = 1 THEN '$label1'
			WHEN  w.`option_pay` = 2 THEN '$label2'
			WHEN  w.`option_pay` = 3 THEN '$label3'
			END AS option_pay,
			(SELECT  first_name FROM rms_users WHERE id=w.user_id LIMIT 1 ) AS by_user
			FROM `rms_investor_withdraw_broker` AS w,
			`rms_broker` AS v
			WHERE v.id = w.broker_id AND w.status=1 AND w.id=$id
			";
			return $db->fetchRow($sql);
	
		}catch (Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	
	public function getAllInvestorPaymentHistory($search){
		$db=$this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$label1 = $tr->translate("NORMAL_WITHDRAW");
		$label2 = $tr->translate("PRINCIPAL_WITHDRAW");
		$label3 = $tr->translate("PAYOFF_WITHDRAW");
		$sql = "SELECT
			crm.*,
			(SELECT i.invest_no FROM `rms_investment` AS i WHERE i.id = crm.investment_id LIMIT 1) AS invest_no,
			DATE_FORMAT(crm.paid_date, '%d-%m-%Y') AS `date_input`,
			(SELECT ve.`name_kh` FROM `ln_view` AS ve WHERE ve.`key_code` = `crm`.`payment_method` AND ve.`type` = 2 LIMIT 1) AS payment_method_title,
			v.name AS investor_name,
			CASE
			WHEN  crm.`option_pay` = 1 THEN '$label1'
			WHEN  crm.`option_pay` = 2 THEN '$label2'
			WHEN  crm.`option_pay` = 3 THEN '$label3'
			END AS option_pay_title,
			(SELECT  u.first_name FROM rms_users AS u WHERE u.id=crm.user_id LIMIT 1 ) AS user_name
		FROM
			`rms_investor_withdraw` AS crm,
			`rms_investor` AS v
		WHERE
			crm.investor_id = v.id
			AND crm.status=1
			 ";
		
		$from_date =(empty($search['start_date']))? '1': "crm.paid_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': "crm.paid_date <= '".$search['end_date']." 23:59:59'";
		$sql.= " AND  ".$from_date." AND ".$to_date;
		$where = "";
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
			$s_where[] = " crm.receipt_no LIKE '%{$s_search}%'";
			$s_where[] = " crm.principle_paid LIKE '%{$s_search}%'";
			$s_where[] = " crm.interest_paid LIKE '%{$s_search}%'";
			$s_where[] = " crm.total_payment LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT i.invest_no FROM `rms_investment` AS i WHERE i.id = crm.investment_id LIMIT 1) LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if(!empty($search['investor_id'])){
			$where.=" AND crm.investor_id = ".$search['investor_id'];
		}
		$order=" ORDER BY crm.`id` DESC ";
		
		return $db->fetchAll($sql.$where.$order);
	}
	
	public function getAllBrokerPaymentHistory($search){
		$db=$this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$label1 = $tr->translate("NORMAL_WITHDRAW");
		$label2 = $tr->translate("PRINCIPAL_WITHDRAW");
		$label3 = $tr->translate("PAYOFF_BROKER_WITHDRAW");
		$sql = "SELECT
				crm.*,
				(SELECT i.invest_no FROM `rms_investment` AS i WHERE i.id = crm.investment_id LIMIT 1) AS invest_no,
				DATE_FORMAT(crm.paid_date, '%d-%m-%Y') AS `date_input`,
				(SELECT ve.`name_kh` FROM `ln_view` AS ve WHERE ve.`key_code` = `crm`.`payment_method` AND ve.`type` = 2 LIMIT 1) AS payment_method_title,
				v.name AS broker_name,
				CASE
				WHEN  crm.`option_pay` = 1 THEN '$label1'
				WHEN  crm.`option_pay` = 2 THEN '$label2'
				WHEN  crm.`option_pay` = 3 THEN '$label3'
				END AS option_pay_title,
				(SELECT  u.first_name FROM rms_users AS u WHERE u.id=crm.user_id LIMIT 1 ) AS user_name
			FROM
				`rms_investor_withdraw_broker` AS crm,
				`rms_broker` AS v
			WHERE
				crm.broker_id = v.id
				AND crm.status=1
		  ";
		
		$from_date =(empty($search['start_date']))? '1': "crm.paid_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': "crm.paid_date <= '".$search['end_date']." 23:59:59'";
		$sql.= " AND  ".$from_date." AND ".$to_date;
		$where = "";
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_search = str_replace(' ', '', addslashes(trim($search['adv_search'])));
			$s_where[] = " crm.receipt_no LIKE '%{$s_search}%'";
			$s_where[] = " crm.principle_paid LIKE '%{$s_search}%'";
			$s_where[] = " crm.interest_paid LIKE '%{$s_search}%'";
			$s_where[] = " crm.total_payment LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT i.invest_no FROM `rms_investment` AS i WHERE i.id = crm.investment_id LIMIT 1) LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if(!empty($search['broker_id'])){
			$where.=" AND crm.broker_id = ".$search['broker_id'];
		}
		$order=" ORDER BY crm.`id` DESC ";
		
		return $db->fetchAll($sql.$where.$order);
	}
	
	function submitClosingEngry($data){
		$db = $this->getAdapter();
		if(!empty($data['id_selected'])){
			$ids = explode(',', $data['id_selected']);
			$key = 1;
			$arr = array(
					"is_closed"=>1,
			);
			foreach ($ids as $i){
				$this->_name="rms_investor_withdraw";
				$where="id= ".$i;
				$this->update($arr, $where);
			}
		}
	}
	function submitClosingEngryBroker($data){
		$db = $this->getAdapter();
		if(!empty($data['id_selected'])){
			$ids = explode(',', $data['id_selected']);
			$key = 1;
			$arr = array(
					"is_closed"=>1,
			);
			foreach ($ids as $i){
				$this->_name="rms_investor_withdraw_broker";
				$where="id= ".$i;
				$this->update($arr, $where);
			}
		}
	}
}