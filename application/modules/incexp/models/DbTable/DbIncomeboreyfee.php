<?php
class Incexp_Model_DbTable_DbIncomeboreyfee extends Zend_Db_Table_Abstract
{
	protected $_name = 'ln_income';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	
	}
	function addIncomeBoreyFee($data){
		$_db= $this->getAdapter();
		$_db->beginTransaction();
		try{
			$dbInc = new Incexp_Model_DbTable_DbIncome();
			$invoice = $dbInc->getInvoiceNo($data['branch_id']);
			$array = array(
				'branch_id'		=>$data['branch_id'],
				'sale_id'		=>$data['sale_client'],
				'house_id'		=>$data['house_id'],
				'client_id'		=>$data['customerId'],
				'title'			=>$data['title'],
				'total_amount'	=>$data['total_amount'],
				'invoice'		=>$invoice,
				'category_id'	=>$data['income_category'],
				'payment_id'	=>$data['payment_type'],
				'bank_id'		=>$data['bank_id'],
				'cheque'		=>$data['cheque'],
				'description'	=>$data['Description'],
				'date'			=>$data['Date'],
				'incomeType'	=>2,
				'status'		=>1,
				'user_id'		=>$this->getUserId(),
				'create_date'	=>date('Y-m-d'),
					
				'qty'			=>$data['qty'],
				'unit_price'	=>$data['unit_price'],
				'amount'		=>$data['amount'],
				'disAmount'		=>$data['disAmount'],
				'disPercent'		=>$data['disPercent'],
				'from_date'=>$data['from_date'],
				'next_date'=>$data['end_date'],
			);
			$this->insert($array);
			$_db->commit();
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
 	 }
	 function updateIncomeBoreyFee($data){
	 	$_db= $this->getAdapter();
	 	$_db->beginTransaction();
	 	try{
			$id = empty($data['id']) ? 0 : $data['id'];
			$arr = array(
				'sale_id'		=>$data['sale_client'],
				'house_id'		=>$data['house_id'],
				'branch_id'		=>$data['branch_id'],
				'client_id'		=>$data['customerId'],
				'title'			=>$data['title'],
				'total_amount'	=>$data['total_amount'],
				'invoice'		=>$data['invoice'],
				'category_id'	=>$data['income_category'],
				'payment_id'	=>$data['payment_type'],
				'bank_id'		=>$data['bank_id'],
				'cheque'		=>$data['cheque'],
				'description'	=>$data['Description'],
				'date'			=>$data['Date'],
				'status'		=>$data['Stutas'],
				'user_id'		=>$this->getUserId(),
					
				'qty'			=>$data['qty'],
				'unit_price'	=>$data['unit_price'],
				'amount'		=>$data['amount'],
				'disAmount'		=>$data['disAmount'],
				'disPercent'		=>$data['disPercent'],
				'from_date'		=>$data['from_date'],
				'next_date'		=>$data['end_date'],
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
		$sql=" SELECT * FROM ln_income where id=$id AND incomeType = 2 ";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission("branch_id");
		return $db->fetchRow($sql);
	}
	
	function getAllIncomeBoreyFee($search=null){
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
				(SELECT  first_name FROM rms_users WHERE id=user_id limit 1 ) AS user_name 
		";
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->caseStatusShowImage("status");
		$sql.=" FROM ln_income ";
		$where.= " AND ln_income.incomeType = 2 ";
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
		
}