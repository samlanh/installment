<?php

class Loan_Model_DbTable_DbTransferCoClient extends Zend_Db_Table_Abstract
{
	protected $_name = 'ln_tranfser_co';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace('auth');
		return $session_user->user_id;
	}
    public function getcoinfo(){
    	$db = $this->getAdapter();
    	$sql = 'SELECT m.member_id,m.client_id,m.loan_number,m.group_id,
		(SELECT (SELECT co.`co_id` FROM `ln_co` AS co WHERE co.`co_id`= g.`g_id` LIMIT 1) FROM `ln_loan_group` AS g  
		WHERE g. `g_id` = m.`group_id` LIMIT 1) AS co_id ,
		(SELECT (SELECT co.`co_code` FROM `ln_co` AS co WHERE co.`co_id`= g.`g_id` LIMIT 1) FROM `ln_loan_group` AS g  
		WHERE g. `g_id` = m.`group_id` LIMIT 1) AS co_code ,
		(SELECT (SELECT co.`co_khname` FROM `ln_co` AS co WHERE co.`co_id`= g.`g_id` LIMIT 1) FROM `ln_loan_group` AS g  
		WHERE g. `g_id` = m.`group_id` LIMIT 1) AS co_name ,
		(SELECT c.`name_kh` FROM `ln_client` AS c WHERE c.`client_id` = m.client_id LIMIT 1) AS client_name ,
		(SELECT c.`client_number` FROM `ln_client` AS c WHERE c.`client_id` = m.client_id LIMIT 1) AS client_code 
		FROM `ln_loan_member` AS m WHERE status = 1';
    	return $db->fetchAll($sql);
    }
    function getAllTransferCO($search){
    	$db = $this->getAdapter();
    	$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	
    	$sql = 'SELECT tf.id ,
    	(SELECT `branch_namekh` FROM `ln_branch` WHERE `br_id` = tf.branch_id LIMIT 1) AS branch_name,
    	CONCAT ( (SELECT co.`co_code` FROM ln_co AS co WHERE co.`co_id` = tf.from LIMIT 1),",",
    	(SELECT co.co_khname FROM ln_co AS co WHERE co.`co_id` = tf.from LIMIT 1)) AS from_coname,
    	CONCAT (  (SELECT co.`co_code` FROM ln_co AS co WHERE co.`co_id` = tf.to LIMIT 1),",",
    	(SELECT co.co_khname FROM ln_co AS co WHERE co.`co_id` = tf.to LIMIT 1)) AS to_coname,
    	tf.date,tf.note,
    	(SELECT `name_en` FROM `ln_view` WHERE TYPE = 3 AND key_code = tf.status ) AS status
    	FROM ln_tranfser_co AS tf WHERE tf.status = 1 AND tf.type = 1';
    	$order = " ORDER BY tf.id DESC";
    	if($search['status']>1){
    		$where.= " tf.status = ".$search['status'];
    	}
    	if(($search['branch_name'])>0){
    		$where.= " AND tf.branch_id = ".$search['branch_name'];
    	}
    	
    	if(!empty($search['note'])){
    		$where.= " AND tf.note  LIKE '%{$search['note']}%'";
    		 
    	}
    	if(($search['co_code'])>0){
    		$where.= " AND tf.from = ".$search['co_code'] ;
    	}
    	if(($search['name_co'])>0){
    		$where.= " AND tf.to = ".$search['name_co'] ;
    	}
//     	echo $sql.$where.$order;
    	return $db->fetchAll($sql.$where.$order);
    }
    public function getAllinfoCo($search){//type =2
    	$db = $this->getAdapter();
    	$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	
    	$sql = 'SELECT tf.id ,
    	(SELECT `branch_namekh` FROM `ln_branch` WHERE `br_id` = tf.branch_id LIMIT 1) AS branch_name,
		CONCAT(  (SELECT client_number FROM `ln_client` WHERE client_id = tf.client_id LIMIT 1) ,",",
		         (SELECT name_kh FROM `ln_client` WHERE client_id = tf.client_id LIMIT 1)) AS client_name ,
       CONCAT (  (SELECT co.`co_code` FROM ln_co AS co WHERE co.`co_id` = tf.to LIMIT 1),",",
                 (SELECT co.co_khname FROM ln_co AS co WHERE co.`co_id` = tf.to LIMIT 1)) AS to_coname,
                 tf.date,tf.note,
                (SELECT `name_en` FROM `ln_view` WHERE type = 3 AND key_code = tf.status ) AS status
    	 FROM ln_tranfser_co AS tf WHERE tf.status = 1 AND tf.type = 2';
    	$order = " ORDER BY tf.id DESC";
    	if($search['status']>1){
    		$where.= " tf.status = ".$search['status'];
    	}
    	if(($search['branch_name'])>0){
    		$where.= " AND tf.branch_id = ".$search['branch_name'];
    	}
    	if(($search['name_co'])>0){
    		$where.= " AND ( tf.from = ".$search['name_co'] ;
    		$where.= " OR tf.to = ".$search['name_co']." )" ;
    		
    	}
    	if(!empty($search['note'])){
    		$where.= " AND tf.note  LIKE '%{$search['note']}%'";
    	
    	}
    	return $db->fetchAll($sql.$where.$order);
    }
    public function getAllinfoCoLoan($search){//type=3
    	
    	$db = $this->getAdapter();
    	$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
    	$where = " AND ".$from_date." AND ".$to_date;
    	
    	$sql = 'SELECT tf.id ,
    	(SELECT `branch_namekh` FROM `ln_branch` WHERE `br_id` = tf.branch_id LIMIT 1) AS branch_name,
		(SELECT loan_number FROM ln_loan_member WHERE  member_id = tf.loan_id LIMIT 1) AS loan_number,
		CONCAT(  (SELECT client_number FROM `ln_client` WHERE client_id = tf.client_id LIMIT 1) ,",",
		         (SELECT name_kh FROM `ln_client` WHERE client_id = tf.client_id LIMIT 1)) AS client_name ,
		CONCAT ( (SELECT co.`co_code` FROM ln_co AS co WHERE co.`co_id` = tf.from LIMIT 1),",",
		         (SELECT co.co_khname FROM ln_co AS co WHERE co.`co_id` = tf.from LIMIT 1)) AS from_coname,
       CONCAT (  (SELECT co.`co_code` FROM ln_co AS co WHERE co.`co_id` = tf.to LIMIT 1),",",
                 (SELECT co.co_khname FROM ln_co AS co WHERE co.`co_id` = tf.to LIMIT 1)) AS to_coname,
                 tf.date,tf.note,
                (SELECT `name_en` FROM `ln_view` WHERE TYPE = 3 AND key_code = tf.status ) AS status
    	 FROM ln_tranfser_co AS tf WHERE tf.status = 1 AND tf.type = 3';
    	$order = " ORDER BY tf.id DESC";
    	if($search['status']>1){
    		$where.= " tf.status = ".$search['status'];
    	}
    	if(($search['branch_name'])>0){
    		$where.= " AND tf.branch_id = ".$search['branch_name'];
    	}
    	if(($search['loan_number'])>0){
    		$where.= " AND tf.loan_id = ".$search['loan_number'];
    	}
    	if(!empty($search['note'])){
    		$where.= " AND tf.note  LIKE '%{$search['note']}%'";
    		 
    	}
    	if(($search['name_co'])>0){
    		$where.= " AND ( tf.from = ".$search['name_co'] ;
    		$where.= " OR tf.to = ".$search['name_co']." )" ;
    		
    	}
//     	echo $sql.$where.$order;
    	return $db->fetchAll($sql.$where.$order);
    }
    public function getAllinfoTransfer($id){
    	$db = $this->getAdapter();
    	$sql ="SELECT * FROM `ln_tranfser_co` WHERE id = $id";
    	return $db->fetchRow($sql);
    }
   
    public function insertTransferloan($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try {
    		$dbg = new Application_Model_DbTable_DbGlobal();
    		$row = $dbg->getClientIdBYMemberId($data['loan_client']);
    		$_data_arr = array(
    				'client_id'=>$row['client_id'],
    				'branch_id'=> $data['branch_name'],
    				'loan_id'=> $data['loan_number'],
    				'code_to'=> $data['name_co'],
    				'from'=>$row['co_id'],
    				'to'=> $data['name_co'],
    				'status'=> $data['status'],
    				'date'=> $data['Date'],
    				'note'=> $data['Note'],
    				'type'=> 3,
    				'user_id'=>$this->getUserId()
    		);
    		$this->insert($_data_arr);
    		$this->_name ="ln_loanmember_funddetail";
    		$_arr_fund = array(
    				'collect_by'=>$data['name_co'],
    		);
    		$where = " member_id = ".$data['loan_number']." AND is_completed = 0 AND status = 1 ";
    		$this->update($_arr_fund, $where);
    		$db->commit();
    	}catch (Exception $e){
    		$db->rollBack();
    	}
    }
    public function updatTransferloan($data,$id){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try {
    		$dbg = new Application_Model_DbTable_DbGlobal();
    		$row = $dbg->getClientIdBYMemberId($data['loan_client']);
    		$_data_arr = array(
    				'client_id'=>$row['client_id'],
    				'branch_id'=> $data['branch_name'],
    				'loan_id'=> $data['loan_number'],
    				'code_to'=> $data['name_co'],
    				'from'=>$row['co_id'],
    				'to'=> $data['name_co'],
    				'status'=> $data['status'],
    				'date'=> $data['Date'],
    				'note'=> $data['Note'],
    				'type'=> 3,
    				'user_id'=>$this->getUserId()
    		);
    		$wheres = "id = $id";
    		$this->update($_data_arr, $wheres);
    		$this->_name ="ln_loanmember_funddetail";
    		$_arr_fund = array(
    				'collect_by'=>$data['name_co'],
    		);
    		$where = "member_id = ".$data['loan_number']." AND is_completed = 0 AND status = 1 ";
    		$this->update($_arr_fund, $where);
    		$db->commit();
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("INSERT_FAIL");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    	}
    }
    public function insertTransfer($data){//type =2
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try {
    		$_data_arr = array(
    				'branch_id'=> $data['branch_name'],
    				'client_id'=> $data['member'],
    				'to'=> $data['name_co'],
    				'status'=> $data['status'],
    				'date'=> $data['Date'],
    				'note'=> $data['Note'],
    				'type'=> 2,
    				'user_id'=>$this->getUserId()
    		);
    		$this->insert($_data_arr);
    		$this->_name ="ln_loanmember_funddetail";
    		
    		$rows = $this->getAllIdFundDetailByClient($data['member']);
    		$arr= array(
    				'collect_by'=>$data['name_co'],
    		);
    		foreach ($rows as $row){
    			$where ='  is_completed = 0 AND status = 1 AND id = '. $row['id'];
    			$this->update($arr, $where);
    		}
    		$db->commit();
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("INSERT_FAIL");
    		$err =$e->getMessage();
    		Application_Model_DbTable_DbUserLog::writeMessageError($err);
    		$db->rollBack();
    	}
    }
    function getAllIdFundDetailByClient($client_id){
    	$db = $this->getAdapter();
    	$sql = "SELECT f.id FROM `ln_loan_member` AS m ,`ln_loanmember_funddetail` AS f WHERE m.member_id = f.member_id AND
    	m.client_id = $client_id AND f.status=1 AND f.is_completed = 0 ";
    	return $db->fetchAll($sql);
    	 
    }
    public function updatTransfer($data,$id){//can not undo to old collect by
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try {
    		$_data_arr = array(
    				'branch_id'=> $data['branch_name'],
    				'client_id'=> $data['member'],
    				'to'=> $data['name_co'],
    				'status'=> $data['status'],
    				'date'=> $data['Date'],
    				'note'=> $data['Note'],
    				'type'=> 2,
    				'user_id'=>$this->getUserId()
    		);
    		$wheres = "id = $id";
    		$this->update($_data_arr, $wheres);
    		
    		$this->_name ="ln_loanmember_funddetail";
    		
    		$rows = $this->getAllIdFundDetailByClient($data['member']);
    		$arr= array(
    				'collect_by'=>$data['name_co'],
    		);
    		foreach ($rows as $row){
    			$where ='  is_completed = 0 AND status = 1 AND id = '. $row['id'];
    			$this->update($arr, $where);
    		}
    		$db->commit();
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("INSERT_FAIL");
    		$err =$e->getMessage();
    		Application_Model_DbTable_DbUserLog::writeMessageError($err);
    		$db->rollBack();
    	}
    }
  
}

