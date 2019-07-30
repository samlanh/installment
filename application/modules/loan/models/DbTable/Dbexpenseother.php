<?php
class Loan_Model_DbTable_DbExpenseother extends Zend_Db_Table_Abstract
{
	protected $_name = 'ln_expense_other';
	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	}
	
	function addExpens($data){
		$_db= $this->getAdapter();
		$_db->beginTransaction();
			try{
				$invoice = $this->getInvoiceNo($data['branch_id']);
				$_arr = array(
					'branch_id'		=>$data['branch_id'],
					'sale_id'		=>$data['sale_client'],
					'house_id'		=>$data['house_id'],
					'client_id'		=>$data['customer'],
					//'title'			=>$data['title'],
					'total_amount'	=>$data['total_amount'],
					'payment_method'=>$data['payment_method'],
					'invoice'		=>$invoice,
					'category_id'	=>$data['income_category'],
					'cheque'		=>$data['cheque'],
					'description'	=>$data['Description'],
					'date'			=>$data['Date'],
					'user_id'		=>$this->getUserId(),
					'create_date'	=>date('Y-m-d'),
					//'is_beginning'=>$data['is_beginning'],
					);
				$expend_id = $this->insert($_arr);
				
				$ids = explode(',', $data['identity']);
				$this->_name='ln_expense_detail';
				foreach ($ids as $j){
					$arr = array(
							'expense_id'	=>$expend_id,
							'service_id'	=>$data['description_'.$j],
							'description'	=>$data['remark_'.$j],
							'price'			=>$data['price_'.$j],
							'qty'			=>$data['qty_'.$j],
							'total'			=>$data['total_'.$j],
					);
					$this->insert($arr);
				}
				$part= PUBLIC_PATH.'/images/document/';
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
							$image_name = "document_".date("Y").date("m").date("d").time().$i.".".end($ss);
							$tmp = $_FILES['attachment'.$i]['tmp_name'];
							if(move_uploaded_file($tmp, $part.$image_name)){
								$photo = $image_name;
								$arr = array(
										'client_id'=>$expend_id,
										'document_name'=>$photo,
										'type'=>2,
								);
								$this->_name = "ln_client_document";
								$this->insert($arr);
							}
							else
								$string = "Image Upload failed";
							//     				}
						}
					}
				}
				$_db->commit();
				}catch(Exception $e){
					echo $e->getMessage();exit();
					Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				}
 	 }
 function updateExpense($data,$id){
 	$_db= $this->getAdapter();
 	$_db->beginTransaction();
	 	try{
		$arr = array(
					'sale_id'	=>$data['sale_client'],
					'house_id'	=>$data['house_id'],
					'branch_id'	=>$data['branch_id'],
					'client_id'	=>$data['customer'],
					//'title'		=>$data['title'],
					'total_amount'=>$data['total_amount'],
					'payment_method'=>$data['payment_method'],
					'invoice'	=>$data['invoice'],
					'category_id'=>$data['income_category'],
					'cheque'	=>$data['cheque'],
					'description'=>$data['Description'],
					'date'		=>$data['Date'],
 					'status'	=>$data['Stutas'],
// 				   'is_beginning'=>$data['is_beginning'],
					'user_id'	=>$this->getUserId(),
				);
				$where=" id = ".$id;
				$this->update($arr, $where);
				
				$this->_name='ln_expense_detail';
				$where = "expense_id = ".$id;
				$this->delete($where);
				$ids = explode(',', $data['identity']);
				foreach ($ids as $j){
					$arr = array(
							'expense_id'	=>$id,
							'service_id'	=>$data['description_'.$j],
							'description'	=>$data['remark_'.$j],
							'price'			=>$data['price_'.$j],
							'qty'			=>$data['qty_'.$j],
							'total'			=>$data['total_'.$j],);
					$this->insert($arr);
				}
				$part= PUBLIC_PATH.'/images/document/';
				if (!file_exists($part)) {
					mkdir($part, 0777, true);
				}
				if (!empty($data['identity1'])){
					$identity = $data['identity1'];
					$ids = explode(',', $identity);
					$detailId="";
					foreach ($ids as $i){
						if (empty($detailId)){
							if (!empty($data['detailid'.$i])){
								$detailId = $data['detailid'.$i];
							}
						}else{
							if (!empty($data['detailid'.$i])){
								$detailId= $detailId.",".$data['detailid'.$i];
							}
						}
					}
					$this->_name = "ln_client_document";
					$where1 =" client_id=".$id;
					if (!empty($detailId)){
						$where1.=" AND id NOT IN ($detailId) ";
					}
					$this->delete($where1);
						
					$image_name="";
					$photo="";
						
					foreach ($ids as $i){
						if (!empty($data['detailid'.$i])){
							$name = $_FILES['attachment'.$i]['name'];
							if (!empty($name)){
								$ss = 	explode(".", $name);
								$image_name = "document_".date("Y").date("m").date("d").time().$i.".".end($ss);
								$tmp = $_FILES['attachment'.$i]['tmp_name'];
								if(move_uploaded_file($tmp, $part.$image_name)){
									$photo = $image_name;
									$arr = array(
											'client_id'=>$id,
											'document_name'=>$photo,
											'type'=>2,
									);
									$this->_name = "ln_client_document";
									$where=" id=".$data['detailid'.$i];
									$this->update($arr, $where);
								}
								else
									$string = "Image Upload failed";
								//     				}
							}
						}else{
							$name = $_FILES['attachment'.$i]['name'];
							if (!empty($name)){
								$ss = 	explode(".", $name);
								$image_name = "document_".date("Y").date("m").date("d").time().$i.".".end($ss);
								$tmp = $_FILES['attachment'.$i]['tmp_name'];
								if(move_uploaded_file($tmp, $part.$image_name)){
									$photo = $image_name;
									$arr = array(
											'client_id'=>$id,
											'document_name'=>$photo,
											'type'=>2,
									);
									$this->_name = "ln_client_document";
									$this->insert($arr);
								}
								else
									$string = "Image Upload failed";
								//     				}
							}
						}
					}
				}else{
					$this->_name = "ln_client_document";
					$where1 =" client_id=".$id;
					$this->delete($where1);
				}
				$_db->commit();
				}catch(Exception $e){
					echo $e->getMessage();exit();
					Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				}
	}
	function getexpensebyid($id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM ln_expense_other where id=$id ";
		return $db->fetchRow($sql);
	}
	function getexpenseDetailbyid($id){
		$db = $this->getAdapter();
		$sql="SELECT * FROM ln_expense_detail WHERE expense_id=".$id;
		return $db->fetchAll($sql);
	}
	function getAllExpense($search=null){
		$db = $this->getAdapter();
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
		$where = " WHERE ".$from_date." AND ".$to_date;
		
		$sql=" SELECT id,
		(SELECT project_name FROM `ln_project` WHERE ln_project.br_id =branch_id LIMIT 1) AS branch_name,
		(SELECT name_kh FROM `ln_client` WHERE ln_client.client_id =ln_expense_other.client_id LIMIT 1) AS client_name,
		(SELECT CONCAT(land_address,',',street) FROM `ln_properties` WHERE id=house_id LIMIT 1) AS house_no,
		(SELECT name_kh FROM ln_view WHERE type=2 and key_code=payment_method limit 1) AS payment_method,
		invoice,
		(SELECT name_kh FROM `ln_view` WHERE TYPE=12 AND key_code=category_id LIMIT 1) AS category_name,
		total_amount,description,DATE,
		(SELECT  first_name FROM rms_users WHERE id=user_id LIMIT 1 ) AS user_name,
		status FROM ln_expense_other";
		
		if (!empty($search['adv_search'])){
				$s_where = array();
				$s_search = trim(addslashes($search['adv_search']));
				$s_where[] = " description LIKE '%{$s_search}%'";
				$s_where[] = " title LIKE '%{$s_search}%'";
				$s_where[] = " total_amount LIKE '%{$s_search}%'";
				$s_where[] = " invoice LIKE '%{$s_search}%'";
				$where .=' AND ('.implode(' OR ',$s_where).')';
			}
// 			if($search['client_name']>0){
// 				$where.= " AND ln_income.client_id = ".$search['client_name'];
// 			}
// 			if($search['land_id']>0){
// 				$where.= " AND ln_income.house_id = ".$search['land_id'];
// 			}
			if(!empty($search['category_id'])){
				$where.= " AND category_id = ".$search['category_id'];
			}
			if($search['branch_id']>-0){
				$where.= " AND branch_id = ".$search['branch_id'];
			}
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
		
		$prefix ="";// $this->getPrefixCodeByBranch($branch_id);
		
		$sql = " select count(id) from ln_income where branch_id = $branch_id";
		$amount = $db->fetchOne($sql);
		$pre = 'inc1:';
		$result = $amount + 1;
		$length = strlen((int)$result);
		for($i = $length;$i < 3 ; $i++){
			$pre.='0';
		}
		return $prefix.$pre.$result;
	}

	function getAllExpenseCategory($type=12,$parent = 0, $spacing = '', $cate_tree_array = ''){
		if (!is_array($cate_tree_array))
			$cate_tree_array = array();
		$db = $this->getAdapter();
		$sql = " select key_code as id,name_kh as name from ln_view where type=$type AND name_kh!='' AND `parent_id` = $parent ";
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
		$sql = " SELECT key_code AS id,name_kh as name FROM ln_view where type=$type AND name_kh!='' AND `parent_id` = $parent ";
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
		$sql="SELECT COUNT(id) FROM ln_view WHERE type = $type ORDER BY key_code DESC LIMIT 1";
		$result = $db->fetchOne($sql);
		$key_code = $result + 1;
		return $key_code;
	}
	
	function AddNewCategory($data,$type){ // type=1 => income , type=2 => expense
		$db = $this->getAdapter();
		if($type==1){
			$type=12;
		}else{
			$type=13;
		}
		$key_code = $this->getNewKeyCode($type);
		
		$this->_name = "ln_view" ;
		$array = array(
				'name_kh'	=>$data['cate_name'],
				'type'		=>$type,
				'key_code'	=>$key_code,
				'status'	=>$data['status_j'],
				);
		$this->insert($array);
		return $key_code; // to set key_code value to added new field
	}
	
	function getAllCustomer($branch_id){
		$db = $this->getAdapter();
		$sql="SELECT client_id as id,name_kh as name FROM ln_client WHERE status = 1 and branch_id = $branch_id";
		return $db->fetchAll($sql);
	}
	
	
}







