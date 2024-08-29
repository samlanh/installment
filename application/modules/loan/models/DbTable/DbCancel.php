<?php

class Loan_Model_DbTable_DbCancel extends Zend_Db_Table_Abstract
{	protected $_name = 'ln_sale_cancel';
	
	public function getUserId(){
		$session_user=new Zend_Session_Namespace(SYSTEM_SES);
		return $session_user->user_id;
	
	}
	public function getCientAndPropertyInfo($sale_id){
		$db = $this->getAdapter();
		$sql="
			SELECT 
				s.`id`
				,s.`sale_number` AS `name`
				,c.`client_number`
				,c.`name_en`
				,c.`name_kh`
				,s.`price_before`,s.`price_sold`
				,s.`paid_amount`
				,((SELECT SUM(COALESCE(total_principal_permonthpaid,0)+COALESCE(extra_payment,0)) FROM `ln_client_receipt_money` WHERE sale_id=$sale_id AND status=1 LIMIT 1) + ((SELECT COALESCE(SUM(crd.total_amount),0) FROM `ln_credit` AS crd WHERE crd.status=1 AND crd.sale_id = s.id LIMIT 1)) ) AS total_principal
				,(SELECT COUNT(id) FROM `ln_client_receipt_money` WHERE status=1 AND is_completed=1 AND sale_id=$sale_id LIMIT 1) as installment_paid
				,s.`balance`
				,s.`discount_amount`
				,s.`other_fee`
				,s.`payment_id`
				,s.`graice_period`
				,s.`total_duration`
				,s.`buy_date`
				,s.`end_line`
				,s.`client_id`
				,s.`house_id`
				,p.`id` as property_id
				,p.`land_code`
				,p.`land_address`
				,p.hardtitle
				,p.`land_size`
				,p.`width`
				,p.`height`
				,p.`street`
				,p.`land_price`
				,p.`house_price`
				,p.`street`
				,(SELECT t.type_nameen FROM `ln_properties_type` AS t WHERE t.id = p.`property_type` LIMIT 1) AS pro_type
				,COALESCE((SELECT t.serviceFee FROM `ln_properties_type` AS t WHERE t.id = p.`property_type` LIMIT 1),0) AS serviceFee
				,s.staff_id
				,s.`comission`
				,s.full_commission
				,((SELECT COALESCE(SUM(total_amount),0) FROM `ln_comission` WHERE sale_id=s.id AND status=1 LIMIT 1) + (SELECT COALESCE(SUM(cpd.payment_amount),0) FROM `rms_commission_payment_detail` as cpd, rms_commission_payment AS cp WHERE cp.id = cpd.payment_id AND cpd.sale_id=s.id AND cp.status=1 LIMIT 1)) AS comission_paid
				,(SELECT category_id FROM `ln_comission` WHERE sale_id=s.id AND status=1 LIMIT 1) as category_id
				
				,s.`create_date`
				,s.`note` AS sale_note
			FROM 
				`ln_sale` AS s 
				,`ln_client` AS c
				,`ln_properties` AS p
			WHERE c.`client_id` = s.`client_id` AND p.`id`=s.`house_id` AND s.id =".$sale_id;
		return $db->fetchRow($sql);
	}
	public function getCancelSale($search=null){
		$db = $this->getAdapter();
		$from_date =(empty($search['from_date_search']))? '1': "c.`create_date` >= '".$search['from_date_search']." 00:00:00'";
		$to_date = (empty($search['to_date_search']))? '1': "c.`create_date` <= '".$search['to_date_search']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		$sql ='SELECT c.`id`,
		    p.`project_name`,
			clie.`name_kh` AS client_name,
			(SELECT protype.type_nameen FROM `ln_properties_type` AS protype WHERE protype.id = pro.`property_type` LIMIT 1) AS property_type,
			pro.`land_address`,pro.`street`,
			s.price_sold,c.installment_paid,c.paid_amount,c.return_back,c.`create_date`,
		    (SELECT  first_name FROM rms_users WHERE rms_users.id=c.user_id LIMIT 1 ) AS user_name ,
			c.`status`
			FROM `ln_sale_cancel` AS c , `ln_sale` AS s, `ln_project` AS p,`ln_properties` AS pro,
			`ln_client` AS clie
			WHERE s.`id` = c.`sale_id` AND p.`br_id` = c.`branch_id` AND pro.`id` = c.`property_id` AND
			clie.`client_id` = s.`client_id`';
		if($search['branch_id_search']>-1){
			$where.= " AND c.branch_id = ".$search['branch_id_search'];
		}
		if(!empty($search['client_name']) AND $search['client_name']>-1){
			$where.= " AND s.`client_id` = ".$search['client_name'];
		}
		if(!empty($search['adv_search'])){
			$s_where = array();
			$s_search = addslashes(trim($search['adv_search']));
			$s_where[] = " clie.`client_number` LIKE '%{$s_search}%'";
			$s_where[] = " clie.`name_kh` LIKE '%{$s_search}%'";
			$s_where[] = " s.`sale_number` LIKE '%{$s_search}%'";
			$s_where[] = " c.`installment_paid` LIKE '%{$s_search}%'";
			$s_where[] = " c.`installment_paid` LIKE '%{$s_search}%'";
			$s_where[] = " p.`project_name` LIKE '%{$s_search}%'";
			$s_where[] = " pro.`land_code` LIKE '%{$s_search}%'";
			$s_where[] = " pro.`land_address` LIKE '%{$s_search}%'";
			$s_where[] = " pro.`street` LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		
		if(!empty($search['land_id']) AND $search['land_id']>-1){
			$where.= " AND (c.`property_id` = ".$search['land_id']." OR pro.old_land_id LIKE '%".$search['land_id']."%')";
		}
		
		$dbp = new Application_Model_DbTable_DbGlobal();
		$where.=$dbp->getAccessPermission("c.`branch_id`");
		
		$where.=" ORDER BY c.`id` DESC ";
		return $db->fetchAll($sql.$where);
	}
	public function addCancelSale($data){
		try{
			$db= $this->getAdapter();
			
			$expenid='';
			/*
			if($data['return_back']>0){
				$dbexpense = new Loan_Model_DbTable_DbExpense();
				$invoice = $dbexpense->getInvoiceNo($data['branch_id']);
				$dbsale = new Loan_Model_DbTable_DbLandpayment();
				$row = $dbsale->getTranLoanByIdWithBranch($data['sale_no'],null);
				$title="ត្រលប់ប្រាក់ទៅអោយអតិថិជនវិញ";
				$arr1 = array(
					'branch_id'		=>$data['branch_id'],
					'title'			=>$title,
					'total_amount'	=>$data['return_back'],
					'invoice'		=>$invoice,
					'category_id'	=>$data['income_category'],
					'date'			=>$data['expense_date'],
					'status'		=>1,
					'description'	=>$data['reason'],
					'user_id'		=>$this->getUserId(),
					'create_date'	=>$data['expense_date'],
					);
				$this->_name="ln_expense";
				$expenid = $this->insert($arr1);
			}
			*/
			
			 $arr = array(
					'branch_id'=>$data['branch_id'],
					'sale_id'=>$data['sale_no'],
					'property_id'=>$data['property_id'],
					'create_date'=>$data['cancel_date'],
					'user_id'=>$this->getUserId(),
					'status'=>1,
					'reason'=>$data['reason'],
					'paid_amount'=>$data['paid_amount'],
					'installment_paid'=>$data['installment_paid'],
					'return_back'=>$data['return_back'],
					'expense_id'=>$expenid,
					
					'cancel_type'=>$data['cancel_type'],
					'condition_return'=>$data['condition_return'],
					'date_for_return'=>$data['expense_date'],
					'return_back_aftter'=>$data['return_back'],
					);
			 $this->_name="ln_sale_cancel";
			 $this->insert($arr);
			 
			 $db = new Loan_Model_DbTable_DbLandpayment();
			 $row = $db->getTranLoanByIdWithBranch($data['sale_no'],null);
			 if(!empty($row)){
			 	if($row['typesale']==2){//multi sale
			 		$ids = explode(',', $row['old_land_id']);
			 		foreach($ids as $land){
			 			$this->_name="ln_properties";
			 			$arr = array(
			 					"is_lock"=>0
			 			);
			 			$where = "id =".$land;
			 			$this->update($arr, $where);
			 		}
			 		
			 		$this->_name="ln_properties";
			 		$arr = array(
			 				"status"=>-1
			 		);
			 		$where = "id =".$data['property_id'];
			 		$this->update($arr, $where);
			 	}
			 }
			 
			 $arr_1 = array(
			 		'is_lock'=>0, //property can sell
			 		);
			 $this->_name="ln_properties";
			 $where1 =" id = ".$data['property_id'];
			 $this->update($arr_1, $where1);
			 
			 $arr_ = array(
			 		'is_cancel'=>1, //sale was cancel
			 		);
			 $this->_name="ln_sale";
			 $where =" id = ".$data['sale_no'];
			 $this->update($arr_, $where);
			 
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	
	function recordhistory($_data){
		$arr=array();
		$stringold="";
		$string="";
		
		$db_pro = new Project_Model_DbTable_DbProject();
		$dbsale = new Loan_Model_DbTable_DbLandpayment();
		$dbclient = new Group_Model_DbTable_DbClient();
		$dbproper = new Project_Model_DbTable_DbLand();
		if (!empty($_data['id'])){
	
			$row=$this->getCancelById($_data['id']);
			$project = $db_pro->getBranchById($row['branch_id']);
			$rowsale = $dbsale->getTranLoanByIdWithBranch($row['sale_id'],null);
			$client = $dbclient->getClientById($rowsale['client_id']);
			$land = $dbproper->getClientById($rowsale['house_id']);
			
			$stringold="Project : ID:".$row['branch_id']."-".$project['project_name']."<br />";
			$stringold.="SALE : ID:".$row['sale_id']."-".$rowsale['sale_number']."<br />";
			$stringold.="Customer : id=".$rowsale['client_id']."-".$client['name_kh']."<br />";
			$stringold.="Property : id=".$rowsale['house_id']."-".$land['land_address']." Street ".$land['street']."<br />";
			
			$stringold.="Reason : ".$row['reason']."<br />";
			$stringold.="Paid Amount : ".$row['paid_amount']."<br />";
			$stringold.="Installment Paid : ".$row['installment_paid']."<br />";
			$stringold.="Return Amount : ".$row['return_back']."<br />";

	
			$project = $db_pro->getBranchById($_data['branch_id']);
			$rowsale = $dbsale->getTranLoanByIdWithBranch($_data['sale_no'],null);
			$client = $dbclient->getClientById($rowsale['client_id']);
			$land = $dbproper->getClientById($rowsale['house_id']);
				
			$string="Project : ID:".$_data['branch_id']."-".$project['project_name']."<br />";
			$string.="SALE : ID:".$_data['sale_no']."-".$rowsale['sale_number']."<br />";
			$string.="Customer : id=".$rowsale['client_id']."-".$client['name_kh']."<br />";
			$string.="Property : id=".$rowsale['house_id']."-".$land['land_address']." Street ".$land['street']."<br />";
				
			$string.="Reason : ".$_data['reason']."<br />";
			$string.="Paid Amount : ".$_data['paid_amount']."<br />";
			$string.="Installment Paid : ".$_data['installment_paid']."<br />";
			$string.="Return Amount : ".$_data['return_back']."<br />";
			
			$labelactivity="Edit Cancel ";
		}else{
			$string="";
			
			$project = $db_pro->getBranchById($_data['branch_id']);
			$rowsale = $dbsale->getTranLoanByIdWithBranch($_data['sale_no'],null);
			$client = $dbclient->getClientById($rowsale['client_id']);
			$land = $dbproper->getClientById($rowsale['house_id']);
			
			$stringold="Project : ID:".$_data['branch_id']."-".$project['project_name']."<br />";
			$stringold.="SALE : ID:".$_data['sale_no']."-".$rowsale['sale_number']."<br />";
			$stringold.="Customer : id=".$rowsale['client_id']."-".$client['name_kh']."<br />";
			$stringold.="Property : id=".$rowsale['house_id']."-".$land['land_address']." Street ".$land['street']."<br />";
			
			$stringold.="Reason : ".$_data['reason']."<br />";
			$stringold.="Paid Amount : ".$_data['paid_amount']."<br />";
			$stringold.="Installment Paid : ".$_data['installment_paid']."<br />";
			$stringold.="Return Amount : ".$_data['return_back']."<br />";
			
			$labelactivity="Issue Cancel On Sale : ".$rowsale['sale_number']." ".$client['name_kh']."-".$land['land_address']." Street ".$land['street'];
		}
		$arr['activityold']=$stringold;
		$arr['after_edit_info']=$string;
		
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$_datas = array('description'=>$labelactivity,'activityold'=>$stringold,'after_edit_info'=>$string);
		$dbgb->addActivityUser($_datas);
		
		return $arr;
	}
	public function editCancelSale($data){
		try{
			$db= $this->getAdapter();
			
			$result = $this->getCancelById($data['id']);
			$dbsale = new Loan_Model_DbTable_DbLandpayment();
			$row = $dbsale->getTranLoanByIdWithBranch($data['sale_no'],null);
			$title=" ត្រលប់ប្រាក់ទៅអោយអតិថិជនវិញ";
			$expenid='';
			/*
			if(!empty($result['expense_id'])){
				$arr1 = array(
					'branch_id'		=>$data['branch_id'],
					'title'			=>$row['sale_number'].$title,
					'total_amount'	=>$data['return_back'],
					'category_id'	=>$data['income_category'],
					'date'			=>$data['expense_date'],
					'status'		=>1,
					'description'	=>$data['reason'],
					'user_id'		=>$this->getUserId(),
					'category_id'	=>$data['income_category'],
					'create_date'	=>$data['expense_date'],
						);
				$this->_name="ln_expense";
				$where = 'id = '.$result['expense_id'];
				$this->update($arr1,$where);
				$expenid = $result['expense_id'];
			}else{
				if($data['return_back']>0){
					$dbexpense = new Loan_Model_DbTable_DbExpense();
					$invoice = $dbexpense->getInvoiceNo($data['branch_id']);
					$arr1 = array(
						'branch_id'		=>$data['branch_id'],
						'title'			=>$row['sale_number'].$title,
						'total_amount'	=>$data['return_back'],
						'invoice'		=>$invoice,
						'category_id'	=>$data['income_category'],
						'date'			=>$data['expense_date'],
						'status'		=>1,
						'description'	=>$data['reason'],
						'user_id'		=>$this->getUserId(),
						'create_date'	=>$data['expense_date'],
							);
					$this->_name="ln_expense";
					$expenid = $this->insert($arr1);
				}
			}
			*/
			
			$dbLand = new Project_Model_DbTable_DbLand();
			if ($data['sale_no']==$data['old_sale_id']){
				if ($data['status_using']==0){
					$arr_1 = array(
							'is_lock'=>1, //property can't sell
					);
					$this->_name="ln_properties";
					$where1 =" id = ".$data['property_id'];
					$this->update($arr_1, $where1);
					
					$landInfo = $dbLand->getClientById($data['property_id']);
					if (!empty($landInfo['old_land_id'])){
						$arr_1 = array(
								'status'=>-2,
						);
						$this->_name="ln_properties";
						$where1 =" id = ".$data['property_id'];
						$this->update($arr_1, $where1);
						
						$arr_child = array(
								'is_lock'=>1, //property can't sell
						);
						$this->_name="ln_properties";
						$where_child =" id IN (".$landInfo['old_land_id'].")";
						$this->update($arr_child, $where_child);
					}
					
					$arr_ = array(
							'is_cancel'=>0,// sale not cancel
					);
					$this->_name="ln_sale";
					$where =" id = ".$data['sale_no'];
					$this->update($arr_, $where);
				}else{
					$arr_1 = array(
							'is_lock'=>0, //property can sell
					);
					$this->_name="ln_properties";
					$where1 =" id = ".$data['property_id'];
					$this->update($arr_1, $where1);
						
					$landInfo = $dbLand->getClientById($data['property_id']);
					if (!empty($landInfo['old_land_id'])){
						$arr_child = array(
								'is_lock'=>0, //property can sell
						);
						$this->_name="ln_properties";
						$where_child =" id IN (".$landInfo['old_land_id'].")";
						$this->update($arr_child, $where_child);
					}
					
					$arr_ = array(
							'is_cancel'=>1,// sale cancel
					);
					$this->_name="ln_sale";
					$where =" id = ".$data['sale_no'];
					$this->update($arr_, $where);
				}
				$arr = array(
						'branch_id'=>$data['branch_id'],
						'sale_id'=>$data['sale_no'],
						'property_id'=>$data['property_id'],
						'create_date'=>$data['cancel_date'],
						'reason'=>$data['reason'],
						'user_id'=>$this->getUserId(),
						'status'=>$data['status_using'],
						'paid_amount'=>$data['paid_amount'],
						'installment_paid'=>$data['installment_paid'],
						'return_back'=>$data['return_back'],
						'expense_id'=>$expenid,
						
						'cancel_type'=>$data['cancel_type'],
						'condition_return'=>$data['condition_return'],
						'date_for_return'=>$data['expense_date'],
						'return_back_aftter'=>$data['return_back'],
					
				);
				$this->_name="ln_sale_cancel";
				$where ="id = ".$data['id'];
				$this->update($arr, $where);
			}else{
				$arr_1 = array(
						'is_lock'=>0, //property can sell
				);
				$this->_name="ln_properties";
				$where1 =" id = ".$data['property_id'];
				$this->update($arr_1, $where1);
				
				$arr_ = array(
						'is_cancel'=>1, //sale was cancel 
				);
				$this->_name="ln_sale";
				$where =" id = ".$data['sale_no'];
				$this->update($arr_, $where);
				
				$arr = array(
						'branch_id'=>$data['branch_id'],
						'sale_id'=>$data['sale_no'],
						'property_id'=>$data['property_id'],
						'create_date'=>$data['cancel_date'],
						'user_id'=>$this->getUserId(),
						'status'=>$data['status_using'],
						'paid_amount'=>$data['paid_amount'],
						'installment_paid'=>$data['installment_paid'],
						'return_back'=>$data['return_back'],
						'expense_id'=>$expenid,
						
						'cancel_type'=>$data['cancel_type'],
						'condition_return'=>$data['condition_return'],
						'date_for_return'=>$data['expense_date'],
						'return_back_aftter'=>$data['return_back'],
					
				);
				$this->_name="ln_sale_cancel";
				$where ="id = ".$data['id'];
				$this->update($arr, $where);
				
				$arr_1old = array(//old property update
						'is_lock'=>1,
				);
				$this->_name="ln_properties";
				$where1 =" id = ".$data['old_property_id'];
				$this->update($arr_1old, $where1);
				
				$arr_old = array( //old sale update
						'is_cancel'=>0,
				);
				$this->_name="ln_sale";
				$where =" id = ".$data['old_sale_id'];
				$this->update($arr_old, $where);
			}
			
		}catch(Exception $e){
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
	}
	public function getCancelById($id){
		$db = $this->getAdapter();
		$sql= "SELECT *,
		(SELECT create_date FROM ln_expense WHERE ln_expense.id=c.expense_id LIMIT 1) as expense_date,
		(SELECT e.invoice FROM `ln_expense` AS e WHERE e.id = c.expense_id LIMIT  1) AS reciept
		 FROM `ln_sale_cancel` AS c WHERE c.`id`=".$id;
		$dbp = new Application_Model_DbTable_DbGlobal();
		$sql.=$dbp->getAccessPermission("`c`.`branch_id`");
		$sql.=" LIMIT 1 ";
		return $db->fetchRow($sql);
	}
	
	public function getSaleNoByProject($branch_id,$sale_id,$issue_plong=0,$is_completed=0,$is_comission=0){
		$db = $this->getAdapter();
		$sale='';
		if(!empty($sale_id)){
			$sale=' OR s.`id`= '.$sale_id;
		}
		$sql="SELECT *, s.`id`,
		CONCAT((SELECT c.name_kh FROM `ln_client` AS c WHERE c.client_id = s.`client_id` LIMIT 1),' (',
		(SELECT COALESCE(land_address,',',street) FROM `ln_properties` WHERE id=s.`house_id` LIMIT 1),')' ) AS `name`
		FROM `ln_sale` AS s
		WHERE (s.`is_cancel` =0 ".$sale." ) AND s.`branch_id` =".$branch_id;
		if($is_completed==0){//get all sale completed 
			$sql.=" AND  s.`is_completed` =0 ";
		}
		if($issue_plong>0){
			$sql.=" AND is_issueplong = ".$issue_plong;
		}
		if($is_comission==1){
			$sql.=" AND s.full_commission >0 ";
		}
		//AND s.full_commission > (SELECT SUM(c.total_amount) FROM ln_comission AS c WHERE c.sale_id=s.id LIMIT 1)
		return $db->fetchAll($sql);
	}
	
	function getCancelSaleReturnBack($data){
		$db = $this->getAdapter();
		$sale='';
		$cancelId = empty($data['cancelId'])?0:$data['cancelId'];
		$branch_id = empty($data['branch_id'])?0:$data['branch_id'];
		
		$sql="SELECT 
				sc.`id`,
				CONCAT((SELECT c.name_kh FROM ln_client AS c WHERE c.client_id = (SELECT s.client_id FROM `ln_sale` AS s WHERE s.id = sc.sale_id LIMIT 1 ) LIMIT 1),' ',(SELECT CONCAT(p.land_address,',',p.street) FROM `ln_properties` AS p WHERE p.id = (SELECT s.house_id FROM `ln_sale` AS s WHERE s.id = sc.sale_id LIMIT 1 ) LIMIT 1)) AS `name`
				
			FROM `ln_sale_cancel` AS sc
				
			WHERE sc.status=1
			AND sc.cancel_type=2 
			AND sc.return_back_aftter>0 
			AND sc.branch_id=".$branch_id;
		if(!empty($cancelId)){
			$sql.=' OR sc.id IN ('.$cancelId.')';
		}
		return $db->fetchAll($sql);
	}
}