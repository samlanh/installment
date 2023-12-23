<?php
class Incexp_Model_DbTable_DbInvExpense extends Zend_Db_Table_Abstract
{
	protected $_name = 'ln_invexpense';
	
	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}
	
	public function getBranchId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->branch_id;
	}
	
	function addInvExpense($data){
		$_db= $this->getAdapter();
		$_db->beginTransaction();
		try{
			
			$dataRss = array(
				'branch_id'			=> $data['branch_id'],
				'title'				=> $data['title'],
				'total_amount'		=> $data['total_amount'],
				'total_amount_after'	=> $data['total_amount'],
				
				'other_invoice'	=> $data['other_invoice'],
				'category_id'	=> $data['income_category'],
				'description'	=> $data['Description'],
				'date'			=> $data['Date'],
				'status'		=> 1,
				'supplier_id'	=> $data['supplier_id'],
				'user_id'		=> $this->getUserId(),
				'create_date'   => date('Y-m-d'),
			);
			$this->_name="ln_invexpense";
			$invExpsenseId  = $this->insert($dataRss);
			
			if (!empty($data['identity'])){
				$ids = explode(',', $data['identity']);
				foreach ($ids as $i){
					$this->_name='ln_expense_detail';
						$_arr = array(
								'expense_id'=>$invExpsenseId,
								'pro_id'=>$data['product_name_'.$i],
								'qty'	=>$data['qty_'.$i],
								'cost'	=>$data['cost_'.$i],
								'date'	=>date("Y-m-d"),
								'amount'=>$data['amount_'.$i],
								'note'	=>$data['note_'.$i],
						);
						$this->insert($_arr);
				}
			}				
			$_db->commit();
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
 	}
 	function updatInvExpense($data){
 		$_db= $this->getAdapter();
 		$_db->beginTransaction();
 		try{
			$arr = array(
			
				'branch_id'			=> $data['branch_id'],
				'title'				=> $data['title'],
				'total_amount'		=> $data['total_amount'],
				'total_amount_after'	=> $data['total_amount'],
				
				'other_invoice'	=> $data['other_invoice'],
				'category_id'	=> $data['income_category'],
				'description'	=> $data['Description'],
				'date'			=> $data['Date'],
				'status'		=> $data['Stutas'],
				'supplier_id'	=> $data['supplier_id'],
				'user_id'		=> $this->getUserId(),
			);
			$where=" id = ".$data['id'];
			$this->_name="ln_invexpense";
			$this->update($arr, $where);
			
			$invExpsenseId =$data['id'];
			$ids = explode(',', $data['identity']);
			$detailid="";
			if (!empty($ids)){
				foreach ($ids as $i){
					$data['detailidItem'.$i] = empty($data['detailidItem'.$i])?"":$data['detailidItem'.$i];
					if (empty($detailid)){
						$detailid = $data['detailidItem'.$i];
					}else{
						if (!empty($data['detailidItem'.$i])){
							$detailid = $detailid.",".$data['detailidItem'.$i];
						}
					}
				}
			}
			$whereDetail=" expense_id =".$invExpsenseId;
			if(!empty($detailid)){
				$whereDetail.=" AND id NOT IN ($detailid)";
			}
			$this->_name='ln_expense_detail';
			$this->delete($whereDetail);
			
			if (!empty($data['identity'])){
				$ids = explode(',', $data['identity']);
				if (!empty($ids)){
					foreach ($ids as $i){
						if (!empty($data['detailidItem'.$i])){
							$_arr = array(
									'expense_id'	=>$invExpsenseId,
									'pro_id'		=>$data['product_name_'.$i],
									'qty'			=>$data['qty_'.$i],
									'cost'			=>$data['cost_'.$i],
									'date'			=>date("Y-m-d"),
									'amount'		=>$data['amount_'.$i],
									'note'			=>$data['note_'.$i],
							);
							$wheresee=" id = ".$data['detailidItem'.$i];
							$this->_name='ln_expense_detail';
							$this->update($_arr, $wheresee);

						}else{
							$_arr = array(
									'expense_id'	=>$invExpsenseId,
									'pro_id'		=>$data['product_name_'.$i],
									'qty'			=>$data['qty_'.$i],
									'cost'			=>$data['cost_'.$i],
									'date'			=>date("Y-m-d"),
									'amount'		=>$data['amount_'.$i],
									'note'			=>$data['note_'.$i],
							);
							$this->_name='ln_expense_detail';
							$this->insert($_arr);
						}
					}
				}
			}
			$_db->commit();
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	
	function getInvExpenseById($id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM ln_invexpense where id=$id ";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission("branch_id");
		return $db->fetchRow($sql);
	}

	function getAllInvExpensee($search=null){
		$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
		
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
		$where = " WHERE ".$from_date." AND ".$to_date;
		
		$sql=" SELECT 
				id
				,(SELECT project_name FROM `ln_project` WHERE ln_project.br_id =branch_id LIMIT 1) AS branch_name
				,(SELECT sup.name FROM `ln_supplier` AS sup WHERE sup.id = supplier_id LIMIT 1) AS supplier
				,title
				,other_invoice
				,(SELECT name_kh FROM `ln_view` WHERE type=13 and key_code=category_id limit 1) AS category_name
				,total_amount
				,date
				,(SELECT  first_name FROM rms_users WHERE id=user_id limit 1 ) AS user_name  
			";
		
		$sql.=$dbp->caseStatusShowImage("status");
		$sql.=" FROM ln_invexpense ";
		
		if (!empty($search['adv_search'])){
				$s_where = array();
				$s_search = trim(addslashes($search['adv_search']));
				$s_where[] = " description LIKE '%{$s_search}%'";
				$s_where[] = " title LIKE '%{$s_search}%'";
				$s_where[] = " total_amount LIKE '%{$s_search}%'";
				$s_where[] = " other_invoice LIKE '%{$s_search}%'";
				$s_where[] = " (SELECT name_kh FROM `ln_view` WHERE type=13 and key_code=category_id limit 1) LIKE '%{$s_search}%'";
				$s_where[] = " (SELECT sup.name FROM `ln_supplier` AS sup WHERE sup.id = supplier_id LIMIT 1) LIKE '%{$s_search}%'";
				$where .=' AND ('.implode(' OR ',$s_where).')';
			}
	
		if($search['branch_id']>0){
			$where.= " AND branch_id = ".$search['branch_id'];
		}
		if($search['category_id_expense']>0){
			$condiction = $dbp->getChildType($search['category_id_expense']);
			if (!empty($condiction)){
				$where.=" AND category_id IN ($condiction)";
			}else{
				$where.=" AND category_id=".$search['category_id_expense'];
			}
		}
		if(!empty($search['supplier_id'])){
			$where.= " AND supplier_id = ".$search['supplier_id'];
		}
		if($search['status']>-1){
			$where.= " AND status = ".$search['status'];
		}
		$where.=$dbp->getAccessPermission("branch_id");
		$order=" order by id desc ";
		return $db->fetchAll($sql.$where.$order);
	}
	
	function getExpenseDetail($id){
    	$db=$this->getAdapter();
    	$sql="SELECT *,
		(SELECT ide.title FROM `rms_product` AS ide WHERE ide.id = pro_id LIMIT 1) AS pro_name
    	FROM ln_expense_detail WHERE expense_id=$id";
    	return $db->fetchAll($sql);
    }
	
	function checkHaspayment($purchase_id){
    	$db = $this->getAdapter();
    	$sql="SELECT * FROM `rms_expense_payment_detail` AS pr WHERE pr.`purchase_id`=$purchase_id
    	AND (SELECT p.`status` FROM `rms_expense_payment` AS p WHERE p.`id` = pr.`payment_id` LIMIT 1) =1 LIMIT 1";
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission('(SELECT p.`branch_id` FROM `rms_expense_payment` AS p WHERE p.`id` = pr.`payment_id` LIMIT 1)');
    	return $db->fetchRow($sql);
    }
}