<?php
class Invpayment_Model_DbTable_DbExpense extends Zend_Db_Table_Abstract
{
	protected $_name = 'st_expense';
	public function getUserId()
	{
		$session_user = new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}
	function addExpense($data)
	{
		$_db = $this->getAdapter();
		$_db->beginTransaction();
		try {
			$_arr = array(
				'projectId' => $data['branch_id'],
				'paymentNo' => $data['paymentNo'],
				'externalInvoice' => $data['externalInvoice'],
				'expenseTitle' => $data['expenseTitle'],
				'receiver' => $data['receiver'],
				'note' => $data['note'],
				'paymentMethod' => $data['paymentMethod'],
				'bankId' => $data['bankId'],
				'accNameAndChequeNo' => $data['accNameAndChequeNo'],
				'paymentDate' => $data['paymentDate'],
				'totalAmount' => $data['totalAmount'],
				'budgetId' => $data['budgetItem'],
				'userId' => $this->getUserId(),
				'createDate' => date('Y-m-d H:i:s'),
			);
			$expend_id = $this->insert($_arr);
			$ids = explode(',', $data['identity']);
			$this->_name = 'st_expense_detail';
			foreach ($ids as $j) {
				$arr = array(

					'expenseId' => $expend_id,
					'cateExpenseId' => $data['cate_expense_id_' . $j],
					'price' => $data['price_' . $j],
					'qty' => $data['qty_' . $j],
					'total' => $data['total_' . $j],
					'note' => $data['remark_' . $j],
				);
				$this->insert($arr);
			}
			$_db->commit();
		} catch (Exception $e) {
			$_db->rollBack();
			echo $e->getMessage();
		}
	}

	function updateData($data)
	{

		$db = $this->getAdapter();
		$db->beginTransaction();
		try {
			$id = $data['id'];
			$arr = array(
				'projectId' => $data['branch_id'],
				'paymentNo' => $data['paymentNo'],
				'externalInvoice' => $data['externalInvoice'],
				'expenseTitle' => $data['expenseTitle'],
				'receiver' => $data['receiver'],
				'note' => $data['note'],
				'paymentMethod' => $data['paymentMethod'],
				'bankId' => $data['bankId'],
				'accNameAndChequeNo' => $data['accNameAndChequeNo'],
				'paymentDate' => $data['paymentDate'],
				'totalAmount' => $data['totalAmount'],
				'budgetId' => $data['budgetItem'],
				'userId' => $this->getUserId(),
				'createDate' => date('Y-m-d H:i:s'),
				'status' => $data['status']
			);
			$this->_name = 'st_expense';
			$where = "id=" . $id;
			$this->update($arr, $where);

			if ($data['status'] == 1) {

				$identitys = explode(',', $data['identity']);

				$detailId = "";
				if (!empty($identitys)) {
					foreach ($identitys as $i) {
						if (empty($detailId)) {
							if (!empty($data['detailId' . $i])) {
								$detailId = $data['detailId' . $i];
							}
						} else {
							if (!empty($data['detailId' . $i])) {
								$detailId = $detailId . "," . $data['detailId' . $i];
							}
						}
					}
				}
				$this->_name = 'st_expense_detail';
				$whereDl = 'expenseId = ' . $id;
				if (!empty($detailId)) {
					$whereDl .= " AND id NOT IN ($detailId)";
				}
				$this->delete($whereDl);

				if (!empty($data['identity'])) {
					$ids = explode(',', $data['identity']);
					foreach ($ids as $i) {

						if (!empty($data['detailId' . $i])) {
							$arr = array(

								'expenseId' => $id,
								'cateExpenseId' => $data['cate_expense_id_' . $i],
								'price' => $data['price_' . $i],
								'qty' => $data['qty_' . $i],
								'total' => $data['total_' . $i],
								'note' => $data['remark_' . $i],
							);
							$this->_name = 'st_expense_detail';
							$where = " id =" . $data['detailId' . $i];
							$this->update($arr, $where);
						} else {

							$arr = array(

								'expenseId' => $id,
								'cateExpenseId' => $data['cate_expense_id_' . $i],
								'price' => $data['price_' . $i],
								'qty' => $data['qty_' . $i],
								'total' => $data['total_' . $i],
								'note' => $data['remark_' . $i],

							);
							$this->_name = 'st_expense_detail';
							$this->insert($arr);
						}
					}
				}


			}
			$db->commit();
		} catch (Exception $e) {
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			$db->rollBack();
		}
	}
	function getexpensebyid($id)
	{
		$db = $this->getAdapter();
		$sql = "SELECT *,  
		(SELECT l.project_name FROM `ln_project` AS l WHERE l.br_id = p.projectId LIMIT 1) AS projectName,
		(SELECT b.budgetTitle FROM `st_budget_item` AS b WHERE b.id = p.budgetId LIMIT 1) AS budgetItem,
		(SELECT vi.name_kh FROM `ln_view` AS vi WHERE vi.type=2 AND vi.key_code=p.`paymentMethod` LIMIT 1) AS paymentMethodTitle,
		(SELECT ba.bank_name FROM `st_bank` AS ba WHERE ba.id=p.`bankId` LIMIT 1) AS bankName,
		(SELECT us.user_name FROM `rms_users` AS us WHERE us.id=p.`userId` LIMIT 1) AS byUser
		FROM `st_expense` AS p	WHERE id=$id ";
		return $db->fetchRow($sql);
	}

