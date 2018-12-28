<?php

class Home_Model_DbTable_DbDashboard extends Zend_Db_Table_Abstract
{

//     protected $_name = 'ln_properties';
//     public function getUserId(){
//     	$session_user=new Zend_Session_Namespace('authinstall');
//     	return $session_user->user_id;
//     }
	public function getAllProperty($soldpropery=null,$available=null){
		$db = $this->getAdapter();
		$sql="SELECT COUNT(p.`id`) AS totalProperty 
			FROM `ln_properties` AS p 
			WHERE p.`status` =1  ";
		if (!empty($soldpropery)){
			$sql.=" AND p.`is_lock` =1";
		}
		if (!empty($available)){
			$sql.=" AND p.`is_lock` =0";
		}
		return $db->fetchOne($sql);
	}
	
	function CountAllClient(){
		$db = $this->getAdapter();
		$sql="SELECT COUNT(p.`client_id`) AS total
			FROM `ln_client` AS p 
			WHERE p.`status` =1 ";
		return $db->fetchOne($sql);
	}
	
	function CountAllAgency(){
		$db = $this->getAdapter();
		$sql="SELECT COUNT(p.`co_id`) AS total
		FROM `ln_staff` AS p 
		WHERE p.`status` =1 ";
		return $db->fetchOne($sql);
	}
	
	function CountAllSale(){
		$db = $this->getAdapter();
		$sql="SELECT COUNT(p.`id`) AS total,SUM(p.`price_sold`) AS totalAmount
			FROM `ln_sale` AS p 
			WHERE p.`status` =1 ";
		return $db->fetchRow($sql);
	}
	
	function CountCompletedSale(){
		$db = $this->getAdapter();
		$sql="SELECT COUNT(p.`id`) AS total,SUM(p.`price_sold`) AS totalAmount
			FROM `ln_sale` AS p 
			WHERE p.`status` =1 AND p.is_completed =1";
		return $db->fetchRow($sql);
	}
	
	function CountCanceledSale(){
		$db = $this->getAdapter();
		$sql="SELECT COUNT(p.`id`) AS total
		FROM `ln_sale` AS p
		WHERE p.`status` =1 AND p.is_cancel =1";
		return $db->fetchOne($sql);
	}
	
	function TotalExpense($cancelProperty=null){
		$db = $this->getAdapter();
		$sql="SELECT SUM(p.`total_amount`) AS totalAmount
			FROM `ln_expense` AS p 
			WHERE p.`status` =1 ";
		if (!empty($cancelProperty)){
			$sql.=" AND p.`category_id`=4";
		}
		return $db->fetchOne($sql);
	}
	function getTotalOtherIncome(){
		$db = $this->getAdapter();
		$sql="SELECT SUM(total_amount) AS total
		FROM ln_income AS i
		WHERE i.`status`=1 ";
		return $db->fetchOne($sql);
	}
	
	function getTotalSaleIncome(){
		$db = $this->getAdapter();
// 		$sql="SELECT  SUM(v.`amount_recieve`) AS total FROM v_getcollectmoney AS v WHERE v.`status`=1";
		$sql="SELECT SUM(recieve_amount) AS total FROM `ln_client_receipt_money` WHERE status=1 ";
		return $db->fetchOne($sql);
	}
	
	function countSaleByYearMonth($yearMont){
		$db = $this->getAdapter();
		$sql="
			SELECT COUNT(s.`id`) AS totalSale
			FROM `ln_sale` AS s 
			WHERE s.`status`=1 AND s.`is_cancel` =0 AND
			DATE_FORMAT(s.`buy_date`, '%Y-%m') ='$yearMont' LIMIT 1
		";
		return $db->fetchOne($sql);
	}
	
	function getMonthlyNetIncomeInGrap($yearMonth){
		$collectMoneyIncome = $this->getMoneyCollenctMonthly($yearMonth);
		$OtherIncomeMonthly = $this->getOtherIncomeMonthly($yearMonth);
		$ExoenseMonthly = $this->getOtherExoenseMonthly($yearMonth);
		$netIncome = ($collectMoneyIncome+$OtherIncomeMonthly) - $ExoenseMonthly;
		return $netIncome;
	}
	function getMoneyCollenctMonthly($yearMonth){
		$db = $this->getAdapter();
		$sql="SELECT  SUM(v.`amount_recieve`) AS total FROM v_getcollectmoney AS v WHERE v.`status`=1
			AND DATE_FORMAT(v.`date_pay`, '%Y-%m') ='$yearMonth' LIMIT 1";
		
// 		$sql="SELECT SUM(recieve_amount) AS total FROM `ln_client_receipt_money` WHERE status=1 ";
		return $db->fetchOne($sql);
	}
	function getOtherIncomeMonthly($yearMonth){
		$db = $this->getAdapter();
		$sql="SELECT SUM(total_amount) AS total
		FROM ln_income AS i
		WHERE i.`status`=1
		AND  DATE_FORMAT(i.`for_date`, '%Y-%m') ='$yearMonth' LIMIT 1";
		return $db->fetchOne($sql);
	}
	
