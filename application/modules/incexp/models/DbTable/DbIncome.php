<?php
class Incexp_Model_DbTable_DbIncome extends Zend_Db_Table_Abstract
{
	protected $_name = 'ln_income';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	
	}
	function addIncome($data){
		$_db= $this->getAdapter();
		$_db->beginTransaction();
		try{
			$invoice = $this->getInvoiceNo($data['branch_id']);
			$array = array(
				'branch_id'		=>$data['branch_id'],
				'sale_id'		=>$data['sale_client'],
				'house_id'		=>$data['house_id'],
				'client_id'		=>$data['customer'],
				'title'			=>$data['title'],
				'total_amount'	=>$data['total_amount'],
				'invoice'		=>$invoice,
				'category_id'	=>$data['income_category'],
				'payment_id'	=>$data['payment_type'],
				'bank_id'		=>$data['bank_id'],
				'cheque'		=>$data['cheque'],
				'description'	=>$data['Description'],
				'date'			=>$data['Date'],
				'status'		=>1,
				'user_id'		=>$this->getUserId(),
				'create_date'	=>date('Y-m-d'),
				'is_beginning'=>$data['is_beginning'],
					
				'qty'=>$data['qty'],
				'unit_price'=>$data['unit_price'],
				'amount'=>$data['total_amount'],
				'from_date'=>$data['from_date'],
				'next_date'=>$data['end_date'],
			);
			$this->insert($array);
			$_db->commit();
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
 	 }
	 function updateIncome($data,$id){
	 	$_db= $this->getAdapter();
	 	$_db->beginTransaction();
	 	try{
			$arr = array(
				'sale_id'	=>$data['sale_client'],
				'house_id'	=>$data['house_id'],
				'branch_id'=>$data['branch_id'],
				'client_id'=>$data['customer'],
				'title'=>$data['title'],
				'total_amount'=>$data['total_amount'],
				'invoice'=>$data['invoice'],
				'category_id'=>$data['income_category'],
				'payment_id'=>$data['payment_type'],
				'cheque'=>$data['cheque'],
				'description'=>$data['Description'],
				'date'=>$data['Date'],
				'status'=>$data['Stutas'],
			    'is_beginning'=>$data['is_beginning'],
				'user_id'=>$this->getUserId(),
					
				'qty'=>$data['qty'],
				'unit_price'=>$data['unit_price'],
				'amount'=>$data['total_amount'],
				'from_date'=>$data['from_date'],
				'next_date'=>$data['end_date'],
			);
			$where=" id =  $id " ;
			$this->update($arr, $where);
			$_db->commit();
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	function getexpensebyid($id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM ln_income where id=$id AND incomeType = 1 ";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission("branch_id");
		return $db->fetchRow($sql);
	}
	
	function getAllIncome($search=null){
		$db = $this->getAdapter();
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
		$where = " WHERE ".$from_date." AND ".$to_date;
		
		$sql=" SELECT id,
				(SELECT project_name FROM `ln_project` WHERE ln_project.br_id =branch_id LIMIT 1) AS branch_name,
				(SELECT name_kh FROM `ln_client` WHERE ln_client.client_id =ln_income.client_id LIMIT 1) AS client_name,
				(SELECT CONCAT(land_address,',',street) FROM `ln_properties` WHERE id=house_id LIMIT 1) as house_no,
				title, invoice,
				(SELECT name_kh FROM `ln_view` WHERE type=12 and key_code=category_id LIMIT 1) AS category_name,
				(SELECT name_kh FROM `ln_view` WHERE type=2 and key_code=payment_id LIMIT 1) AS payment_type,
				total_amount,description,date,
				(SELECT  first_name FROM rms_users WHERE id=user_id limit 1 ) AS user_name ";
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->caseStatusShowImage("status");
		$sql.=" FROM ln_income ";
		$where.= " AND ln_income.incomeType = 1 ";
		if (!empty($search['adv_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['adv_search']));
			$s_where[] = " description LIKE '%{$s_search}%'";
			$s_where[] = " title LIKE '%{$s_search}%'";
			$s_where[] = " total_amount LIKE '%{$s_search}%'";
			$s_where[] = " invoice LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT land_address FROM `ln_properties` WHERE id=house_id LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT street FROM `ln_properties` WHERE id=house_id LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT CONCAT(land_address,',',street) FROM `ln_properties` WHERE id=house_id LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT name_kh FROM `ln_client` WHERE ln_client.client_id =ln_income.client_id LIMIT 1) LIKE '%{$s_search}%'";
			$s_where[] = " (SELECT name_kh FROM `ln_view` WHERE type=12 and key_code=category_id LIMIT 1) LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['client_name']>0){
			$where.= " AND ln_income.client_id = ".$search['client_name'];
		}
		if($search['land_id']>0){
			$where.= " AND ln_income.house_id = ".$search['land_id'];
		}
		if($search['status']>-1){
			$where.= " AND ln_income.status = ".$search['status'];
		}
		if($search['branch_id']>-0){
			$where.= " AND branch_id = ".$search['branch_id'];
		}
		
		if(!empty($search['category_id'])){
			$condiction = $dbp->getChildType($search['category_id']);
			if (!empty($condiction)){
				$where.=" AND category_id IN ($condiction)";
			}else{
				$where.=" AND category_id=".$search['category_id'];
			}
		}
		
		$where.=$dbp->getAccessPermission("branch_id");
		$order=" order by id desc ";
		return $db->fetchAll($sql.$where.$order);
	}
	function getAllExpenseReport($search=null){
		$db = $this->getAdapter();
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
		$where = " WHERE ".$from_date." AND ".$to_date;
	
		$sql=" SELECT id,
		(SELECT branch_namekh FROM `rms_branch` WHERE rms_branch.br_id =branch_id LIMIT 1) AS branch_name,
		account_id,
		(SELECT symbol FROM `ln_currency` WHERE ln_currency.id =curr_type) AS currency_type,invoice,
		curr_type,
		total_amount,disc,date,status FROM $this->_name ";
	
		if (!empty($search['adv_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['adv_search']));
			$s_where[] = " account_id LIKE '%{$s_search}%'";
			$s_where[] = " title LIKE '%{$s_search}%'";
			$s_where[] = " total_amount LIKE '%{$s_search}%'";
			$s_where[] = " invoice LIKE '%{$s_search}%'";
			
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['status']>-1){
			$where.= " AND status = ".$search['status'];
		}
		if($search['currency_type']>-1){
			$where.= " AND curr_type = ".$search['currency_type'];
		}
		$order=" order by id desc ";
		return $db->fetchAll($sql.$where.$order);
	}

	function getExchangeRate(){
		$db = $this->getAdapter();
		$sql="select * from rms_exchange_rate where active = 1 ";
		return $db->fetchRow($sql);
	}

	function getPrefixCodeByBranch($branch_id){
		
		$db = $this->getAdapter();
		$sql="select prefix from ln_project where status = 1 and br_id = $branch_id limit 1";
		return $db->fetchOne($sql);
		
	}
	
	function getInvoiceNo($branch_id){
		$db = $this->getAdapter();
		
		$dbtable = new Application_Model_DbTable_DbGlobal();
		
// 		$receipt = $dbtable->getReceiptByBranch($data);
		
		$prefix ="";// $this->getPrefixCodeByBranch($branch_id);
		
		$sql = " select count(id) from ln_income where branch_id = $branch_id";
		$amount = $db->fetchOne($sql);
		
		$sql1 = " select count(id) from ln_otherincomepayment where branch_id = $branch_id AND cate_type=12 ";
		$amount1 = $db->fetchOne($sql1);
		
		$pre = 'inc1:';
		$result = $amount + 1;
		
		$result =$result+$amount1;
		
		$length = strlen((int)$result);
		for($i = $length;$i < 3 ; $i++){
			$pre.='0';
		}
		return $prefix.$pre.$result;
	}

	function getAllIncomeCategory($type=12,$parent = 0, $spacing = '', $cate_tree_array = ''){
		if (!is_array($cate_tree_array))
			$cate_tree_array = array();
		$db = $this->getAdapter();
		$sql = " SELECT key_code AS id,name_kh AS name FROM ln_view WHERE type=$type AND status=1 AND name_kh!='' AND `parent_id` = $parent AND status=1";
		$query= $db->fetchAll($sql);
		
		$rowCount = count($query);
		if ($rowCount > 0) {
			foreach ($query as $row){
				$cate_tree_array[] = array("id" => $row['id'], "name" => $spacing . $row['name']);
				$cate_tree_array = $this->getAllIncomeCategory($type,$row['id'], $spacing . ' - ', $cate_tree_array);
			}
		}
		return $cate_tree_array;
		
	}
// 	function getAllIncomeCategoryParent($type=12){
// 		$db = $this->getAdapter();
// 		$sql = " select key_code as id,name_kh as name from ln_view where type=$type AND name_kh!='' AND parent_id=0 ";
// 		return $db->fetchAll($sql);
	
// 	}

	public function getAllIncomeCategoryParent($type=12,$cate_id,$parent = 0, $spacing = '', $cate_tree_array = ''){
		$db=$this->getAdapter();
		if (!is_array($cate_tree_array))
			$cate_tree_array = array();
		$sql = " SELECT key_code AS id,name_kh as name FROM ln_view where type=$type AND name_kh!='' AND `parent_id` = $parent AND status=1 ";
		if (!empty($cate_id)){
			$sql.=" AND id != $cate_id";
		}
		$query = $db->fetchAll($sql);
		$rowCount = count($query);
		
		$id='';
		if ($rowCount > 0) {
			foreach ($query as $row){
				$cate_tree_array[] = array("id" => $row['id'], "name" => $spacing . $row['name']);
				$cate_tree_array = $this->getAllIncomeCategoryParent($type,$cate_id,$row['id'], $spacing . ' - ', $cate_tree_array);
			}
		}
		return $cate_tree_array;
	}
	function getNewKeyCode($type){
		$db = $this->getAdapter();
		//$sql="SELECT COUNT(id) FROM ln_view WHERE type = $type ORDER BY key_code DESC LIMIT 1";
		$sql="SELECT key_code FROM ln_view WHERE type = $type ORDER BY key_code DESC LIMIT 1";
		$result = $db->fetchOne($sql);
		$key_code = $result + 1;
		return $key_code;
	}
	
	function AddNewCategory($data,$type){ // type=1 => income , type=2 => expense
		$db = $this->getAdapter();
		if($type==1){
			$type=12;
			if(!empty($data['type'])){
				$type=$data['type'];
			}
		}else{
			$type=13;
		}
		$key_code = $this->getNewKeyCode($type);
		$this->_name = "ln_view" ;
		
		$status = empty($data['status_j'])?1:$data['status_j'];
		$parent = empty($data['parent_id'])?0:$data['parent_id'];
		$array = array(
				'parent_id'		=>$parent,
				'name_kh'	=>$data['cate_name'],
				'name_en'	=>$data['cate_name'],
				'type'		=>$type,
				'key_code'	=>$key_code,
				'status'	=>$status,
				);
		$this->insert($array);
		return $key_code; // to set key_code value to added new field
	}
	
	function getAllCustomer($branch_id){
		$db = $this->getAdapter();
		$sql="SELECT client_id as id,name_kh as name FROM ln_client WHERE status = 1 ";
		return $db->fetchAll($sql);
	}	
}