	function getexpenseDetailbyid($id)
	{
		$db = $this->getAdapter();
		$sql = "SELECT *,
		( SELECT title FROM `st_cate_expense` WHERE st_cate_expense.id = st_expense_detail.cateExpenseId LIMIT 1 ) AS CateExpenseTitle
		 FROM st_expense_detail WHERE expenseId=" . $id;
		return $db->fetchAll($sql);
	}

	function getAllExpense($search = null)
	{
		$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();

		$sql = " SELECT p.id, 
				(SELECT l.project_name FROM `ln_project` AS l WHERE l.br_id = p.projectId LIMIT 1) AS projectName,
				p.expenseTitle,
				(SELECT b.budgetTitle FROM `st_budget_item` AS b WHERE b.id = p.budgetId LIMIT 1) AS budgetItem,
				p.paymentNo, p.receiver,
				(SELECT vi.name_kh FROM `ln_view` AS vi WHERE vi.type=2 AND vi.key_code=p.`paymentMethod` LIMIT 1) AS paymentMethod,
				(SELECT ba.bank_name FROM `st_bank` AS ba WHERE ba.id=p.`bankId` LIMIT 1) AS bankName,
				p.accNameAndChequeNo, p.totalAmount, p.paymentdate, p.status				
			";
		$sql .= $dbp->caseStatusShowImage("p.status");
		$sql .= " FROM `st_expense` AS p ";

		$from_date = (empty($search['start_date'])) ? '1' : " p.paymentdate >= '" . $search['start_date'] . " 00:00:00'";
		$to_date = (empty($search['end_date'])) ? '1' : " p.paymentdate <= '" . $search['end_date'] . " 23:59:59'";
		$where = " WHERE " . $from_date . " AND " . $to_date;

		if (!empty($search['advanceFilter'])) {
			$s_where = array();
			$s_search = trim(addslashes($search['advanceFilter']));
			$s_where[] = " p.expenseTitle LIKE '%{$s_search}%'";
			$s_where[] = " p.paymentNo LIKE '%{$s_search}%'";
			$s_where[] = " p.receiver LIKE '%{$s_search}%'";
			$where .= ' AND (' . implode(' OR ', $s_where) . ')';
		}
		if ($search['branch_id'] > 0) {
			$where .= " AND p.projectId = " . $search['branch_id'];
		}

		$where .= $dbp->getAccessPermission('p.projectId');
		// if($search['status']>-1){
		// 	$where.= " AND p.status = ".$search['status'];
		// }
		// if($search['paymentMethod']>-1){
		// 	$where.= " AND payment_type = ".$search['payment_type'];
		// }
		$order = " order by id desc ";
		return $db->fetchAll($sql . $where . $order);
	}
	function getAllExpenseReport($search = null)
	{
		$db = $this->getAdapter();
		$session_user = new Zend_Session_Namespace(SYSTEM_SES);
		$from_date = (empty($search['start_date'])) ? '1' : " p.paymentDate >= '" . $search['start_date'] . " 00:00:00'";
		$to_date = (empty($search['end_date'])) ? '1' : "p.paymentDate <= '" . $search['end_date'] . " 23:59:59'";
		$where = " AND  " . $from_date . " AND " . $to_date;

		$sql = "SELECT *,  
		(SELECT l.project_name FROM `ln_project` AS l WHERE l.br_id = p.projectId LIMIT 1) AS projectName,
		(SELECT b.budgetTitle FROM `st_budget_item` AS b WHERE b.id = p.budgetId LIMIT 1) AS budgetItem,
		(SELECT vi.name_kh FROM `ln_view` AS vi WHERE vi.type=2 AND vi.key_code=p.`paymentMethod` LIMIT 1) AS paymentMethodTitle,
		(SELECT ba.bank_name FROM `st_bank` AS ba WHERE ba.id=p.`bankId` LIMIT 1) AS bankName,
		(SELECT us.user_name FROM `rms_users` AS us WHERE us.id=p.`userId` LIMIT 1) AS byUser
		FROM `st_expense` AS p	WHERE p.status = 1		
		";

		if (!empty($search['adv_search'])) {
			$s_where = array();
			$s_search = addslashes(trim(($search['adv_search'])));
			$s_where[] = " p.expenseTitle LIKE '%{$s_search}%'";
			$s_where[] = " title LIKE '%{$s_search}%'";
			$s_where[] = " P.totalAmount LIKE '%{$s_search}%'";
			$s_where[] = " p.paymentNo LIKE '%{$s_search}%'";

			$where .= ' AND (' . implode(' OR ', $s_where) . ')';
		}
		if ($search['branch_id'] > 0) {
			$where .= " AND p.projectId = " . $search['status'];
		}

		if (!empty($search['paymentMethod'])) {
			$where .= " AND p.paymentMethod = " . $search['paymentMethod'];
		}
		if (!empty($search['bankId'])) {
			$where .= " AND p.bankId = " . $search['bankId'];
		}
		if (!empty($search['budgetItem'])) {
			$where .= " AND p.budgetId = " . $search['budgetItem'];
		}
		$dbp = new Application_Model_DbTable_DbGlobal();
		$where .= $dbp->getAccessPermission('p.projectId');

		$order = " order by id desc ";
		return $db->fetchAll($sql . $where . $order);
	}

	public function getAllCateExpense($type)
	{
		$db = $this->getAdapter();
		$sql = "SELECT id ,account_name as name FROM `rms_account_name` WHERE status=1 AND account_name!=''
				and account_type = " . $type;
		return $db->fetchAll($sql);
	}

	function addCateExpense($data)
	{
		$this->_name = "st_cate_expense";
		$arr = array(
			'title' => $data['account_name'],
			'parent' => $data['parent'],
			'accountCode' => $data['account_code'],
			'createDate' => date('Y-m-d'),
			'userId' => $this->getUserId(),
		);
		$id = $this->insert($arr);
		$db = new Application_Model_GlobalClass();
		$new_arrar_cate_expense = $db->getAllExpenseIncomeType(5);
		$result = array(
			'id' => $id,
			'new_array' => $new_arrar_cate_expense,
		);
		return $result;
	}

}