	function getOtherExoenseMonthly($yearMonth){
		$db = $this->getAdapter();
		$sql="SELECT SUM(p.`total_amount`) AS totalAmount
			FROM `ln_expense` AS p 
			WHERE p.`status` =1
			AND  DATE_FORMAT(p.`for_date`, '%Y-%m') ='$yearMonth' LIMIT 1
		";
		return $db->fetchOne($sql);
	}
	
	//Dashboard Sale Agent
	
	function getTotalSaleByAgent(){
		$db = $this->getAdapter();
		$dbglobal = new Application_Model_DbTable_DbGlobal();
		$userid = $dbglobal->getUserId();
		$db_user=new Application_Model_DbTable_DbUsers();
		$user_info = $db_user->getUserInfo($userid);
		$user_id = $user_info['staff_id'];
		$sql="SELECT COUNT(s.`id`) AS totalsale FROM `ln_sale` AS s WHERE s.`staff_id` =$user_id";
		return $db->fetchOne($sql);
	}
	function getTotalFullCommission(){
		
		$dbglobal = new Application_Model_DbTable_DbGlobal();
		$userid = $dbglobal->getUserId();
		$db_user=new Application_Model_DbTable_DbUsers();
		$user_info = $db_user->getUserInfo($userid);
		$user_id = $user_info['staff_id'];
		$db = $this->getAdapter();
		$sql="SELECT 
		SUM(s.`full_commission`) AS expect_commission
		 FROM `ln_sale` AS s WHERE s.`staff_id` = $user_id";
		return $db->fetchOne($sql);
	}
	function getCommissionPiadByAgent(){
	
		$dbglobal = new Application_Model_DbTable_DbGlobal();
		$userid = $dbglobal->getUserId();
		$db_user=new Application_Model_DbTable_DbUsers();
		$user_info = $db_user->getUserInfo($userid);
		$user_id = $user_info['staff_id'];
		$db = $this->getAdapter();
		$sql="
		SELECT 
			SUM(s.`comission`) AS total_commission,
			(SELECT SUM(c.`total_amount`) FROM `ln_comission` AS c WHERE s.`staff_id` = c.`staff_id`) AS total_commission_get
		 	FROM `ln_sale` AS s WHERE s.`staff_id` =$user_id ";
		return $db->fetchRow($sql);
	}
	
	function getAllLandInfo($search = null,$is_sold=null){
		$db = $this->getAdapter();
		$this->_name ="ln_properties";
		$where = " WHERE 1 ";
		$sql = "SELECT id,
		(SELECT ln_project.project_name FROM `ln_project` WHERE ln_project.br_id = ln_properties.branch_id LIMIT 1) AS branch_name,
		(SELECT ln_project.map_url FROM `ln_project` WHERE ln_project.br_id = ln_properties.branch_id LIMIT 1) AS map_url,
		land_code,land_address,street,
		(SELECT t.`type_nameen` AS `name` FROM `ln_properties_type` AS t WHERE t.id = property_type limit 1) AS  pro_type,
		price,width,height,land_size,hardtitle,
		(SELECT name_kh FROM `ln_view` WHERE type = 28 AND key_code=is_lock LIMIT 1) sale_type,
		create_date,is_lock,
		(SELECT  CONCAT(last_name,' ', first_name) FROM rms_users WHERE id=user_id limit 1 ) AS user_name,
		status FROM $this->_name ";
		if (!empty($is_sold)){
			$where.= " AND is_lock = 1";
		}
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[] = " land_code LIKE '%{$s_search}%'";
			$s_where[] = " land_address LIKE '%{$s_search}%'";
			$s_where[] = " street LIKE '%{$s_search}%'";
			$s_where[] = " price LIKE '%{$s_search}%'";
			$s_where[] = " land_size LIKE '%{$s_search}%'";
			$s_where[] = " width LIKE '%{$s_search}%'";
			$s_where[] = " height LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if(!empty($search['streetlist'])){
			$where.= " AND street ='".$search['streetlist']."'";
		}
		if($search['branch_id']>-1){
			$where.= " AND branch_id = ".$search['branch_id'];
		}
		if(($search['property_type_search'])>0){
			$where.= " AND property_type = ".$search['property_type_search'];
		}
		if($search['buy_status']>-1){
			$where.= " AND is_lock = ".$search['buy_status'];
		}
	
