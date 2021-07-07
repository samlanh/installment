<?php
class Incexp_Model_DbTable_DbCredit extends Zend_Db_Table_Abstract
{
	protected $_name = 'ln_credit';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}
	function addCredit($data){
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
				'cheque'		=>$data['cheque'],
				'description'	=>$data['Description'],
				'date'			=>$data['Date'],
				'status'		=>1,
				'user_id'		=>$this->getUserId(),
				'create_date'	=>date('Y-m-d'),
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
						'user_id'=>$this->getUserId(),
					);
			$where=" id =  $id " ;
			$this->update($arr, $where);
			$_db->commit();
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	
	function getAllCredit($search=null){
		$db = $this->getAdapter();
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
		$where = " WHERE ".$from_date." AND ".$to_date;
		
		$sql=" SELECT id,
				(SELECT project_name FROM `ln_project` WHERE ln_project.br_id =branch_id LIMIT 1) AS branch_name,
				(SELECT name_kh FROM `ln_client` WHERE ln_client.client_id =ln_credit.client_id LIMIT 1) AS client_name,
				(SELECT CONCAT(land_address,',',street) FROM `ln_properties` WHERE id=house_id LIMIT 1) as house_no,
				title, invoice,
				(SELECT name_kh FROM `ln_view` WHERE type=30 and key_code=category_id LIMIT 1) AS category_name,
				(SELECT name_kh FROM `ln_view` WHERE type=2 and key_code=payment_id LIMIT 1) AS payment_type,
				total_amount,description,date,
				(SELECT  first_name FROM rms_users WHERE id=user_id limit 1 ) AS user_name ";
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->caseStatusShowImage("status");
		$sql.=" FROM ln_credit ";
		
		if (!empty($search['adv_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['adv_search']));
			$s_where[] = " description LIKE '%{$s_search}%'";
			$s_where[] = " title LIKE '%{$s_search}%'";
			$s_where[] = " total_amount LIKE '%{$s_search}%'";
			$s_where[] = " invoice LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['client_name']>0){
			$where.= " AND ln_credit.client_id = ".$search['client_name'];
		}
		if($search['land_id']>0){
			$where.= " AND ln_credit.house_id = ".$search['land_id'];
		}
		if($search['status']>-1){
			$where.= " AND ln_credit.status = ".$search['status'];
		}
		if($search['branch_id']>-0){
			$where.= " AND branch_id = ".$search['branch_id'];
		}
		
		if(!empty($search['credit_category'])){
			$condiction = $dbp->getChildType($search['credit_category']);
			if (!empty($condiction)){
				$where.=" AND category_id IN ($condiction)";
			}else{
				$where.=" AND category_id=".$search['credit_category'];
			}
		}
		
		$where.=$dbp->getAccessPermission("branch_id");
		$order=" order by id desc ";
		return $db->fetchAll($sql.$where.$order);
	}
	function getInvoiceNo($branch_id){
		$db = $this->getAdapter();
		
		$dbtable = new Application_Model_DbTable_DbGlobal();
		$prefix ="";// $this->getPrefixCodeByBranch($branch_id);
		$sql = " select count(id) from ln_credit where branch_id = $branch_id";
		$result = $db->fetchOne($sql);
		$pre = 'CR:';
		
		$length = strlen((int)$result);
		for($i = $length;$i < 3 ; $i++){
			$pre.='0';
		}
		return $prefix.$pre.$result;
	}
	function getCreditbyId($id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM ln_credit where id=$id ";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission("branch_id");
		return $db->fetchRow($sql);
	}
}