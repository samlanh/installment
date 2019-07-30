<?php
class Incexp_Model_DbTable_DbIncomeOtherPayment extends Zend_Db_Table_Abstract
{
	protected $_name = 'ln_otherincomepayment';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}
	function getAllIncomePayment($search=null){
		$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
		
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		$from_date =(empty($search['start_date']))? '1': " op.for_date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " op.for_date <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
	
		$sql="SELECT op.id,
			(SELECT project_name FROM `ln_project` WHERE ln_project.br_id =op.branch_id LIMIT 1) AS branch_name,
			(SELECT name_kh FROM `ln_client` WHERE ln_client.client_id =oi.client_id LIMIT 1) AS client_name,
			(SELECT CONCAT(p.land_address,',',p.street) FROM `ln_properties` AS p WHERE p.id=oi.house_id LIMIT 1) AS house_no,
			op.title_income,
			op.receipt_no,
			(SELECT vt.name FROM `ln_view_type` AS vt WHERE vt.id=op.cate_type LIMIT 1) AS typecate,
			(SELECT v.name_kh FROM ln_view AS v WHERE v.type=op.cate_type AND v.key_code=op.category LIMIT 1) AS category,
			(SELECT ln_view.name_kh FROM `ln_view` WHERE ln_view.type=26 and ln_view.key_code=op.payment_method LIMIT 1) AS payment_type,
			op.balance,
			op.total_paid,
			op.remain,
			(SELECT v.name_kh FROM ln_view AS v WHERE v.type=2 AND key_code=op.payment_method LIMIT 1) AS payment_method,
			op.for_date,
			(SELECT  first_name FROM rms_users WHERE id=op.user_id LIMIT 1 ) AS user_name
			 ";
	
		$sql.=$dbp->caseStatusShowImage("op.status");
		$sql.=" FROM `ln_otherincomepayment` AS op,
			`ln_otherincome` AS oi
			WHERE oi.id = op.otherincome_id ";
		
		if (!empty($search['adv_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['adv_search']));
			$s_where[] = " op.receipt_no LIKE '%{$s_search}%'";
			$s_where[] = " op.balance LIKE '%{$s_search}%'";
			$s_where[] = " op.total_paid LIKE '%{$s_search}%'";
			$s_where[] = " payment_method LIKE '%{$s_search}%'";
			$s_where[] = " op.remain LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if(!empty($search['payment_method'])){
			$where.= " AND payment_method = ".$search['payment_method'];
		}
		if(!empty($search['category_id'])){
			$where.= " AND op.category = ".$search['category_id'];
		}
		if(!empty($search['land_id']) AND $search['land_id']>-1){
			$where.= " AND oi.house_id = ".$search['land_id'];
		}
		if($search['client_name']>0){
			$where.= " AND oi.client_id = ".$search['client_name'];
		}
		if($search['branch_id']>-0){
			$where.= " AND op.branch_id = ".$search['branch_id'];
		}
		if($search['type']>0){
			$where.= " AND op.cate_type = ".$search['type'];
		}
		
		$where.=$dbp->getAccessPermission("op.branch_id");
		
		$order=" order by id desc ";
		return $db->fetchAll($sql.$where.$order);
	}
	
	function getAllOtherIncome($branch_id,$otherincome_id=null){
		$db = $this->getAdapter();
		$sql='SELECT 
			oin.id,
			CONCAT(oin.invoice," ",(SELECT c.name_kh FROM `ln_client` AS c WHERE c.client_id = oin.client_id LIMIT 1)," ",
			(SELECT p.land_address FROM `ln_properties` AS p WHERE p.id = oin.house_id LIMIT 1),",",(SELECT p.street FROM `ln_properties` AS p WHERE p.id = oin.house_id LIMIT 1)) AS `name`
		FROM ln_otherincome
			AS oin
		WHERE oin.is_fullpaid=0 AND oin.branch_id ='.$branch_id.'
		';
		if (!empty($otherincome_id)){
			$sql.=" OR oin.id = $otherincome_id";
		}
		return $db->fetchAll($sql);
	}
	function getOtherIncomeInfo($id){
		$db = $this->getAdapter();
		$sql='
		SELECT
		oin.*,
		(SELECT name_kh FROM `ln_client` WHERE ln_client.client_id =oin.client_id LIMIT 1) AS client_name,
		(SELECT land_address FROM `ln_properties` WHERE id=oin.house_id LIMIT 1) AS land_address,
		(SELECT street FROM `ln_properties` WHERE id=oin.house_id LIMIT 1) AS street
		FROM ln_otherincome
		AS oin
		WHERE oin.id ='.$id.'
		';
		return $db->fetchRow($sql);
	}
	function addIncome($data){
		$_db= $this->getAdapter();
		$_db->beginTransaction();
		try{
// 			$invoice = $this->getInvoiceNoOtherIncomePayment($data['branch_id']);
			if ($data['cate_type']==12){
				$dbincom = new Incexp_Model_DbTable_DbIncome();
				$invoice = $dbincom->getInvoiceNo($data['branch_id']);
			}else{
				$_dbexpense = new Incexp_Model_DbTable_DbExpense();
				$invoice = $_dbexpense->getInvoiceNo($data['branch_id']);
			}
			
			$OtherIncomeInfo = $this->getOtherIncomeInfo($data['otherincome_id']);
			$totalpaid = $data['total_amount'];
			if (!empty($OtherIncomeInfo)){
				if ($data['balance']<0){
					$total_amountAfter = $OtherIncomeInfo['total_amountafter']-$totalpaid;
					$is_fullpaid=1;
					if ($total_amountAfter<0){
						$is_fullpaid=0;
					}
				}else{
					$total_amountAfter = $OtherIncomeInfo['total_amountafter']-$totalpaid;
					$is_fullpaid=1;
					if ($total_amountAfter>0){
						$is_fullpaid=0;
					}
				}
				$array=array(
						'total_amountafter'=>$total_amountAfter,
						'is_fullpaid'=>$is_fullpaid,
				);
				$where="id=".$data['otherincome_id'];
				$this->_name="ln_otherincome";
				$this->update($array, $where);
			}
			
			$_arr = array(
					'branch_id'			=>$data['branch_id'],
					'otherincome_id'	=>$data['otherincome_id'],
					'title_income'	=>$data['title'],
					'receipt_no'		=>$invoice,
					'for_date'			=>$data['for_date'],
					'cate_type'			=>$data['cate_type'],
					'category'			=>$data['income_category'],
					'payment_method'	=>$data['payment_method'],
					'cheque'			=>$data['cheque'],
					'balance'			=>$data['balance'],
					'total_paid'		=>$totalpaid,
					'remain'			=>$data['remain'],
					'note'				=>$data['description'],
					'status'			=>1,
					'user_id'			=>$this->getUserId(),
					'create_date'		=>date('Y-m-d H:i:s'),
					'modify_date'		=>date('Y-m-d H:i:s'),
			);
			$this->_name="ln_otherincomepayment";
			$income_id = $this->insert($_arr);
	
			$_db->commit();
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	function editIncomePayment($data){
		$_db= $this->getAdapter();
		$_db->beginTransaction();
		try{
			//reset old record Other Income
			$row = $this->getOtherIncomePaymentById($data['id']);
			$OtherIncomeInfo = $this->getOtherIncomeInfo($row['otherincome_id']);
			if (!empty($OtherIncomeInfo)){
				$total_amountAfter = $OtherIncomeInfo['total_amountafter']+$row['total_paid'];
				$array=array(
						'total_amountafter'=>$total_amountAfter,
						'is_fullpaid'=>0,
				);
				$where="id=".$row['otherincome_id'];
				$this->_name="ln_otherincome";
				$this->update($array, $where);
			}
		
			if ($data['status']==0){
				$_arr = array(
						'status'			=>$data['status'],
						'user_id'			=>$this->getUserId(),
						'modify_date'		=>date('Y-m-d H:i:s'),
				);
				$this->_name="ln_otherincomepayment";
				$where = " id = ".$data['id'];
				$this->update($_arr, $where);
				$_db->commit();
				return 1;
			}
			
			$OtherIncomeInfo = $this->getOtherIncomeInfo($data['otherincome_id']);
			$totalpaid = $data['total_amount'];
			if (!empty($OtherIncomeInfo)){
				if ($data['balance']<0){
					$total_amountAfter = $OtherIncomeInfo['total_amountafter']-$totalpaid;
					$is_fullpaid=1;
					if ($total_amountAfter<0){
						$is_fullpaid=0;
					}
				}else{
					$total_amountAfter = $OtherIncomeInfo['total_amountafter']-$totalpaid;
					$is_fullpaid=1;
					if ($total_amountAfter>0){
						$is_fullpaid=0;
					}
				}
				$array=array(
						'total_amountafter'=>$total_amountAfter,
						'is_fullpaid'=>$is_fullpaid,
				);
				$where="id=".$data['otherincome_id'];
				$this->_name="ln_otherincome";
				$this->update($array, $where);
			}
				
			$_arr = array(
					'branch_id'			=>$data['branch_id'],
					'otherincome_id'	=>$data['otherincome_id'],
					'title_income'	=>$data['title'],
					'for_date'			=>$data['for_date'],
					'cate_type'			=>$data['cate_type'],
					'category'			=>$data['income_category'],
					'payment_method'	=>$data['payment_method'],
					'cheque'			=>$data['cheque'],
					'balance'			=>$data['balance'],
					'total_paid'		=>$totalpaid,
					'remain'			=>$data['remain'],
					'note'				=>$data['description'],
					'status'			=>$data['status'],
					'user_id'			=>$this->getUserId(),
					'modify_date'		=>date('Y-m-d H:i:s'),
			);
			$this->_name="ln_otherincomepayment";
			$where = " id = ".$data['id'];
			$this->update($_arr, $where);
	
			$_db->commit();
			return 1;
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	function getOtherIncomePaymentById($id){
		$db = $this->getAdapter();
		$sql="SELECT icp.* FROM `ln_otherincomepayment` AS icp WHERE icp.id = $id ";
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission("icp.branch_id");
		$sql.=" LIMIT 1 ";
		return $db->fetchRow($sql);
	}
	function checkOtherIncomeInpay($otherincome){
		$db = $this->getAdapter();
		$sql="SELECT SUM(o.total_paid) FROM `ln_otherincomepayment` AS o WHERE  o.otherincome_id = $otherincome AND o.status=1";
		return $db->fetchOne($sql);
	}
	
// 	function getInvoiceNoOtherIncomePayment($branch_id){
// 		$db = $this->getAdapter();
	
// 		$dbtable = new Application_Model_DbTable_DbGlobal();
// 		$prefix ="";// $this->getPrefixCodeByBranch($branch_id);
// 		$sql = " select count(id) from ln_otherincomepayment where branch_id = $branch_id";
// 		$amount = $db->fetchOne($sql);
	
// 		$pre = 'incP:';
// 		$result = $amount + 1;
	
// 		$length = strlen((int)$result);
// 		for($i = $length;$i < 3 ; $i++){
// 			$pre.='0';
// 		}
// 		return $prefix.$pre.$result;
// 	}
}