		$order=" ORDER BY id DESC ";
		return $db->fetchAll($sql.$where.$order);
	}
	//Menu List Nortification
	function getAllNews($limit=null,$notInId=null){
		$db = $this->getAdapter();
		$dbglobal = new Application_Model_DbTable_DbGlobal();
		$currentlang = $dbglobal->currentlang();
		$datenow = date("Y-m-d");
		$sql="SELECT n.*,
		(SELECT nd.title FROM `ln_news_detail` AS nd WHERE nd.lang=2 AND nd.news_id = n.`id` LIMIT 1) AS title,
		(SELECT nd.description FROM `ln_news_detail` AS nd WHERE nd.lang=$currentlang AND nd.news_id = n.`id` LIMIT 1) AS description,
		 (SELECT CONCAT(last_name ,' ',first_name)  FROM `rms_users` WHERE id = user_id LIMIT 1) AS user_name
		 FROM `ln_news` AS n
		 WHERE n.`status`=1 AND n.`publish_date` <='$datenow' ";
		if (!empty($notInId)){
			$sql.=" AND n.id != $notInId";
		}
		$sql.=" ORDER BY n.`publish_date` DESC";
		if (!empty($limit)){
			$sql.=" LIMIT $limit";
		}
		
		return $db->fetchAll($sql);
	}
	function getClickNewFeedByCusId(){
		
		$dbglobal = new Application_Model_DbTable_DbGlobal();
		$userid = $dbglobal->getUserId();
		
		$db= $this->getAdapter();
		$sql = "SELECT r.`new_feed_id` FROM `ln_news__read` r WHERE r.`is_click`=1  AND r.`cus_id`=".$userid;
		$row = $db->fetchAll($sql);
		$new_feed_read_id='';
		foreach ($row as $rs){
			if (empty($new_feed_read_id)){
				$new_feed_read_id=$rs['new_feed_id'];
			}else{$new_feed_read_id=$new_feed_read_id.",".$rs['new_feed_id'];
			}
		}
		return $new_feed_read_id;
	}
	function getCountNewFeed(){
		$new_feed_cus = $this->getClickNewFeedByCusId();
		if (empty($new_feed_cus)){
			$new_feed_cus=0;
		}
		$db= $this->getAdapter();
		$sql = "SELECT COUNT(n.`id`) AS count_value
		FROM `ln_news` AS n
		WHERE n.`id` NOT IN (".$new_feed_cus.")";
		$row = $db->fetchOne($sql);
		return $row;
	}
	function getNewsFeedNotClick(){
		$new_feed_cus = $this->getClickNewFeedByCusId();
		if (empty($new_feed_cus)){
			$new_feed_cus=0;
		}
		$db= $this->getAdapter();
		$sql = "SELECT n.* 
		FROM `ln_news` AS n
		WHERE n.`id` NOT IN (".$new_feed_cus.")";
		$row = $db->fetchAll($sql);
		return $row;
	}
	public function addNewFeedClick($id_newfeed){
		$dbglobal = new Application_Model_DbTable_DbGlobal();
		$userid = $dbglobal->getUserId();
		
		$db=$this->getAdapter();
		$ids = explode(',', $id_newfeed);
		if (!empty($id_newfeed))foreach ($ids as $i){
			$_arr = array(
					'new_feed_id'=>$i,
					'cus_id'=>$userid,
					'is_click'=>1,
					'date'=>date("Y-m-d")
			);
			$this->_name='ln_news__read';
			$this->insert($_arr);//insert data
		}
		return 1;
	}
	public function UpdateNewFeedRead($id_newfeed){
		$db=$this->getAdapter();
		$dbglobal = new Application_Model_DbTable_DbGlobal();
		$userid = $dbglobal->getUserId();
		$_arr = array(
				'new_feed_id'=>$id_newfeed,
				'is_click'=>1,
				'is_read'=>1,
		);
		$this->_name='ln_news__read';
		$newfeed = $this->getCheckNotiDetail($id_newfeed);
		if (!empty($newfeed)){
			$where =" new_feed_id=".$id_newfeed." AND cus_id =".$userid;
			$this->update($_arr, $where);
		}else{
			$_arr['cus_id']=$userid;
			$this->insert($_arr);//insert data
		}
	}
	public function getCheckNotiDetail($id){
		$db = $this->getAdapter();
		$sql="SELECT * FROM `ln_news__read` AS rs WHERE  rs.`new_feed_id`=$id LIMIT 1";
		return $db->fetchRow($sql);
	}
	function checkReadNewfeed($id_newfeed){
		$dbglobal = new Application_Model_DbTable_DbGlobal();
		$userid = $dbglobal->getUserId();
		$db =$this->getAdapter();
		$sql="SELECT * FROM `ln_news__read` AS r
		WHERE r.`cus_id`=$userid AND r.`new_feed_id`=$id_newfeed LIMIT 1";
		return $db->fetchRow($sql);
	
	}
	function getNewsDetail($id){
		
		$db = $this->getAdapter();
		$dbglobal = new Application_Model_DbTable_DbGlobal();
		$currentlang = $dbglobal->currentlang();
		$datenow = date("Y-m-d");
		$sql="SELECT n.*,
		(SELECT nd.title FROM `ln_news_detail` AS nd WHERE nd.lang=2 AND nd.news_id = n.`id` LIMIT 1) AS title,
		(SELECT nd.description FROM `ln_news_detail` AS nd WHERE nd.lang=$currentlang AND nd.news_id = n.`id` LIMIT 1) AS description,
		(SELECT CONCAT(last_name ,' ',first_name)  FROM `rms_users` WHERE id = user_id LIMIT 1) AS user_name
		FROM `ln_news` AS n
		WHERE n.`status`=1 AND n.`publish_date` <='$datenow' AND n.id=$id ORDER BY n.`publish_date` DESC";
		return $db->fetchRow($sql);
	}
	
	public function getAllLand($branch_id=null,$option=null,$action=null,$propertytype=null){
		$db = $this->getAdapter();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$sql="SELECT `id`,CONCAT(`land_address`,',',street) AS name FROM `ln_properties` WHERE status Not IN (-1,0) AND `land_address`!='' ";//just concate
		$request=Zend_Controller_Front::getInstance()->getRequest();
		if($action==null){
			$sql.=" AND `is_lock`=0  ";
		}
		if($branch_id!=null){
			$sql.=" AND `branch_id`=$branch_id ";
		}
		if (!empty($propertytype)){
			$sql.=" AND `property_type`=$propertytype ";
		}
		$sql.=" ORDER BY id DESC";
		$rows = $db->fetchAll($sql);
		
		$options = '';
		$options .= '<option value="0" >'.htmlspecialchars($tr->translate('SELECT_PROPERTY'), ENT_QUOTES).'</option>';
		if(!empty($rows))foreach($rows as $value){
			$options .= '<option value="'.$value['id'].'" >'.htmlspecialchars($value['name'], ENT_QUOTES).'</option>';
		}
		return $options;
		
		return $options;
	}
	
	function getAllOtherIncome($search=null){
		$db = $this->getAdapter();
		$session_user=new Zend_Session_Namespace('authinstall');
		$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
		$where = " WHERE ".$from_date." AND ".$to_date;
	
		$sql=" SELECT *,
		(SELECT project_name FROM `ln_project` WHERE ln_project.br_id =branch_id LIMIT 1) AS branch_name,
		(SELECT logo FROM `ln_project` WHERE ln_project.br_id =branch_id LIMIT 1) AS projectlogo,
		(SELECT name_kh FROM `ln_client` WHERE ln_client.client_id =ln_otherincome.client_id LIMIT 1) AS client_name,
		(SELECT CONCAT(land_address,',',street) FROM `ln_properties` WHERE id=house_id LIMIT 1) AS house_no,
		(SELECT name_kh FROM ln_view WHERE TYPE=2 AND key_code=payment_method LIMIT 1) AS payment_method,
		(SELECT name_kh FROM `ln_view` WHERE TYPE=12 AND key_code=category_id LIMIT 1) AS category_name,
		(SELECT  first_name FROM rms_users WHERE id=user_id LIMIT 1 ) AS user_name,
		(SELECT pt.type_nameen FROM `ln_properties_type` AS pt WHERE pt.id= (SELECT ln_properties.property_type FROM `ln_properties` WHERE ln_properties.id=house_id LIMIT 1) LIMIT 1) AS property_type
		 FROM ln_otherincome ";
	
		if (!empty($search['advance_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['advance_search']));
			$s_where[] = " description LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT CONCAT(land_address,',',street) FROM `ln_properties` WHERE id=house_id LIMIT 1) LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if(!empty($search['branch_id'])){
			$where.= " AND branch_id = ".$search['branch_id'];
		}
		if(!empty($search['land_id']) AND $search['land_id']>-1){
			$where.= " AND house_id = ".$search['land_id'];
		}
		if($search['customer']>0){
			$where.= " AND ln_otherincome.client_id = ".$search['customer'];
		}
		if(!empty($search['pro_type'])){
			$where.= " AND (SELECT property_type FROM `ln_properties` WHERE id=house_id LIMIT 1) = ".$search['pro_type'];
		}
		
		
		
		$order=" order by id desc ";
		return $db->fetchAll($sql.$where.$order);
	}
}