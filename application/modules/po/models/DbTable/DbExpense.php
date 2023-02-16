<?php
class Po_Model_DbTable_DbExpense extends Zend_Db_Table_Abstract
{
	protected $_name = 'st_expense';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}
	function addExpense($data){
		$_db= $this->getAdapter();
		$_db->beginTransaction();
		try{
			$_arr = array(
					'projectId'				=>$data['branch_id'],
					'paymentNo'				=>$data['paymentNo'],
					'externalInvoice'	 	=>$data['externalInvoice'],
					'expenseTitle'			=>$data['expenseTitle'],
					'receiver'				=>$data['receiver'],
					'note'		   			=>$data['note'],
					'paymentMethod' 		=>$data['paymentMethod'],
					'bankId'				=>$data['bankId'],
					'accNameAndChequeNo'	=>$data['accNameAndChequeNo'],
					'paymentDate'			=>$data['paymentDate'],
					'totalAmount'			=>$data['totalAmount'],
					'budgetId'				=>$data['budgetItem'],
					'userId'				=>$this->getUserId(),
					'createDate'			=>date('Y-m-d H:i:s'),
				);
			$expend_id = $this->insert($_arr);
			$ids = explode(',', $data['identity']);
			$this->_name='st_expense_detail';
			foreach ($ids as $j){
				$arr = array(

						'expenseId'		=>$expend_id,
						'cateExpenseId'	=>$data['cate_expense_id_'.$j],
						'price'			=>$data['price_'.$j],
						'qty'			=>$data['qty_'.$j],
						'total'			=>$data['total_'.$j],
						'note'	        =>$data['remark_'.$j],
					);
			   $this->insert($arr);
			}
			$_db->commit();
		}catch(Exception $e){
			$_db->rollBack();
			echo $e->getMessage();
		}
 	}
	function updatExpense($data){
	 	$_db= $this->getAdapter();
	 	$_db->beginTransaction();
	 	try{
			$arr = array(	
					'branch_id'		=>$data['branch_id'],
					'title'			=>$data['title'],
					'total_amount'	=>$data['total_amount'],
					'invoice'		=>$data['invoice'],
					'payment_type'	=>$data['payment_method'],
					'description'	=>$data['Description'],
					'receiver'		=>$data['receiver'],
					'cheque_no'		=>$data['cheque_num'],
					'external_invoice'=>$data['external_invoice'],
					'date'			=>$data['Date'],
					'status'		=>$data['Stutas'],
					'user_id'		=>$this->getUserId(),
					//'create_date'=>date('Y-m-d H:i:s'),
				);
			$where=" id = ".$data['id'];
			$this->update($arr, $where);
			
			$this->_name='ln_expense_detail';
			$where = "expense_id = ".$data['id'];
			$this->delete($where);
			$ids = explode(',', $data['identity']);
			foreach ($ids as $j){
				$arr = array(
						'expense_id'	=>$data['id'],
						'service_id'	=>$data['expense_id_'.$j],
						'description'	=>$data['remark_'.$j],
						'price'			=>$data['price_'.$j],
						'qty'			=>$data['qty_'.$j],
						'total'			=>$data['total_'.$j],);
				$this->insert($arr);
			}
			$_db->commit();
		}catch(Exception $e){
			$_db->rollBack();
			echo $e->getMessage();
		}
		//print_r($data); exit();
	}
	function getexpensebyid($id){
		$db = $this->getAdapter();
		$sql="SELECT * FROM st_expense where id=$id ";
		return $db->fetchRow($sql);
	}
	
	function getexpenseDetailbyid($id){
		$db = $this->getAdapter();
		$sql="SELECT * FROM ln_expense_detail WHERE expense_id=".$id;
		return $db->fetchAll($sql);
	}

	function getAllExpense($search=null){
		$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
		
		$sql=" SELECT p.id, 
				(SELECT l.project_name FROM `ln_project` AS l WHERE l.br_id = p.projectId LIMIT 1) AS projectName,
				expenseTitle,
				(SELECT b.budgetTitle FROM `st_budget_item` AS b WHERE b.id = p.budgetId LIMIT 1) AS budgetItem,
				paymentNo, receiver,
				(SELECT vi.name_kh FROM `ln_view` AS vi WHERE vi.type=2 AND vi.key_code=p.`paymentMethod` LIMIT 1) AS paymentMethod,
				(SELECT ba.bank_name FROM `st_bank` AS ba WHERE ba.id=p.`bankId` LIMIT 1) AS bankName,
				accNameAndChequeNo,totalAmount, paymentdate, status				
			";
		$sql.=$dbp->caseStatusShowImage("p.status");
		$sql.=" FROM `st_expense` AS p ";
		
		$from_date =(empty($search['start_date']))? '1': " paymentdate >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " paymentdate <= '".$search['end_date']." 23:59:59'";
		$where = " WHERE ".$from_date." AND ".$to_date;
		
		if (!empty($search['adv_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['adv_search']));
			$s_where[] = " p.expenseTitle LIKE '%{$s_search}%'";
			$s_where[] = " p.paymentNo LIKE '%{$s_search}%'";
			$s_where[] = " p.receiver LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['branch_id'] != -1){
			$where.= " AND p.projectId = ".$search['branch_id'];
		}

    	$where.= $dbp->getAccessPermission('p.projectId');
		// if($search['status']>-1){
		// 	$where.= " AND p.status = ".$search['status'];
		// }
		// if($search['paymentMethod']>-1){
		// 	$where.= " AND payment_type = ".$search['payment_type'];
		// }
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
		account_id,invoice,
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
		
		$order=" order by id desc ";
		return $db->fetchAll($sql.$where.$order);
	}

	public function getAllCateExpense($type){
		$db = $this->getAdapter();
		$sql = "SELECT id ,account_name as name FROM `rms_account_name` WHERE status=1 AND account_name!=''
				and account_type = ".$type;
		return $db->fetchAll($sql);
	}

	function addCateExpense($data){
		$this->_name="st_cate_expense";
		$arr = array(
				'title'    		=>$data['account_name'],
				'parent'		=>$data['parent'],
				'accountCode'	=>$data['account_code'],
				'createDate'	=>date('Y-m-d'), 
				'userId'		=>$this->getUserId(),
		);
		$id = $this->insert($arr);
		$db = new Application_Model_GlobalClass();
		$new_arrar_cate_expense = $db->getAllExpenseIncomeType(5);
		$result = array(
				'id'=>$id,
				'new_array'=>$new_arrar_cate_expense,
			);
		return $result;
	}

}