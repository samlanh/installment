<?php
class Incexp_Model_DbTable_DbExpense extends Zend_Db_Table_Abstract
{
	protected $_name = 'ln_expense';
	
	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}
	
	public function getBranchId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->branch_id;
	}
	
	function addExpense($data){
		$_db= $this->getAdapter();
		$_db->beginTransaction();
		try{
			$invoice = $this->getInvoiceNo($data['branch_id']);
			$cancelSale_id = empty($data['cancelSale_id'])?0:$data['cancelSale_id'];
			$saleId = 0;
			
			if(!empty($cancelSale_id)){
				$dbCancel = new Loan_Model_DbTable_DbCancel();
				$rsCancel=$dbCancel->getCancelById($cancelSale_id);
				if(!empty($rsCancel)){
					$saleId =$rsCancel['sale_id'];
					if (!empty($data['total_amount'])){
						$dueafter = $rsCancel['return_back_aftter']-$data['total_amount'];
						$arrSaleCancel = array(
							'return_back_aftter'	=>$dueafter,
						);
						$whereSaleCancel="id=".$cancelSale_id;
						$this->_name="ln_sale_cancel";
						$this->update($arrSaleCancel, $whereSaleCancel);
					}
				}
									
			}
			
			$dataRss = array(
				'branch_id'		=> $data['branch_id'],
				'title'			=> $data['title'],
				'total_amount'	=> $data['total_amount'],
				'total_amount_after'	=> $data['total_amount'],//new 2021-6-02
				'invoice'		=> $invoice,
				'cheque'		=> $data['cheque'],
				'cheque_issuer'	=> $data['cheque_issuer'],
				'other_invoice'	=> $data['other_invoice'],
	            'payment_id'	=> $data['payment_type'],
				'bank_id'		=> $data['bank_id'],
				'category_id'	=> $data['income_category'],
				'description'	=> $data['Description'],
				'date'			=> $data['Date'],
				'status'		=> 1,
				'supplier_id'	=> $data['supplier_id'],
				'user_id'		=> $this->getUserId(),
				'create_date'   => date('Y-m-d'),
				
				'cancelSale_id' =>$cancelSale_id,
				'sale_id'   	=>$saleId,
				'expenseType'	=> $data['expenseType'],
			);
			$this->_name="ln_expense";
			$expense_id  = $this->insert($dataRss);
			
			
			
			$part= PUBLIC_PATH.'/images/document/expense/';
			if (!file_exists($part)) {
				mkdir($part, 0777, true);
			}
			if (!empty($data['identity1'])){
				$identity = $data['identity1'];
				$ids = explode(',', $identity);
				$image_name="";
				$photo="";
				foreach ($ids as $i){
					$name = $_FILES['attachment'.$i]['name'];
					if (!empty($name)){
						$ss = 	explode(".", $name);
						$image_name = "document_exp_".date("Y").date("m").date("d").time().$i.".".end($ss);
						$tmp = $_FILES['attachment'.$i]['tmp_name'];
						if(move_uploaded_file($tmp, $part.$image_name)){
							$photo = $image_name;
							$arr = array(
									'exspense_id'=>$expense_id ,
									'document_name'=>$photo,
									'title'=>$data['title_'.$i],
									'date'   => date('Y-m-d H:i:s'),
							);
							$this->_name = "ln_expense_document";
							$this->insert($arr);
						}
						else
							$string = "Image Upload failed";
						//     				}
					}
				}
			}
			if (!empty($data['identity'])){
				$ids = explode(',', $data['identity']);
				foreach ($ids as $i){
					$this->_name='ln_expense_detail';
						$_arr = array(
								'expense_id'=>$expense_id,
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
 	function updatExpense($data){
 		$_db= $this->getAdapter();
 		$_db->beginTransaction();
 		try{
			$oldData = $this->getexpensebyid($data['id']);
			$cancelSale_id = empty($data['cancelSale_id'])?0:$data['cancelSale_id'];
			$saleId = 0;
			if(!empty($cancelSale_id)){
				$dbCancel = new Loan_Model_DbTable_DbCancel();
				$rsCancel=$dbCancel->getCancelById($cancelSale_id);
				
				$oldTotalAmount = $oldData['total_amount'];
				if($oldData['status']==0){
					$oldTotalAmount =0;
				}
				if(!empty($rsCancel)){
					$saleId =$rsCancel['sale_id'];
					
					$totalAfter = $rsCancel['return_back_aftter']+$oldTotalAmount;//sum back Total after
					if($data['Stutas']==0){
						$data['total_amount']=0;
					}
					$dueafter = $totalAfter-$data['total_amount'];
					$arrSaleCancel = array(
						'return_back_aftter'	=>$dueafter,
					);
					$whereSaleCancel="id=".$cancelSale_id;
					$this->_name="ln_sale_cancel";
					$this->update($arrSaleCancel, $whereSaleCancel);
				
				}
								
			}
			
			$arr = array(
				'branch_id'		=> $data['branch_id'],
				'title'			=> $data['title'],
				'total_amount'	=> $data['total_amount'],
				'total_amount_after'	=> $data['total_amount'],//new 2021-6-02
				'payment_id'	=> $data['payment_type'],
				'bank_id'		=> $data['bank_id'],
				'cheque'		=> $data['cheque'],
				'cheque_issuer'	=> $data['cheque_issuer'],
				'other_invoice'	=> $data['other_invoice'],
				'category_id'	=> $data['income_category'],
				'description'	=> $data['Description'],
				'date'			=> $data['Date'],
				'status'		=> $data['Stutas'],
				'supplier_id'	=> $data['supplier_id'],
				'user_id'		=> $this->getUserId(),	
				
				'cancelSale_id'   =>$cancelSale_id,
				'sale_id'   	=>$saleId,
				
				'expenseType'	=> $data['expenseType'],
			);
			$where=" id = ".$data['id'];
			$this->_name="ln_expense";
			$this->update($arr, $where);
			
			
			
			
			$expense_id =$data['id'];
			$part= PUBLIC_PATH.'/images/document/expense/';
			if (!file_exists($part)) {
				mkdir($part, 0777, true);
			}
			
			$identity = $data['identity1'];
			$ids = explode(',', $identity);
			if (!empty($ids)){
				$detailidlist="";
				$this->_name='ln_expense_document';
				foreach ($ids as $i){
					$data['detailid'.$i] = empty($data['detailid'.$i])?"":$data['detailid'.$i];
					if (empty($detailidlist)){
	    				if (!empty($data['detailid'.$i])){
	    					$detailidlist= $data['detailid'.$i];
	    				}
	    			}else{
	    				if (!empty($data['detailid'.$i])){
	    					$detailidlist = $detailidlist.",".$data['detailid'.$i];
	    				}
	    			}
				}
				$where = " exspense_id = ".$expense_id;
				if (!empty($detailidlist)){ // check if has old payment detail  detail id
					$where.=" AND id NOT IN (".$detailidlist.")";
				}
				$this->delete($where);
			}
				
			if (!empty($data['identity1'])){
				
						
				$this->_name = "ln_expense_document";
				$image_name="";
				$photo="";
				foreach ($ids as $i){
					if (!empty($data['detailid'.$i])){
						$name = $_FILES['attachment'.$i]['name'];
						if (!empty($name)){
							
							$ss = 	explode(".", $name);
							$image_name = "document_exp_".date("Y").date("m").date("d").time().$i.".".end($ss);
							$tmp = $_FILES['attachment'.$i]['tmp_name'];
							if(move_uploaded_file($tmp, $part.$image_name)){
								$photo = $image_name;
								$arr = array(
										'exspense_id'=>$expense_id,
										'document_name'=>$photo,
										'title'=>$data['title_'.$i],
										'date'   => date('Y-m-d H:i:s'),
								);
								$where=" id=".$data['detailid'.$i];
								$this->update($arr, $where);
							}else{
								$string = "Image Upload failed";
							}
							
							
						}else{
							$photo = $data['old_file'.$i];
							$arr = array(
									'exspense_id'=>$expense_id,
									'document_name'=>$photo,
									'title'=>$data['title_'.$i],
									'date'   => date('Y-m-d H:i:s'),
							);
							$where=" id=".$data['detailid'.$i];
							$this->update($arr, $where);
						}
					}else{
						$name = $_FILES['attachment'.$i]['name'];
						if (!empty($name)){
							$ss = 	explode(".", $name);
							$image_name = "document_exp_".date("Y").date("m").date("d").time().$i.".".end($ss);
							$tmp = $_FILES['attachment'.$i]['tmp_name'];
							if(move_uploaded_file($tmp, $part.$image_name)){
								$photo = $image_name;
								$arr = array(
										'exspense_id'=>$expense_id,
										'document_name'=>$photo,
										'title'=>$data['title_'.$i],
										'date'   => date('Y-m-d H:i:s'),
								);
								$this->_name = "ln_expense_document";
								$this->insert($arr);
							}
							else
								$string = "Image Upload failed";
							//     				}
						}
					}
				}
			}
			
			
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
			$whereDetail=" expense_id =".$data['id'];
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
									'expense_id'	=>$data['id'],
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
									'expense_id'	=>$data['id'],
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
	
	function getexpensebyid($id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM ln_expense where id=$id ";
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission("branch_id");
		return $db->fetchRow($sql);
	}

	function getAllExpense($search=null){
		$db = $this->getAdapter();
		$dbp = new Application_Model_DbTable_DbGlobal();
		
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
		$where = " WHERE ".$from_date." AND ".$to_date;
		
		$sql=" SELECT id,
				(SELECT project_name FROM `ln_project` WHERE ln_project.br_id =branch_id LIMIT 1) AS branch_name,
				(SELECT sup.name FROM `ln_supplier` AS sup WHERE sup.id = supplier_id LIMIT 1) AS supplier,
				title,invoice,
				(SELECT name_kh FROM `ln_view` WHERE type=2 and key_code=payment_id limit 1) AS payment_type,
				(SELECT name_kh FROM `ln_view` WHERE type=13 and key_code=category_id limit 1) AS category_name,
				total_amount,description,date,cheque_issuer,
				(SELECT  first_name FROM rms_users WHERE id=user_id limit 1 ) AS user_name  ";
		
		$sql.=$dbp->caseStatusShowImage("status");
		$sql.=" FROM ln_expense ";
		
		if (!empty($search['adv_search'])){
				$s_where = array();
				$s_search = trim(addslashes($search['adv_search']));
				$s_where[] = " description LIKE '%{$s_search}%'";
				$s_where[] = " title LIKE '%{$s_search}%'";
				$s_where[] = " total_amount LIKE '%{$s_search}%'";
				$s_where[] = " invoice LIKE '%{$s_search}%'";
				$s_where[] = " other_invoice LIKE '%{$s_search}%'";
				$s_where[] = " (SELECT name_kh FROM `ln_view` WHERE type=13 and key_code=category_id limit 1) LIKE '%{$s_search}%'";
				$s_where[] = " (SELECT sup.name FROM `ln_supplier` AS sup WHERE sup.id = supplier_id LIMIT 1) LIKE '%{$s_search}%'";
				$where .=' AND ('.implode(' OR ',$s_where).')';
			}
	
		if($search['branch_id']>0){
			$where.= " AND branch_id = ".$search['branch_id'];
		}
// 		if($search['category_id_expense']>0){
// 			$where.= " AND category_id = ".$search['category_id_expense'];
// 		}
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
		if($search['payment_type']>0){
			$where.= " AND payment_id = ".$search['payment_type'];
		}
		if (!empty($search['cheque_issuer_search'])){
			$where.= " AND cheque_issuer = '".$search['cheque_issuer_search']."'";
		}
		if($search['status']>-1){
			$where.= " AND status = ".$search['status'];
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
	
	function getAllExpenseCategory($parent = 0, $spacing = '', $cate_tree_array = ''){
		if (!is_array($cate_tree_array))
			$cate_tree_array = array();
		$db = $this->getAdapter();
		$sql = " select key_code as id,name_kh as name from ln_view where type=13 AND name_kh!='' AND `parent_id` = $parent AND status=1 ";
		$query =  $db->fetchAll($sql);
		
		$rowCount = count($query);
		if ($rowCount > 0) {
			foreach ($query as $row){
				$cate_tree_array[] = array("id" => $row['id'], "name" => $spacing . $row['name']);
				$cate_tree_array = $this->getAllExpenseCategory($row['id'], $spacing . ' - ', $cate_tree_array);
			}
		}
		return $cate_tree_array;
	}
	
	function getPrefixCodeByBranch($branch_id){
	
		$db = $this->getAdapter();
		$sql="select prefix from ln_project where status = 1 and br_id = $branch_id limit 1";
		return $db->fetchOne($sql);
	
	}
	
	function getInvoiceNo($branch_id){
		$db = $this->getAdapter();
		
		$prefix = $this->getPrefixCodeByBranch($branch_id);
		
		$sql = " select count(id) from ln_expense where branch_id = $branch_id";
		$amount = $db->fetchOne($sql);
		
		$sql1 = " select count(id) from ln_comission where branch_id = $branch_id";
		$amount1 = $db->fetchOne($sql1);
		
		$sql3 = " select count(id) from ln_otherincomepayment where branch_id = $branch_id AND cate_type=13 ";
		$amount2 = $db->fetchOne($sql3);
		
		$sql4 = " select count(id) from rms_commission_payment where branch_id = $branch_id ";
		$amount4 = $db->fetchOne($sql4);
		
		$amount = $amount+$amount1+$amount2+$amount4;
		$pre = 'inc1:';
		$result = $amount + 1;
		$length = strlen((int)$result);
		for($i = $length;$i < 3 ; $i++){
			$pre.='0';
		}
		return $pre.$result;
	}
	function getAllChequeIssue(){
		$db = $this->getAdapter();
		$sql = " SELECT DISTINCT cheque_issuer as name,cheque_issuer as id FROM `ln_expense` WHERE cheque_issuer!='' ORDER BY cheque_issuer ASC ";
		return $db->fetchAll($sql);
	}
	
	function getExpenseDocumentbyid($id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM ln_expense_document WHERE exspense_id=$id AND documentforType = 1 ";
		return $db->fetchAll($sql);
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